<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Model\Foodrequest;
use Log;

class NotifyIdealOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:idealorder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('ideal order');
        $time = Carbon::now()->subHour();
        $ideal_order = Foodrequest::where('order_accepted_type',1)->where('status',1)->where('updated_at','<',$time)->get();
        if(count($ideal_order)!=0){
            $url = NOTIFICATION_URL.'notify-ideal-order';
            $curl = curl_init();               
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = curl_exec($curl);
            curl_close($curl);
        }
        Log::info('ideal order count:'.count($ideal_order));
    }
}
