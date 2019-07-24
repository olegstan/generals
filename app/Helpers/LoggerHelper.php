<?php
namespace App\Helpers;

use File;
use Log;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Request;

class LoggerHelper
{
    /**
     * @var array
     */
    public static $loggers = [];

    /**
     * @var string|bool
     */
    public static $commandKey = false;

    /**
     * @var bool
     */
    public static $removeDefaultHandler = true;

    /**
     * @param string $key
     * @return Logger
     */
    public static function getLogger($key = 'laravel')
    {
        if(!isset(self::$loggers[$key]))
        {
            /**
             * @var Logger $monolog
             */
            if($key === 'laravel'){
                $monolog = Log::getMonolog();

                if(self::$removeDefaultHandler){
                    $handlers = $monolog->getHandlers();

                    foreach ($handlers as $handler){
                        $monolog->popHandler();
                    }

                    self::$removeDefaultHandler = false;
                }
            }else{
                $monolog = new Logger($key);
            }

            if(self::$commandKey){
                $path = storage_path('logs/commands/' . self::prepareCommandKey(self::$commandKey));
                if(!File::exists($path)){
                    File::makeDirectory($path, 0777, true, true);
                }

                $filename = $path . '/' . $key . '.log';
                self::setHandler($monolog, $filename);
            }else{
                $headerKey = Request::server('HTTP_REFERER');
                $path = storage_path('logs/' . self::prepareKey($headerKey));
                if(!File::exists($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }

                if($headerKey && in_array($headerKey, [
                        'admin',
                        'driver',
                    ]))
                {
                    if(function_exists('posix_geteuid')){
                        $processUser = posix_getpwuid( posix_geteuid() );
                        $processName= $processUser[ 'name' ];

                        $filename = $path . '/' . $key . '-' . php_sapi_name() . '-' . $processName . '.log';
                        self::setHandler($monolog, $filename);
                    }else{
                        $filename = $path . '/' . $key . '.log';
                        self::setHandler($monolog, $filename);
                    }
                }else{
                    $filename = $path . '/' . $key . '.log';
                    self::setHandler($monolog, $filename);
                }
            }

            self::$loggers[$key] = $monolog;
        }
        return self::$loggers[$key];
    }

    /**
     * @param Logger $monolog
     * @param $filename
     */
    public static function setHandler($monolog, $filename)
    {
        $handler = new RotatingFileHandler($filename, 10, Logger::DEBUG, true, 0777);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));
        $monolog->pushHandler($handler);
    }

    /**
     * @param $key
     * @return null|string|string[]
     */
    public static function prepareKey($key)
    {
        if(in_array($key, [
            'admin',
            'driver',
        ])){
            return $key;
        }else{
            return 'common';
        }
    }

    /**
     * @param $key
     * @return null|string|string[]
     */
    public static function prepareCommandKey($key)
    {
        $key = str_replace(' ', '-', $key);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $key);
    }
}