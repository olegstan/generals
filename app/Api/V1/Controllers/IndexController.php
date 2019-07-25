<?php

namespace App\Api\V1\Controllers;

use App;
use App\Api\V1\Requests\RequestInterface;
use App\Api\V1\Requests\StartRequest;
use App\Helpers\LoggerHelper;
use App\Helpers\SafeVariable;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use ReflectionException;
use ReflectionMethod;
use Request;
use Session;

/**
 * Class IndexController
 * @package App\Api\V1\Controllers
 */
class IndexController extends Controller
{
    /**
     * @param $target
     * @param $method
     * @param StartRequest $request
     * @return mixed
     * @throws Exception
     * @throws ReflectionException
     */
	public function index($target, $method, StartRequest $request)
    {
        parent::$target = $target;
        parent::$method = $method;
        $role = Auth::getRole();

        if(env('EXTENDED_LOG', false)) {
            LoggerHelper::getLogger('helper')->debug($role);
            LoggerHelper::getLogger('helper')->debug($this->getTarget($target));
        }

        $controllerName = __NAMESPACE__ . '\\'.ucfirst($role).'\\'.$this->getTarget($target);
        if(!class_exists($controllerName)){
			$controllerName = __NAMESPACE__ . '\Common\\'.$this->getTarget($target);
			if(!class_exists($controllerName)){
				return $this->response()->error('Не найдено', 404, 'Not found controller ' . $controllerName);
			}
		}else{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug($controllerName);
            }
            $request->routeController = $controllerName;
        }

		if(!method_exists($controllerName, $this->getMethod($method))){
			return $this->response()->error('Не найдено', 404, 'In controller ' . $controllerName . ' not found method ' . $this->getMethod($method));
		}else{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug($this->getMethod($method));
            }
            $request->routeMethod = $this->getMethod($method);
        }

        $controller = new $controllerName($request, $request->getQuery());

        if(property_exists($controller, 'disabledMethods') && in_array($this->getMethod($method), $controller->disabledMethods)){
            return $this->response()->error('Отказано', 403);
        }

        $arguments = $request->getArguments();

        $refMethod = new ReflectionMethod($controller, $this->getMethod($method));
        $params = $refMethod->getParameters();
        for($i = count($arguments); $i < $refMethod->getNumberOfParameters(); $i++){
            if($params[$i]->isDefaultValueAvailable()){
                $arguments[] = $params[$i]->getDefaultValue();
            }else{
                $type = $params[$i]->getType();
                if(($type && $type->getName() === RequestInterface::class) || $params[$i]->getName() === 'request'){
                    $arguments[] = $request;
                }else{
                    throw new Exception('Обязательный аргумент #'.($i + 1) . ' не был передан');
                }
            }
        }

		
		if($errors = $controller->registerValidator())
		{
            if(env('EXTENDED_LOG', false)){
                LoggerHelper::getLogger('validation')->debug(
                             '$_SERVER => ' . var_export(SafeVariable::getServerVariables(), true) . "\n" .
                             '$_GET => ' . var_export(SafeVariable::getFilterVariable($_GET), true) . "\n" .
                             '$_POST => ' . var_export(SafeVariable::getFilterVariable($_POST), true) . "\n" .
                             'AUTH => ' . var_export(Auth::check() ? SafeVariable::getFilterVariable(Auth::user()->toArray()) : '', true) . "\n" .
                             '$_SESSION => ' . var_export(Session::all(), true) . "\n" .
                             'ERRORS => ' . var_export($errors, true)
                );
            }
			return $this->response()->error($errors);
		}
		
        return call_user_func_array([$controller, $this->getMethod($method)], $arguments);
	}

    /**
     * @param $method
     * @return string
     */
	public function getMethod($method)
    {
		return camel_case(strtolower(Request::method()) . ucfirst($method));
	}

    /**
     * @param $target
     * @return string
     */
	public function getTarget($target)
    {
		if(strpos($target, '.') !== false){
			$str = '';
			$arr = explode('.', $target);
			foreach($arr as $key => $val)
			{
				if($key < count($arr) - 1){
					$str .= ucfirst(camel_case($val)) . '\\';
				}
			}
			$str .= ucfirst(camel_case($arr[count($arr)-1])).'Controller';
			return $str;
		}else{
			return ucfirst(camel_case($target)).'Controller';
		}
	}
}
