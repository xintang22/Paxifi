<?php namespace Paxifi\Notification\Transformer;

use League\Fractal\TransformerAbstract;
use Paxifi\Notification\Repository\EloquentNotificationRepository;
use Paxifi\Order\Repository\EloquentOrderRepository as Order;
use Paxifi\Store\Repository\Product\EloquentProductRepository as Product;
use Paxifi\Payment\Repository\EloquentPaymentRepository as Payment;
use Illuminate\Support\Collection;

class NotificationTransformer extends TransformerAbstract
{
    protected $translator;

    public function __construct()
    {
        $this->translator = \App::make('translator');
    }

    public function transform(EloquentNotificationRepository $notification)
    {
        $response = [];

        $response['driver_id'] = $notification->driver->id;

        switch($notification->type->type) {
            case "thumbs":
                $response = $this->transformRanking($notification);
                break;

            case "billing":
                $response = $this->transformBilling($notification);
                break;

            case "sales":
                $response = $this->transformSales($notification);
                break;

            case "inventory":
                $response = $this->transformStock($notification);
                break;

            case "emails":
                $response = $this->transformEmails($notification);
                break;

            default:;
        }

        $response['time'] = $notification->created_at->format('Y-m-d H:i:s');
        $response['id'] = $notification->id;

        return $response;
    }

    /**
     * Transform the billing response
     *
     * @param $notification
     *
     * @return array
     */
    protected function transformBilling($notification)
    {
        $billing = [];

        if ($notification->billing) {
            $billing['message'] = $this->translator->trans('notifications.billing', [':commission' => $notification->value]);
            $billing['type'] = 'billing';
        }

        return $billing;
    }

    /**
     * @param $notification
     *
     * @return null
     */
    public function transformRanking($notification)
    {
        $ranking = [];

        $ranking['message'] = $this->translator->trans('notifications.ranking.' . $notification->value);
        $ranking['type'] = 'ranking';

        return $ranking;
    }

    /**
     * @param $notification
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static
     */
    public function transformStock($notification)
    {
        $stock = [];

        $product = Product::find($notification->value);

        if ($product->inventory > 0) {
            $stock['message'] = $this->translator->trans('notifications.stock_almost_reminder', ['product_name' => $product->name]);
        } else {
            $stock['message'] = $this->translator->trans('notifications.stock_reminder', ['product_name' => $product->name]);
        }

        $stock['type'] = 'stock_reminder';

        return $stock;
    }

    /**
     * @param $notification
     *
     * @return array
     */
    public function transformSales($notification)
    {
        $payment = Payment::find($notification->value);
        $payment_status = strtolower($payment->status);

        // get the payment type.
        switch ($payment->payment_method()->first()->name)
        {
            case 'paypal':

                $translation = "notifications.sales.paypal.completed";

                $sales = [
                    'message' => $this->translator->trans($translation , ['currency' => $notification->driver->currency, 'amount' => $payment->order->total_sales]),
                    'type' => 'sales',
                    'status' => $payment_status,
                    'payment' => $payment
                ];

                break;

            case 'stripe':
                $translation = "notifications.sales.stripe.completed";
                
                $sales = [
                    'message' => $this->translator->trans($translation , ['currency' => $notification->driver->currency, 'amount' => $payment->order->total_sales]),
                    'type' => 'sales',
                    'status' => $payment_status,
                    'payment' => $payment
                ];

                break;
            default:
                if ($payment_status == 0) {
                    $status = 'waiting';
                }

                if ($payment_status == -1) {
                    $status = 'canceled';
                }

                if ($payment_status == 1) {
                    $status = 'received';
                }

                $translation = "notifications.sales.cash." . $status;

                $sales = [
                    'message' => $this->translator->trans($translation , ['currency' => $notification->driver->currency, 'amount' => $payment->order->total_sales]),
                    'type' => 'sales',
                    'status' => $payment_status,
                    'payment' => $payment
                ];
        }

        return $sales;
    }

    /**
     * @param $notification
     *
     * @return array
     */
    public function transformEmails($notification)
    {
        $emails = [];

        $emails['message'] = $this->translator->trans('notifications.emails');
        $emails['type'] = 'emails';
        $emails['email'] = $notification->value;

        return $emails;
    }
} 