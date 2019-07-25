<?php

namespace App\Api\V1\Response;

use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse
{
	protected static $transformer;
	
	protected $result = 'success';
	
	protected $meta = [];
	
	protected $type;
	
	protected $transformData;	
	
	public function __construct($content, $status = 200, $headers = [], $transformer = null, $type = 'json')
    {
        parent::__construct($content, $status, $headers);
		static::$transformer = $transformer;
		$this->type = $type;
		$this->transformData = $content;
    }
	
	public function send(){
		$this->morph();
		return parent::send();
	}
	
	public function success(){
		$this->result = 'success';
	}
	
	public function error($code){
		$this->setStatusCode($code);
		$this->result = 'error';
	}

	public function morph($format = 'json')
    {	
		switch($this->type){
			case 'paginator':
					if(static::$transformer)
						$this->transformData->getCollection()->transform(function($value){
							return static::$transformer->transform($value);
						});
				break;
			case 'collect':
				if(static::$transformer)
					$this->transformData->transform(function($value){
						return static::$transformer->transform($value);
					});
				break;
			case 'item':
				if($this->transformData){
					if(static::$transformer)
						$this->transformData = static::$transformer->transform($this->transformData);
				}else{
					$this->transformData = [];
				}
				break;
		}
		$arr['meta'] = [];
		$arr['result'] = $this->result;
		$arr['data'] = is_array($this->transformData) ? $this->transformData : $this->transformData->toArray();
		$this->setMeta($arr);
		$this->content = json_encode($arr);
        return $this;
    }
	
    public function addMeta($key, $value)
    {
        $this->meta[$key]=$value;
		return $this;
    }

    /**
     * @param $arr
     */
	protected function setMeta(&$arr)
    {
		foreach($this->meta as $key => &$val){
			$arr['meta'][$key] = $val;
		}
	}
}
