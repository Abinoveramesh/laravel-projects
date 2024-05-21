<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\AvailableProviders;
use App\Model\Deliverypartners;
use App\Model\Addzone;
use App\Library\pointLocation;
use Log;

class DriverZoneAlertNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:driverzone';

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
        try{
            $data = AvailableProviders::where('status','!=','0')->get();        
            Log::info('zone alert driver count:'.count($data));
            if(!empty($data)){
                $riderId = array();
                $device_token = array();
                foreach($data as $key => $value){
                    $lat = $value->lat;
                    $lng = $value->lng;
                    $driver_data = Deliverypartners::where('id',$value->provider_id)->select(['id','zone_id','device_type','device_token'])->first();			                
                    if(!empty($driver_data)){
                        $zone_data = Addzone::with(['zone_geofencing'])->select('id')->find($driver_data->zone_id);                  
                        if(!empty($zone_data->zone_geofencing->polygons)){
                            $polygon = json_decode($zone_data->zone_geofencing->polygons);
                            $point = array($lng, $lat);
                            $pointLocation = new pointLocation();
                            $is_avail = 0;
                            $is_avail = $pointLocation->pointInPolygon($point, $polygon[0][0]);
                            if ($is_avail != 1) {
                                if (isset($driver_data->device_token) && $driver_data->device_token != '' && isset($driver_data->device_type) && $driver_data->device_type != '') {
                                    $riderId[] = $driver_data->id;
                                    if ($driver_data->device_type == 'android') {
                                        $fields = array(
                                            'registration_ids' => array(
                                                $driver_data->device_token
                                            ),
                                            'data' => array(
                                                "title" => "Delivery zone alert",
                                                "message" => "Dear Rider, Your are out of your delivery zone, kindly back to your zone for to receice more delivery",
                                                'request_id' => "",
                                                'delivery_type' => "",
                                                'image' => '',
                                            )
                                        );
                                    }
                                    if ($driver_data->device_type == 'ios') {
                                        $fields = array(
                                            'to' => $driver_data->device_token,
                                            'mutable_content'=>true,
                                            'content_available'=>true,
                                            'notification' => array(
                                                "title" => "Delivery zone alert",
                                                "text" => "Dear Rider, Your are out of your delivery zone, kindly back to your zone for to receive more delivery!",
                                                "body" => "Dear Rider, Your are out of your delivery zone, kindly back to your zone for to receive more delivery!",
                                                'request_id' => "",
                                                'delivery_type' => "",
                                                'image' => '',
                                                'sound' => 'sound.mp3',
                                            ),
                                            'data' => array(
                                                'request_id' => ""
                                            ),
                                            'apns' => array("headers" => array("apns-priority"=>"10")),
                                            'webpush' => array("headers" => array("Urgency" => "high")),
                                            'android' => array("priority"=>"high"),
                                            'priority' => 10
                                        );
                                    }
                                    $fields = json_encode($fields);
                                    $headers = array(
                                        'Authorization: key= ' . USER_NOTIFICATION_KEY,
                                        'Content-Type: application/json'
                                    );
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                                    $result = curl_exec($ch);
                                    curl_close($ch);
                                }	
                            }
                        }				
                    }           
                }
                if(!empty($riderId) && count($riderId)!=0){
                    $riderIdData = implode(",",$riderId);
                    $url = NOTIFICATION_URL.'notify-driver-zone?riderid='.$riderIdData;
                    $curl = curl_init();               
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    $data = curl_exec($curl);
                    curl_close($curl);
                }
            }
        }catch(\Exception $e){
            Log::error($e);
        }        
    }
}
