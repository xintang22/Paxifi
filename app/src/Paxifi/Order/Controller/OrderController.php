<?php namespace Paxifi\Order\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Order\Repository\Factory\OrderInvoiceFactory;
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
     * Get order invoice email with a copy of invoice pdf file.
     *
     * @param $order
     *
     * @internal param $id
     * @return mixed
     */
    public function email($order)
    {
        try {
            \DB::beginTransaction();

            $buyer_email = \Input::get('buyer_email');

            if (empty($buyer_email))

                return;

            if ($order->status) {

                $order->setBuyerEmail($buyer_email)
                    ->save();

                $invoiceFactory = new OrderInvoiceFactory($order, $this->getInvoiceContentTranslation());

                $invoiceFactory->build();

                // Config email options
                $emailOptions = array(
                    'context' => $this->translator->trans('email.invoice'),
                    'to' => $buyer_email,
                    'data' => $invoiceFactory->getInvoiceData(),
                    'attach' => $invoiceFactory->getPdfFilePath(),
                    'as' => 'invoice_' . $order->id . '.pdf',
                    'mime' => 'application/pdf'
                );

                // Fire email invoice pdf event.
                \Event::fire('email.invoice', array($emailOptions));

                \DB::commit();

                return $this->setStatusCode(200)->respond(
                    [
                        "success" => true,
                    ]
                );
            }

            return $this->setStatusCode(406)->respondWithError($this->translator->trans('responses.invoice.invoice_not_available', ['order_id' => $order->id]));

        } catch (\Exception $e) {
            return $this->errorWrongArgs($e->getMessage());
        }
    }

    /**
     * @internal param \Paxifi\Order\Repository\EloquentOrderRepository $order
     *
     * @return array
     */
    public function getInvoiceContentTranslation()
    {
        return $this->translator->trans('pdf.content');
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