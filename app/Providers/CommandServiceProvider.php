<?php


namespace App\Providers;


use App\Console\Commands\BucketCreate;
use App\Console\Commands\BucketDelete;
use App\Console\Commands\DatabaseCreate;
use App\Console\Commands\DatabaseDrop;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->when(DatabaseDrop::class)
            ->needs('$tableName')
            ->give(env('AWS_DYNAMODB_TABLE'));

        $this->app->when(DatabaseCreate::class)
            ->needs('$tableName')
            ->give(env('AWS_DYNAMODB_TABLE'));

        $this->app->when(BucketCreate::class)
            ->needs('$bucketName')
            ->give(env('AWS_BUCKET_NAME'));

        $this->app->when(BucketDelete::class)
            ->needs('$bucketName')
            ->give(env('AWS_BUCKET_NAME'));
    }
}
