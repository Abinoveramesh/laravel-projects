<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\Model\Foodrequest;
use App\Model\AvailableProviders;
use App\Model\Deliverypartners;
use App\Model\Addzone;
use App\Library\pointLocation;
use App\Service\queueDriverAssign;
use Carbon;

class AssignDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:driver {request_id}{type}';


    protected $request_id;
    protected $type;


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
    // public function __construct($request_id)
    // {
    //     parent::__construct();
    //     $this->request_id = $request_id;
    // }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $request_id = $this->argument('request_id');
            $type = $this->argument('type');
            $orderDetails = Foodrequest::where('id',$request_id)->first();
            if($orderDetails->delivery_boy_id == 0 && $orderDetails->temp_drivers == null) {
                Log::info('Broadcast Triggered:: ' . $type);
                Log::info('request_id:: ' . $request_id);
                Log::info('request delay Type:: ' . $type);
                $foodPreparationTime = $orderDetails->food_preparation_time;
                $foodWaitingTime = $foodPreparationTime + 3;
                Log::info('foodPreparationTime:: ' . $foodPreparationTime);
                $restaurantLat = $orderDetails->Restaurants->lat;
                $restaurantLng = $orderDetails->Restaurants->lng;
                $reject_drivers = json_decode($orderDetails->reject_drivers);
                Log::info('restaurant lat:: ' . $restaurantLat);
                Log::info('restaurant lng:: ' . $restaurantLng);

                $all_zones = Addzone::select('add_zone.*', 'zone_geofencing.polygons')->leftJoin('zone_geofencing', function ($join) {
                            $join->on('zone_geofencing.zone_id', '=', 'add_zone.id');
                        })->get();

                $get_zone = '';
                foreach ($all_zones as $zone) {
                    if(!empty($zone->polygons)) {
                        $polygon = json_decode($zone->polygons);
                        $ponits = array($restaurantLng, $restaurantLat);
                        $pointLocation = new pointLocation();
                        $is_avail = $pointLocation->pointInPolygon($ponits, $polygon[0][0]);
                        if ($is_avail == 1) {
                            $get_zone = $zone;
                        }
                    }
                }

                if(!empty($get_zone)) {
                    $currenttime = Carbon\Carbon::now();
                    if(empty($reject_drivers)) {
                        $exist_reject_drivers = array(0);
                    }else {
                        $exist_reject_drivers = $reject_drivers;
                    }
                    $currentAvailableProviders = AvailableProviders::where('status','1')->whereNotIn('provider_id',$exist_reject_drivers)->select('provider_id','updated_at','check_time','lat','lng')->get();
                    $nearest_riders_travel_time = [];
                    $nearest_executive_rider_id = 0;
                    $nearest_executive_travel_time = 0;
                    $highest_ideal_time = [];
                    $highest_ideal_time_rider_id = 0;
                    $highest_ideal_time_rider_travel_time = 0;

                    $second_option_drivers = [];

                    foreach($currentAvailableProviders as $key => $provider) {
                        Log::info('currentAvailableProviders provider_id:: ' . $provider->provider_id);
                        Log::info('currentAvailableProviders lat:: ' . $provider->lat);
                        Log::info('currentAvailableProviders lng:: ' . $provider->lng);
                        $checkOrder = Foodrequest::where('delivery_boy_id' , $provider->provider_id)->whereNotIn('status',[7,10,0,1])->get();
                        $checkDriver = Deliverypartners::where('id', $provider->provider_id)->where('delivery_type',1)->where('zone_id',$get_zone->id)->where('status',1)->first();
                        if(count($checkOrder) == 0 && !empty($checkDriver)) {
                            if(!empty($get_zone->polygons)) {
                                $polygon = json_decode($get_zone->polygons);
                                $ponits = array($provider->lng, $provider->lat);
                                $pointLocation = new pointLocation();
                                $is_avail = $pointLocation->pointInPolygon($ponits, $polygon[0][0]);
                                if($is_avail == 1) {
                                    Log::info('is_zone_driver:: ' . $provider->provider_id);
                                        
                                    // Get Nearest Executive (N) Rider
                                    $getTravelTime = $this->getTravelTimeMiuntes($restaurantLat,$restaurantLng,$provider->lat,$provider->lng);
                                    if($type == 1) {
                                        if($getTravelTime <= 13) {
                                            $nearest_riders_travel_time[$key]['rider_id'] = $provider->provider_id;
                                            $nearest_riders_travel_time[$key]['travel_time_mins'] = $getTravelTime;
                                        }
                                    }else {
                                        if($getTravelTime <= $foodWaitingTime) {
                                            $nearest_riders_travel_time[$key]['rider_id'] = $provider->provider_id;
                                            $nearest_riders_travel_time[$key]['travel_time_mins'] = $getTravelTime;
                                        }
                                    }

                                    // Second Option Drivers
                                    if(!empty($getTravelTime)) {
                                        Log::info('over_all_distance for Second option available rider in mins:: ' . $getTravelTime);
                                        $second_option_drivers[$key]['rider_id'] = $provider->provider_id;
                                        $second_option_drivers[$key]['travel_time_mins'] = $getTravelTime;
                                    }

                                    // Get Highest Ideal Time Driver
                                    $lastOrderTime = Foodrequest::where('delivery_boy_id' , $provider->provider_id)->where('status',7)->orderBy('updated_at','DESC')->select('updated_at')->first();
                                    if(!empty($lastOrderTime)) {
                                        $lastOrderDate = $lastOrderTime->updated_at;
                                        $lastOrderString = $lastOrderDate->toDateTimeString();
                                        $providerDate = $provider->check_time;
                                        $providerString = $providerDate;
                                        if(strtotime($lastOrderString) > strtotime($providerString)) {
                                            $riderTime = $lastOrderTime->updated_at;
                                        }else {
                                            $riderTime = $provider->check_time;
                                        }
                                    }else {
                                        $riderTime = $provider->check_time;
                                    }
                                    $differnceDate = strtotime($currenttime->toDateTimeString()) - strtotime($riderTime);
                                    $result = $differnceDate/60;
                                    $ideal_time = round($result);
                                    Log::info('ideal Time:: ' . round($result));

                                    if($ideal_time != '') {
                                        if($type == 1) {
                                            if($getTravelTime <= 13) {
                                                $highest_ideal_time[$key]['rider_id'] = $provider->provider_id;
                                                $highest_ideal_time[$key]['ideal_time'] = $ideal_time;
                                                $highest_ideal_time[$key]['travel_time_mins'] = $getTravelTime;
                                            }
                                        }else {
                                            if($getTravelTime <= $foodWaitingTime) {
                                                $highest_ideal_time[$key]['rider_id'] = $provider->provider_id;
                                                $highest_ideal_time[$key]['ideal_time'] = $ideal_time;
                                                $highest_ideal_time[$key]['travel_time_mins'] = $getTravelTime;
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }

                    

                    if(count($nearest_riders_travel_time) != 0) {
                        $prices = array_column($nearest_riders_travel_time, 'travel_time_mins');
                        array_multisort($prices, SORT_ASC, $nearest_riders_travel_time);            
                        $nearest_executive_rider_id = $nearest_riders_travel_time[0]['rider_id'];
                        $nearest_executive_travel_time = $nearest_riders_travel_time[0]['travel_time_mins'];
                    }

                    if(count($highest_ideal_time) != 0) {
                        $prices = array_column($highest_ideal_time, 'ideal_time');
                        array_multisort($prices, SORT_DESC, $highest_ideal_time);            
                        $highest_ideal_time_rider_id = $highest_ideal_time[0]['rider_id'];
                        $highest_ideal_time_rider_travel_time = $highest_ideal_time[0]['travel_time_mins'];
                    }

                    // Priliminary Poll calculations ( i/n)
                    // n<= i+3

                    if($nearest_executive_travel_time <= $highest_ideal_time_rider_travel_time + 3) {
                        $first_poll_rider_id = $highest_ideal_time_rider_id;
                        $first_poll_travel_time = $highest_ideal_time_rider_travel_time;
                    }else {
                        $first_poll_rider_id = $nearest_executive_rider_id;
                        $first_poll_travel_time = $nearest_executive_travel_time;
                    }
                    if($first_poll_travel_time > 10) {
                        $first_poll_rider_food_waiting_time = $first_poll_travel_time - 10;
                    }else {
                        $first_poll_rider_food_waiting_time = 0;
                    }

                    // Get Busy Driver

                    $busy_riders_list = [];
                    $busy_rider_id = 0;
                    $busy_rider_travel_time = 0;
                    if(!empty($reject_drivers)) {
                        $zero = '0';
                        array_push($reject_drivers , $zero);
                        $reject_drivers = $reject_drivers;
                    }else {
                        $reject_drivers = array(0);
                    }
                    $attendingOrderDrivers = Foodrequest::whereNotIn('delivery_boy_id',$reject_drivers)->whereIn('status',[5,8])->groupBy('delivery_boy_id')->get();
                    foreach($attendingOrderDrivers as $key => $attendingOrder) {
                        Log::info('connect attending order riders id 1:: ' . $attendingOrder->delivery_boy_id);
                        $checkDriver = Deliverypartners::where('id', $attendingOrder->delivery_boy_id)->where('delivery_type',1)->where('zone_id',$get_zone->id)->where('status',1)->first();
                        if(isset($checkDriver)) {
                            Log::info('checkDriver Deliverypartners :: ' . $checkDriver->id);
                            $anotherOrderLat = $attendingOrder->d_lat;
                            $anotherOrderLng = $attendingOrder->d_lng;
                            $check = AvailableProviders::where('provider_id',(string)$attendingOrder->delivery_boy_id)->where('status','1')->first();
                            if(!empty($get_zone) && !empty($check)) {
                                if(!empty($get_zone->polygons)) {
                                    Log::info('get zone id :: ' . $get_zone->id);
                                    $ponits = array($check->lng, $check->lat);
                                    $polygon = json_decode($get_zone->polygons);
                                    $pointLocation = new pointLocation();
                                    $is_avail = $pointLocation->pointInPolygon($ponits, $polygon[0][0]);
                                    if($is_avail == 1) {
                                        Log::info('attending order riders id type 1:: ' . $check->provider_id);
                                        Log::info('attending order id :: ' . $attendingOrder->order_id);
                                        $another_order_to_restaurant_distance = $this->getTravelTimeMiuntes($restaurantLat,$restaurantLng,$anotherOrderLat,$anotherOrderLng);
                                        if(!empty($another_order_to_restaurant_distance) && !empty($check)) {
                                            Log::info('attending order riders type 2:: ' . $check->provider_id);
                                            $rider_to_another_order_distance = $this->getTravelTimeMiuntes($anotherOrderLat,$anotherOrderLng,$check->lat,$check->lng);
                                            $over_all_distance = $another_order_to_restaurant_distance + $rider_to_another_order_distance;
                                            if($type == 1) {
                                                if($over_all_distance <= 13) {
                                                    Log::info('over_all_distance for attending rider in mins:: ' . $over_all_distance);
                                                    $busy_riders_list[$key]['rider_id'] = $check->provider_id;
                                                    $busy_riders_list[$key]['travel_time_mins'] = $over_all_distance;
                                                }
                                            }else {
                                                if($over_all_distance <= $foodWaitingTime) {
                                                    Log::info('over_all_distance for attending rider in mins:: ' . $over_all_distance);
                                                    $busy_riders_list[$key]['rider_id'] = $check->provider_id;
                                                    $busy_riders_list[$key]['travel_time_mins'] = $over_all_distance;
                                                }
                                            }

                                            // Second Option Drivers
                                            if(!empty($over_all_distance)) {
                                                Log::info('over_all_distance for second option attending rider in mins:: ' . $over_all_distance);
                                                $second_option_drivers[$key]['rider_id'] = $provider->provider_id;
                                                $second_option_drivers[$key]['travel_time_mins'] = $over_all_distance;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Busy rider Poll
                    if(count($busy_riders_list) != 0) {
                        $prices = array_column($busy_riders_list, 'travel_time_mins');
                        array_multisort($prices, SORT_ASC, $busy_riders_list);            
                        $busy_rider_id = $busy_riders_list[0]['rider_id'];
                        $busy_rider_travel_time = $busy_riders_list[0]['travel_time_mins'];
                    }
                    if($busy_rider_travel_time > 10) {
                        $busy_rider_food_waiting_time = $busy_rider_travel_time - 10;
                    }else {
                        $busy_rider_food_waiting_time = 0;
                    }

                    // Final Poll calculations (b / i OR n)
                    $final_sort_rider = 0;
                    if($first_poll_rider_id != 0 && $busy_rider_id != 0) {
                        Log::info('first');
                        if($busy_rider_food_waiting_time == $first_poll_rider_food_waiting_time) {
                            Log::info('first first if');
                            Log::info('Select First (N or I) Poll Rider');
                            // go with first poll rider
                            $final_sort_rider = $first_poll_rider_id;
                        }else if($busy_rider_food_waiting_time > $first_poll_rider_food_waiting_time) {
                            Log::info('first second if');
                            Log::info('Select First (N or I) Poll Rider');
                            // go with first poll rider
                            $final_sort_rider = $first_poll_rider_id;
                        }else if($busy_rider_food_waiting_time < $first_poll_rider_food_waiting_time) {
                            Log::info('first third if');
                            Log::info('Select Busy (B) Poll Rider');
                            // go with busy rider poll
                            $final_sort_rider = $busy_rider_id;
                        }
                    }else if($first_poll_rider_id != 0) {
                        Log::info('second');
                        Log::info('Select First (N or I) Poll Rider');
                        // go with first poll rider
                        $final_sort_rider = $first_poll_rider_id;
                    }else if($busy_rider_id != 0) {
                        Log::info('third');
                        Log::info('Select Busy (B) Poll Rider');
                        // go with busy rider poll
                        $final_sort_rider = $busy_rider_id;
                    }else if($first_poll_rider_id == 0 && $busy_rider_id == 0) {
                        Log::info('fourth');
                        // Second option rider
                        if(count($second_option_drivers) != 0) {
                            Log::info('fourth first if');
                            Log::info('Select Second Option Rider');
                            $prices = array_column($second_option_drivers, 'travel_time_mins');
                            array_multisort($prices, SORT_ASC, $second_option_drivers);            
                            $final_sort_rider = $second_option_drivers[0]['rider_id'];
                            Log::info('Second Option Rider ID:: ' . $final_sort_rider);
                            Log::info('Second Option Travel Time:: ' . $second_option_drivers[0]['travel_time_mins']);
                        }
                    }
                    if($final_sort_rider != 0) {
                        Log::info('-------------');
                        Log::info('First Poll Rider ID:: ' . $first_poll_rider_id);
                        Log::info('First Poll Travel Time:: ' . $first_poll_travel_time);
                        Log::info('First Poll Food Waiting Time:: ' . $first_poll_rider_food_waiting_time);
                        Log::info('-------------');

                        Log::info('Busy Poll Rider ID:: ' . $busy_rider_id);
                        Log::info('Busy Poll Travel Time:: ' . $busy_rider_travel_time);
                        Log::info('Busy Poll Food Waiting Time:: ' . $busy_rider_food_waiting_time);
                        Log::info('-------------');

                        Log::info('Order Sort Rider ID ::' . $final_sort_rider);
                        queueDriverAssign::queueDriverOrderAssign($request_id,$final_sort_rider);
                    }
                }else {
                    Log::info('Restaurant Location did not match Any Zone Locations');
                }
            }else {
                Log::info('Broadcasting flow already trigged -- order ID ::' . $request_id);
            }
        }catch(\Exception $e){
            Log::error($e);
        }

        // Old Logic 

        // $request_id = $this->argument('request_id');
        // $orderDetails = Foodrequest::where('id',$request_id)->first();
        // $foodPreparationTime = $orderDetails->food_preparation_time;
        // Log::info('request_id new1:: ' . $request_id);
        // Log::info('foodPreparationTime new:: ' . $foodPreparationTime);
        // $restaurantLat = $orderDetails->Restaurants->lat;
        // $restaurantLng = $orderDetails->Restaurants->lng;
        // Log::info('restaurant lat new:: ' . $restaurantLat);
        // Log::info('restaurant lng new:: ' . $restaurantLng);

        // $all_zones = Addzone::select('add_zone.*', 'zone_geofencing.polygons')->leftJoin('zone_geofencing', function ($join) {
        //             $join->on('zone_geofencing.zone_id', '=', 'add_zone.id');
        //         })->get();
        // $get_zone = '';
        // foreach ($all_zones as $zone) {
        //     if(!empty($zone->polygons)) {
        //         $polygon = json_decode($zone->polygons);
        //         $ponits = array($restaurantLng, $restaurantLat);
        //         $is_avail = $this->contains($ponits, $polygon[0]);
        //         if ($is_avail == 1) {
        //             $get_zone = $zone;
        //         }
        //     }
        // }

        // // Get highest idle time for available riders
        // $currenttime = Carbon\Carbon::now();
        // $currentAvailableProviders = AvailableProviders::where('status','1')->select('provider_id','updated_at','check_time','lat','lng')->get();
        // $drivers = [];
        // $is_zone_driver = 0;
        // foreach($currentAvailableProviders as $key => $provider) {
        //     if(!empty($get_zone)) {
        //         if(!empty($zone->polygons)) {
        //             $polygon = json_decode($get_zone->polygons);
        //             $ponits = array($provider->lng, $provider->lat);
        //             $is_avail = $this->contains($ponits, $polygon[0]);
        //             if ($is_avail == 1) {
        //                 Log::info('is_zone_driver:: ' . $provider->provider_id);
        //                 $is_zone_driver = 1;
        //             }
        //         }
        //     }
        //     $checkOrder = Foodrequest::where('delivery_boy_id' , $provider->provider_id)->whereNotIn('status',[7,10,0,1])->get();
        //     if(count($checkOrder) == 0 && $is_zone_driver == 1) {
        //         $checkDriver = Deliverypartners::where('id', $provider->provider_id)->where('delivery_type',1)->where('status',1)->first();
        //         if(isset($checkDriver)) {
        //             $lastOrderTime = Foodrequest::where('delivery_boy_id' , $provider->provider_id)->where('status',7)->orderBy('updated_at','DESC')->select('updated_at')->first();
        //             if(!empty($lastOrderTime)) {
        //                 $lastOrderDate = $lastOrderTime->updated_at;
        //                 $lastOrderString = $lastOrderDate->toDateTimeString();
        //                 $providerDate = $provider->check_time;
        //                 $providerString = $providerDate;
        //                 if(strtotime($lastOrderString) > strtotime($providerString)) {
        //                     $riderTime = $lastOrderTime->updated_at;
        //                 }else {
        //                     $riderTime = $provider->check_time;
        //                 }
        //             }else {
        //                 $riderTime = $provider->check_time;
        //             }

        //             $DiffernceDate = strtotime($currenttime->toDateTimeString()) - strtotime($riderTime);
        //             $result = $DiffernceDate/60;
        //             $provider->idle_time_mins = round($result);
        //             $provider->Z = $foodPreparationTime - round($result);



        //             $provider->travel_time_mins = '';
        //             $provider->Y = '';
        //             Log::info('rider new:: ' . $provider->provider_id);
                    
        //             $riderLat = $provider->lat;
        //             $riderLng = $provider->lng;
        //             Log::info('rider lat new:: ' . $riderLat);
        //             Log::info('rider lng new:: ' . $riderLng);

        //                 // Get distance and time using google map distance matrix

        //             $q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$restaurantLat,$restaurantLng&destinations=$riderLat,$riderLng&mode=driving&sensor=false&key=".GOOGLE_API_KEY;
        //             $json = file_get_contents($q);
        //             $details = json_decode($json, TRUE);

        //             if(isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS'){
        //                 $current_distance_with_unit = $details['rows'][0]['elements'][0]['distance']['text'];
        //                 $current_distance_km = $details['rows'][0]['elements'][0]['distance']['text'];
        //                 $travel_time_text = $details['rows'][0]['elements'][0]['duration']['text'];
        //                 $travel_time_sec = $details['rows'][0]['elements'][0]['duration']['value'];
        //                 $unit = str_after($current_distance_with_unit, ' ');
        //                 $current_distance = str_replace($unit ,'',$current_distance_km);
        //                 if(strpos($current_distance, ',') !== false)
        //                 {
        //                     $current_distance = str_replace(',' ,'',$current_distance);
        //                 }
        //                 // if($current_distance <= 5) {
        //                     $provider->travel_time_mins = round(($travel_time_sec)/60);
        //                     $provider->Y = $foodPreparationTime - round(($travel_time_sec)/60);
        //                 // }
        //             }

        //             Log::info('idle_time_mins new:: ' . $provider->idle_time_mins);
        //             Log::info('travel_time_mins new:: ' . $provider->travel_time_mins);

        //             $provider->food_waiting_time = '';
        //             if(!empty($provider->Z) && !empty($provider->Y)) {
        //                 $provider->food_waiting_time = $provider->Z - $provider->Y;
        //             }

        //             Log::info('provider Z new:: ' . $provider->Z);
        //             Log::info('provider Y new:: ' . $provider->Y);
        //             Log::info('provider food_waiting_time new:: ' . $provider->food_waiting_time);

        //             $provider->eligible_status = 0;
        //             if(!empty($provider->food_waiting_time) && $provider->food_waiting_time <= 3) {
        //                 $provider->eligible_status = 1;
        //             }
        //             Log::info('provider eligible_status new:: ' . $provider->eligible_status);
                
        //             if($provider->eligible_status == 1) {
        //                 $drivers[$key]['provider_id'] = $provider->provider_id;
        //                 $drivers[$key]['travel_time_mins'] = $provider->travel_time_mins;
        //                 $drivers[$key]['idle_time_mins'] = $provider->idle_time_mins;
        //                 $drivers[$key]['type'] = 1;
        //                 // n<= i+3
        //                 $check = $provider->idle_time_mins + 3;
        //                 if($provider->travel_time_mins <= $check) {
        //                     $drivers[$key]['calculate_time'] = $provider->idle_time_mins;
        //                 }else {
        //                     $drivers[$key]['calculate_time'] = $provider->travel_time_mins;
        //                 }
        //                 Log::info('final Driver idle_time_mins new:: ' . $drivers[$key]['idle_time_mins']);
        //                 Log::info('final Driver travel_time_mins new:: ' . $drivers[$key]['travel_time_mins']);
        //             }
        //         }
        //     }
        // }

        // // get attending order riders
        // $attendingOrderDrivers = Foodrequest::where('delivery_boy_id','!=',0)->whereNotIn('status',[7,10,0,1])->groupBy('delivery_boy_id')->get();
        // foreach($attendingOrderDrivers as $key => $attendingOrder) {
        //     $checkDriver = Deliverypartners::where('id', $attendingOrder->delivery_boy_id)->where('delivery_type',1)->where('status',1)->first();
        //     if(isset($checkDriver)) {
        //         $anotherOrderLat = $attendingOrder->d_lat;
        //         $anotherOrderLng = $attendingOrder->d_lng;
        //         $type = 1;
        //         $check = AvailableProviders::where('provider_id',(string)$attendingOrder->delivery_boy_id)->where('status','1')->first();
        //         if(!empty($get_zone) && !empty($check)) {
        //             if(!empty($zone->polygons)) {
        //                 $polygon = json_decode($get_zone->polygons);
        //                 $ponits = array($check->lng, $check->lat);
        //                 $is_avail = $this->contains($ponits, $polygon[0]);
        //                 if ($is_avail == 1) {
        //                     $is_zone_driver = 1;
        //                 }
        //             }
        //             Log::info('attending order riders id type 1:: ' . $check->provider_id);
        //             Log::info('attending order id :: ' . $attendingOrder->order_id);
        //             $another_order_to_restaurant_distance = $this->getDistance($restaurantLat,$restaurantLng,$anotherOrderLat,$anotherOrderLng,$type);
        //             if(!empty($another_order_to_restaurant_distance) && !empty($check) && $is_zone_driver == 1) {
        //                 $type = 2;
        //                 Log::info('attending order riders type 2:: ' . $check->provider_id);
        //                 $rider_to_another_order_distance = $this->getDistance($anotherOrderLat,$anotherOrderLng,$check->lat,$check->lng,$type);
        //                 $over_all_distance = $another_order_to_restaurant_distance + $rider_to_another_order_distance;
        //                 Log::info('over_all_distance for attending rider in mins:: ' . $over_all_distance);
        //                 if($over_all_distance < $foodPreparationTime) {
        //                     $drivers[$key]['type'] = 2;
        //                     $drivers[$key]['provider_id'] = $check->provider_id;
        //                     $drivers[$key]['calculate_time'] = $over_all_distance;
        //                 }
        //             }
        //         }
        //     }
        // }

        // $collection = collect($drivers);
        // $collection = $collection->sortBy('calculate_time');
        // $empty = [];
        // foreach($collection as $value) {
        //     Log::info('calculate_time new:: ' . $value['calculate_time']);
        //     Log::info('queue drivers new:: ' . $value['provider_id']);
        //     array_push($empty , $value['provider_id']);
        // }
        // Foodrequest::where('id',$request_id)->update(['queue_drivers' => json_encode($empty)]);
        // $final_data = Foodrequest::where('id',$request_id)->first();
        // if(isset($final_data)) {
        //     Log::info('final dataa:: ' . $final_data);
        // }else {
        //     Log::info('final dataa:: ' . 'empty');
        // }
    }
    
    private function getTravelTimeMiuntes($restaurantLat,$restaurantLng,$driverLat,$driverLng) {
        Log::info('connect getTravelTimeMiuntes');
        $q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$restaurantLat,$restaurantLng&destinations=$driverLat,$driverLng&mode=driving&sensor=false&key=".GOOGLE_API_KEY;
        $json = file_get_contents($q);
        $details = json_decode($json, TRUE);
        $get_distance = '';
        if(isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS'){
            $travel_time_sec = $details['rows'][0]['elements'][0]['duration']['value'];
            Log::info('GEt Distance(travel Time) in Mins' . round(($travel_time_sec)/60));
            $get_distance = round(($travel_time_sec)/60);
        }
        return $get_distance;
    }
}