<?php namespace FrenchFrogs\Models\Business;

use App\Http\Controllers\Engie\ProjectController;
use Carbon\Carbon;
use FrenchFrogs\Business\Business;
use \FrenchFrogs\Models\Db\Mail\Mail as ModelMail;
use FrenchFrogs\Models\Db\Tracking\Tracking as ModelTracking;
use Models\Db;
use Illuminate\Mail\Message;
use FrenchFrogs\Models\Db\Mail\Version;

/**
 *  Class Mail
 *  @property Mail
 *
 *  Attributes
 *
 *  @property array     $attach
 *  @property array     $sender
 *  @property string    $subject
 *  @property array     $from
 *  @property array     $to
 *  @property array     $cc
 *  @property array     $bcc
 *  @property array     $replyTo
 *  @property int       $priority
 *  @property Message   $msg
 */
class Mail extends Business
{
    const STATUS_BLOCKED = 'blocked';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_ERROR = 'error';
    const STATUS_FILED = 'filed';
    const STATUS_OPENED = 'opened';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';

    const CONTROLLER_DIR = 'App\Http\Controllers\\';

    static protected $modelClass = ModelMail::class;

    protected $attach = [];
    protected $sender = [];
    protected $subject = '';
    protected $from = [];
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $replyTo = [];
    protected $priority;
    protected $msg;

    /**
     * Manage Db/Version via Business
     *
     * @param $id
     * @return Business
     */
    static public function getVersion($id)
    {
        static::$modelClass = Version::class;
        return parent::get($id);
    }

