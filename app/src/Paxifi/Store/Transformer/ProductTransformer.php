<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Product\ProductRepository;

class ProductTransformer extends TransformerAbstract
{
    public function transform(ProductRepository $product)
    {
        return array(
            'id' => $product->id,
            'name' => $product->name,
            'photos' => $product->photos,
            'price' => $product->price,
            'average_cost' => $product->average_cost,
            'quantity' => $product->quantity,
            'tax' => $product->tax
        );
    }
} 