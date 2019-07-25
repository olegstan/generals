<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\RequestInterface;
use App\Api\V1\Requests\StartRequest;
use App\Api\V1\Requests\Validators\ValidatorAble;
use App\Api\V1\Response\Response;
use App\Api\V1\Transformers\TransformerAble;
use App\Helpers\LoggerHelper;
use App\Http\Controllers\Controller;
use App\Models\BaseModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

/**
 * Class RestController
 * @package App\Api\V1\Controllers
 */
abstract class RestController extends Controller
{
    use ValidatorAble, TransformerAble;
    /**
     * @var bool
     */
    public $sofDelete = false;
    /**
     * @var string
     */
    public $modelName = '';
    /**
     * @var
     */
    public $modelQuery;
    /**
     * @var bool
     */
    public $modelTableAlias = false;
    /**
     * @var string
     */
    public $transformer = '';
    /**
     * @var int
     */
    public $perPage = 25;
    /**
     * @var string
     */
    public $successCreateText;
    /**
     * @var string
     */
    public $errorCreateText;
    /**
     * @var string
     */
    public $successUpdateText;
    /**
     * @var string
     */
    public $errorUpdateText;
    /**
     * @var string
     */
    public $successDeleteText;
    /**
     * @var string
     */
    public $errorDeleteText;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var string
     */
    public $cointrollerName;
    /**
     * @var string
     */
    public $actionName;
    /**
     * @var string
     */
    public $cacheSortByPart = '';
    /**
     * @var array
     */
    public $onlyFieldsCreate = [];
    /**
     * @var array
     */
    public $onlyFieldsUpdate = [];
    /**
     * @var array
     */
    public $baseOnlyFields = [];
    /**
     * @var 
     */
    public $queryBuild;
    /**
     * @var array 
     */
    public $validators = [];
    /**
     * @var array 
     */
    public $disabledMethods = [];
    /**
     * @var array 
     */
    public $builderAvailableMethod = [
        'select',
		'where',
		'has',
		'whereIn',
		'whereNotIn',
		'whereHas',
		'whereDoesntHave',
		'orWhere',
		'orderBy',
		'groupBy',
		'whereNull',
		'whereNotNull',
		'with',
		'withCount',
		'limit',
        'distinct',
		'owner',
    ];

    /**
     * RestController constructor.
     * @param RequestInterface $request
     * @param array $query
     * @throws Exception
     */
    public function __construct(RequestInterface $request, array $query = [])
    {
		
        if ($request->has('page')) {
            \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($request) {
                return $request->get('page');
            });
        }

        $this->registerTransformer();
        $this->request = $request;
        $this->queryBuild = $request->getQuery();
		
		$this->modelQuery = $this->modelName::query();
        $model = new $this->modelName;
		
