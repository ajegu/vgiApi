<?php

namespace App\Providers;

use App\Database\ClientAdapter;
use Aws\DynamoDb\DynamoDbClient;
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
        $this->app->singleton(DynamoDbClient::class, function() {
            return new DynamoDbClient([
                'version' => 'latest',
                'region' => env('AWS_REGION')
            ]);
        });

        $this->app->when(ClientAdapter::class)
            ->needs('$tableName')
            ->give(env('AWS_DYNAMODB_TABLE'));
    }
}
