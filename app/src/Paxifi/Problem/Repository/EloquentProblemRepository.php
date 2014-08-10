<?php namespace Paxifi\Problem\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentProblemRepository extends BaseModel {
    protected $table = "problems";

    protected $fillable = ['problem_type_id', 'payment_id', 'product_id', 'reporter_email'];

    /**
     * Product - Product Types one to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->belongsTo('Paxifi\Problem\Repository\EloquentProblemTypesRepository', 'problem_type_id');
    }

    /**
     * Problem - Product one on one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('Paxifi\Store\Repository\Product\EloquentProductRepository', 'product_id');
    }
} 