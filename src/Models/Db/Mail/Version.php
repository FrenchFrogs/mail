<?php namespace FrenchFrogs\Models\Db\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 *  Class Version
 *  @property \FrenchFrogs\Models\Db\Mail\Version
 *
 *  Magic properties
 *
 *  @property string    $view_name
 *  @property string    $view_hash
 *  @property string    $action
 *  @property boolean   $is_active
 *
 *  Scopes
 *
 *  @method static static version(string $version)
 *  @method static static active()
 *  @method static static inactive()
 */
class Version extends Model {

    protected $table = 'mail_version';
    protected $primaryKey = 'mail_version_id';
    protected $fillable = [
        'mail_version_id',
        'name',
        'version_number',
        'controller',
        'action',
        'view_hash',
        'view_name',
        'is_active'
    ];
    public $incrementing = false;

    public static function whereActionIs($action) {
        return Version::query()->where('action', 'LIKE', $action)->first();
    }

    public static function firstVersion($action) {
        return Version::query()->where('action', 'LIKE', $action)->where('is_active', '=', 1)->orderByRaw('RAND()')->first();
    }

    public function scopeVersion(Builder $query, $version) {
        return $query->where('mail_version_id', '=', $version)->first();
    }

    public function scopeActive(Builder $query) {
        return $query->where('is_active', '=', 1);
    }

    public function scopeInactive(Builder $query) {
        return $query->where('is_active', '=', 0);
    }
}