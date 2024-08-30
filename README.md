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
SendWelcomeEmail::handle(); // execute the job
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
**Info: It is possible to have inject a chain into a batch!**

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
  ->catch(function ($batch, $e) {
    // logic here when one of the job in batch or chain fail
  })
  ->then(function ($batch) {
    // logic here when all jobs succeed
  })
  ->finally(function ($batch) {
    //
  })
  ->dispatch();
```

```sh
# create the job_batches table
php artisan queue:batches-table
php artisan migrate
```

## Control & limit jobs

Sometimes, we want to avoid two workers to run concurrently on the same resource (ex: file system) which could cause troubles.

## Design relliable jobs

Execute a job after a transaction commits. It's also configurable from `queue.php` setting to `'after_commit' => true`
```php
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
  $user = User::create([]);
  SendWelcomeEmail::dispatch($user)->afterCommit();
});
```

It's also possible to encrypt the payload of a job using the `ShouldBeEncrypted` interface on the job class.

## Deployments

A worker should always be up and running but it may sometimes crash. That's why we should use a monitoring tool. "Supervisor" in one of the most popular (already installed when using Forge).

When deploying, we should always be carefull that all workers gracefully shutdown before deployment. Otherwise, that could lead to weird issues.
