<?php

namespace App\Providers;

use App\Database\ClientAdapter;
use Aws\DynamoDb\DynamoDbClient;
use Aws\S3\S3Client;
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
        $this->registerDynamoDB();
        $this->registerS3();
    }

    private function registerDynamoDB(): void
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

    private function registerS3(): void
    {
        $this->app->singleton(S3Client::class, function() {
            return new S3Client([
                'version' => 'latest',
                'region' => env('AWS_REGION')
            ]);
        });

        $this->app->when(\App\Storage\ClientAdapter::class)
            ->needs('$bucketName')
            ->give(env('AWS_BUCKET_NAME'));
    }
}
