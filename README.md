# Laravel Queues

Execute those commands in `tinker` or Tinkerwell to try it out!

## Dispatch and run jobs
```sh
# create a new job
php artisan make:job SendWelcomeEmail

# create a new jobs table (if not existing)
php artisan queue:table
php artisan migrate

# execute a worker
php artisan queue:work
```

```php
use App\Jobs\SendWelcomeEmail;
use App\Jobs\ProcessPayment;

// usefull methods
SendWelcomeEmail::handle(); // execte the job
SendWelcomeEmail::dispatch(); // send it in a queue
SendWelcomeEmail::dispatch()->delay(5); // execute the job after a delay of 5 seconds

// worker is processing jobs one by one
foreach(range(1, 100) as $i) {
  SendWelcomeEmail::dispatch();
}
ProcessPayment::dispatch()->onQueue('payments'); //
```

## Configure jobs

```sh
php artisan make:job ProcessPayment
# give a higher priority to a specific queue
php artisan queue:work --queue=payments,default
```

```php
// worker is processing jobs one by one
foreach(range(1, 100) as $i) {
  SendWelcomeEmail::dispatch();
}
// dispatch a job in a specific queue (to give it a higher priority for example)
ProcessPayment::dispatch()->onQueue("payments");
```

## Handle attempts & failures

```sh
# get the uuid from the failed_jobs and execute
php artisan queue:retry {uuid}
```

## Dispatch workflows

Sometimes we want to group multiple jobs together. We call that a workflow. There are:
- Chaines workflows (one job after the other)
- Batches workflows (run parrallel without depending each other)

Chain:
```php
$chain = [
 new ProcessPayment,
 new SendWelcomeEmail,
];
Bus::chain($chain)->dispatch();
```

Batch:
- add `use Batchable` in the concerning job class
- create the `job_batches` table (if not existing)
```php
$batch = [
  new ProcessPayment(),
  new SendWelcomeEmail()
];
Bus::batch($batch)
  ->allowFailures() // Avoid cancelling all jobs from a batch if one fail
  ->dispatch();
```

```sh
# create the job_batches table
php artisan queue:batches-table
php artisan migrate
```
