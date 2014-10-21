<?php

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

Route::get('/', function () {
    return Response::json([
        'status' => 'Ok',
        'time' => \Carbon\Carbon::now()->toISO8601String(),
    ]);
});

App::missing(function($exception) {
    return Redirect::to('/');
});

App::error(function (MethodNotAllowedHttpException $exception) {
    return Redirect::to('/');
});