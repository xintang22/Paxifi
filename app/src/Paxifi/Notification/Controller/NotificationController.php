<?php namespace Paxifi\Notification\Controller;

use Carbon\Carbon;
use Paxifi\Notification\Repository\EloquentNotificationTypeRepository;
use Paxifi\Notification\Repository\NotificationRepository;
use Paxifi\Notification\Transformer\NotificationTransformer;
use Paxifi\Store\Repository\Driver\DriverRepository;
use Paxifi\Store\Repository\Driver\EloquentDriverRepository;
use Paxifi\Support\Controller\ApiController;
use Paxifi\Support\Validation\ValidationException;
use Paxifi\Payment\Repository\EloquentPaymentRepository as Payment;
use Cache;

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

            $cacheKey = [DriverRepository::getTable(), NotificationRepository::getTable()];

            $to = Carbon::now();

            $from = (\Input::has('from')) ? Carbon::createFromTimestamp(\Input::get('from', $driver->created_at->format('U'))) : Carbon::createFromTimestamp($driver->created_at->format('U'));

            if (Cache::getDefaultDriver() == "file" || Cache::getDefaultDriver() == "database" || \Input::has('from')) {
                $cachedNotifications = $driver->with_notifications($from, $to);
            } else {

                if (is_null(Cache::tags($cacheKey)->get($driver->id))) {
                    Cache::tags($cacheKey)->put($driver->id, $driver->with_notifications($from, $to), 10);
                }

                $cachedNotifications = Cache::tags($cacheKey)->get($driver->id);
            }

            return $this->setStatusCode(200)->respondWithCollection($cachedNotifications);

        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * @param $commission
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function billing($commission)
    {
        try {
            \DB::beginTransaction();

            if ($driver = EloquentDriverRepository::find($commission->driver_id)) {

                $notification = [];

                if ($driver->notify_billing) {
                    $notification['driver_id'] = $commission->driver_id;
                    $notification['billing'] = $commission->commissions . ' ' . $commission->currency;

                    if ($notification = NotificationRepository::create($notification)) {

                        \DB::commit();

                        return $this->setStatusCode(201)->respondWithItem($notification);
                    }

                }

            }

            return true;

        } catch (ValidationException $e) {

            return $this->errorWrongArgs($e->getErrors());

        }
    }

    /**
     * @param $feedback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ranking($feedback)
    {
        try {
            $driver = $feedback->driver;

            if ($driver->notify_feedback) {
                \DB::beginTransaction();

                $notification = [];

                // billing is for cash checkout
                $notification['driver_id'] = $driver->id;

                if ($feedback->feedback == 1)
                    $notification['ranking'] = 'up';

                if ($feedback->feedback == -1)
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

        } catch (\Exception $e) {

            return $this->errorInternalError();

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

            return $this->errorWrongArgs($e->getErrors());

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

                    if (EloquentNotificationTypeRepository::find($notification->type_id)->type == 'sales') {

                        if (Payment::find($notification->value)->status == 0) {
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

            return $this->setStatusCode(404)->respondWithError($this->translator->trans('notifications.no_available_resources'));
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * When payment get canceled, the related notifications get delete also.
     *
     * @param $payment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSales($payment)
    {
        try {

            if (NotificationRepository::where('sales', '=', $payment->id)->delete()) {
                return $this->setStatusCode(204)->respond([]);
            }

            return true;
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