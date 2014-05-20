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
        $categories = CategoryRepository::enabled();

        return $this->respondWithCollection($categories);
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