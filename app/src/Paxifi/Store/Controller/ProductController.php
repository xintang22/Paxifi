<?php namespace Paxifi\Store\Controller;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Paxifi\Problem\Repository\EloquentProblemTypesRepository as ProblemType;
use Paxifi\Problem\Repository\ProblemRepository as Problem;
use Paxifi\Store\Repository\Product\EloquentProductRepository;
use Paxifi\Store\Repository\Product\ProductRepository;
use Paxifi\Store\Repository\Product\Validation\CreateProblemValidator;
use Paxifi\Store\Repository\Product\Validation\CreateProductValidator;
use Paxifi\Store\Repository\Product\Validation\UpdateProductValidator;
use Paxifi\Store\Transformer\ProductTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Cache, Paginator;

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
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            if (\Input::has('page')) {
                return $this->respondWithCollection($driver->products()->cacheTags(array('drivers', 'products'))->remember(10)->paginate(\Input::get('per_page', 6)));
            } else {
                return $this->respondWithCollection($driver->products()->cacheTags(array('drivers', 'products'))->remember(10)->get());
            }
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Get the total product counts of the driver.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsCount($driver = null) {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

           return $this->respond(['products_count' => $driver->products()->count()]);
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
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

            return $this->errorWrongArgs($e->getErrors());

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

                }
            }

            \Event::fire('paxifi.product.updated', [$product]);

            \DB::commit();

            return $this->respondWithItem(ProductRepository::find($productId));

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

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

                }
            }

            \Event::fire('paxifi.product.updated', [$product]);

            \DB::commit();

            return $this->respondWithItem(ProductRepository::find($productId));

        } catch (ModelNotFoundException $e) {

            return $this->errorNotFound($this->translator->trans('responses.product.not_found', array('id' => $productId)));

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

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
     * Get all problems types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function problems()
    {
        if ($problem_types = ProblemType::all())
        {
            return $this->setStatusCode(200)->respond($problem_types);
        }

        return $this->errorInternalError();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function problem()
    {
        try {
            \DB::beginTransaction();

            $inputs = \Input::all();

            with(new CreateProblemValidator())->validate($inputs);

            // Check whether the product problem has already reported.
            if ($problem = Problem::reported($inputs)) {
                return $this->setStatusCode(200)->respond([
                    "success" => true,
                    "problem_id" => $problem->id,
                    "created_at" => $problem->created_at,
                    "reporter_email" => $problem->reporter_email
                ]);
            }

            if($problem = Problem::create($inputs))
            {
                \DB::commit();

                $emailOptions = array(
                    'template' => 'emails.problem.report',
                    'context' => $this->translator->trans('email.problem'),
                    'to' => $problem->product->driver->email,
                    'data' => [
                        "driver_name" => $problem->product->driver->name,
                        "reporter_email" => $problem->reporter_email,
                        "product" => $problem->product->toArray(),
                        "problem_type" => $problem->type->toArray(),
                    ]
                );

                // Fire email invoice pdf event.
                \Event::fire('paxifi.email', array($emailOptions));

                return $this->setStatusCode(200)->respond([
                    "success" => true,
                    "problem_id" => $problem->id,
                    "created_at" => $problem->created_at,
                    "reporter_email" => $problem->reporter_email
                ]);
            }

            return $this->errorInternalError('Sorry, the system is not available now, please try it later');

        } catch(ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
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
     * Sort the products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setWeight()
    {
        try {
            $driver = $this->getAuthenticatedDriver();

            $weights = \Input::get('weights');

            foreach($weights as $index => $weights)
            {
                if ($product = EloquentProductRepository::find($weights['id']))
                {
                    if ($product->driver_id == $driver->id) {
                        $product->weight = $weights['weight'];
                        $product->save();
                    }
                }
            }

            return $this->setStatusCode(200)->respondWithCollection($driver->products);

        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
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