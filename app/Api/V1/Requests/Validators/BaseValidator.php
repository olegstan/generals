<?php
namespace App\Api\V1\Requests\Validators;

use App\Api\V1\Requests\StartRequest;
use Closure;
use Validator;

/**
 * Class BaseValidator
 * @package App\Api\V1\Requests\Validators
 */
class BaseValidator
{
    /**
     * @var
     */
	public $request;

    /**
     * @var \Illuminate\Validation\Validator
     */
	public $validator;

	/**
     * @var Closure
     */
	public $after;

    /**
     * BaseValidator constructor.
     * @param StartRequest $request
     */
	public function __construct($request)
    {
        $this->trimInput($request);
        $this->after = function ($validator) {

        };
        $this->request = $request;

        $this->request->merge($this->customRequest());
        $this->validator = Validator::make($this->request->all(), $this->rules(), $this->messages());
        $this->validator->after($this->after);
    }

    /**
     * @param StartRequest $request
     */
    public function trimInput($request)
    {
        $input = $request->all();
        $input = is_array($input) ? $input : [];
        array_walk_recursive($input, function(&$item)
        {
            if(is_array($item)){

            }else{
                $item = trim($item);
            }
            return $item;
        });

        $request->merge($input);
    }

    /**
     * @param $callable
     */
	public function setAfter($callable)
    {
		$this->after = $callable;
	}

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
	public function __call($name, $arguments)
    {
		return call_user_func_array([$this->validator, $name], $arguments);
	}


    /**
     * @return \Closure
     */
	public function afterValidate()
    {
		return function($validator){
			
		};
	}

    /**
     * Set request.
     *
     * @return array
     */
	public function customRequest(): array
    {
        return [];
    }
}
