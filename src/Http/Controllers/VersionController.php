<?php namespace FrenchFrogs\Mail\Http\Controllers;

use Illuminate\Routing\Controller;
use Models\Db\Mail\Version;
use FrenchFrogs\Models\Business\Mail;
use Models\Db;
use Request;

class VersionController extends Controller {
    /**
     * Create static Datatable
     *
     * @return \FrenchFrogs\Table\Table\Table
     */
    public static function table()
    {
        $query = Db\Mail\Version::all()->toArray();
        $table = table($query);
        $panel = $table->useDefaultPanel('Liste des mails en queue')->getPanel();
        $panel->addButton('add', 'Ajouter', action_url(static::class, 'anyEdit'))->setOptionAsInfo()->enableRemote();
        $panel->addButton('verify', 'Vérifer', action_url(static::class, 'anyVerify'));
        $table->setConstructor(__METHOD__)->enableRemote()->enableDatatable();
        $table->addText('mail_version_id', '#');
        $table->addText('mail_version_label', 'Libellé');
        $table->addText('controller', 'Controller');
        $table->addText('action', 'Action');
        $table->addText('view_name', 'Rendu');
        $table->addText('version_number', 'Version')->center();
        $table->addText('created_at', 'Crétaion');
        $table->addBoolean('is_active', 'Actif ?');
        $action = $table->addContainer('multi', 'Actions')->setWidth(90);
        $action->addButton('edit', 'Editer', action_url(static::class, 'anyEdit', ['mail_version_id' => '%s']), 'mail_version_id')->addAttribute('class', 'btn btn-primary')->icon('fa fa-pencil', true)->enableRemote();
        $action->addButton('acitvate', 'Activer', action_url(static::class, 'anyActivation', ['mail_version_id' => '%s']), 'mail_version_id')->icon('fa fa-check', true);
//        $action->addButton('send', 'Envoyer', action_url(static::class, 'anySend', ['mail_version_id' => '%s']), 'mail_version_id')->addAttribute('class', 'btn btn-success')->icon('fa fa-mail-forward', true);
        $action->addButton('delete', 'Supprimer', action_url(static::class, 'anyDelete', ['mail_version' => '%s']), 'mail_version_id')->addAttribute('class', 'btn btn-danger')->icon('fa fa-trash-o', true)->enableRemote();
        return $table;
    }
    /**
     * List all users
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        $table = static::table();
        return view('phoenix.mail.index', compact('table'));
    }

    /**
     * Edit a mail Version
     *
     * @param int $id
     * @return mixed
     */
    public function anyEdit($id = 0)
    {
        $form = form()->enableRemote();
        $form->addLabel('controller', 'Controller');
        $form->addLabel('action', 'Action');
        $form->addLabel('version_number', 'Version');
        $form->addLabel('view_hash', 'Hash');
        $form->addText('mail_version_label', 'Libellé');
        $form->addText('view_name', 'Rendu');
        $form->addCheckbox('is_active', 'Activer');

        if ($id !== 0) {
            if (empty(Version::where('mail_version_id', '=', $id)->first())) {
                abort('404', 'Ce mail n\'existe pas');
            }

            $version = \DB::table('mail_version')->where('mail_version_id', '=', $id)->first();
            $form->setLegend('Editer la version de mail n°'.$id);
            $form->addSubmit('Editer');
            $form->populate($version);

            if (Request::has('Editer')) {
                $form->valid(Request::all());
                if ($form->isValid()) {
                    $data = $form->getFilteredValues();
                    try {
                        \DB::transaction(function () use ($id, $data) {
                            $data['controller'] = strstr($data['mail_version_label'], '@', true);
                            $data['action'] = ltrim(strstr($data['mail_version_label'], '@'), '@');
                            Version::query()->where('mail_version_id', '=', $id)->update($data);
                        });
                        js()->success()->closeRemoteModal()->reloadDataTable();
                    } catch (\Exception $e) {
                        js()->error($e->getMessage());
                    }
                }
            }
        } else {

            $form->setLegend('Ajouter une version de mail :');
            $form->addSubmit('Ajouter');
            // enregistrement
            if (Request::has('Ajouter')) {
                $form->valid(Request::all());
                if ($form->isValid()) {
                    $data = $form->getFilteredValues();
                    try {
                        \DB::transaction(function () use ($data) {
                            $data['controller'] = strstr($data['mail_version_label'], '@', true);
                            $data['action'] = ltrim(strstr($data['mail_version_label'], '@'), '@');
                            Version::create($data);
                        });
                        js()->success()->closeRemoteModal()->reloadDataTable();
                    } catch (\Exception $e) {
                        js()->error($e->getMessage());
                    }
                }
            }
        }
        return response()->modal($form);
    }

    /**
     * Activate a mail Version
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function anyActivation($id)
    {
        if (empty(Version::where('mail_version_id', '=', $id)->first())) {
            abort('404', 'Cet email n\'existe pas');
        }
        $value = (boolean) !Version::query()->where('mail_version_id', '=', $id)->first(['is_active'])->toArray()['is_active'];
        try {
            Version::query()->where('mail_version_id', '=', $id)->update(['is_active' => $value]);
            js()->success()->closeRemoteModal()->reloadDataTable();
        } catch (\Exception $e) {
            js()->error($e->getMessage());
        }
        return redirect()->back();
    }

    /**
     * Delete a mail Version
     *
     * @param $id
     * @return mixed
     */
    public function anyDelete($id)
    {
        // verification paramètre
        if (empty(Version::where('mail_version_id', '=', $id)->first())) {
            abort('404', 'Cet email n\'existe pas');
        }
        // Formulaire
        $version = Version::query()->where('mail_version_id', '=', $id)->firstOrFail();
        $form = form()->enableRemote();
        $form->setLegend('Êtes-vous sûr de vouloir supprimer le mail n°'.$id.' ?');
        $form->addSubmit('Oui');
        $form->addSubmit('Non');
        $form->populate($version->toArray());
        // enregistrement
        if (Request::has('Oui')) {
            $form->valid(Request::all());
            if ($form->isValid()) {
                try {
                    \DB::transaction(function () use ($version, $id) {
                        $version->destroy($id);
                    });
                    js()->success()->closeRemoteModal()->reloadDataTable();
                } catch(\Exception $e) {
                    js()->error($e->getMessage());
                }
            }
        }
        elseif (Request::has('Non')) {
            js()->closeRemoteModal()->reloadDataTable();
        }
        return response()->modal($form);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function anyVerify()
    {
        try {
            Mail::checkForUpdate();
            js()->success()->closeRemoteModal()->reloadDataTable();
        } catch (\Exception $e) {
            js()->error($e->getMessage());
        }
        return redirect()->back();
    }
}