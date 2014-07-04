<?php

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

App::missing(function($exception)
{
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