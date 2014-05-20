<?php namespace Paxifi\Store\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Store\Repository\Category\CategoryRepositoryInterface;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(CategoryRepositoryInterface $category)
    {
        return array(
            'name' => $category->name,
        );
    }
} 