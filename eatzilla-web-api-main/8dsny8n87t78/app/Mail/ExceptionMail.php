<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $html;
    protected $apiPath;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($html,$apiPath)
    {
        $this->html = $html;
        $this->apiPath = $apiPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->html;
        $apiPath = $this->apiPath;
        return $this->subject(url('/'))->view('email/error_log',compact('data','apiPath'));
    }
}