        $this->prepareQuery();
        if ($this->modelTableAlias) {
            $this->modelQuery->from($model->getTable() . ' AS ' . $this->modelTableAlias);
        }
        $this->softDeleteCondition($request);
        $this->defaultOrderBy($request);
    }

    /**
     * @param $funcName
     * @throws Exception
     */
    public function queryCheckAbailable($funcName){
		if(array_search($funcName, $this->builderAvailableMethod) === false){
			throw new Exception('Вы пытаетесь вызвать заблокированный метод: ' . $funcName);
		}
	}
	
	public function setModelQuery($modelName){
		//$this->modelQuery = $modelName::query();
        //$model = new $this->modelName;
	}

    /**
     * @param $args
     * @throws Exception
     */
    public function queryCheckSelect($args){
		if(is_array($args[0])){
			$arr = $args[0];
		}else{
			$arr = $args;
		}
		foreach($arr as &$val)
		{
			if(strpos($val, ' as ') !== false)
				throw new Exception('Не используйте конструкцию "as" в select выражениях: ' . $val);
		}
	}

    /**
     * @throws Exception
     */
    public function prepareQuery()
    {
        $query = $this->queryBuild;
        foreach ($query as $val)
        {
            $key = key($val);
            $args = $val[$key];
            if ($key == 'with') {
                $args = $this->prepareWith($val);
            } else if ($key == 'limit') {
                $this->perPage = !isset($args[0]) || (isset($args[0]) && $args[0]) > 500 ? 500 : $args[0];
            } else if($key == 'select'){
                $this->queryCheckSelect($args);
            } else {
                $this->prepareBase($args);
            }
			//if($key == 'where' || $key == 'orWhere'){
			//	if(isset($args[1]) && $args[1] === 'like'){
					
			//	}
			//}
			$this->queryCheckAbailable($key);
            call_user_func_array([$this->modelQuery, $key], $args);
        }
    }

    /**
     * @param $val
     * @return array
     */
    public function prepareWith($val)
    {
        $args = [];
        if (isset($val['with'][1]) && isset($val['with'][1]['query'])) {
            $withQuery = $val['with'][1]['query'];
            $args[] = [$val['with'][0] => function ($j) use ($withQuery) {
                foreach ($withQuery as $query)
                {
                    $key = key($query);
                    $args = $query[$key];
                    if ($key == 'with') {
                        $args = $this->prepareWith($query);
                    } else if($key == 'select'){
						$this->queryCheckSelect($args);
					} else {
                        $this->prepareBase($args);
                    }
					$this->queryCheckAbailable($key);
                    return call_user_func_array([$j, $key], $args);
                }
            }];
        } else {
            $args[] = $val['with'];
        }
        return $args;
    }

    /**
     * @param $args
     */
    public function prepareBase(&$args)
    {
        foreach ($args as &$arg)
        {
            if (is_array($arg) && array_key_exists('query', $arg)) {
                $chuldQuery = $arg['query'];
                $arg = function ($j) use ($chuldQuery) {
                    foreach ($chuldQuery as $query)
                    {
                        $key = key($query);
                        $args = $query[$key];
                        if ($key == 'with') {
                            $args = $this->prepareWith($query);
                        } else if($key == 'select'){
							$this->queryCheckSelect($args);
						}  else {
                            $this->prepareBase($args);
                        }
						$this->queryCheckAbailable($key);
                        call_user_func_array([$j, $key], $args);
                    }
                };
            }
        }
    }

    /**
     * @param Request $request
     */
    public function softDeleteCondition(Request $request)
    {
        if ($this->sofDelete)
            $this->modelTableAlias ? $this->modelQuery->whereNull($this->modelTableAlias . '.deleted_at') : $this->modelQuery->whereNull('deleted_at');
    }

    /**
     *
     */
    public function defaultOrderBy($request)
    {
        $this->modelTableAlias ? $this->modelQuery->orderBy($this->modelTableAlias . '.' . 'id', 'DESC') : $this->modelQuery->orderBy('id', 'DESC');
    }


    /**
     * @param RequestInterface $request
     * @return Response
     * @throws Exception
     */
    public function getIndex($request)
    {
        $this->queryCondition($request);
        $this->indexCallback($request);
        return $this->responseIndex($request->get('paginateType'));
    }

    /**
     *
     */
    public function queryCondition($request)
    {

    }

    /**
     * @param RequestInterface $request
     */
    public function indexCallback($request)
    {

    }

    /**
     * @param $paginate
     * @param bool $withTransform
     * @return Response
     */
    public function responseIndex($paginate, $withTransform = true)
    {
        switch ($paginate) {
            case 'all':
				if(!$this->modelQuery->getQuery()->limit || $this->modelQuery->getQuery()->limit > 500)
					$this->modelQuery->limit(500);

                return $this->response()->collection(collect($this->modelQuery->get()->all()), new $this->transformer);
            case 'first':
                return $this->response()->item($this->modelQuery->first(), new $this->transformer);
            case 'paginate':
            default:
                return $this->response()
                    ->paginator($this->modelQuery->paginate($this->perPage), $withTransform ? new $this->transformer : false)
                    ->addMeta('cache_key', $this->getCacheKey());

        }
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        $key = $this->cacheKeyMainPart();
        if ($this->request->query->get('page')) {
            $key .= "page:" . $this->request->query->get('page');
        } else {
            $key .= "page:0";
        }

        if ($this->cacheSortByPart) {
            $key .= $this->cacheSortByPart;
        } else {
            $key .= ":orderby:id:desc";
        }

        return $key;
    }

    /**
     * @return string
     */
    public function cacheKeyMainPart()
    {
        $path = $this->request->path();
        $pathParts = explode('/', $path);
        return $pathParts[3] . ":";
    }

    /**
     * @param StartRequest $request
     *
     * @return \App\Api\V1\Response\Response
     */
    public function postStore($request)
    {
        $item = call_user_func([$this->modelName, 'create'], $request->only($this->onlyFieldsCreate));
        if ($item) {
            return $this->response()->success('Запись успешно создана');
        }
        return $this->response()->error('Не удалось создать запись');
    }

    //public function putStatus($id)
    //{
    //    $item = $this->modelQuery->where('id', '=', $id)->get()->first();
    //}

    /**
     * @param $id
     * @param $request
     * @return Response
     */
    public function putActive($id, $request)
    {
        $this->queryCondition($request);
        /**
         * @var BaseModel $item
         */
        $item = $this->modelQuery->where('id', '=', $id)->firstOrFail();
        $item->fieldSwitch('active');
        return $this->response()->success();
    }

    /**
     * @param $id
     * @param $request
     * @return \App\Api\V1\Response\Response
     */
    public function putUpdate($id, $request)
    {
        $this->queryCondition($request);
        $this->updateCallback($request);
        $item = $this->modelQuery->where('id', '=', $id)->first(); // firstOrError?
        if ($item && $item->update($request->only($this->onlyFieldsUpdate))) {
            return $this->response()->success('Запись успешно обновлена');
        }

        return $this->response()->error('Не удалось обновить запись');
    }

    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function updateCallback($request)
    {

    }

    /**
     * @param $id
     * @param $request
     * @return \App\Api\V1\Response\Response
     */
    public function deleteDestroy($id, $request)
    {
        $this->queryCondition($request);
        $this->destroyCallback($request);
        $item = $this->modelQuery->where('id', '=', $id)->first();
        if ($this->sofDelete) {
            //TODO complete delete row
//            if ($item && $item->deleted_at) {
//                $item->delete();
//                return $this->response()->success('Запись успешно удалена');
//            }
            if ($item && $item->update([
                    'deleted_at' => Carbon::now()
                ]))
                return $this->response()->success('Запись успешно удалена');
        } else {
            if ($item && $item->delete())
                return $this->response()->success('Запись успешно удалена');
        }
        return $this->response()->error('Не удалось удалить запись');
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */

    public function destroyCallback($request)
    {

    }

    /**
     * @return string
     */
    public function getMessageSuccess()
    {
        switch ($this->getActionName()) {
            case 'store':
                return $this->successCreateText;
            case 'update':
                return $this->successUpdateText;
            case 'destroy':
                return $this->successDeleteText;
        }
    }

    /**
     * @return mixed|string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return string
     */
    public function getMessageError()
    {
        switch ($this->getActionName()) {
            case 'store':
                return $this->errorCreateText;
            case 'update':
                return $this->errorUpdateText;
            case 'destroy':
                return $this->errorDeleteText;
        }
    }

}
