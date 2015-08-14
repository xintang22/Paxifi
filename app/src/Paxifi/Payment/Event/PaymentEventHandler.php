<?php
namespace Paxifi\Payment\Event;

use Event, App;
use Paxifi\Payment\Repository\Factory\PaymentInvoiceFactory;

class PaymentEventHandler
{

    /**
     * Register payment events
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen('paxifi.build.invoice', 'Paxifi\Payment\Event\PaymentEventHandler@onInvoiceBuild');
    }

    /**
     * @param $payment
     */
    public function onInvoiceBuild($payment) {
        $mailer = App::make('Paxifi\Payment\PaymentInvoiceMailer');

        $mailer->push($payment);

//        $this->flysystem = App::make('flysystem');
//
//        $invoiceFactory = new PaymentInvoiceFactory($payment->order, trans('pdf.content'));
//
//        $invoiceFactory->build();
//
//        // Config email options
//        $emailOptions = array(
//            'template' => 'invoice.email',
//            'context' => trans('email.invoice'),
//            'to' => $payment->invoice_email,
//            'data' => $invoiceFactory->getInvoiceData(),
//            'attach' => $this->flysystem->getAdapter()->getClient()->getObjectUrl(getenv('AWS_S3_BUCKET'), $invoiceFactory->getPdfUrlPath()),
//            'as' => 'invoice_' . $payment->id . '.pdf',
//            'mime' => 'application/pdf'
//        );
//
//        // Fire email invoice pdf event.
//        Event::fire('paxifi.email', array($emailOptions));

    }
} 