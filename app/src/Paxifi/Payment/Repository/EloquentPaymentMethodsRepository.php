<?php namespace Paxifi\Payment\Repository;

use Paxifi\Support\Repository\BaseModel;

class EloquentPaymentMethodsRepository extends BaseModel {
    protected $table = 'payment_methods';

    protected $fillable = ['name', 'description', 'enabled'];

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getMethodIdByName($name)
    {
        $method = self::where('name', '=', $name)->first();

        return $method->id;
    }

    /**
     * Payment - Payment methods one to one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo('Paxifi\Payment\Repository\EloquentPaymentRepository', 'payment_method_id');
    }
} 