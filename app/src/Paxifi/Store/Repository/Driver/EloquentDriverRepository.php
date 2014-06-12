<?php namespace Paxifi\Store\Repository\Driver;

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Paxifi\Support\Contracts\RatingInterface;
use Paxifi\Support\Contracts\AddressInterface;
use Paxifi\Support\Repository\BaseModel;

class EloquentDriverRepository extends BaseModel implements DriverRepositoryInterface, AddressInterface, UserInterface, RemindableInterface, RatingInterface
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'drivers';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array('password');

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'seller_id', 'photo', 'password', 'email', 'address', 'currency', 'thumbs_up', 'thumbs_down', 'status',);

    /**
     * The data validation rules
     *
     * @var array
     */
    protected $rules = array(
        'name' => 'required',
        'seller_id' => 'required|unique:drivers|alpha_dash|max:12',
        'email' => 'required|email|unique:drivers',
        'password' => 'required',
        'photo' => 'url',
        'address' => 'required',
        'currency' => 'required',
    );

    /**
     * Serialize the address.
     *
     * @param $value
     */
    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = serialize([
            'street' => $value['street'] ? : '',
            'city' => $value['city'] ? : '',
            'country' => $value['country'] ? : '',
            'postcode' => $value['postcode'] ? : '',
        ]);
    }

    /**
     * Returns un-serialized address.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getAddressAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Retrieve the driver's country.
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->address['city'];
    }

    /**
     * Retrieve the driver's country.
     *
     * @return mixed
     */
    public function getCountry()
    {
        return $this->address['country'];
    }

    /**
     * Retrieve the driver's postcode.
     *
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->address['postcode'];
    }

    /**
     * Retrieve the driver's street.
     *
     * @return mixed
     */
    public function getStreet()
    {
        return $this->address['street'];
    }

    /**
     * Hash the password before save.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }


    /**
     * Find driver by his/her seller id.
     *
     * @param $sellerId
     *
     * @return mixed
     */
    public function findBySellerId($sellerId)
    {
        return $this->where('seller_id', '=', $sellerId)->take(1)->get();
    }

    /**
     * Increment the thumbs up.
     *
     * @return $this
     */
    public function thumbsUp()
    {
        $this->thumbs_up++;
        $this->save();

        return $this;
    }

    /**
     * Increment the thumbs down.
     *
     * @return $this
     */
    public function thumbsDown()
    {
        $this->thumbs_down++;
        $this->save();

        return $this;
    }

    /**
     * @param \Illuminate\Support\Collection $params
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function search($params)
    {
        $query = $this->newQuery();

        $params->each(function ($param) use ($query) {
            $query->where($param['column'], $param['operator'], $param['value']);
        });

        $models = $query->get(array('id', 'name', 'seller_id'));

        if (! $models->isEmpty()) return $models;

        throw with(new ModelNotFoundException)->setModel(get_class($this->model));
    }
}