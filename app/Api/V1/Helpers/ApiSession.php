<?php

namespace App\Api\V1\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;

/**
 * Class ApiSession
 * @package App\Api\V1\Helpers
 */
class ApiSession
{
    /**
     * @param $key
     * @param $value
     */
    public static function put($key, $value)
    {
		Cache::put(session()->getId() . ':' . $key, $value, 30);
	}

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static function get($key, $default = null)
    {
		return Cache::get(session()->getId() . ':' . $key) ? Cache::get(session()->getId() . ':'.$key) : $default;
	}

    /**
     * @param $key
     * @return int|null
     */
	public static function getInt($key)
    {
        $value = ApiSession::get($key) ?? null;

        return  !is_null($value) ? (int) $value : null;
    }


    /**
     * @param $key
     */
    public static function forget($key)
    {
		Cache::forget(session()->getId() . ':'.$key);
	}

    /**
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        if(self::get($key)) {
            return true;
        }
		else
			return false;
	}

    /**
     * @return array
     */
	public static function all()
    {
        $pattern = Config::get('cache.prefix') . ':' . session()->getId() . ':*';
        $keys = Redis::scan(0, 'match', $pattern, 'COUNT', 1000);

        if(isset($keys[1])){
            $keys = $keys[1];


            $array = [];
            foreach ($keys as &$key)
            {
                $array[$key] = Redis::get($key);
            }

            return $array;
        }else{
            return [];
        }
    }
}
