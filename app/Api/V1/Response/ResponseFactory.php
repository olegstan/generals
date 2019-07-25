<?php

namespace App\Api\V1\Response;

use App;
use App\Helpers\LoggerHelper;
use App\Helpers\SafeVariable;
use Auth;
use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Session;

/**
 * Class ResponseFactory
 * @package App\Api\V1\Response
 */
class ResponseFactory
{
    /**
     * @param Collection $collection
     * @param $transformer
     * @param array $parameters
     * @param Closure|null $after
     * @return Response
     */
    public function collection(Collection $collection, $transformer, $parameters = [], Closure $after = null)
    {
        return new Response($collection, 200, [], $transformer, 'collect');
    }

    /**
     * @param Paginator $paginator
     * @param $transformer
     * @param array $parameters
     * @param Closure|null $after
     * @return Response
     */
    public function paginator(Paginator $paginator, $transformer, array $parameters = [], Closure $after = null)
    {
        return new Response($paginator, 200, [], $transformer, 'paginator');
    }

    /**
     * @param $item
     * @param $transformer
     * @param array $parameters
     * @param Closure|null $after
     * @return Response
     */
    public function item($item, $transformer, $parameters = [], Closure $after = null)
    {
        return new Response($item, 200, [], $transformer, 'item');
    }

    /**
     * @param array $data
     * @return Response
     */
    public function json(array $data){
		return new Response($data);
	}

    /**
     * @param string $text
     * @return Response
     */
    public function success($text = ''){
		$response = new Response([]);
		if(!empty($text)){
			$response->addMeta('text', $text);
		}
        $response->morph();
		return $response;
	}

    /**
     * @param null $errors
     * @param int $code
     * @param string $context
     * @return Response
     */
    public function error($errors = null, $code = 422, $context = '')
    {
		$response = new Response([]);
		$response->error($code);
		if($errors){
			if(gettype($errors) == 'array' || gettype($errors) == 'object'){
				$response->addMeta('errors', $errors);
			}elseif(gettype($errors) == 'string'){
				$response->addMeta('text', $errors);
			}
		}
        $response->morph();

		LoggerHelper::getLogger()->error(
            'Request: ' . App::make('request')->url() . "\n" .
            'CODE => ' . $code . "\n" .
            '$_SERVER => ' . var_export(SafeVariable::getServerVariables(), true) . "\n" .
            '$_GET => ' . var_export(SafeVariable::getFilterVariable($_GET), true) . "\n" .
            '$_POST => ' . var_export(SafeVariable::getFilterVariable($_POST), true) . "\n" .
            'AUTH => ' . var_export(Auth::check() ? SafeVariable::getFilterVariable(Auth::user()->toArray()) : '', true) . "\n" .
            '$_SESSION => ' . var_export(Session::all(), true) . "\n" .
            (!empty($context) ? 'CONTEXT => ' . $context . "\n" : '') .
            'ERRORS => ' . var_export($errors, true)
        );

		return $response;
	}
}
