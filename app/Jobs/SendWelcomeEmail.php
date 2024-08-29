<?php

namespace App\Jobs;

use DateTime;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWelcomeEmail implements ShouldQueue
{
    use Batchable, Queueable;

    // timeout before the job is considered failed
    // public $timeout = 1;

    // number of times the job will be attempted
    public $tries = 3;

    // number of seconds to wait before retrying the job
    // public $backoff = 3;

    // wait 2 seconds before the first retry, then 10 seconds before the second retry, and 20 seconds before the third retry
    // public $backoff = [2, 10, 20];

    // maximum number of exceptions before the job is marked as failed
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // check if the batch has been cancelled
        // put the business logic after this check
        if ($this->batch()->cancelled()) {
            return;
        }

        // throw new \Exception('Failed to send welcome email');
        sleep(1);

        // release the job back onto the queue in 5 seconds
        // return $this->release(5);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    // public function retryUntil(): DateTime
    // {
    //     return now()->addMinute();
    // }

    /**
     * The job failed to process.
     */
    public function failed(Exception $exception)
    {
        info('Failed to send welcome email');
    }
}
