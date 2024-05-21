<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderComplete extends Mailable
{
    use Queueable, SerializesModels;

    protected $requestCompleted;
    protected $restaurantDetail;
    protected $userDetails;
    protected $foodDetail;
    protected $pdf;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requestCompleted,$restaurantDetail,$userDetails,$foodDetail,$pdf)
    {
        $this->requestCompleted = $requestCompleted;
        $this->restaurantDetail = $restaurantDetail;
        $this->userDetails = $userDetails;
        $this->foodDetail = $foodDetail;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $requestCompletedDetails = $this->requestCompleted;
        $restaurantDetail = $this->restaurantDetail;
        $userDetails = $this->userDetails;
        $foodDetails = $this->foodDetail;
        $pdfs = $this->pdf;

        return $this->subject('Truely Order Completed')
        ->view('order/order_completed',compact('requestCompletedDetails','restaurantDetail','userDetails','foodDetails'))
        ->attachData($pdfs->output(),'file.pdf',[
            'mime' => 'application/pdf',
        ]);
    }
}
