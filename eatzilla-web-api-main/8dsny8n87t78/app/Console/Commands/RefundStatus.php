<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\Model\Refund;
use App\Http\Controllers\api\RestaurantController;
use App\Service\RefundStatusUpdate;
class RefundStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refund:status';

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
        Log::info('connect-Refund Cron');
        $refunds = Refund::where('refund_status',0)->get();
         foreach ($refunds as $refund){
          Log::info($refund);
          $RefundStatusUpdate = new RefundStatusUpdate;
          $RefundStatusUpdate->refundStatusview($refund->request_id);
        }
    }
}