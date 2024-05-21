<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
     
    protected $sender_email;
    protected $sender_name;
    protected $subject;
    protected $requestdata;
    protected $type;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sender_email , $sender_name , $subject,$requestdata, $type='')
    {
        //
        $this->sender_email = $sender_email;
        $this->sender_name = $sender_name;
        $this->subject = $subject;
        $this->requestdata = $requestdata;
        $this->type = $type;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $sender_email = $this->sender_email;
        $sender_name = $this->sender_name;
        $subject = $this->subject;
        $requestdata = $this->requestdata;
        $type = $this->type;
        Mail::send('email.'.$type, array('data'=>$requestdata), function($message) use($sender_email, $subject, $sender_name){
            $message->to($sender_email, $sender_name)->subject
                ($subject);
            $message->from(EMAIL_USER_NAME,APP_NAME);
        });
    }
}
