<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Driver\DriverRepositoryInterface;

class DriverTransformer extends TransformerAbstract
{

    public function transform(DriverRepositoryInterface $driver)
    {
        $transformer = array(
            'id' => $driver->id,
            'email' => $driver->email,
            'photo' => $driver->photo,
            'name' => $driver->name,
            'seller_id' => $driver->seller_id,
            'address' => $driver->address,
            'currency' => $driver->currency,
            'tax_enabled' => (boolean)$driver->tax_enabled,
            'settings' => array(
                'notify_sale' => (boolean)$driver->notify_sale,
                'notify_inventory' => (boolean)$driver->notify_inventory,
                'notify_feedback' => (boolean)$driver->notify_feedback,
                'notify_billing' => (boolean)$driver->notify_billing,
                'notify_others' => (boolean)$driver->notify_others,
            ),
        );

        if ($driver->tax_enabled) {
            $transformer['taxes'] = $this->transformTaxRates($driver->getTaxRates());
        }

        return $transformer;
    }

    protected function transformTaxRates($taxRates)
    {
        if ($taxRates) {
            return $taxRates->map(function ($tax) {
                return [
                    'id' => $tax->id,
                    'category' => $tax->category,
                    'amount' => $tax->amount,
                    'included_in_price' => (boolean)$tax->included_in_price
                ];
            });
        }

        return [];
    }
}
