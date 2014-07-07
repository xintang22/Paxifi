<?php namespace Paxifi\Store\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Store\Repository\Product\Cost\EloquentCostRepository;
use Paxifi\Store\Repository\Product\ProductRepository;
use Paxifi\Store\Repository\Product\Validation\CreateProductValidator;
use Paxifi\Store\Repository\Product\Validation\UpdateProductValidator;
use Paxifi\Store\Transformer\ProductTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;

class ProductController extends ApiController
{
    /**
     * Display a listing of all products.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($driver = null)
    {

        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        if ($driver instanceof EloquentDriverRepository) {

            return $this->respondWithCollection($driver->products()->get());

        }

        return $this->respondWithCollection(ProductRepository::all());
    }

    /**
     * Store a newly created product
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($driver = null)
    {
        try {

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            \DB::beginTransaction();

            with(new CreateProductValidator())->validate(\Input::except('costs'));

            $product = $driver->products()->create(\Input::all());

            foreach (\Input::get('costs') as $cost) {

                $product->costs()->create($cost);

            }

            \Event::fire('paxifi.product.created', [$product]);

            \DB::commit();

            return $this->setStatusCode(201)->respondWithItem(ProductRepository::find($product->id));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        } catch (\Exception $e) {

            return $this->errorInternalError('System error.');

        }
    }

    /**
     * Display the specified product.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver, $productId)
    {
        try {

            $product = $driver->products()->findOrFail($productId);

            return $this->respondWithItem($product);

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        }

    }

    /**
     * Display the specified product.
     *
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showMine($productId)
    {
        try {

            $driver = $this->getAuthenticatedDriver();

            $product = $driver->products()->findOrFail($productId);

            return $this->respondWithItem($product);

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        }

    }

    /**
     * Update the specified product in storage.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($driver, $productId)
    {
        try {

            \DB::beginTransaction();

            $product = $driver->products()->findOrFail($productId);

            with(new UpdateProductValidator())->validate(\Input::except('costs'));

            $product->update(\Input::except('costs'));

            // @TODO: find a better way to handle updating/deleting product's costs
            if (\Input::get('costs')) {

                // delete all product's costs
                $product->costs()->get()->each(function ($cost) {
                    $cost->delete();
                });

                foreach (\Input::get('costs') as $cost) {

                    $product->costs()->create($cost);

                    // EloquentCostRepository::updateOrCreate(array('product_id' => $product->id, 'unit_cost' => $cost['unit_cost']), array(
                    //     'unit_cost' => $cost['unit_cost'],
                    //    'inventory' => $cost['inventory'],
                    // ));
                }
            }

            \Event::fire('paxifi.product.updated', [$product]);

            \DB::commit();

            return $this->respondWithItem(ProductRepository::find($productId));

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        } catch (\Exception $e) {

            return $this->errorInternalError('System error.');

        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMine($productId)
    {
        try {

            $driver = $this->getAuthenticatedDriver();

            \DB::beginTransaction();

            $product = $driver->products()->findOrFail($productId);

            with(new UpdateProductValidator())->validate(\Input::except('costs'));

            $product->update(\Input::except('costs'));

            // @TODO: find a better way to handle updating/deleting product's costs
            if (\Input::get('costs')) {

                // delete all product's costs
                $product->costs()->get()->each(function ($cost) {
                    $cost->delete();
                });

                foreach (\Input::get('costs') as $cost) {

                    $product->costs()->create($cost);

                    // EloquentCostRepository::updateOrCreate(array('product_id' => $product->id, 'unit_cost' => $cost['unit_cost']), array(
                    //     'unit_cost' => $cost['unit_cost'],
                    //    'inventory' => $cost['inventory'],
                    // ));
                }
            }

            \Event::fire('paxifi.product.updated', [$product]);

            \DB::commit();

            return $this->respondWithItem(ProductRepository::find($productId));

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        } catch (\Exception $e) {

            return $this->errorInternalError('System error.');

        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param \Paxifi\Store\Repository\Driver\EloquentDriverRepository $driver
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($driver, $productId)
    {
        try {

            $product = $driver->products()->findOrFail($productId);

            $product->delete();

            \Event::fire('paxifi.product.deleted', [$product]);

            return $this->setStatusCode(204)->respond(array());

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param int $productId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMine($productId)
    {
        try {

            $driver = $this->getAuthenticatedDriver();

            $product = $driver->products()->findOrFail($productId);

            $product->delete();

            \Event::fire('paxifi.product.deleted', [$product]);

            return $this->setStatusCode(204)->respond(array());

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (\Exception $e) {

            return $this->errorInternalError();

        }
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