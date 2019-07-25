<?php

namespace App\Api\V1\Transformers\Base;

use App\Models\Notification;
use Auth;
use App\Models\User;
use App\Api\V1\Transformers\BaseTransformer;

class UserProfileTransformer extends BaseTransformer
{
    /**
     * @param User $model
     * @return array
     */
    public function transform($model)
    {
        return $this->withRelations([
            'id' => $model->id,
            'name' => $model->name,
            'phone' => $model->phone,
        ], $model);
    }

}