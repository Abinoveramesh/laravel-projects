<?php
namespace App\Service;
use App\Model\Trackorderstatus;
use App\Model\CurrentRequest;
use App\Model\NewRequest;
use App\Model\Foodrequest;
use App\Model\Deliverypartners;
use GuzzleHttp\Client;
use Log;

class queueDriverAssign {

    /**
    * broadcast Assign Single Rider
    *
    * @param int $request_id , $rider_id
    *
    * @return json $response
    */
    static function queueDriverOrderAssign($request_id , $rider_id) {
        Log::info('connect queueDriverOrderAssign :::');
        Foodrequest::where('id',$request_id)->update(['temp_drivers'=>$rider_id,'status'=>2]);
        $orderDetails = Foodrequest::where('id',$request_id)->first();
        $trackorderstatus = new Trackorderstatus();
        $trackorderstatus->request_id = $request_id;
        $trackorderstatus->status = 2;
        $trackorderstatus->detail = "Food is being prepared";
        $trackorderstatus->save();

        CurrentRequest::where('request_id',$request_id)
            ->update(['request_id' => $request_id , 'user_id' => $orderDetails->user_id , 'provider_id' => $rider_id , 'status' => '2']);

        $provider_id = $rider_id;
        $client = new Client();
        $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);

        // to insert into mongodb

        $checkNewRequest = NewRequest::where('request_id' , $request_id)->where('provider_id',$provider_id)->first();
        if($checkNewRequest == null)
        {
            $newRequest = new NewRequest();
            $newRequest->request_id = (string)$request_id;
            $newRequest->user_id = (string)$orderDetails->user_id;
            $newRequest->provider_id = (string)$provider_id;
            $newRequest->status = "1";        
            $newRequest->save();
        }
        $client->get(SOCKET_URL.'/new_request_status/'.$request_id.'/'.$provider_id);

        //send push notification to user
        $provider = Deliverypartners::where('id',(int)$provider_id)->first();
        if(isset($provider->device_token) && $provider->device_token!='')
        {
            Log::info('connect_push_notification');
            $title = $message = trans('constants.new_order');
            $data = array(
                'device_token' => $provider->device_token,
                'device_type' => $provider->device_type,
                'title' => $title,
                'message' => $message,
                'request_id' => $request_id,
                'delivery_type' => $orderDetails->delivery_type
            );

            $params = $data;
            $url = 'https://fcm.googleapis.com/fcm/send';
            $fields=array();
            if($params['device_type'] == 'android'){
                $fields = array(
                    'registration_ids' => array(
                        $params['device_token']
                    ),
                    'data' => array(
                        "title" => isset($params['title'])?$params['title']:"",
                        "message" => isset($params['message'])?$params['message']:"",
                        'request_id' => isset($params['request_id'])?$params['request_id']:"",
                        'delivery_type' => isset($params['delivery_type'])?$params['delivery_type']:"",
                        'image' => isset($params['image'])?$params['image']:"",
                    )
                );
            }
            if($params['device_type'] == 'ios'){
                $fields = array(
                    'to' => $params['device_token'],
                    'mutable_content'=>true,
                    'content_available'=>true,
                    'notification' => array(
                            "title" => isset($params['title'])?$params['title']:"",
                            "text" => isset($params['message'])?$params['message']:"",
                            "body" => isset($params['message'])?$params['message']:"",
                            'request_id' => isset($params['request_id'])?$params['request_id']:"",
                            'delivery_type' => isset($params['delivery_type'])?$params['delivery_type']:"",
                            'sound' => 'sound.mp3',
                    ),
                    'data'=>array(
                        'request_id' => isset($params['request_id'])?$params['request_id']:""
                    ),
                    "apns" => array("headers" => array("apns-priority"=>"10")),
                    "webpush" => array("headers" => array("Urgency" => "high")),
                    "android" => array("priority"=>"high"),
                    "priority" => 10
                );
            }
            $fields = json_encode($fields);
            $headers = array(
                'Authorization: key= '.USER_NOTIFICATION_KEY,
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

            Log::info('push notification result :'.$result);
        }
    }

    /**
    * broadcast AssignDriver
    *
    * @param int $request_id
    *
    * @return json $response
    */
    static function broadCastAssignDriver($request_id) {
        $orderDetails = Foodrequest::where('id',$request_id)->first();
        $get_queue_drivers = json_decode($orderDetails->queue_drivers);
        if(!empty($get_queue_drivers)) {
            foreach($get_queue_drivers as $queue_driver) {
                Log::info('connect broadcastTrigger else driver: ' . $queue_driver);
                $delivery_boy_id = $queue_driver; 
                $checkRiderAvailability = Foodrequest::where(function($query) use($delivery_boy_id){
                    $query->where('delivery_boy_id',$delivery_boy_id)->orWhere(function($query) use($delivery_boy_id){
                        $query->where('delivery_boy_id',0)->where('temp_drivers',$delivery_boy_id);
                    });
                })->whereIn('status', [1,2,3,4,5,6,8])->get();
                if(count($checkRiderAvailability) == 0) {
                    Log::info('connect broadcastTrigger else part driver if checkRiderAvailability: ' . count($checkRiderAvailability));
                    Log::info('connect broadcastTrigger else part driver id: ' . $queue_driver);
                    if($orderDetails->delivery_boy_id == 0 && empty($orderDetails->temp_drivers)) {
                        queueDriverAssign::queueDriverOrderAssign($request_id,$queue_driver);
                        break;
                    }
                }else {
                    Log::info('connect broadcastTrigger else part driver else checkRiderAvailability: ' . count($checkRiderAvailability));
                    $first_element = array_shift($get_queue_drivers);
                    $get_queue_drivers[count($get_queue_drivers) + 1] = $first_element;
                    $remaining_drivers = array_values($get_queue_drivers);
                    $remaining_drivers_list = json_encode($remaining_drivers);
                    Foodrequest::where('id',$request_id)->update(['queue_drivers' => $remaining_drivers_list]);
                }
            }
        }
    }

}