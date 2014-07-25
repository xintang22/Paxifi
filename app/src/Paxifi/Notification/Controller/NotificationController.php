<?php namespace Paxifi\Notification\Controller;

use Carbon\Carbon;
use Paxifi\Notification\Repository\NotificationRepository;
use Paxifi\Notification\Transformer\NotificationTransformer;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Paxifi\Payment\Repository\EloquentPaymentRepository as Payment;

class NotificationController extends ApiController
{

    /**
     * Get all the notifications.
     */
    public function index()
    {
        try {
            if ($notifications = NotificationRepository::all()) {

                return $this->setStatusCode(200)->respondWithCollection($notifications);

            }

            return $this->setStatusCode(200)->respond($this->translator->trans('notifications.no_new_notifications'));
        } catch (\Exception $e) {
            return $this->errorInternalError($this->translator->trans('responses.exceptions.system_error'));
        }
    }

    /**
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($driver = NULL)
    {
        try {
            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $to = Carbon::createFromTimestamp(Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U'));

            $from = Carbon::createFromTimestamp(Carbon::now()->setTimezone(\Config::get('app.timezone'))->format('U') - (60 * 60 * \Config::get('notification_hours')));

            if ($notifications = $driver->with_notifications($from, $to)) {
                return $this->setStatusCode(200)->respondWithCollection($notifications);
            }

            return $this->setStatusCode(200)->respond([]);

        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * @param null $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function billing($order)
    {
        try {
            \DB::beginTransaction();

            // Todo:: Paxifi billing, related to paypal.

            return $this->errorInternalError('System error.');

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        }
    }

    /**
     * @param $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ranking($order)
    {
        try {
            $driver = $order->OrderDriver();

            if ($driver->notify_feedback) {
                \DB::beginTransaction();

                $notification = [];

                // billing is for cash checkout
                $notification['driver_id'] = $driver->id;

                if ($order->feedback == 1)
                    $notification['ranking'] = 'up';

                if ($order->feedback == -1)
                    $notification['ranking'] = 'down';

                if ($response = NotificationRepository::create($notification)) {

                    \DB::commit();
                    return $this->setStatusCode(201)->respondWithItem($response);
                }

                return $this->errorInternalError('System error.');
            }

            return true;
        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        }
    }

    /**
     * @param $product
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stock($product)
    {
        try {
            $driver = $product->driver;

            if ($driver->notify_inventory) {
                \DB::beginTransaction();

                $notification = [];

                // billing is for cash checkout
                $notification['driver_id'] = $driver->id;
                $notification['stock_reminder'] = $product->id;

                if ($response = NotificationRepository::create($notification)) {

                    \DB::commit();

                    return $this->setStatusCode(201)->respondWithItem($response);
                }

                return $this->errorInternalError('System error.');
            }
            return true;
        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        }
    }

    /**
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @Todo:: sales for payment.
     */
    public function sales($payment)
    {
        try {
            $driver = $payment->order->OrderDriver();

            if ($driver->notify_sale) {

                \DB::beginTransaction();

                if (!$notification = NotificationRepository::findByPaymentId($payment->id)) {
                    $notification = [];

                    // sales is for cash checkout
                    $notification['driver_id'] = $driver->id;
                    $notification['sales'] = $payment->id;

                    if ($response = NotificationRepository::create($notification)) {

                        \DB::commit();

                        return $this->setStatusCode(201)->respondWithItem($response);
                    }
                }

                return $this->setStatusCode(404)->errorNotFound('Notification is not available.');
            }

            return true;
        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        }
    }

    /**
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function emails($driver)
    {
        try {
            \DB::beginTransaction();

            $notification = [];

            $notification['driver_id'] = $driver->id;
            $notification['emails'] = $driver->email;

            if ($response = NotificationRepository::create($notification)) {

                \DB::commit();

                return $this->setStatusCode(201)->respondWithItem($response);

            }

            return $this->errorInternalError('System error.');

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors()->all());

        }
    }

    /**
     * @param $driver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($driver = NULL)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            if ($notifications = $driver->notifications) {

                $notifications->map(function ($notification) {

                    if ($notification['sales']) {
                        $payment = Payment::find($notification['sales']);
                        if ($payment->status == 0) {
                            return;
                        }
                    }

                    $notification->delete();
                });

                \DB::commit();

                return $this->setStatusCode(204)->respond([
                    'success' => true,
                    'message' => $this->translator->trans('notifications.deleted')
                ]);
            }

            return $this->setStatusCode(404)->respond([
                'error' => true,
                'message' => $this->translator->trans('notifications.no_available_resources')
            ]);
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
        return new NotificationTransformer();
    }
}