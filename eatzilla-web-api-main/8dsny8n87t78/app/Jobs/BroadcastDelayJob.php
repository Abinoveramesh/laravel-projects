<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use App\Model\Foodrequest;
use App\Service\queueDriverAssign;
use Artisan;

class BroadcastDelayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request_id;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request_id , $type)
    {
        //
        $this->request_id = $request_id;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('connect BroadcastDelyJob');
        $request_id = $this->request_id;
        $type = $this->type;
        Log::info('BroadcastDelyJob request_id' . $request_id);
        Artisan::call('assign:driver',['request_id' => $request_id , 'type' => $type]);
    }
}