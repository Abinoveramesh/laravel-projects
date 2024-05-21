<?php

namespace App\Jobs;

use App\Mail\OrderComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;


class OrderCompleteMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestCompleted;
    protected $restaurantDetail;
    protected $userDetails;
    protected $foodDetail;
    protected $invoiceAmount;
    public $timeout = 0;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requestCompleted,$restaurantDetail,$userDetails,$foodDetail,$invoiceAmount)
    {
        $this->requestCompleted = $requestCompleted;
        $this->restaurantDetail = $restaurantDetail;
        $this->userDetails = $userDetails;
        $this->foodDetail = $foodDetail;
        $this->invoiceAmount = $invoiceAmount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $requestDetails = $this->requestCompleted;
        $restaurantDetail = $this->restaurantDetail;
        $userDetails = $this->userDetails;
        $foodDetails = $this->foodDetail;
        $invoiceAmount = $this->invoiceAmount;
        $getInvoicePdf = view('order.get-invoice' , compact('requestDetails','restaurantDetail','userDetails','foodDetails','invoiceAmount'))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($getInvoicePdf)->setOptions(['dpi' => 200, 'defaultFont' => 'sans-serif', 'chroot' => public_path()])->setPaper('A4');
        $pdf->stream();
        Mail::to($this->userDetails->email)->send(new OrderComplete($this->requestCompleted, $this->restaurantDetail,$this->userDetails,$this->foodDetail,$pdf));
    }
}
