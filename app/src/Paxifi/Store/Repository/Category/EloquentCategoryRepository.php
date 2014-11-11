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
    protected $fillable = array('name', 'description', 'enabled');

    /**
     * Return categories with active status.
     *
     * @return mixed
     */
    public function enabled()
    {
        return $this->where('enabled', '=', 1)->get();
    }

    public static function getCategoryNameById($id) {
        return self::where('id', '=', $id)->first()->name;
    }
}