<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Product\ProductRepositoryInterface;

class ProductTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to embed via this transformer
     *
     * @var array
     */
    protected $availableEmbeds = [
        'driver'
    ];

    public function transform(ProductRepositoryInterface $product)
    {
        return array(
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'photos' => $product->photos,
            'price' => $product->price,
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
    public function embedAuthor(ProductRepositoryInterface $product)
    {
        $driver = $product->driver;

        return $this->item($driver, new DriverTransformer);
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
                'cost' => $cost->cost,
                'inventory' => $cost->inventory,
            );
        })->all();
    }
} 