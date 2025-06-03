<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        RateLimiter::for('api', function (Request $request) {
            $limit = 10000;
            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->group(function() {                    
                    Route::prefix('common')
                        ->group(glob(base_path('http/Common/*/routes.php')));
                });
        });

        Route::pattern('id', '[0-9]+');
    }
}
