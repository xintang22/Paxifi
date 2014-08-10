<?php namespace Paxifi\Problem\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentProblemTypesRepository extends BaseModel {
    protected $table = "problem_types";

    protected $fillable = ['name', 'description', 'enabled'];

    /**
     * Problem - Problem Types one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function problem()
    {
        return $this->hasMany('Paxifi\Store\Repository\Product\Problem\EloquentProblemRepository', 'problem_type_id', 'id');
    }
} 