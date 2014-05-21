<?php namespace Paxifi\Store\Controller;

use Paxifi\Support\Controller\ApiController;

class ProductController extends ApiController
{

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