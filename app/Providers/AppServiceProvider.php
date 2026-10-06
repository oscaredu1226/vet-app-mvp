<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // BOT_SEPARADO: la IA se ejecuta en el proyecto bot-telegram.
        // $this->app->bind(\App\Services\Telegram\ConversationAi::class, \App\Services\Telegram\OpenAiAssistant::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
