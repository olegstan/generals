<?php
namespace App\Api\V1\Helpers;

use Illuminate\Support\Facades\Auth;
use Request;

class ApiAuthFacade extends Auth
{
    const DEFAULT_ROLE = 'guest';

    public static function getRole()
    {
        if (self::check()) {
            $roles = config('hosts');
            foreach ($roles as $role) {
                if (self::user()->is($role)) {
                    return $role;
                }
            }
        }

        return self::DEFAULT_ROLE;
    }

    public static function getPrefix()
    {
        return config('hosts')[self::getPrefixId()] ?: 0;
    }

    public static function getPrefixId()
    {
        $role = self::getRole();
        $hosts = config('hosts');

        return array_search($role, $hosts);
    }
}
