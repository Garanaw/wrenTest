<?php

namespace App\Providers;

use App\Console\Commands\ProductBulkImporter;
use App\Services\Parser\CsvParser;
use App\Services\Parser\Parser;
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
        $this->app->when(ProductBulkImporter::class)
            ->needs(Parser::class)
            ->give(CsvParser::class);
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
