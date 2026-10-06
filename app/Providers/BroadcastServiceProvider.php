<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
// MVP_POSTERIOR: Canales de WebSockets fuera del MVP
// MVP_POSTERIOR |         Broadcast::routes();

// MVP_POSTERIOR: Canales de WebSockets fuera del MVP
// MVP_POSTERIOR |         require base_path('routes/channels.php');
    }
}
