<?php namespace Paxifi\Store\Repository;

use Illuminate\Auth\UserInterface;
use Paxifi\Support\Contracts\AddressInterface;
use Paxifi\Support\Repository\BaseModel;

class EloquentDriverRepository extends BaseModel implements DriverRepositoryInterface, AddressInterface, UserInterface
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
     * The data validation rules
     *
     * @var array
     */
    protected $rules = array(
        'name' => 'required',
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
     * Add a new driver.
     *
     * @param $data
     *
     * @return bool
     */
    public function store($data)
    {
        if ($this->validate($data)) {

            $this->name = $data['name'];
            $this->email = $data['email'];
            $this->password = $data['password'];
            $this->photo = $data['photo'];
            $this->address = $data['address'];
            $this->currency = $data['currency'];

            $this->save();

            return true;
        }

        return false;
    }

}