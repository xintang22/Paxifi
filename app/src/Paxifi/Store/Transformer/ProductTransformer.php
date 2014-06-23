<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Product\ProductRepositoryInterface;

class ProductTransformer extends TransformerAbstract
{
    /**
     * Resources that can be included if requested
     *
     * @var array
     */
    protected $availableEmbeds = array('driver');

    public function transform(ProductRepositoryInterface $product)
    {
        return array(
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'photos' => $product->photos,
            'unit_price' => $product->unit_price,
            'average_cost' => $product->average_cost,
            'inventory' => $product->inventory,
            'tax' => $product->tax,
            'costs' => $this->transformCosts($product->costs),
        );
    }

    /**
     * Embed Driver
     *
     * @param \Paxifi\Store\Repository\Product\ProductRepositoryInterface $product
     *
     * @return \League\Fractal\Resource\Item
     */
    public function embedDriver(ProductRepositoryInterface $product)
    {
        $driver = $product->driver;

        return $this->item($driver, new DriverTransformer, 'driver');
    }

    /**
     * Format the costs.
     *
     * @param \Illuminate\Support\Collection $costs
     *
     * @return mixed
     */
    protected function transformCosts($costs)
    {
        return $costs->map(function ($cost) {
            return array(
                'id' => $cost->id,
                'cost' => $cost->unit_cost,
                'inventory' => $cost->inventory,
            );
        })->all();
    }
} 