<?php namespace Paxifi\OnlinePayment\Controller;

use Paxifi\Support\Controller\BaseApiController;

abstract class OnlinePaymentController extends BaseApiController
{
    abstract public function charge();

    abstract public function refund();
} 