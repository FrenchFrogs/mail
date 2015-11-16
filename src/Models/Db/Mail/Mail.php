<?php namespace FrenchFrogs\Models\Db\Mail;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Mail
 * @package Models\Db\Mail\Mail
 *
 * Magic properties
 *
 * @property string      $mail_id
 * @property string      $mail_status_id
 * @property string      $mail_version_id
 * @property string      $message
 * @property string      $args
 * @property string      $opened_at
 * @property string      $inserted_at
 * @property string      $created_at
 * @property string      $updated_at
 *
 * Relations
 *
 * @property \FrenchFrogs\Models\Db\Mail\Status   $mail_status
 * @property \FrenchFrogs\Models\Db\Mail\Version  $mail_version
 *
 */
class Mail extends Model {

    protected $table = 'mail';
    protected $primaryKey = 'mail_id';
    protected $fillable = ['mail_status_id', 'mail_version_id', 'message', 'args', 'opened_at', 'inserted_at'];

    public function status() {
        return $this->hasOne(Status::class, 'mail_status_id', 'mail_status_id');
    }

    public function version() {
        return $this->hasOne(Version::class, 'mail_version_id', 'mail_version_id');
    }
}