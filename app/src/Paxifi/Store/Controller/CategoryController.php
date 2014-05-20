<?php namespace Paxifi\Store\Controller;

use Paxifi\Store\Repository\Category\CategoryRepositoryInterface;
use Paxifi\Store\Transformer\CategoryTransformer;
use Paxifi\Support\Controller\ApiController;

class CategoryController extends ApiController
{

    /**
     * @var \Paxifi\Store\Repository\Category\CategoryRepositoryInterface
     */
    protected $category;

    /**
     * @var \Paxifi\Store\Transformer\CategoryTransformer
     */
    protected $transformer;

    function __construct(CategoryRepositoryInterface $category, CategoryTransformer $transformer)
    {
        $this->category = $category;
        $this->transformer = $transformer;

        parent::__construct();
    }

    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = $this->category->enabled();

        return $this->respondWithCollection($categories);
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
}