    /**
     * @param string $attachment
     * @return $this
     */
    public function setAttach($attachment)
    {
        $this->attach = $attachment;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttach()
    {
        return $this->attach;
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function addAttach($data, $name, array $options = [])
    {
        $attach = $this->getAttach();
        $attach[] = [$data,$name,$options];
        $this->setAttach($attach);
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasAttach()
    {
        return !empty($this->attach);
    }

    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function setSender($address, $name = '')
    {
        $this->sender = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return boolean
     */
    public function hasSender()
    {
        return !empty($this->sender);
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return boolean
     */
    public function hasSubject()
    {
        return !empty($this->subject);
    }

    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function setFrom($address, $name = '')
    {
        $this->from = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return boolean
     */
    public function hasFrom()
    {
        return !empty($this->from);
    }

    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function setTo($address, $name = '')
    {
        $this->to = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return boolean
     */
    public function hasTo()
    {
        return !empty($this->to);
    }

    /***
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function setCc($address, $name = '')
    {
        $this->cc = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @return boolean
     */
    public function hasCc()
    {
        return !empty($this->cc);
    }

    /**
     * @param $address
     * @param string $name
     * @return $this
     */
    public function setBcc($address, $name = '')
    {
        $this->bcc = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @return boolean
     */
    public function hasBcc()
    {
        return !empty($this->bcc);
    }

    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function setReplyTo($address, $name = '')
    {
        $this->replyTo = [$address, $name];
        return $this;
    }

    /**
     * @return array
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @return boolean
     */
    public function hasReplyTo()
    {
        return !empty($this->replyTo);
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return boolean
     */
    public function hasPriority()
    {
        return !empty($this->priority);
    }

    /**
     * @param Message $msg
     * @return $this
     */
    public function setMessage(Message $msg)
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * @return \Illuminate\Mail\Message
     */
    public function getMessage()
    {
        return ($this->msg instanceof Message) ? $this->msg : $this->generateMessage()->getMessage();
    }

    /**
     * Generates a \Illuminate\Mail\Message template ready to send
     *
     * @param array $args = []
     * @return $this
     */
    public function generateMessage($args = [])
    {
        if (!empty($args)) {
            foreach ($args as $att => $v) {
                if (array_key_exists($att, get_object_vars($this))) {
                    $this->$att = $v;
                }
            }
        }
        $msg = new Message(new \Swift_Message());
        if ($this->hasFrom()) {
            $from = $this->getFrom();
            $msg->from($from[0], $from[1]);
        }
        if ($this->hasSender()) {
            $sender = $this->getSender();
            $msg->sender($sender[0], $sender[1]);
        }
        if ($this->hasTo()) {
            $to = $this->getTo();
            $msg->to($to[0], $to[1]);
        }
        if ($this->hasCc()) {
            $cc = $this->getCc();
            $msg->cc($cc[0], $cc[1]);
        }
        if ($this->hasBcc()) {
            $bcc = $this->getBcc();
            $msg->bcc($bcc[0], $bcc[1]);
        }
        if ($this->hasReplyTo()) {
            $reply_to = $this->getReplyTo();
            $msg->replyTo($reply_to[0], $reply_to[1]);
        }
        if ($this->hasSubject()) {
            $msg->subject($this->getSubject());
        }
        if ($this->hasAttach()) {
            foreach($this->getAttach() as $attach){
                $msg->attachData($attach[0], $attach[1], $attach[2]);
            }
        }
        if ($this->hasPriority()) {
            $msg->priority($this->getPriority());
        }
        $this->setMessage($msg);
        return $this;
    }

    /**
     *  Generates the \Illuminate\Mail\Message and sends it
     *
     * @param string $action
     * @param array $args
     * @return $this
     */
    public function generateAndSend($action, $args)
    {

        $this->send($action, $args);
        return $this;
    }

    public function generate($data, $callback)
    {

    }

    /**
     * Send a mail with a given SwiftMessage
     *
     * @param string $version
     * @param Message $msg
     * @param array $args
     * @param int $id
     *
     * @return $this
     * @throws \Exception
     */
    public function send()
    {

        //On récupère le model
        $model = $this->getModel();

        //On recuper la version lié au mail
        $version = Version::query()
                        ->where('mail_version_id', '=', uuid('bytes', $model->mail_version_id))
                        ->first();
        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version pour le mail ');
        }

        try {

            //On genre le swiftMessage
            $controller =  $version->controller;
            $action =  $version->action;
            $class = new $controller();
            $return = $class->$action(...json_decode($model->args));

            //Puis on envoie le mail
            response()->mail($version->view_name, $return[0], $return[1]);

            //Si tout se passe bien en passe le mail en envoyé
            if(empty($model->sent_at)){
                $model->mail_status_id = self::STATUS_SENT;
                $model->sent_at = Carbon::now();
            }
        }catch (\Exception $e){
            //en cas d'erreur on log l'erreur
            $model->mail_status_id = self::STATUS_ERROR;
            $model->message = $e->getMessage();
        }

        //On sauvegarde les infos
        $model->save();

        return $this;
    }

    static function sendFromId($id)
    {
        //On recupere le mail
        $class = self::class;
        $mail = new $class($id);

        return $mail->send();
    }

    /**
     * @param $controller
     * @param $action
     * @param ...$args
     * @throws \Exception
     */
    static public function async($controller, $action, ...$args)
    {
        $version = Version::query()
                        ->where('controller', '=', $controller)
                        ->where('action', '=', $action)
                        ->where('is_active',1)
                        ->first();

        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }

        $mail = ModelMail::create([
            'mail_status_id' => Mail::STATUS_FILED,
            'mail_version_id' => $version->getKey(),
            'args' => json_encode($args)
        ]);

        return $mail->mail_id;
    }

    /**
     * @param $controller
     * @param $action
     * @param ...$args
     * @throws \Exception
     */
    static public function sync($controller, $action, ...$args)
    {
        $version = Version::query()
            ->where('controller', '=', $controller)
            ->where('action', '=', $action)
            ->where('is_active',1)
            ->first();

        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }

        $mail = ModelMail::create([
            'mail_status_id' => self::STATUS_FILED,
            'mail_version_id' => $version->getKey(),
            'args' => json_encode($args)
        ]);

        //On envoie le mail directement
        $class = self::class;
        $business = new $class($mail->mail_id);
        $business->send();

        return $mail->mail_id;
    }

    /**
     * Get Mail by id
     *
     * @param int $id
     * @return \FrenchFrogs\Models\Db\Mail\Mail
     */
    public static function getById($id)
    {
        return ModelMail::where('mail_id', '=', uuid('bytes', $id))->firstOrFail();
    }

    /**
     * Return the overview of a mail
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public static function view($id)
    {
        $mail = \DB::table('mail as m')
            ->where('mail_id', '=', uuid('bytes', $id))
            ->join('mail_version as v', 'm.mail_version_id', '=', 'v.mail_version_id')
            ->first(['controller', 'action', 'args', 'view_name']);

        // instanciation du controller pour récupérer la vue du mail
        $controller = new \ReflectionClass($mail['controller']);
        $method = $controller->getMethod($mail['action']);
        $args = (array) json_decode($mail['args']);

        $args[] = true; // RENDER

        return $method->invoke((new $mail['controller']()), ...$args);
    }

    /**
     *  Verify view updates for mail Versions
     */
    public static function checkForUpdate()
    {
        // recuperation du path des views de mail
        // @TODO voir pour une constante
        $root = base_path() . '/resources/views/mail/';
        // on récupère les versions active
        $rowset = \DB::table('mail_version')->where('is_active', '=', 1)->get();
        $mail = [];
        foreach ($rowset as $row) {
            $mail[sprintf('%s%s.blade.php', $root, substr(strrchr($row['view_name'], '.'), 1))] = $row;
        }
        // on verifie le hash de chaque fichier pour voir s'il a changé
        foreach ($mail as $file => $data) {

            // on verifie si le fichier existe
            if (file_exists($file)) {
                $hash = md5_file($file);
                $version = \DB::table('mail_version')->where('mail_version_id', '=', $data['mail_version_id']);
                unset($data['created_at'], $data['updated_at']);

                // si le hash est different
                if ($data['view_hash'] != $hash) {

                    $data['view_hash'] = $hash;
                    if (!empty($version->first()['view_hash'])) {
                        // si un hash est déjà en base on le remplace en mettant à jour le numéro de version
                        $data['mail_version_id'] = uuid();
                        $data['version_number'] += 1;
                        Version::create($data);
                        // on met à jour les mail utilisant la version
                        \DB::table('mail')->where('mail_version_id', '=', $version->first()['mail_version_id'])
                            ->update(['mail_version_id' => $data['mail_version_id']]);

                        // on désactive l'ancienne version
                        $version->update(['is_active' => 0]);

                    } else {
                        // sinon on l'ajoute simplement
                        \DB::beginTransaction(function() use ($data, $version) {
                            $version->update($data);
                        });
                    }
                }
            } else {
                throw new \Exception($file . ' no found.');
            }
        }
    }
}