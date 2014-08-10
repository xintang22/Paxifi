<?php namespace Paxifi\Order\Controller;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository as Order;
use Paxifi\Order\Transformer\OrderTransformer;
use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Paxifi\Support\Controller\ApiController;

class OrderController extends ApiController
{
    public function store()
    {
        try {
            \DB::beginTransaction();

            $items = Collection::make(\Input::get('items'));

            $order = new Order();
            // create the order
            $order->save();

            // Attach items to order
            $items->each(function ($item) use ($order) {
                $order->addItem($item);
            });

            // Calculate commission & profit
            /** @var \Paxifi\Support\Commission\CalculatorInterface $calculator */
            $calculator = \App::make('Paxifi\Support\Commission\CalculatorInterface');
            $order->setCommission($calculator->calculateCommission($order));
            $order->setProfit($calculator->calculateProfit($order));

            // save order
            $order->save();

            \DB::commit();

            return $this->setStatusCode(201)->respondWithItem(Order::find($order->id));

        } catch (ModelNotFoundException $e) {

            return $this->errorWrongArgs('Invalid product id');

        } catch (\InvalidArgumentException $e) {

            return $this->errorWrongArgs($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function soldouts()
    {
        try {
            $this->soldouts = [];
            $this->orders = [];

            $refresh_time = \Input::get('refresh_time');

            $from =  $refresh_time ? Carbon::createFromTimestamp(Carbon::now()->format('U') - $refresh_time) : Carbon::createFromTimestamp(0);

            $payments = EloquentPaymentRepository::take(5)->where('status', '=', 1)->where('updated_at', '<', Carbon::now())->where('updated_at', '>', $from)->orderBy('updated_at', 'desc')->get();

            $payments->map(function($payment) {

                $order = $payment->order;

                $order->products->map(function($product) use($order) {
                    $this->soldouts[] = [
                        "product" => $product->toArray(),
                        "driver" => $product->driver,
                        "time" => Carbon::createFromTimeStamp($order->payment->updated_at->format('U'))->diffForHumans()
                    ];
                });

            });

            $this->soldouts = (count($this->soldouts) > 5) ? array_slice($this->soldouts, 0, 5) : $this->soldouts;

            return $this->setStatusCode(200)->respond($this->soldouts);
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
        return new OrderTransformer;
    }
}