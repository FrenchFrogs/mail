<?php namespace FrenchFrogs\Mail\Http\Controllers;

use FrenchFrogs\Models\Db;
use FrenchFrogs\Models\Business;
use Illuminate\Routing\Controller;
use FrenchFrogs\Form\Renderer\Modal;
use Request;

class QueueController extends Controller {

    /**
     * Create static Datatable
     *
     * @return \FrenchFrogs\Table\Table\Table
     */
    public static function table()
    {
        $query = Db\Mail\Mail::query()
            ->join('mail_version as v', 'mail.mail_version_id', '=', 'v.mail_version_id')
            ->join('mail_status as s', 'mail.mail_status_id', '=', 's.mail_status_id')
            ->get()->toArray();
        $table = table($query);

        $table->useDefaultPanel('Liste des mails en queue');
        $table->setConstructor(__METHOD__)->enableRemote()->enableDatatable();
        $table->addText('mail_id', '#');
        $table->addText('mail_status_label', 'Status');
        $table->addText('view_name', 'Vue');
        $table->addText('version_number', 'Version');
        $table->addText('inserted_at', 'Envoyé le');
        $action = $table->addContainer('multi', 'Actions')->setWidth(90);
        $action->addButton('view', 'Voir', action_url(static::class, 'anyView', ['mail' => '%s']), 'mail_id')->icon('fa fa-eye', true)->enableRemote();
        $action->addButton('send', 'Envoyer', action_url(static::class, 'anySend', ['mail' => '%s']), 'mail_id')->icon('fa fa-mail-forward', true);
        $action->addButton('delete', 'Supprimer', action_url(static::class, 'anyDelete', ['mail' => '%s']), 'mail_id', ['class' => 'label-danger'])->icon('fa fa-trash-o', true)->enableRemote();
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
     * @param int $id
     * @return Modal
     */
    public function anyDelete($id)
    {
        // verification paramètre
        if (!Mail::exists($id)) {
            abort('404', 'Cet email n\'existe pas');
        }

        // Récuperation du model
        $mail = Mail::getById($id);

        // Formulaire
        $form = form()->enableRemote();
        $form->setLegend('Êtes-vous sûr de vouloir supprimer le mail n°'.$mail->getKey().' ?');
        $form->addSubmit('Oui');
        $form->addSubmit('Non');

        // maj info
        $form->populate($mail->toArray());

        // enregistrement
        if (Request::has('Oui')) {

            $form->valid(Request::all());

            if ($form->isValid()) {
                try {
                    \DB::transaction(function () use ($mail, $id) {
                        $mail->destroy($id);
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
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function anySend($id)
    {
        // verification paramètre
        if (!Mail::exists($id)) {
            abort('404', 'Cet email n\'existe pas');
        }

        $mail = \DB::table('mail as m')  ->where('mail_id', '=', $id)
            ->join('mail_version as v', 'm.mail_version_id', '=', 'v.mail_version_id')
            ->first(['args', 'action']);

        $args = (array) json_decode($mail['args']);
        foreach ($args as $k => $v) {
            $args[$k] = $v;
        }

        $m = new Mail();
        try {
            $m->generateMessage($args)->sendMessage($mail['action'], $m->getMessage(), $args, $id);
            js()->success()->closeRemoteModal()->reloadDataTable();
        } catch (\Exception $e) {
            js()->error($e->getMessage());
        }
        return redirect()->back();
    }

    /**
     * @param int $id
     * @param boolean $iframe
     * @return Modal
     */
    public function anyView($id, $iframe = false)
    {
        // verification paramètre
        if (!Mail::exists($id)) {
            abort('404', 'Cet email n\'existe pas');
        }

        if ($iframe) {

            // Mode debug, affiche le contenu du mail au lieu de l'envoyer
            $content = Mail::view($id);
            return $content->render();

        } else {

            // On récupère les mails dans la queue
            $mail = \DB::table('mail as m')
                ->where('mail_id', '=', $id)
                ->join('mail_version as v', 'm.mail_version_id', '=', 'v.mail_version_id')
                ->join('mail_status as s', 'm.mail_status_id', '=', 's.mail_status_id')
                ->first(['mail_id', 'mail_status_label', 'args', 'message', 'view_name', 'action', 'version_number', 'm.created_at', 'm.inserted_at']);

            // On fait le form présentant le mail
            $form = form()->enableRemote();
            $form->setLegend('Aperçu du mail n°' . $mail['mail_id']);
            $form->addLabel('mail_id', '#');
            $form->addLabel('mail_status_label', 'Statut');
            $form->addLabel('message', 'Message');
            $form->addSeparator();
            $form->addLabel('view_name', 'View');
            $form->addLabel('action', 'Action');
//            $form->addLabel('args', 'Arguments');
            $form->addLabel('version_number', 'Version');
            $form->addSeparator();
            $form->addLabel('created_at', 'Date dépôt');
            $form->addLabel('inserted_at', 'Date envoi');
            $form->addButton('reload', 'Recharger', ['class' => 'btn-primary', 'onclick' => 'document.getElementById("mailcontent").contentWindow.location.reload(true);']);
            $form->populate($mail);

            // on ajoute l'Iframe pour l'aperçu du Mail
            $return = response()->modal($form);
            $url = $this->getRedirectUrl() . '/view/' . $id . '/1';
            $return .= "<iframe id='mailcontent' style='width:100%;min-height:100%;' src='$url'></iframe>";
            return $return;
        }
    }
}