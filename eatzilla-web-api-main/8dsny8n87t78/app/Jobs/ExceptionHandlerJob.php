<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExceptionMail;
use Log;

class ExceptionHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $html;
    protected $errorHandlerMailId;
    protected $apiPath;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($html,$errorHandlerMailId,$apiPath)
    {
        $this->html = $html;
        $this->errorHandlerMailId = $errorHandlerMailId;
        $this->apiPath = $apiPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('connect ExceptionHandlerJob');
        $html = $this->html;
        $errorHandlerMailId = $this->errorHandlerMailId;
        $apiPath = $this->apiPath;
        Mail::to($errorHandlerMailId)->send(new ExceptionMail($html,$apiPath));
    }
}