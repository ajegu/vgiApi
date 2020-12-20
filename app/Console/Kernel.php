<?php

namespace App\Console;

use App\Console\Commands\BucketCreate;
use App\Console\Commands\BucketDelete;
use App\Console\Commands\DatabaseCreate;
use App\Console\Commands\DatabaseDrop;
use App\Console\Commands\DatabasePurge;
use App\Console\Commands\DatabaseSeed;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DatabasePurge::class,
        DatabaseSeed::class,
        DatabaseDrop::class,
        DatabaseCreate::class,
        BucketCreate::class,
        BucketDelete::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
