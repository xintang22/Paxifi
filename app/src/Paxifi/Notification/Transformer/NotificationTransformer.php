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

        if ($notification->billing)
            $response = $this->transformBilling($notification);

        if ($notification->ranking)
            $response = $this->transformRanking($notification);

        if ($notification->stock_reminder)
            $response = $this->transformStock($notification);

        if ($notification->sales)
            $response = $this->transformSales($notification);


        if ($notification->emails)
            $response = $this->transformEmails($notification);

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
            $billing['message'] = $this->translator->trans('notifications.billing', [':commission' => $notification->billing]);
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

        if ($notification->ranking) {
            $ranking['message'] = $this->translator->trans('notifications.ranking.' . $notification->ranking);
            $ranking['type'] = 'ranking';
        }

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

        if ($notification->stock_reminder) {

            $product = Product::find($notification->stock_reminder);

            $stock['message'] = $this->translator->trans('notifications.stock_reminder', ['product_name' => $product->name]);
            $stock['type'] = 'stock_reminder';
        }

        return $stock;
    }

    /**
     * @param $notification
     *
     * @return array
     */
    public function transformSales($notification)
    {
        $sales = [];

        if ($notification->sales) {
            $payment = Payment::find($notification->sales);
            $payment_status = strtolower($payment->status);

            // get the payment type.
            switch ($payment->payment_method()->first()->name)
            {
                case 'paypal':

                    $translation = "notifications.sales.paypal.completed";

                    $payment->order->products->map(function($product, $index) use(&$sales, $translation, $notification, $payment, $payment_status) {

                        $sale = [
                            'message' => $this->translator->trans($translation , ['product_name' => $product->name, 'currency' => $notification->driver->currency, 'amount' => $payment->order->total_sales]),
                            'type' => 'sales',
                            'status' => $payment_status,
                            'payment' => $payment->toArray()
                        ];

                        $sales = $sale;
                    });

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

        if ($notification->emails) {
            $emails['message'] = $this->translator->trans('notifications.emails');
            $emails['type'] = 'emails';
            $emails['email'] = $notification->emails;
        }

        return $emails;
    }
} 