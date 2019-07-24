<?php

namespace App\Providers;

use App;
use App\Helpers\LoggerHelper;
use DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

class LogProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        if(!App::runningInConsole() && env('EXTENDED_LOG', false))
        {
//        if(env('APP_ENV') === 'local'){
            DB::listen(function($sql)
            {
                /**
                 * @var QueryExecuted $sql
                 */
                if($sql->time > 100){
                    $key = 'slow-query';
                }else{
                    $key = 'query';
                }

                $sqlWithBindings = $sql->sql;

                foreach ($sql->bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                    $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
                }

                LoggerHelper::getLogger($key)->debug(
                    'SQL => ' . $sqlWithBindings . PHP_EOL .
                    'TIME => ' . $sql->time . ' milliseconds' . PHP_EOL
                );
            });
//        }
        }
    }

    /**
     *
     */
    public function register()
    {

    }
}
