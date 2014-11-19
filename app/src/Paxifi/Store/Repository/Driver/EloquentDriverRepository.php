<?php namespace Paxifi\Store\Repository\Driver;

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Illuminate\Support\Collection;
use Paxifi\Settings\Repository\EloquentCountryRepository;
use Paxifi\Support\Contracts\RatingInterface;
use Paxifi\Support\Contracts\AddressInterface;
use Paxifi\Support\Repository\BaseModel;
use Paxifi\Tax\Repository\OfficialTaxRate;

class EloquentDriverRepository extends BaseModel implements DriverRepositoryInterface, AddressInterface, UserInterface, RemindableInterface, RatingInterface
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

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
    protected $hidden = array('password', 'paypal_refresh_token', 'remember_token');

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'seller_id', 'photo', 'password', 'email', 'address', 'currency', 'thumbs_up', 'thumbs_down', 'paypal_account', 'status', 'tax_enabled', 'tax_included_in_price', 'tax_global_amount', 'notify_sale', 'notify_inventory', 'notify_feedback', 'notify_billing', 'notify_others', 'paypal_refresh_token');

    /**
     * Driver - Product one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany('Paxifi\Store\Repository\Product\EloquentProductRepository', 'driver_id', 'id')->orderBy('weight');
    }

    /**
     * Driver - Subscription one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription()
    {
        return $this->hasOne('Paxifi\Subscription\Repository\EloquentSubscriptionRepository', 'driver_id', 'id');
    }

    // check if user has subscribed before.
    public function hasSubscribed()
    {
        return $this->hasOne('Paxifi\Subscription\Repository\EloquentSubscriptionRepository', 'driver_id', 'id')->withTrashed();
    }

    /**
     * Driver - Sticker one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sticker()
    {
        return $this->hasOne('Paxifi\Sticker\Repository\EloquentStickerRepository', 'driver_id', 'id');
    }

    /**
     * Driver - Notifications one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notifications()
    {
        return $this->hasMany('Paxifi\Notification\Repository\EloquentNotificationRepository', 'driver_id', 'id');
    }

    /**
     * Drvier - Comments one to many relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feedbacks()
    {
        return $this->hasMany('Paxifi\Feedback\Repository\EloquentFeedbackRepository', 'driver_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commissions()
    {
        return $this->hasMany('Paxifi\Commission\Repository\EloquentCommissionRepository', 'driver_id', 'id');
    }

    /**
     * Check whether user has try to pay the commission.
     *
     * If status pending, another script which will find all the failed commission, and re-charge the user.
     *
     * @param $current_period_end
     *
     * @return mixed
     */
    public function paidCommission($current_period_end) {
        return $this->commissions()
                ->where('commission_end', '=', $current_period_end)
                ->first();
    }

    /**
     * Get all comments of the drivers.
     *
     * @return mixed
     */
    public function comments()
    {
        $query = $this->feedbacks();

        return $query->where('comment', '<>', "")->orderBy('created_at', 'desc');
    }

    /**
     * Get driver notifications with conditions
     *
     * @param $from
     *
     * @param $to
     *
     * @return mixed
     */
    public function with_notifications($from, $to)
    {
        $query = $this->notifications();

        if (!$this->notify_sale)
            $query->where('sales', '=', 0);

        if (!$this->notify_inventory)
            $query->where('stock_reminder', '=', 0);

        if (!$this->notify_feedback)
            $query->where('ranking', '=', NULL);

        if (!$this->notify_others)
            $query->where('emails', '=', NULL);

        return $query
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Active driver account
     */
    public function active()
    {
        $this->status = 1;
        $this->save();
    }

    /**
     * Inactive driver account
     */
    public function inactive()
    {
        $this->status = 0;
        $this->save();
    }

    /**
     * Returns a list of driver's sales
     *
     * @param null $from
     * @param null $to
     *
     * @return array
     */
    public function sales($from, $to, $paginate = false)
    {
        $query = \DB::table('drivers')
            ->join('products', 'drivers.id', '=', 'products.driver_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->select('orders.id')
            ->where('drivers.id', '=', $this->id)
            ->where('payments.created_at', '>=', $from)
            ->where('payments.created_at', '<=', $to)
            ->where('payments.status', '=', 1)
            ->distinct();

        if ($paginate) {
            return $query->paginate($paginate['per_page']);
        } else {
            return $query->get();
        }
    }


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
     * @param $email
     *
     * @return mixed
     */
    public static function findByEmail($email)
    {
        return self::where('email', '=', $email)->get()->first();
    }

    /**
     * Get driver country's settings.
     */
    public function getSettingsByDriverCountry() {
        $country = self::getCountry();
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
            if (\Config::get('paxifi.paypal.environment') == 'production') {
                $query->where('status', '=', 1);
            }
        });

        $models = $query->get(array('id', 'name', 'seller_id', 'email', 'photo', 'address', 'currency', 'thumbs_up', 'thumbs_down', 'tax_enabled', 'tax_included_in_price', 'tax_global_amount', 'status', 'paypal_account'));

        if (!$models->isEmpty()) return $models;

        throw with(new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Retrieves official tax rates
     *
     * @return array
     */
    public function officialTaxRates()
    {
        $rates = new Collection();

        if ($this->getCountry() == 'US') {
            $rates = OfficialTaxRate::postcode($this->getPostcode())->get(array('category', 'amount', 'included_in_price'));
        } else if ($this->getCountry() == 'UK') {
            $rates = OfficialTaxRate::country('UK')->get(array('category', 'amount', 'included_in_price'));
        }

        return $rates->map(function ($rate) {
            return [
                'amount' => $rate->amount,
                'category' => ucwords($rate->category),
                'included_in_price' => (boolean)$rate->included_in_price,
            ];
        });
    }

    /**
     * Suspended account.
     */
    public function suspend() {
        $this->suspended = true;
        $this->save();
    }

    /**
     * Get the stores's tax rate.
     *
     * @return \Paxifi\Tax\Repository\TaxRateInterface
     */
    public function getTaxRates()
    {
        if (!$this->officialTaxRates()->isEmpty()) {
            return $this->officialTaxRates()->toArray();
        }

        return [[
            'amount' => $this->tax_global_amount,
            'category' => 'Global',
            'included_in_price' => (boolean)$this->tax_included_in_price,
        ]];
    }

    /**
     * Get driver commission rate.
     *
     * @return mixed
     */
    public function getCommissionRate() {
        return EloquentCountryRepository::where('iso', '=', $this->getCountry())->first()->commission_rate;
    }

    /**
     * Get driver sticker price.
     *
     * @return mixed
     */
    public function getStickerPrice() {
        return EloquentCountryRepository::where('iso', '=', $this->getCountry())->first()->sticker_price;
    }
}