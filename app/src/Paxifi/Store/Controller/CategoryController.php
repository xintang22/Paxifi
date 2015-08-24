<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Category\CategoryRepository;
use Paxifi\Store\Transformer\CategoryTransformer;
use Paxifi\Support\Controller\ApiController;

class CategoryController extends ApiController
{
    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {

            $categories = CategoryRepository::rememberForever()->where('enabled', '=', 1)->get();

            return $this->respondWithCollection($categories);
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
        return new CategoryTransformer();
    }
}