<?php namespace Paxifi\PushNotifications\Repository;

use Event;
use Paxifi\Support\Repository\BaseModel;

class PushDevice extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'push_devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['driver_id', 'token', 'type',];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function (PushDevice $device) {

            if (!$device->exists) {
                Event::fire('driver.push-notification-device.registered', [$device->driver_id, $device]);
            }

        });
    }

    /**
     * The owner of the device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Driver\EloquentDriverRepository', 'driver_id');
    }

    /**
     * Check if the token is valid.
     *
     * @param $token
     * @param $type
     * @return bool
     */
    public static function checkToken($token, $type)
    {
        if ($type == 'ios') {
            return (ctype_xdigit($token) && 64 == strlen($token));
        } elseif ($type == 'android') {
            return (bool)preg_match('/^[0-9a-zA-Z\-\_]+$/i', $token);
        } elseif ($type == 'jpush') {
            //@TODO: JPush registration_id validation to be defined
            return true;
        }

        return false;
    }

    /**
     * Get device by token
     *
     * @param string $tokens
     * @return \Illuminate\Support\Collection|static|null
     */
    public static function findByTokens($tokens)
    {
        return static::whereIn('token', $tokens)->get();
    }
}