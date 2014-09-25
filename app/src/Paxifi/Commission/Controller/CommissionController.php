<?php
/**
 * Created by PhpStorm.
 * User: sonnyshen
 * Date: 9/25/14
 * Time: 5:39 PM
 */

namespace Paxifi\Commission\Controller;


use Carbon\Carbon;
use Paxifi\Commission\Repository\EloquentCommissionRepository;
use Paxifi\Commission\Transformer\CommissionTransformer;
use Paxifi\Paypal\Helper\PaypalHelper;
use Paxifi\Sales\Repository\SaleCollection;
use Paxifi\Support\Controller\ApiController;

class CommissionController extends ApiController
{

    // Create commission paypal payment.
    public function commission($driver = null, $ipn = null)
    {
        if (is_null($driver)) {
            $driver = $this->getAuthenticatedDriver();
        }

        if (is_null($ipn)) {
            $ipn = \Input::all();
        }

        try {

            if ($driver->id == $ipn['custom']) {
                // Get the pre-month commissions.
                $sales = new SaleCollection($driver->sales(Carbon::now()->subMonth(), Carbon::now()));

                if ($sales) {
                    $paypalHelper = new PaypalHelper($driver);

                    if ($res = $paypalHelper->createPaypalFuturePayment($sales->toArray()['totals'])) {
                        print_r($res);

                        // Todo:: fire notification event the commission fee get paid.
                        $commission_payment = [
                            'driver_id' => $driver->id,
                            'total_commission' => $sales->toArray()['totals']['commission'],
                            'status' => $res['state'],
                            'commission_ipn' => $res,
                            'commission_payment_id' => $res['id']
                        ];

                        if ($commission = EloquentCommissionRepository::create($commission_payment)) {
                            return $this->setStatusCode(201)->respondWithItem($commission);
                        }
                    }
                }
            }

        } catch (\Exception $e) {

            return $this->setStatusCode(400)->respondWithError($e->getMessage());
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new CommissionTransformer();
    }
}