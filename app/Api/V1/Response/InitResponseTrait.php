<?php

namespace App\Api\V1\Response;

trait InitResponseTrait
{
    /**
     * @return ResponseFactory|\Illuminate\Foundation\Application|mixed
     */
	function response()
    {
		return app(ResponseFactory::class);
	}
}
