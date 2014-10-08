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
    public function commission($driver, $ipn)
    {
        try {
            \DB::beginTransaction();

            if ($driver->id == $ipn['custom']) {
                // Get the pre-month commissions.
                $sales = new SaleCollection($driver->sales(Carbon::now()->subMonth(), Carbon::now()));

                if ($sales) {
                    $paypalHelper = new PaypalHelper($driver);

                    // get authorized commission payment
                    if ($authorizedCommissionPayment = $paypalHelper->createPaypalFuturePayment($sales->toArray()['totals'])) {

                        // capture authorized commission payment
                        if ($capture = $paypalHelper->capturePaypalPayment($authorizedCommissionPayment)) {

                            if ($authorizedCommissionPayment['id'] == $capture['parent_payment']) {
                                $commission_payment = [
                                    'driver_id' => $driver->id,
                                    'commissions' => $authorizedCommissionPayment['transactions'][0]['amount']['total'],
                                    'currency' => $authorizedCommissionPayment['transactions'][0]['amount']['currency'],
                                    'status' => $authorizedCommissionPayment['state'],
                                    'commission_ipn' => $authorizedCommissionPayment,
                                    'commission_payment_id' => $authorizedCommissionPayment['id'],
                                    'capture_id' => $capture['id'],
                                    'capture_created_at' => $capture['create_time'],
                                    'capture_updated_at' => $capture['update_time'],
                                    'capture_status' => $capture['state'],
                                    'capture_ipn' => $capture,
                                ];

                                if ($commission = EloquentCommissionRepository::create($commission_payment)) {

                                    \DB::commit();

                                    // Todo:: fire notification event the commission paid.
                                    \Event::fire('paxifi.notifications.billing', [$commission]);

                                    return $this->setStatusCode(201)->respondWithItem($commission);
                                }
                            }

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