<?php

namespace App\Providers;

use App\Services\BankEmailParsers\BankEmailParserManager;
use App\Services\BankEmailParsers\GenericBankParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BankEmailParserManager::class, function ($app) {
            $manager = new BankEmailParserManager($app->make(GenericBankParser::class));

            // Register bank-specific parsers here as they are implemented:
            // $manager->register($app->make(AlRajhiParser::class));
            // $manager->register($app->make(SnbParser::class));

            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
