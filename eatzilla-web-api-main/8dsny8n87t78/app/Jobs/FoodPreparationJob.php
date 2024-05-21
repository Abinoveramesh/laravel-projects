<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Model\Foodlist;
use App\Model\Foodrequest;
use Artisan;
use Log;

class FoodPreparationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $foodId;
    protected $foodQty;
    protected $requestId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($foodId , $foodQty , $requestId)
    {
        $this->foodId = $foodId;
        $this->foodQty = $foodQty;
        $this->requestId = $requestId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $foodId = $this->foodId;
        $foodQty = $this->foodQty;
        $requestId = $this->requestId;

        $foodItems = $foodId;
        $foodCount = count($foodItems);

        $existData = [];
        
        foreach($foodItems as $key => $food) {
            $getData = Foodlist::where('id',$food)->first();
            if(!empty($getData)) {
                array_push($existData , $getData->food_preparation_time);
            }
        }

        $high_food_preparation_time = max($existData);
        $checkTime = 0;
        switch ($foodCount) {
            case $foodCount <= 3:
                $checkTime = $high_food_preparation_time * 1.5;
            break;
            case $foodCount == 4 || $foodCount == 5:
                $checkTime = $high_food_preparation_time * 2;
            break;
            case $foodCount == 6 || $foodCount == 7:
                $checkTime = $high_food_preparation_time * 2.5;
            break;
            case $foodCount > 7:
                $checkTime = $high_food_preparation_time * 3;
            break;
        }

        $totalValue = 0;
        info('foodId ' , [$foodId]);
        info('foodQty ' , [$foodQty]);
        foreach ($foodId as $key => $value) {
            $record = Foodlist::where('id',$value)->select('id','name','bulk_cut_off','bulk_quantity_set')->first();
            if(!empty($record->bulk_cut_off) && !empty($record->bulk_quantity_set)) {
                info('food Name '.$record->name .' foodQty '. $foodQty[$key] . ' -- foodId --'. $foodId[$key] . ' - food bulk_cut_off ' . $record->bulk_cut_off);
                if($record->bulk_cut_off < $foodQty[$key]) {
                    $bulkTime = (int)$foodQty[$key] - (int)$record->bulk_cut_off;
                    $bulkTime = $bulkTime / (int)$record->bulk_quantity_set;
                    $totalValue += round($bulkTime);
                    info('bulkTime '.round($bulkTime));
                }
            }
        }
        Log::info('bulkTime value :: ' . $totalValue);
        $bulkMultiplierValue = $totalValue * 5;
        Log::info('bulkMultiplierValue :: ' . $bulkMultiplierValue);
        Log::info('high food price :: ' . $checkTime);
        $totalPreparationTime = $checkTime + $bulkMultiplierValue;
        Log::info('order foodPreparationTime :: ' . round($totalPreparationTime));
        Foodrequest::where('id',$requestId)->update(['food_preparation_time' => round($totalPreparationTime)]);

        // Artisan::call('assign:driver',['request_id' => $requestId]);
    }
}