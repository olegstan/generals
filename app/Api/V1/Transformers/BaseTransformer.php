<?php
namespace App\Api\V1\Transformers;

use App\Helpers\LoggerHelper;
use App\Helpers\Str as MyStr;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
	public $register = [];
    /**
     * @var array
     */
	public $registerForce = [];
    /**
     * @var array
     */
	public $withOnly = [];
    /**
     * @var array
     */
	public $fields = [];

    /**
     * @param Model $model
     * @return mixed
     */
	public function transform($model)
    {
		$this->registerAttributes($model);
		$arr = [];
		foreach($this->fields as &$value)
		{
			if(isset($this->registerForce[$value])){
				$arr[$value] = $this->registerForce[$value];
			}else{
				if($model->hasAttribute($value)){
					$arr[$value] = isset($this->register[$value]) ? $this->register[$value] : $model->{$value};
				}
			}
		}
		return $this->withRelations($arr, $model);
	}

    /**
     * @param $transformed
     * @param Model $model
     * @return mixed
     */
    public function withRelations($transformed, $model)
    {
		$relations = $model->getRelations();
		//dd($model);
		if(count($this->withOnly) > 0){
			$relations = Arr::only($relations, $this->withOnly);
		}
		unset($relations['pivot']);
		foreach($relations as $key => $value)
		{
			if($value && Str::is('*Collection', get_class($value))){
				$first = $value->first();
				if($first){
					$name =  MyStr::getClass($first, 'App\Models\\');
					$transformName = self::getTransformClass($name);
					$transform = new $transformName();
					$transformed[$key] = [];

					foreach($value as $v)
					{
						$transformed[$key][] = $transform->transform($v);
					}
				}else{
					$transformed[$key] = [];
				}
			}else{
				if($value){
					$name = MyStr::getClass($value, 'App\Models\\');
					$transformName = self::getTransformClass($name);
					$transform = new $transformName();
					$transformed[$key] = $transform->transform($value);
				}else{
					$transformed[$key] = null;
				}
			}
		}


		return $transformed;
    }

    /**
     * @param $model
     */
	public function registerAttributes($model)
    {
		$this->register['created_at'] = isset($model->created_at) ? $model->created_at->format(config('datetime.format')) : null;
	}

    /**
     * @param $name
     * @return string
     */
	public static function getTransformClass($name)
    {
		$role = Auth::getRole();

        $str = __NAMESPACE__ . '\\'.ucfirst($role).'\\' . $name . 'Transformer';



		if(class_exists($str))
		{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug($str);
            }

			return $str;
		}else{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug('Not found ' . $str);
            }
        }


		$str = __NAMESPACE__ . '\Base\\' . $name . 'Transformer';

		if(class_exists($str))
		{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug($str);
            }

			return $str;
		}else{
            if(env('EXTENDED_LOG', false)) {
                LoggerHelper::getLogger('helper')->debug('Not found ' . $str);
            }
        }

		return __NAMESPACE__ . '\Base\\' . $name . 'Transformer';
	}
}