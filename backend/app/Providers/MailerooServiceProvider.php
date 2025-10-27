<?php

namespace App\Providers;

use App\Mail\MailerooTransport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class MailerooServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Mail::extend('maileroo', function (array $config) {
            return new MailerooTransport($config['api_key']);
        });
    }
}
