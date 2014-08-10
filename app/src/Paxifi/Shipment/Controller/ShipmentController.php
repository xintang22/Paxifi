<?php namespace Paxifi\Shipment\Controller;

use Paxifi\Shipment\Repository\Validation\CreateShipmentValidator;
use Paxifi\Shipment\Transformer\ShipmentTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Shipment\Repository\EloquentShipmentRepository as Shipment;
use Paxifi\Support\Validation\ValidationException;

class ShipmentController extends ApiController
{

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
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $new_shipment = [
                "sticker_id" => $driver->sticker->id,
                "address" => \Input::get('address')
            ];

            with(new CreateShipmentValidator())->validate($new_shipment);

            if ($shipment = Shipment::create($new_shipment)) {
                return $this->setStatusCode(201)->respondWithItem($shipment);
            }

            return $this->errorInternalError();
        } catch (ValidationException $e) {
            return $this->errorWrongArgs($e->getErrors());
        } catch (\Exception $e) {
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