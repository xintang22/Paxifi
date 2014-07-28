<?php namespace Paxifi\Order\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Order\Repository\Validation\UpdateOrderValidator;
use Paxifi\Order\Transformer\OrderTransformer;
use Paxifi\Support\Controller\ApiController;

class OrderController extends ApiController
{
    public function store()
    {
        try {
            \DB::beginTransaction();

            $items = Collection::make(\Input::get('items'));

            $order = new EloquentOrderRepository();
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

            return $this->setStatusCode(201)->respondWithItem(EloquentOrderRepository::find($order->id));

        } catch (ModelNotFoundException $e) {

            return $this->errorWrongArgs('Invalid product id');

        } catch (\InvalidArgumentException $e) {

            return $this->errorWrongArgs($e->getMessage());
        }
    }

    /**
     * Update feedback after the passenger paid the order by cash.
     *
     * @param $order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback($order)
    {
        try {
            \DB::beginTransaction();

            if (\Input::has('feedback')) {
                $feedback = \Input::get('feedback');

                if (!empty($order->feedback)) {
                    return $this->setStatusCode(400)->respondWithError('Can only rating once.');
                }

                with(new UpdateOrderValidator)->validate(\Input::only('feedback'));

                $order->feedback = $feedback;

                $order->save();

                \Event::fire('paxifi.notifications.ranking', [$order]);

                \DB::commit();

                return $this->setStatusCode(200)->respondWithItem($order);

            }

            return $this->setStatusCode(400)->respondWithError("Missing argument feedback field.");

        } catch (\Exception $e)
        {
            return $this->errorWrongArgs($e->getMessage());
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