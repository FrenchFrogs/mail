<?php namespace FrenchFrogs\Mail;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/create_mail_table.php' => database_path('migrations/' . Carbon::now()->format('Y_m_d_His') . '_create_mail_table.php'),
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}