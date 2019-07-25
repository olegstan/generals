<?php

namespace App\Models;

/**
 * Class GameUser
 * @package App\Models
 */
class GameUser extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'game_users';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'game_id',
    ];

    public $timestamps = false;
}
