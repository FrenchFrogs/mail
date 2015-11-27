<?php namespace FrenchFrogs\Models\Db\Mail;

use FrenchFrogs\Laravel\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 *  Class Status
 *  @property \FrenchFrogs\Models\Db\Mail\Status
 *
 *  Magic properties
 *
 *  @property string    $mail_status_id
 *  @property string    $mail_status_label
 *
 *  Scopes
 *
 *  @method static static label(string $status)
 */
class Status extends Model {

    protected $table = 'mail_status';
    protected $primaryKey = 'mail_status_id';
    protected $fillable = ['mail_status_id', 'mail_status_label'];
    public $timestamps = false;

    public function scopeLabel(Builder $query, $status) {
        return $query->where('mail_status_id', '=', $status)->first(['mail_status_label']);
    }
}