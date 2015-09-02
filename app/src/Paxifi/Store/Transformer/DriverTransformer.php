<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;
use Paxifi\Subscription\Transformer\SubscriptionTransformer;
use StripeTransformer;

class DriverTransformer extends TransformerAbstract
{

    public function transform(DriverRepositoryInterface $driver)
    {
        $transformer = array(
            'id' => $driver->id,
            'email' => $driver->email,
            'photo' => !empty($driver->photo) ? $driver->photo : cloudfront_asset('photos/driver-logo.png'),
            'name' => $driver->name,
            'seller_id' => !empty($driver->seller_id) ? $driver->seller_id : "",
            'address' => $driver->address,
            'currency' => $driver->currency,
            'tax' => $this->transformTaxConfiguration($driver),
            // 'subscription' => $this->transformSubscription($driver),
            'settings' => array(
                'notify_sale' => (boolean)$driver->notify_sale,
                'notify_inventory' => (boolean)$driver->notify_inventory,
                'notify_feedback' => (boolean)$driver->notify_feedback,
                'notify_billing' => (boolean)$driver->notify_billing,
                'notify_others' => (boolean)$driver->notify_others,
            ),
            'thumbs_up' => $driver->thumbs_up,
            'thumbs_down' => $driver->thumbs_down,
            'suspended' => $driver->suspended,
            'status' => $driver->status,
            'created_at' => $driver->created_at,
            'comments_count' => $driver->comments()->get()->count(),
            'available_payment_methods' => $this->transformPaymentMethods($driver),
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

    /**
     * Transform stripe content for driver.
     *
     * @param $driver
     * @return string
     */
    protected function transformStripe($driver) {
        return $driver->stripe_connected ? $driver->stripe()->first() : false;
    }

    /**
     * Transform payment methods.
     *
     * @param $driver
     * @return array
     */
    protected function transformPaymentMethods($driver) {
        $methods = $driver->available_payment_methods()->get();

        $details = [
            "cash" => true
        ];

        if ($methods) {
            foreach($methods as $key => $method) {
                if ($method->name != 'cash') {
                    $details[$method->name] = $driver->{$method->name}->first()->toArray();
                }
            }
        }

        return $details;
    }
}
