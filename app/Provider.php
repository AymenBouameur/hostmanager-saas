<?php namespace App;

use System\Classes\AppBase;
use App\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Schema;

/**
 * Provider is an application level plugin, all registration methods are supported.
 */
class Provider extends AppBase
{
    /**
     * register method, called when the app is first registered.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->prependMiddleware(CorsMiddleware::class);

    }
}
