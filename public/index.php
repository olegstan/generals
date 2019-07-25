<?php

if(isset($_GET['phpinfo']) && $_GET['phpinfo'] == 1){
    phpinfo();
    die();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/


function rdate($format, $timestamp = null, $case = 0)
{

    if ( $timestamp === null )
        $timestamp = time();

    $loc =
        'Январ,я,я,е,ю,ём,е
  Феврал,я,я,е,ю,ём,е
  Март, а,а,е,у,ом,е
  Апрел,я,я,е,ю,ем,е
  Ма,я,я,е,ю,ем,е
  Июн,я,я,е,ю,ем,е
  Июл,я,я,е,ю,ем,е
  Август, а,а,е,у,ом,е
  Сентябр,я,я,е,ю,ём,е
  Октябр,я,я,е,ю,ём,е
  Ноябр,я,я,е,ю,ём,е
  Декабр,я,я,е,ю,ём,е';

    if ( is_string($loc) )
    {
        $months = array_map('trim', explode("\n", $loc));
        $loc = [];
        foreach($months as $monthLocale)
        {
            $cases = explode(',', $monthLocale);
            $base = array_shift($cases);

            $cases = array_map('trim', $cases);

            $loc[] = [
                'base' => $base,
                'cases' => $cases,
            ];
        }
    }

    $m = (int)date('n', $timestamp)-1;

    $F = $loc[$m]['base'].$loc[$m]['cases'][$case];

    $format = strtr($format, array(
        'F' => $F,
        'M' => substr($F, 0, 3),
    ));

    return date($format, $timestamp);
}

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/
/**
 * @var App $app
 */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->alias('request', 'App\Api\V1\Requests\StartRequest');

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = App\Api\V1\Requests\StartRequest::capture()
);

$response->send();

$kernel->terminate($request, $response);

define('LARAVEL_END', microtime(true));

if(!$app->runningInConsole() && env('EXTENDED_LOG', false)){
    \App\Helpers\LoggerHelper::getLogger('system')->info(
    //'$_SERVER: ' . print_r($_SERVER, true) . "\n" .
        'Request: ' . $app->make('request')->url() . "\n" .
        'Script executed: ' . number_format((LARAVEL_END - LARAVEL_START), 3) . ' Seconds' . "\n" .
        'Memory usage: ' . (memory_get_usage() / 1024 / 1024) . ' MB' . "\n" .
        'Memory peak usage: ' . (memory_get_peak_usage(true) / 1024 / 1024) . ' MB'
    );
}