<?php

namespace App\Api\V1\Requests\Validators;

use Auth;
use Request;

trait ValidatorAble
{
    public static $namespaceValidators = 'App\Api\V1\Requests\Validators\\';

    /**
     * @param string $t
     * @return bool
     */
	public function registerValidator($t = '')
    {
        if ($validate = $this->getValidateClass()) {
            $validate = new $validate($this->request);

            if ($validate->fails()) {
                return $validate->errors();
            }

        }

        return false;
    }

    /**
     * @return bool|string
     */
	public function getValidateClass()
    {
        $key = strtolower(Request::method()) . '-' . parent::$method;
        $validators = $this->validators;

        if (property_exists($this, 'defaultValidators') && is_array($this->defaultValidators)) {
            $validators = array_merge($this->defaultValidators, $validators);
        }

        if ($validators[$key] ?? null) {
            if (strpos($validators[$key], self::$namespaceValidators) !== false && class_exists($validators[$key])) {
                $validate = $validators[$key];
            } else {
                $validate = self::$namespaceValidators . ucfirst(Auth::getPrefix()) . '\\' . $validators[$key];
            }

            return $validate;
        }

        $validate = self::$namespaceValidators . ucfirst(Auth::getPrefix()) . '\\' . ucfirst(parent::$target) . 'Request';

        if (class_exists($validate)) {
            return $validate;
        }

        $validate = 'App\Api\V1\Requests\Validators\Base\\' . ucfirst(parent::$target) . 'Request';
        if (class_exists($validate)) {
            return $validate;
        }

        return false;
	}
}
