<?php namespace Paxifi\Store\Repository\Category;

use Paxifi\Support\Repository\BaseModel;

class EloquentCategoryRepository extends BaseModel implements CategoryRepositoryInterface
{

    protected $table = "categories";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name', 'description', 'status');

    /**
     * Return categories with active status.
     *
     * @return mixed
     */
    public function enabled()
    {
        return $this->where('status', '=', 1)->get();
    }
}