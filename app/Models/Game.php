<?php

namespace App\Models;
use App\Models\GameVersions\Game1;

/**
 * Class Game
 * @package App\Models
 */
class Game extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'games';

    /**
     * @var
     */
    public static $versionValue;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'current_user_id',
        'data',
        'version',
    ];

    public $timestamps = false;

    public static function create(array  $attributes)
    {
        $attributes['version'] = static::$versionValue;
        $attributes['data'] = json_encode(isset($attributes['data']) ? $attributes['data'] : []);

        $model = new static($attributes);

        $model->save();

        return $model;
    }

    public static function getInstance($callback)
    {
        $model = $callback();

        if($model)
        {
            switch($model->version)
            {
                case 1:
                    return new Game1($model->toArray());
            }
        }
    }

    /**
     * @param $id
     * @return Game1
     */
    public static function getInstanceById($id)
    {
        return self::getInstance(function () use($id)
        {
            return self::find($id);
        });
    }
}
