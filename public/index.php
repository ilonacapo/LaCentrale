<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;


require_once dirname(__DIR__).'/vendor/autoload_runtime.php';


return function (array $context) {
    $_SERVER['APP_ENV'] = $context['APP_ENV'] ?? 'dev';
    $_SERVER['APP_DEBUG'] = filter_var($context['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

    $kernel = new Kernel($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

    //return $kernel;
};
