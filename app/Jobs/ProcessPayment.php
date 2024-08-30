<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ProcessPayment implements ShouldQueue
    //  ShouldBeUniqueUntilProcessing, ShouldBeUnique, ShouldBeEncrypted
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the unique ID for the job.
     */
    // public function uniqueId(): string
    // {
    //     return 'payments';
    // }

    /**
     * Get the number of seconds before the next attempt.
     */
    // public function uniqueFor()
    // {
    //     return 10;
    // }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // // USE THESES EXEMPLES OR THE MIDDLEWARE
        // avoid having multiple jobs processing the payment at the same time
        Cache::lock('payment')->block(5, function () {
            // put the business logic here
            sleep(1);
        });

        // only allow 5 jobs to process the payment at the same time
        Redis::funnel('payment')->limit(5)->block(5, function () {
            // put the business logic here
            sleep(1);
        });

        // 5 executions every 5 seconds with a 5-second block
        Redis::throttle('payment')->allow(5)->every(5)->block(5, function () {
            // put the business logic here
            sleep(1);
        });

        sleep(1);
    }

    /**
     * The middleware the job should pass through.
     */
    public function middleware()
    {
        // return [new WithoutOverlapping('payments', 10)];
        return [new ThrottlesExceptions(5)];
    }
}
