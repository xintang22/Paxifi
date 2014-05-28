<?php namespace Paxifi\Store\Controller;

use Illuminate\Support\Collection;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository;
use Paxifi\Store\Repository\Product\ProductRepository;
use Paxifi\Store\Transformer\ProductTransformer;
use Paxifi\Support\Controller\ApiController;

class ProductController extends ApiController
{
    /**
     * Display a listing of all products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithCollection(ProductRepository::all());
    }

    /**
     * Store a newly created product
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        if ($product = ProductRepository::create(\Input::all())) {

            $costs = Collection::make(\Input::get('costs'));

            foreach ($costs as $cost) {

                $product->costs()->save(new EloquentCostRepository($cost));
            }

            return $this->setStatusCode(201)->respondWithItem(ProductRepository::find($product->id));
        }

        return $this->errorWrongArgs(ProductRepository::getValidationErrors());
    }

    /**
     * Display the specified product.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified product in storage.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new ProductTransformer();
    }
}