<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;
use App\Model\AvailableProviders;

class CheckIdealDriverStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:idealdriverstatus';

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
        $time = Carbon::now()->subMinutes(IDEAL_TIME);
        $data = AvailableProviders::where('status','1')->where('updated_at','<',$time)->get();
        if(!empty($data) && count($data)){
            AvailableProviders::where('updated_at','<',$time)->update(['status'=>'0']);  
        }
    }
}
