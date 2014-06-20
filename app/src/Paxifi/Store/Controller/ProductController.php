<?php namespace Paxifi\Store\Controller;

use Illuminate\Support\Collection;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository;
use Paxifi\Store\Repository\Product\ProductRepository;
use Paxifi\Store\Repository\Product\Validation\CreateProductValidator;
use Paxifi\Store\Transformer\ProductTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

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
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($driver)
    {
        try {

            with(new CreateProductValidator())->validate(\Input::except('costs'));

            $product = $driver->products()->create(\Input::all());

            foreach (\Input::get('costs') as $cost) {

                $product->costs()->create($cost);

            }

            return $this->setStatusCode(201)->respondWithItem(ProductRepository::find($product->id));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        }
    }

    /**
     * Display the specified product.
     *
     * @param $driver
     * @param $product
     *
     * @internal param int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver, $product)
    {
        return $this->respondWithItem($product);
    }

    /**
     * Update the specified product in storage.
     *
     * @param $driver
     * @param $product
     *
     * @internal param int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($driver, $product)
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