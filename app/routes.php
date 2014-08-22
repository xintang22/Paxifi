<?php

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

App::missing(function($exception)
{
    if ($_SERVER['SCRIPT_NAME'] == '/index.php' && \Input::has('paypal_ipn')) {

        $f = fopen(public_path('data.txt'), 'w');
        fwrite($f, '123');
        fclose($f);
//        $payment = Paxifi\Payment\Repository\EloquentPaymentRepository::find(1);

//        return Response::json(json_decode($payment));
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