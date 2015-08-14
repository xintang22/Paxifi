<?php
namespace Paxifi\Payment;

use Illuminate\Queue\QueueManager;
use Event, App;
use Paxifi\Order\Repository\EloquentOrderRepository;
use Paxifi\Payment\Repository\Factory\PaymentInvoiceFactory;

class PaymentInvoiceMailer
{

    /**
     * @var QueueManager
     */
    protected $queue;

    /**
     * @var
     */
    protected $flysystem;

    /**
     * @param QueueManager $queue
     */
    function __construct(QueueManager $queue)
    {
        $this->queue = $queue;
        $this->flysystem = App::make('flysystem');
    }

    /**
     * Push payment invoice mailer
     *
     * @param $payment
     * @return mixed
     */
    public function push($payment)
    {
        return $this->queue->push('Paxifi\Payment\PaymentInvoiceMailer@handlerPaymentInvoiceQueue', $payment);
    }

    /**
     * @param $job
     * @param $payment
     */
    function handlerPaymentInvoiceQueue($job, $payment)
    {
        if ($job->attempts() > 1) {
            $job->delete();

            return;
        }

        $order = EloquentOrderRepository::findOrFail($payment['order_id'])->first();
        $invoiceFactory = new PaymentInvoiceFactory($order, trans('pdf.content'));

        $invoiceFactory->build();

        // Config email options
        $emailOptions = array(
            'template' => 'invoice.email',
            'context' => trans('email.invoice'),
            'to' => $payment['invoice_email'],
            'data' => $invoiceFactory->getInvoiceData(),
            'attach' => $this->flysystem->getAdapter()->getClient()->getObjectUrl(getenv('AWS_S3_BUCKET'), $invoiceFactory->getPdfUrlPath()),
            'as' => 'invoice_' . $payment['id'] . '.pdf',
            'mime' => 'application/pdf'
        );

        // Fire email invoice pdf event.
        Event::fire('paxifi.email', array($emailOptions));

        $job->delete();
    }
} 