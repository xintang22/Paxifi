<?php

use Paxifi\Payment\Repository\EloquentPaymentRepository;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


App::missing(function($exception)
{
    print_r($_SERVER);
    if ($_SERVER['SCRIPT_NAME'] == '/index.php' && \Input::has('paypal_ipn')) {

        $payment = EloquentPaymentRepository::find(3);
        $payment->status = 1;
        $payment->save();

        return Response::json(json_decode($payment));
    }

    return Response::json(array(
        "status" => 501,
        'error' => "not_implemented",
        'message' => 'Not Implemented'
    ), 501);
});

App::error(function (MethodNotAllowedHttpException $exception) {
    return Response::json(array(
        "status" => 405,
        'error' => "method_not_allowed",
        'message' => 'Method Not Allowed',
    ), 405);
});