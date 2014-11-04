<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;
use Paxifi\Subscription\Transformer\SubscriptionTransformer;

class DriverTransformer extends TransformerAbstract
{

    public function transform(DriverRepositoryInterface $driver)
    {
        $transformer = array(
            'id' => $driver->id,
            'email' => $driver->email,
            'photo' => !empty($driver->photo) ? $driver->photo : "",
            'name' => $driver->name,
            'seller_id' => !empty($driver->seller_id) ? $driver->seller_id : "",
            'address' => $driver->address,
            'currency' => $driver->currency,
            'tax' => $this->transformTaxConfiguration($driver),
            'subscription' => $this->transformSubscription($driver),
            'settings' => array(
                'notify_sale' => (boolean)$driver->notify_sale,
                'notify_inventory' => (boolean)$driver->notify_inventory,
                'notify_feedback' => (boolean)$driver->notify_feedback,
                'notify_billing' => (boolean)$driver->notify_billing,
                'notify_others' => (boolean)$driver->notify_others,
            ),
            'paypal_account' => !empty($driver->paypal_account) ? $driver->paypal_account : "",
            'paypal_authorized' => (boolean)$driver->paypal_refresh_token ? true: false,
            'thumbs_up' => $driver->thumbs_up,
            'thumbs_down' => $driver->thumbs_down,
            'paypal_account' => $driver->paypal_account,
            'status' => $driver->status,
            'created_at' => $driver->created_at,
            'comments_count' => $driver->comments()->get()->count()
        );

        return $transformer;
    }

    /**
     * @param $driver
     *
     * @return array
     */
    protected function transformTaxConfiguration($driver)
    {
        $tax = [
            'enabled' => (boolean)$driver->tax_enabled,
        ];

        if ($driver->tax_enabled) {
            $tax['included_in_price'] = (boolean)$driver->tax_included_in_price;
            $tax['amount'] = $driver->tax_global_amount;
            $tax['rates'] = $driver->getTaxRates();
        }

        return $tax;
    }

    /**
     * @param $driver
     *
     * @return mixed
     */
    protected function transformSubscription($driver)
    {
        return $driver->subscription ? with(new SubscriptionTransformer())->transform($driver->subscription) : "";
    }
}
