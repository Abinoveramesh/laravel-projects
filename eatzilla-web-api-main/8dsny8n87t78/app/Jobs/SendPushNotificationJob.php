<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Model\Users;
use App\Model\Deliverypartners;
use Log;
use DB;
use App\Model\Foodrequest;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $title;
    private $message;
    private $image;
    private $sendto;
    private $user_id;
    private $driver_id;
    private $status;
    private $customstartdate;
    private $customenddate;
    private $city;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($title, $message, $image,$sendto,$user_id,$driver_id,$status,$customstartdate,$customenddate,$city)
    {
        
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
        $this->sendto = $sendto;
        $this->user_id = $user_id;
        $this->driver_id = $driver_id;
        $this->status = $status;
        $this->customenddate = $customenddate;
        $this->customstartdate = $customstartdate;
        $this->city=$city;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id = $this->user_id;
        $driver_id = $this->driver_id;
        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification_key = USER_NOTIFICATION_KEY;
        $fields = array();
        $user = new Users();
        $provider = new Deliverypartners();
        if($this->sendto == 'ALL' || $this->sendto == 'USERS'){
            if(!empty($user_id) && count($user_id)){
                $user = $user->whereIn('users.id',$user_id)
                ->where('device_token','!=',NULL)
                ->where('device_token','!=','')
                ->where('users.device_type','!=','')
                ->where('users.device_type','!=',NULL)
                ->select('users.id','device_token','users.device_type');    
            }else{
                $user_id = Foodrequest::whereBetween('ordered_time',[$this->customstartdate." 00:00:00",$this->customenddate." 23:59:59"])->select('user_id')->groupBy('user_id')->get()->pluck('user_id')->toArray();
                $user = $user
                ->join('requests','requests.user_id','=','users.id','left')
                ->where('device_token','!=',NULL)
                ->where('device_token','!=','')
                ->where('users.device_type','!=','')
                ->where('users.device_type','!=',NULL);
                if(!empty($this->city)){
                    $user = $user->where('users.state_id',$this->city);
                }
                $user = $user->select('users.id','device_token','users.device_type',DB::raw("count(requests.id) as request_count"),'ordered_time','users.state_id');
                if(!empty($this->status)){
                    switch($this->status){
                        case '1':
                            if(!empty($this->customstartdate) && $this->customstartdate!="" && count($user_id)!=0){
                                $user = $user->whereIn('users.id',$user_id);
                            }else{
                                $user = $user->having('request_count','>',0);    
                            }
                        break;
                        case '2':
                            if(!empty($this->customstartdate) && $this->customstartdate!="" && count($user_id)!=0){
                                $user = $user->whereNotIn('users.id',$user_id);
                            }else{
                                $user = $user->having('request_count','=',0);    
                            }
                            
                        break;
                        default:
                            $user = $user;
                    }
                }
            }            
        }
        if($this->sendto == 'ALL' || $this->sendto == 'PROVIDERS'){
            if(!empty($driver_id) && count($driver_id)){
                $provider = $provider->whereIn('delivery_partners.id',$driver_id)
                ->where('device_token','!=',NULL)
                ->where('device_token','!=','')
                ->where('delivery_partners.device_type','!=','')
                ->where('delivery_partners.device_type','!=',NULL)
                ->select('delivery_partners.id','device_token','delivery_partners.device_type');    
            }else{
                $driver_id = Foodrequest::whereBetween('ordered_time',[$this->customstartdate." 00:00:00",$this->customenddate." 23:59:59"])->where('delivery_boy_id','!=','')->select('delivery_boy_id')->groupBy('delivery_boy_id')->get()->pluck('delivery_boy_id')->toArray();
                $provider = $provider
                ->join('requests','requests.delivery_boy_id','=','delivery_partners.id','left')
                ->where('device_token','!=',NULL)
                ->where('device_token','!=','')
                ->where('delivery_partners.device_type','!=','')
                ->where('delivery_partners.device_type','!=',NULL);
                if(!empty($this->city)){
                    $provider = $provider->where('delivery_partners.city_id',$this->city);
                }
                $provider = $provider->select('delivery_partners.id','device_token','delivery_partners.device_type',DB::raw("count(requests.id) as request_count"),'ordered_time');
                if(!empty($this->status)){
                    switch($this->status){
                        case '1':
                            if(!empty($this->customstartdate) && $this->customstartdate!="" && count($driver_id)!=0){
                                $provider = $provider->whereIn('delivery_partners.id',$driver_id);
                            }else{
                                $provider = $provider->having('request_count','>',0);    
                            }
                        break;
                        case '2':
                            if(!empty($this->customstartdate) && $this->customstartdate!="" && count($driver_id)!=0){
                                $provider = $provider->whereNotIn('delivery_partners.id',$driver_id);
                            }else{
                                $provider = $provider->having('request_count','=',0);    
                            }
                            
                        break;
                        default:
                            $provider = $provider;
                    }
                }
            }    
        }
        switch($this->sendto){
            case 'USERS':
                $device_token_data = $user->groupBy('users.id')->get()->toArray();
                Log::info('USERS:'.count($device_token_data));
            break;
            case 'PROVIDERS':
                $device_token_data = $provider->groupBy('delivery_partners.id')->get()->toArray();
                Log::info('RIDER:'.count($device_token_data));
            break;
            default:
                $user = $user->groupBy('users.id')->get()->toArray();
                $provider = $provider->groupBy('delivery_partners.id')->get()->toArray();
                $device_token_data = array_merge($user,$provider);
                Log::info('USERS And PROVIDERS:'.count($device_token_data));    
        }        
        if(!empty($device_token_data) && count($device_token_data)){
            foreach($device_token_data as $key => $value){
                if (isset($value['device_token']) && $value['device_token'] != '' && isset($value['device_type']) && $value['device_type'] != '') {
                    if ($value['device_type'] == 'android') {
                        $fields = array(
                            'registration_ids' => array(
                                $value['device_token']
                            ),
                            'data' => array(
                                "title" => isset($this->title) ? $this->title : "",
                                "message" => isset($this->message) ? $this->message : "",
                                'request_id' => "",
                                'delivery_type' => "",
                                'image' => isset($this->image) ? $this->image : "",
                            )
                        );
                    }
                    if ($value['device_type'] == 'ios') {
                        $fields = array(
                            'to' => $value['device_token'],
                            'mutable_content'=>true,
                            'content_available'=>true,
                            'notification' => array(
                                "title" => isset($this->title) ? $this->title : "",
                                "text" => isset($this->message) ? $this->message : "",
                                "body" => isset($this->message) ? $this->message : "",
                                'request_id' => "",
                                'delivery_type' => "",
                                'image' => isset($this->image) ? $this->image : "",
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
                        'Authorization: key= ' . $notification_key,
                        'Content-Type: application/json'
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    Log::info('admin push notification result :'.$result);
                }                
            }    
        }else{
            Log::info('admin push notification not send');
        }        
    }
}
