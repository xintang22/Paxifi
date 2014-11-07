<?php namespace Paxifi\Order\Controller;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository as Order;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Order\Transformer\OrderTransformer;
use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Paxifi\Support\Controller\ApiController;

class OrderController extends ApiController
{
    /**
     * Get specific order information
     *
     * @param $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($order)
    {
        return $this->respondWithItem($order);
    }

    /**
     * Create order record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
            $this->soldouts = new Collection();

            $payments = EloquentPaymentRepository::where('status', '=', 1)->orderBy('updated_at', true)->get();
//            print_r($payments->toArray());
//            die;
            $payments->map(function($payment, $index)  {
                $products = $payment->order->products;

                $products->map(function($product, $key) use($index, $payment) {
                    $product->pivot;
                    $product->driver;
                    $product->time = Carbon::createFromTimeStamp($payment->updated_at->format('U'))->diffForHumans();
                    $product->date = $payment->updated_at;

                    $this->soldouts->push($product->toArray());
                });
            });

            $page = (\Input::get('page') > 0) ? \Input::get('page', 1) : 1;
            $per_page = \Input::get('per_page', 5);

            return $this->soldouts->slice(($page - 1) * $per_page, $per_page)->toArray();

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