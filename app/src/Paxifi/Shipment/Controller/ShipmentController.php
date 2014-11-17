<?php namespace Paxifi\Shipment\Controller;

use Paxifi\Paypal\Paypal;
use Paxifi\Shipment\Repository\Validation\CreateShipmentValidator;
use Paxifi\Shipment\Transformer\ShipmentTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Shipment\Repository\EloquentShipmentRepository as Shipment;
use Paxifi\Support\Validation\ValidationException;

class ShipmentController extends ApiController
{
    protected $paypal;

    function __construct(Paypal $paypal) {
        parent::__construct();

        $this->paypal = $paypal;
    }

    /**
     * Create the shipment for sticker.
     *
     * @param null $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shipment($driver = null)
    {
        try {
            \DB::beginTransaction();
            
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $new_shipment = [
                "sticker_id" => $driver->sticker->id,
                "address" => \Input::get('address', $driver->address),
                "status" => "waiting",
                "paypal_payment_status" => "pending"
            ];

            with(new CreateShipmentValidator())->validate($new_shipment);

            if($capturedPayment = $this->paypal->buySticker($driver)) {
                $new_shipment['paypal_payment_id'] = $capturedPayment->parent_payment;

                if ($shipment = Shipment::create($new_shipment)) {

                    \DB::commit();

                    return $this->setStatusCode(201)->respondWithItem($shipment);
                }

                return $this->errorInternalError('Shipment for buying sticker created failed.');
            }

            return $this->errorInternalError();
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return $this->errorInternalError();
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new ShipmentTransformer();
    }

} 