<?php

namespace App\Providers;

use App\Models\Common\Lesson;
use App\Models\Common\Section;
use App\Models\StageLesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use File;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Lang::setLocale('ru');
        \Illuminate\Support\Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('Y-m-d H:i:s');
        });
        //Carbon::setWeekStartsAt(Carbon::MONDAY);
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale('ru');

        Schema::defaultStringLength(191);


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
