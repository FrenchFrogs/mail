<?php namespace FrenchFrogs\Models\Business;

use Carbon\Carbon;
use FrenchFrogs\Business\Business;
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
    const STATUS_QUEUED = 'queued';

    const CONTROLLER_DIR = 'App\Http\Controllers\\';

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
     * @param string $attachment
     * @return $this
     */
    public function setAttach($attachment)
    {
        $this->attach = $attachment;
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
     * @return boolean
     */
    public function hasPriority()
    {
        return !empty($this->priority);
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
        $this->msg = new Message(new \Swift_Message());
        if ($this->hasFrom()) {
            $this->msg->from($this->from[0], $this->from[1]);
        }
        if ($this->hasSender()) {
            $this->msg->sender($this->sender[0], $this->sender[1]);
        }
        if ($this->hasTo()) {
            $this->msg->to($this->to[0], $this->to[1]);
        }
        if ($this->hasCc()) {
            $this->msg->cc($this->cc[0], $this->cc[1]);
        }
        if ($this->hasBcc()) {
            $this->msg->bcc($this->bcc[0], $this->bcc[1]);
        }
        if ($this->hasReplyTo()) {
            $this->msg->replyTo($this->replyTo[0], $this->replyTo[1]);
        }
        if ($this->hasSubject()) {
            $this->msg->subject($this->subject);
        }
        if ($this->hasAttach()) {
            $this->msg->attach($this->attach[0], $this->attach[1]);
        }
        if ($this->hasPriority()) {
            $this->msg->priority($this->priority);
        }
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
        $this->generateMessage();
        $this->send($action, $args);
        return $this;
    }

    /**
     * Send a mail with a given SwiftMessage
     *
     * @param string $action
     * @param Message $msg
     * @param array $args
     * @param int $id
     *
     * @return $this
     * @throws \Exception
     */
    public function sendMessage($action, $msg, $args = [], $id = null)
    {
        $version = Version::whereActionIs($action);
        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }
        foreach (get_object_vars($this) as $k => $v) {
            if (!empty($v)) {
                $args[$k] = $v;
            }
        }
        unset($args['msg']);
        \DB::beginTransaction(function() use ($version, $id, $args) {
            if (!is_null($id)) {
                \FrenchFrogs\Models\Db\Mail\Mail::query()->where('mail_id', '=', \Uuid::import($id)->bytes)->update([
                    'mail_status_id' => Mail::STATUS_SENT,
                    'mail_version_id' => $version->getKey(),
                    'args' => json_encode($args),
                    'inserted_at' => Carbon::now()
                ]);
            } else {
                \FrenchFrogs\Models\Db\Mail\Mail::create([
                    'mail_id' => static::generateUuid(),
                    'mail_status_id' => Mail::STATUS_SENT,
                    'mail_version_id' => $version->getKey(),
                    'args' => json_encode($args),
                    'inserted_at' => Carbon::now()
                ]);
            }
        });

        response()->mail($version->view_name, $args, $msg, $this->attach);
        return $this;
    }

    /**
     * Send a mail in queue
     *
     * @param string $action
     * @param array $args
     * @return $this
     * @throws \Exception
     */
    public function send($action, $args)
    {
        $version = Version::whereActionIs($action);
        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }
        if (!empty($this->msg)) {
            foreach (get_object_vars($this) as $k => $v) {
                if (!empty($v)) {
                    $args[$k] = $v;
                }
            }
            unset($args['msg']);
            \DB::beginTransaction(function() use ($version, $args) {
                \FrenchFrogs\Models\Db\Mail\Mail::create([
                    'mail_id' => static::generateUuid(),
                    'mail_status_id' => Mail::STATUS_SENT,
                    'mail_version_id' => $version->getKey(),
                    'args' => json_encode($args),
                    'inserted_at' => Carbon::now()
                ]);
            });
            response()->mail($version->view_name, $args, $this->msg);
        }
        return $this;
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
            ->where('controller', 'LIKE', $controller)
            ->where('action', 'LIKE', $action)->first();

        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }

        \DB::beginTransaction(function() use ($version, $args) {
            \FrenchFrogs\Models\Db\Mail\Mail::create([
                'mail_id' => static::generateUuid(),
                'mail_status_id' => Mail::STATUS_SENT,
                'mail_version_id' => $version->getKey(),
                'args' => json_encode($args),
                'inserted_at' => Carbon::now()
            ]);
        });

        $class = new $controller();
        $class->$action(...$args);
    }

    /**
     * Add a message in queue
     *
     * @param string $action
     * @param array $args
     * @return $this
     * @throws \Exception
     */
    public function queue($action, $args)
    {
        $version = Version::whereActionIs($action);
        if (empty($version)) {
            throw new \Exception('Impossible de trouver une version active pour le mail ' . $action);
        }
        foreach (get_object_vars($this) as $k => $v) {
            if (!empty($v)) {
                $args[$k] = $v;
            }
        }
        unset($args['msg']);

        \DB::beginTransaction(function() use ($version, $args) {
            \FrenchFrogs\Models\Db\Mail\Mail::create([
                'mail_id' => static::generateUuid(),
                'mail_status_id' => Mail::STATUS_QUEUED,
                'mail_version_id' => $version->getKey(),
                'args' => json_encode($args),
                'inserted_at' => Carbon::now()
            ]);
        });

        return $this;
    }

    /**
     * Get Mail by id
     *
     * @param int $id
     * @return \FrenchFrogs\Models\Db\Mail\Mail
     */
    public static function getById($id)
    {
        return \FrenchFrogs\Models\Db\Mail\Mail::where('mail_id', '=', $id)->firstOrFail();
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
            ->where('mail_id', '=', $id)
            ->join('mail_version as v', 'm.mail_version_id', '=', 'v.mail_version_id')
            ->first(['controller', 'action', 'args', 'view_name']);

        // instanciation du controller pour récupérer la vue du mail
        $controller = new \ReflectionClass(self::CONTROLLER_DIR.$mail['controller']);
        $method = $controller->getMethod($mail['action']);
        $object = $controller->getConstant('CLASS_NAME');
        // passage de l'ID du mail
        $args[] = $id;
        // passage des arguments de l'action et/ou de la vue
        $args[] = (array) json_decode($mail['args']);
        // activation du mode debug pour retrouner le visuel du mail et pas l'envoyer
        $args[] = true;
        return $method->invoke((new $object()), ...$args);
    }

    /**
     *  Verify view updates for mail Versions
     */
    public static function checkForUpdate()
    {
        // recuperation du path des views de mail
        // @TODO voir pour une constante
        $root = base_path() . '/resources/views/phoenix/mail/';
        // on récupère les versions active
        $rowset = \DB::table('mail_version')->where('is_active', '=', 1)->get();
        $mail = [];

        foreach ($rowset as $row) {
            $mail[sprintf('%s%s.blade.php', $root, $row['action'])] = $row;
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
                        unset($data['mail_version_id']);
                        $data['version_number'] += 1;
                        Version::create($data);
                        $version->update(['is_active' => 0]);

                    } else {
                        // sinon on l'ajoute simplement
                        $version->update($data);
                    }
                }
            } else {
                throw new \Exception($file . ' no found.');
            }
        }
    }
}