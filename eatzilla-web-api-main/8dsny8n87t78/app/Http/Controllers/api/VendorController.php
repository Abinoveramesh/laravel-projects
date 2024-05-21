<?php

namespace App\Http\Controllers\api;

use App\Service\MultiOrderAssign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Validator;
use App\Http\Controllers\api\BaseController;
use App\Service\queueDriverAssign;
use App\Model\CurrentRequest;
use App\Model\NewRequest;
use App\Model\AvailableProviders;
use App\Jobs\BroadcastDelayJob;
use App\Jobs\ImportMenuItemJob;
use Log;
use stdClass;
use App\Base\Helpers\ExceptionHandlerModel;
use Illuminate\Support\Facades\Redis;

class VendorController extends BaseController
{

    // Status code:
    // public static final String ORDER_CREATED = "0";
    // public static final String RESTAURANT_ACCEPTED = "1";
    // public static final String FOOD_PREPARED = "2";
    // public static final String DELIVERY_REQUEST_ACCEPTED = "3";
    // public static final String REACHED_RESTAURANT = "4";
    // public static final String FOOD_COLLECTED_ONWAY = "5";
    // public static final String FOOD_DELIVERED = "6";
    // public static final String ORDER_COMPLETE = "7";
    // public static final String ORDER_CANCELLED = "10";

    /**
    * vendor login check
    *
    * @param object $request
    *
    * @return json response
    */
    public function vendor_login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'email' => 'required',
                    'password' => 'required',
                    // 'device_token' => 'required',
                    'device_type' => 'required|in:' . ANDROID . ',' . IOS . ',' . WEB,
                ));
            if($request->device_token == '' || $request->device_token == 'null')
            {
                $response_array = array('status' => false, 'message' => 'Please activate the VPN on your device to have full App installation');
                return response()->json($response_array, 200);
            }
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang) ? $request->lang : 'en';
                $restaurants = $this->restaurants;
                $device_token = $request->device_token;
                $device_type = $request->device_type;
                $email = $request->email;
                $data = $restaurants::where('email', $email)->where('org_password', $request->password)->first();
                if ($data) {
                    $authId = $data->id;
                    $image = $data->image ? SPACES_BASE_URL . $data->image : "";
                    if ($data->restaurant_name != NULL) {
                        $name = $data->restaurant_name;
                    } else {
                        $name = "";
                    }
                    $name_ar = isset($data->restaurant_name_ar) ? $data->restaurant_name_ar : "";
                    $name_kur = isset($data->restaurant_name_kur) ? $data->restaurant_name_kur : "";
                    $email = $data->email ? $data->email : "";
                    $phone = $data->phone;
                    $authToken = $this->generateRandomString();
                    $restaurants::where('id', $data->id)->update(['device_token' => $device_token, 'authToken' => $authToken, 'device_type' => $device_type, 'is_loggedin' => 1]);
                    $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.login_success', $lang), 'authId' => $authId, 'authToken' => $authToken, 'phone' => $phone, 'profile_image' => $image, 'email' => $email, 'name' => $name, 'name_ar' => $name_ar, 'name_kur' => $name_kur);
                } else {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.invalid_login', $lang));
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_profile(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;

            $restaurantdet = $this->restaurants->find($restaurant_id);
            if($restaurantdet)
            {
                $restaurantdet->image = SPACES_BASE_URL.$restaurantdet->image;
                $response_array = array('status' => true,'details' => $restaurantdet);
            }else
            {
                $response_array = array('status' => false,'message' => 'No data found');
            }
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
    * To get order list based on status
    *
    * @param object $request, int $type
    *
    * @return json $response
    */
    public function order_list(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $lang = isset($request->lang)?$request->lang:'en';
            if ($request->type == 1) $status = [0]; // New
            if ($request->type == 2) $status = [1, 2, 3, 4, 5, 6]; // Inprogress
            if ($request->type == 3) $status = [7]; // Delivered
            $query = $this->foodrequest->where('restaurant_id', $restaurant_id)
                ->whereIn('status', $status)->orderBy('id','desc');
            $limit = PAGINATION;
            $page = isset($request->page) ? $request->page : 1;
            $offset = ($page - 1) * $limit;
            $query = $query->when(($limit != '-1' && isset($offset)),
                function ($q) use ($limit, $offset) {
                    return $q->offset($offset)->limit($limit);
                });
            $data = $query->get();
            $order_list = array();
            foreach ($data as $key) {
                $order_list_detail = array();
                foreach ($key->Requestdetail as $k) {
                    $add_ons = array();
                    if (isset($k->RequestdetailAddons) && !empty($k->RequestdetailAddons)) {
                        foreach ($k->RequestdetailAddons as $addon) {
                            $add_ons[] = array(
                                'id' => $addon->id,
                                'restaurant_id' => isset($key->restaurant_id)?$key->restaurant_id:"",
                                'name' => isset($addon->name)?$addon->name:"",
                                'name_ar' => isset($addon->name_ar)?$addon->name_ar:"",
                                'name_kur' => isset($addon->name_kur)?$addon->name_kur:"",
                                'price' => isset($addon->price)?$addon->price:"",
                                'created_at' => date("Y-m-d H:i:s", strtotime($addon->created_at)),
                                'updated_at' => date("Y-m-d H:i:s", strtotime($addon->updated_at)),
                            );
                        }
                    }
                    $food_quantity = array();
                    if (!empty($k->FoodQuantity)) {
                        // foreach($k->FoodQuantity as $qty){
                        $food_quantity[] = array(
                            'id' => isset($k->FoodQuantity->id) ? $k->FoodQuantity->id : '',
                            'name' => (isset($k->FoodQuantity->name) ? $k->FoodQuantity->name : ''),
                            'price' => $k->food_quantity_price,
                            'created_at' => isset($k->FoodQuantity->created_at) ? date("Y-m-d H:i:s", strtotime($k->FoodQuantity->created_at)) : '',
                            'updated_at' => isset($k->FoodQuantity->updated_at) ? date("Y-m-d H:i:s", strtotime($k->FoodQuantity->updated_at)) : '',
                        );
                        // }
                    }
                    $order_list_detail[] = array(
                        'food_id' => (!empty($k->Foodlist) ? $k->Foodlist->id : ""),
                        'food_name' => (!empty($k->Foodlist) ? $k->Foodlist->name : ""),
                        'food_name_ar' => (!empty($k->Foodlist) ? $k->Foodlist->name_ar : ""),
                        'food_name_kur' => (!empty($k->Foodlist) ? $k->Foodlist->name_kur : ""),
                        'food_quantity' => $k->quantity,
                        'tax' => (!empty($k->Foodlist) ? $k->Foodlist->tax : ""),
                        'item_price' => (!empty($k->Foodlist) ? $k->Foodlist->price : 0) * $k->quantity,
                        'is_veg' => (!empty($k->Foodlist) ? $k->Foodlist->is_veg : ""),
                        'food_size' => $food_quantity,
                        'add_ons' => $add_ons
                    );
                }
                $order_list[] = array(
                    'request_id' => $key->id,
                    'order_id' => $key->order_id,
                    'ordered_on' => $key->ordered_time,
                    'bill_amount' => $key->bill_amount,
                    'item_list' => $order_list_detail,
                    'item_total' => $key->item_total,
                    'offer_discount' => $key->offer_discount,
                    'restaurant_discount' => $key->restaurant_discount,
                    'restaurant_packaging_charge' => $key->restaurant_packaging_charge,
                    'tax' => $key->tax,
                    'status' => $key->status,
                    'delivery_charge' => $key->delivery_charge,
                    'delivery_address' => $key->delivery_address,
                    'delivery_type' => $key->delivery_type,
                    'total_members' => $key->total_members,
                    'pickup_dining_time' => $key->pickup_dining_time,
                    'paid_type' => $key->paid_type,
                    'user_name' => isset($key->Users) ? $key->Users->name : "",
                    'user_email' => isset($key->Users) ? $key->Users->email : "",
                    'user_phone' => isset($key->Users) ? $key->Users->phone : "",
                    'restaurant_delivery_type' => $key->Restaurants->deliverytype,
                    'order_delivery_type' => $key->order_accepted_type
                );

            }
            if (count($data) != 0) {
                $response_array = array('status' => true, 'order_list' => $order_list);
            } else {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_orders', $lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }



    /**
    * Update status for orders
    *
    * @param int $id, int $status, object $request
    *
    * @return json $response
    */
    public function status_update($request_id,$status,$order_delivery_type,Request $request)
    {
        try {
            //status = 1 => accept
            //status = 10 => cancel
            //status = 2 => assign
            $request_id = (string)$request_id;
            if($request->header('authId')!="")
            {
                $restaurant_id = $request->header('authId');
            }else
            {
                $restaurant_id = $request->authId;
            }
            $client = new Client();
            $trackorderstatus = $this->trackorderstatus;
            $orderdet = $this->foodrequest->find($request_id);
            $lang = isset($request->lang)?$request->lang:'en';
            if(empty($orderdet))
            {
                $response_array = array('status' => false,'message' => $this->language_string_translation('constants.invalid_request_id', $lang));
                return response()->json($response_array, 200);
            }
            if($status==1)
            {
                if($orderdet->status>=1)
                {
                    $response_array = array('status' => false,'message' => $this->language_string_translation('constants.request_already_accepted', $lang));
                    return response()->json($response_array, 200);
                }
                $this->foodrequest->where('id',$request_id)->update(['status'=> 1,'order_accepted_type'=> $order_delivery_type]);
                $trackorderstatus->request_id = $request_id;
                $trackorderstatus->status = 1;
                $trackorderstatus->detail = "Order Accepted by Restaurant";
                $trackorderstatus->save();
                $user_data = $this->foodrequest->where('id',$request_id)->first();
                // to insert into mongodb
                Log::info('request_id: ' . $request_id);

                $currentRequest = CurrentRequest::where('request_id',$request_id)
                        ->update(['request_id' => $request_id , 'user_id' => $user_data->user_id , 'status' => '1']); 
                Log::info('currentRequestStaus: ' . $currentRequest);
                $provider_id = 0;
                $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                $message = $this->language_string_translation('constants.request_accepted_successfully', $lang);

                // Broadcasting flow
                if($user_data->order_accepted_type == 2) {
                    $this->broadcastTrigger($user_data,$request_id);
                }
            }elseif($status == 2)
            {
                if($orderdet->status>2)
                {
                    $response_array = array('status' => false,'message' => $this->language_string_translation('constants.request_already_inprogress', $lang));
                    return response()->json($response_array, 200);
                }
                $restuarant_detail = $this->restaurants->where('id',$restaurant_id)->first();
                $source_lat = $restuarant_detail->lat;
                $source_lng = $restuarant_detail->lng;
                // $data = file_get_contents(FIREBASE_URL."/available_providers/.json");
                // $data = json_decode($data);
                $temp_driver = $last_distance = $old_provider = 0;
                // Log::info('available_providers: ' . file_get_contents(FIREBASE_URL."/available_providers/.json"));

                $availableProviders = AvailableProviders::where('status','1')->get(); 

                Log::info('availableProviders: ' . $availableProviders);

                $data = $availableProviders;

                if($data != NULL && $data !="")
                {
                    foreach ($data as $key => $value)
                    {
                        // Log::info('value: ' . $value->id);

                        $driver_id = $value->provider_id;
                        // Log::info('driver_id: ' . $driver_id);


                        //check previous rejected drivers    
                        // $current_request = file_get_contents(FIREBASE_URL."/current_request/".$request_id.".json");
                        // $current_request = json_decode($current_request);
                        // if(isset($current_request->reject_drivers) && !empty($current_request->reject_drivers))
                        // {
                        //     $reject_drivers = explode(',',$current_request->reject_drivers);
                        //     if(in_array($driver_id, $reject_drivers))
                        //     {
                        //         continue;
                        //     }
                        // }
                        $check = $this->deliverypartners->where('id',$driver_id)->where('restaurant_id',0)->where('status',1)->first();
                        $check_request = 0;
                        if($check && $check_request==0)
                        {
                            if($old_provider==0){
                                $old_provider = -1;
                            }
                            if($driver_id != $old_provider)
                            {
                                if ($value != NULL && $value != "")
                                {
                                    $driver_lat = $value->lat;
                                    $driver_lng = $value->lng;
                                    try {
                                        $q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false&key=".GOOGLE_API_KEY;
                                        $json = file_get_contents($q);
                                        $details = json_decode($json, TRUE);

                                        if(isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS'){
                                            $current_distance_with_unit = $details['rows'][0]['elements'][0]['distance']['text'];
                                            $current_distance_km = $details['rows'][0]['elements'][0]['distance']['text'];
                                            $unit = str_after($current_distance_with_unit, ' ');
                                            $current_distance = str_replace($unit ,'',$current_distance_km);
                                            if(strpos($current_distance, ',') !== false)
                                            {
                                                $current_distance = str_replace(',' ,'',$current_distance);
                                            }
                                            if($unit == 'm')
                                            {
                                                $current_distance = $current_distance/1000;
                                            };
                                            log::info('current_distance  ::' . $current_distance);
                                            if((int)$current_distance <= DEFAULT_RADIUS){
                                                if($temp_driver == 0){
                                                    $temp_driver = $driver_id;
                                                    $last_distance = $current_distance;
                                                    log::info('if current_distance  ::' . $current_distance);
                                                }else{
                                                    log::info('else current_distance  ::' . $current_distance);
                                                    if((int)$current_distance < $last_distance){
                                                        log::info('else and if current_distance  ::' . $current_distance);
                                                        $temp_driver = $driver_id;
                                                        $last_distance = $current_distance;
                                                    }
                                                }
                                            }
                                        }
                                    }catch(Exception $e){

                                    }
                                }
                            }
                        }
                    }
                }
                log::info('temp_driver  ::' . $temp_driver);
                if ($temp_driver != 0 ) {
                    # code...
                    $ins_data = array();
                    $user_data = $this->foodrequest->where('id',$request_id)->first();
                    $this->foodrequest->where('id',$request_id)->update(['temp_drivers'=>$temp_driver,'status'=>2]);
                    $check_status = $trackorderstatus->where('request_id',$request_id)->where('status',2)->count();
                    if($check_status==0)
                    {
                        $trackorderstatus->request_id = $request_id;
                        $trackorderstatus->status = 2;
                        $trackorderstatus->detail = "Food is being prepared";
                        $trackorderstatus->save();

                        // to insert into firebase
                        // $postdata = array();
                        // $postdata['request_id'] = $request_id;
                        // $postdata['provider_id'] = (String)$temp_driver;
                        // $postdata['user_id'] = $user_data->user_id;
                        // $postdata['status'] = 2;
                        // $postdata = json_encode($postdata);
                        // $this->update_firebase($postdata, 'current_request', $request_id);

                        // to insert into mongodb
                        // $primaryKey = $request_id;
                        // $currentRequest= CurrentRequest::find($primaryKey);
                        // $currentRequest->request_id = $request_id;
                        // $currentRequest->user_id = $user_data->user_id;
                        // $currentRequest->provider_id = (String)$temp_driver;
                        // $currentRequest->status = 2;        
                        // $currentRequest->save();

                        $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['request_id' => $request_id , 'user_id' => $user_data->user_id , 'provider_id' => $temp_driver , 'status' => '2']);

                        $provider_id = $temp_driver;
                        $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);

                        // sending request to driver
                        // $postdata = array();
                        // $postdata['request_id'] = $request_id;
                        // $postdata['user_id'] = $user_data->user_id;
                        // $postdata['status'] = 1;
                        // $postdata = json_encode($postdata);
                        // $this->update_firebase($postdata, 'new_request', $temp_driver.'/'.$request_id);

                        // to insert into mongodb

                        $checkNewRequest = NewRequest::where('request_id' , $request_id)->where('provider_id',$provider_id)->first();
                        if($checkNewRequest == null)
                        {
                            $newRequest = new NewRequest();
                            $newRequest->request_id = (string)$request_id;
                            $newRequest->user_id = (string)$user_data->user_id;
                            $newRequest->provider_id = (string)$temp_driver;
                            $newRequest->status = "1";        
                            $newRequest->save();
                        }
                        $client->get(SOCKET_URL.'/new_request_status/'.$request_id.'/'.$provider_id);

                        // $newRequest = NewRequest::create(['request_id' => $request_id , 'user_id' => $user_data->user_id , 'provider_id' => (string)$temp_driver , 'status' => 1]);

                        Log::info('newRequest' . $newRequest);

                        //send push notification to user
                        $provider = $this->deliverypartners->find($temp_driver);
                        if(isset($provider->device_token) && $provider->device_token!='')
                        {
                            $title = $message = trans('constants.new_order');
                            $data = array(
                                'device_token' => $provider->device_token,
                                'device_type' => $provider->device_type,
                                'title' => $title,
                                'message' => $message,
                                'request_id' => $request_id,
                                'delivery_type' => $orderdet->delivery_type
                            );
                            $this->user_send_push_notification($data);
                        }
                        $message = $this->language_string_translation('constants.request_assigned_successfully', $lang);
                    }else
                    {
                        $message = $this->language_string_translation('constants.no_providers_available', $lang);
                    }
                }else
                {
                    $message = $this->language_string_translation('constants.no_providers_available', $lang);
                }
            }elseif($status == 7)
            {
                // to insert into firebase
                // $postdata = array();
                // $postdata['request_id'] = $request_id;
                // $postdata['user_id'] = $orderdet->user_id;
                // $postdata['status'] = 7;
                // $postdata = json_encode($postdata);
                // $this->update_firebase($postdata, 'current_request', $request_id);

                // to insert into mongodb
                // $primaryKey = $request_id;
                // $currentRequest= CurrentRequest::find($primaryKey);
                // $currentRequest->request_id = $request_id;
                // $currentRequest->user_id = $orderdet->user_id;
                // $currentRequest->status = 7;        
                // $currentRequest->save();

                $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['request_id' => $request_id , 'user_id' => $orderdet->user_id , 'status' => '7']); 
                $provider_id = 0;
                $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);

                if($orderdet->status==7)
                {
                    $response_array = array('status' => false,'message' => $this->language_string_translation('constants.request_already_completed', $lang));
                    return response()->json($response_array, 200);
                }
                $this->foodrequest->where('id',$request_id)->update(['status'=>7,'is_paid'=>1]);
                $trackorderstatus->request_id = $request_id;
                $trackorderstatus->status = 7;
                $trackorderstatus->detail = "Order Completed by Restaurant";
                $trackorderstatus->save();
                $message = $this->language_string_translation('constants.order_completed_successfully', $lang);
            }else
            {
                if($status == 10){
                    // to insert into firebase
                    // $postdata = array();
                    // $postdata['request_id'] = $request_id;
                    // $postdata['user_id'] = $orderdet->user_id;
                    // $postdata['status'] = 10;
                    // $postdata = json_encode($postdata);
                    // $this->update_firebase($postdata, 'current_request', $request_id);

                    // to insert into mongodb
                    // $primaryKey = $request_id;
                    // $currentRequest= CurrentRequest::find($primaryKey);
                    // $currentRequest->request_id = $request_id;
                    // $currentRequest->user_id = $orderdet->user_id;
                    // $currentRequest->status = 10;        
                    // $currentRequest->save();

                    if($orderdet->status !=7)
                    {
                    $this->foodrequest->where('id',$request_id)->update(['status'=>$status]);
                    $trackorderstatus->request_id = $request_id;
                    $trackorderstatus->status = $status;
                    $trackorderstatus->detail = "Order Cancelled by Restaurant";
                    $trackorderstatus->save();
                    $message = $this->language_string_translation('constants.request_rejected_successfully', $lang);
        
                    $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['request_id' => $request_id , 'user_id' => $orderdet->user_id , 'status' => '10']); 

                    $providerId = CurrentRequest::where('request_id',$request_id)->first();

                    $provider_id = $providerId->provider_id;


                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);

                    MultiOrderAssign::request_driver_commission_delete($request_id);
                    }
                }
                if($orderdet->status==10)
                {
                    $response_array = array('status' => false,'message' => $this->language_string_translation('constants.request_already_cancelled', $lang));
                    return response()->json($response_array, 200);
                }elseif($orderdet->status>=2)
                {
                    $response_array = array('status' => false,'message' => $this->language_string_translation('constants.request_already_assigned', $lang));
                    return response()->json($response_array, 200);
                }
            }
            $response_array = array('status' => true,'message' => $message);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
    * broadcast delay job flow
    *
    * @param int $request_id
    *
    * @return json $response
    */
    public function broadcast_delay_job($request_id,$prepared_type)
    {
        Log::info('connect restaurant broadcast_delay_job api');
        Log::info('connect restaurant broadcast_delay_job api request_id :' . $request_id);
        $user_data = $this->foodrequest->where('id',$request_id)->first();
        if($prepared_type == 1) {
            Log::info('restaurant food prepared');
            Log::info('delayTime: Empty 0');
            $type = 1;
            if($user_data->order_accepted_type == 2) {
                try
                {
                    $job = (new BroadcastDelayJob($request_id , $type));
                    dispatch($job);
                }catch(\Exception $e)
                {
                    Log::error('BroadcastDelyJob Mail error:: ' . $e->getMessage());
                }
            }
        }
        $this->broadcastTrigger($user_data,$request_id);
    }

    /**
    * broadcast Trigger flow
    *
    * @param int $request_id , $user_data
    *
    * @return json $response
    */
    public function broadcastTrigger($user_data,$request_id) {
        $lastTenMinutes = 10;
        if($user_data->food_preparation_time > $lastTenMinutes) {
            $delayTime = $user_data->food_preparation_time - $lastTenMinutes;
            Log::info('delayTime: ' . $delayTime);
            $type = 1;
            try
            {
                $job = (new BroadcastDelayJob($request_id , $type))->delay(Carbon::now()->addMinutes($delayTime));
                dispatch($job);
            }catch(\Exception $e)
            {
                Log::error('BroadcastDelyJob Mail error:: ' . $e->getMessage());
            }
        }else {
            Log::info('delayTime: Empty 0');
            $type = 2;
            if($user_data->order_accepted_type == 2) {
                try
                {
                    $job = (new BroadcastDelayJob($request_id , $type));
                    dispatch($job);
                }catch(\Exception $e)
                {
                    Log::error('BroadcastDelyJob Mail error:: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Logout from vendor app
     *
     * @param object $request
     *
     * @return json $response
     */
    public function logout(Request $request)
    {
        if($request->header('authId')!="")
        {
            $restaurant_id = $request->header('authId');
        }else
        {
            $restaurant_id = $request->authId;
        }
        $restaurantdet = $this->restaurants->find($restaurant_id);
        if(empty($restaurantdet))
        {
            $response_array = array('status' => false,'message' => "Invalid authid");
            $response = response()->json($response_array, 200);
            return $response;
        }
        $restaurantdet->device_type='';
        $restaurantdet->device_token='';
        $restaurantdet->is_loggedin=0;
        $restaurantdet->authToken='';
        $restaurantdet->save();

        $response_array = array('status' => true,'message' => 'Logout successfully');
        $response = response()->json($response_array, 200);
        return $response;
    }

    /**
     * @param Request $request
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function available_status_update(Request $request, $status)
    {
        try {
            if($request->header('authId')!="")
            {
                $restaurant_id = $request->header('authId');
            }else
            {
                $restaurant_id = $request->authId;
            }
            $restaurantdet = $this->restaurants->find($restaurant_id);
            $lang = isset($request->lang)?$request->lang:'en';
            if(empty($restaurantdet))
            {
                $response_array = array('status' => false,'message' => $this->language_string_translation('constants.invalid_authid', $lang));
                return response()->json($response_array, 200);
            }
            if($status != 1){
                $data = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereIn('status',[1,2,3,4,5,6,8])->get();
                if(count($data)>0){
                    $response_array = array('status'=>false,'message'=>$this->language_string_translation('constants.restaurant_updated_failed_msg', $lang));
                    return response()->json($response_array, 200);
                }else
                {
                    $restaurantdet->status = $status;
                    $restaurantdet->save();
                    $this->banner->where('restaurant_id',$restaurant_id)->update(['status'=>$status]);
                    count(Redis::keys('banners*')) != 0 ? Redis::del(Redis::keys('banners*')) : '';
                    $response_array = array('status' => true,'message' => $this->language_string_translation('constants.status_updated_successfully', $lang));
                    return response()->json($response_array, 200);
                }
            }else{
                $restaurantdet->status = $status;
                $restaurantdet->save();
                $this->banner->where('restaurant_id',$restaurant_id)->update(['status'=>$status]);
                count(Redis::keys('banners*')) != 0 ? Redis::del(Redis::keys('banners*')) : '';
                $response_array = array('status' => true,'message' => $this->language_string_translation('constants.status_updated_successfully', $lang));
                return response()->json($response_array, 200);
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_dashboard_details(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $dt = Carbon::now();
            $year = $dt->year;
            $current_date = $dt->toDateString();
            $yesterday = Carbon::yesterday()->toDateString();
            $current_week = $dt->weekOfYear;
            $last_week = $dt->yesterday()->weekOfYear;
            $current_month = $dt->month;
            $last_month = $dt->subMonth()->month;

            $get_data = DB::select("select (select count(*) from requests where date(ordered_time)=$current_date) as today_total_order, (select count(*) from requests where status=7 and date(ordered_time)=$current_date and restaurant_id=$restaurant_id) as today_complete_order, (select count(*) from requests where status=10 and date(ordered_time)=$current_date and restaurant_id=$restaurant_id) as today_cancel_order,
                        (select sum(bill_amount) from requests where date(ordered_time)=$current_date and restaurant_id=$restaurant_id) as today_total_amount, (select sum(bill_amount) from requests where status=7 and date(ordered_time)=$current_date and restaurant_id=$restaurant_id) as today_complete_amount, (select sum(bill_amount) from requests where status=10 and date(ordered_time)=$current_date and restaurant_id=$restaurant_id) as today_cancel_amount,
                        (select count(*) from requests where date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_total_order, (select count(*) from requests where status=7 and date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_complete_order, (select count(*) from requests where status=10 and date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_cancel_order,
                        (select sum(bill_amount) from requests where date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_total_amount, (select sum(bill_amount) from requests where status=7 and date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_complete_amount, (select sum(bill_amount) from requests where status=10 and date(ordered_time)=$yesterday and restaurant_id=$restaurant_id) as yesterday_cancel_amount,
                        (select count(*) from requests where date(ordered_time)=$current_date and status=0 and restaurant_id=$restaurant_id) as today_new_order, (select count(*) from requests where date(ordered_time)=$yesterday and status=0 and restaurant_id=$restaurant_id) as yesterday_new_order");

            $get_weekdata = DB::select("select (select count(*) from requests where week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_total_order, (select count(*) from requests where status=7 and week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_complete_order, (select count(*) from requests where status=10 and week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_cancel_order,
                        (select sum(bill_amount) from requests where week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_total_amount, (select sum(bill_amount) from requests where status=7 and week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_complete_amount, (select sum(bill_amount) from requests where status=10 and week(ordered_time)=$current_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as week_cancel_amount,
                        (select count(*) from requests where week(ordered_time)=$last_week  and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_total_order, (select count(*) from requests where status=7 and week(ordered_time)=$last_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_complete_order, (select count(*) from requests where status=10 and week(ordered_time)=$last_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_cancel_order,
                        (select sum(bill_amount) from requests where week(ordered_time)=$last_week  and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_total_amount, (select sum(bill_amount) from requests where status=7 and week(ordered_time)=$last_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_complete_amount, (select sum(bill_amount) from requests where status=10 and week(ordered_time)=$last_week and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_week_cancel_amount,
                        (select count(*) from requests where date(ordered_time)=$current_week and year(ordered_time)=$year and status=0 and restaurant_id=$restaurant_id) as week_new_order, (select count(*) from requests where date(ordered_time)=$last_week and year(ordered_time)=$year and status=0 and restaurant_id=$restaurant_id) as last_week_new_order");

            $get_monthdata = DB::select("select (select count(*) from requests where month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_total_order, (select count(*) from requests where status=7 and month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_complete_order, (select count(*) from requests where status=10 and month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_cancel_order,
                        (select sum(bill_amount) from requests where month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_total_amount, (select sum(bill_amount) from requests where status=7 and month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_complete_amount, (select sum(bill_amount) from requests where status=10 and month(ordered_time)=$current_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as month_cancel_amount,
                        (select count(*) from requests where date(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_total_order, (select count(*) from requests where status=7 and date(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_complete_order, (select count(*) from requests where status=10 and month(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_cancel_order,
                        (select sum(bill_amount) from requests where month(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_total_amount, (select sum(bill_amount) from requests where status=7 and month(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_complete_amount, (select sum(bill_amount) from requests where status=10 and date(ordered_time)=$last_month and year(ordered_time)=$year and restaurant_id=$restaurant_id) as last_month_cancel_amount,
                        (select count(*) from requests where month(ordered_time)=$current_month and year(ordered_time)=$year and status=0 and restaurant_id=$restaurant_id) as month_new_order, (select count(*) from requests where month(ordered_time)=$last_month and year(ordered_time)=$year and status=0 and restaurant_id=$restaurant_id) as last_month_new_order");

            $today = array(
                'today_total_order' => $get_data[0]->today_total_order,
                'today_complete_order' => $get_data[0]->today_complete_order,
                'today_cancel_order' => $get_data[0]->today_cancel_order,
                'today_total_amount' => ($get_data[0]->today_total_amount!=null)?$get_data[0]->today_total_amount:0,
                'today_complete_amount' => ($get_data[0]->today_complete_amount!=null)?$get_data[0]->today_complete_amount:0,
                'today_cancel_amount' => ($get_data[0]->today_cancel_amount!=null)?$get_data[0]->today_cancel_amount:0,
                'today_new_order' => $get_data[0]->today_new_order,
            );
            $yesterday = array(
                'yesterday_total_order' => $get_data[0]->yesterday_total_order,
                'yesterday_complete_order' => $get_data[0]->yesterday_complete_order,
                'yesterday_cancel_order' => $get_data[0]->yesterday_cancel_order,
                'yesterday_total_amount' => ($get_data[0]->yesterday_total_amount!=null)?$get_data[0]->yesterday_total_amount:0,
                'yesterday_complete_amount' => ($get_data[0]->yesterday_complete_amount!=null)?$get_data[0]->yesterday_complete_amount:0,
                'yesterday_cancel_amount' => ($get_data[0]->yesterday_cancel_amount!=null)?$get_data[0]->yesterday_cancel_amount:0,
                'yesterday_new_order' => $get_data[0]->yesterday_new_order,
            );
            $this_week = array(
                'week_total_order' => $get_weekdata[0]->week_total_order,
                'week_complete_order' => $get_weekdata[0]->week_complete_order,
                'week_cancel_order' => $get_weekdata[0]->week_cancel_order,
                'week_total_amount' => ($get_weekdata[0]->week_total_amount!=null)?$get_weekdata[0]->week_total_amount:0,
                'week_complete_amount' => ($get_weekdata[0]->week_complete_amount!=null)?$get_weekdata[0]->week_complete_amount:0,
                'week_cancel_amount' => ($get_weekdata[0]->week_cancel_amount!=null)?$get_weekdata[0]->week_cancel_amount:0,
                'week_new_order' => $get_weekdata[0]->week_new_order,
            );
            $last_week = array(
                'last_week_total_order' => $get_weekdata[0]->last_week_total_order,
                'last_week_complete_order' => $get_weekdata[0]->last_week_complete_order,
                'last_week_cancel_order' => $get_weekdata[0]->last_week_cancel_order,
                'last_week_total_amount' => ($get_weekdata[0]->last_week_total_amount!=null)?$get_weekdata[0]->last_week_total_amount:0,
                'last_week_complete_amount' => ($get_weekdata[0]->last_week_complete_amount!=null)?$get_weekdata[0]->last_week_complete_amount:0,
                'last_week_cancel_amount' => ($get_weekdata[0]->last_week_cancel_amount!=null)?$get_weekdata[0]->last_week_cancel_amount:0,
                'last_week_new_order' => $get_weekdata[0]->last_week_new_order,
            );
            $this_month = array(
                'month_total_order' => $get_monthdata[0]->month_total_order,
                'month_complete_order' => $get_monthdata[0]->month_complete_order,
                'month_cancel_order' => $get_monthdata[0]->month_cancel_order,
                'month_total_amount' => ($get_monthdata[0]->month_total_amount!=null)?$get_monthdata[0]->month_total_amount:0,
                'month_complete_amount' => ($get_monthdata[0]->month_complete_amount!=null)?$get_monthdata[0]->month_complete_amount:0,
                'month_cancel_amount' => ($get_monthdata[0]->month_cancel_amount!=null)?$get_monthdata[0]->month_cancel_amount:0,
                'month_new_order' => $get_monthdata[0]->month_new_order,
            );
            $last_month = array(
                'last_month_total_order' => $get_monthdata[0]->last_month_total_order,
                'last_month_complete_order' => $get_monthdata[0]->last_month_complete_order,
                'last_month_cancel_order' => $get_monthdata[0]->last_month_cancel_order,
                'last_month_total_amount' => ($get_monthdata[0]->last_month_total_amount!=null)?$get_monthdata[0]->last_month_total_amount:0,
                'last_month_complete_amount' => ($get_monthdata[0]->last_month_complete_amount!=null)?$get_monthdata[0]->last_month_complete_amount:0,
                'last_month_cancel_amount' => ($get_monthdata[0]->last_month_cancel_amount!=null)?$get_monthdata[0]->last_month_cancel_amount:0,
                'last_month_new_order' => $get_monthdata[0]->last_month_new_order,
            );

            $response_array = array('status' => true,'today' => $today,'yesterday' => $yesterday,'this_week' => $this_week,'last_week' => $last_week,'this_month' => $this_month,'last_month' => $last_month);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * update discount data
     *
     * @param object $request
     *
     * @return json $response
     */
    public function update_discount(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'offer_type'=>'required',
                    'discount_type' => 'required',
                    'target_amount' => 'required',
                    'offer_amount' => 'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                $restaurant_id = $request->header('authId') ?: $request->authId;
                $restaurantdet = $this->restaurants->find($restaurant_id);
                $restaurantdet->discount_type = $request->discount_type;
                $restaurantdet->target_amount = $request->target_amount;
                $restaurantdet->offer_amount = $request->offer_amount;
                $restaurantdet->offer_type = !empty($request->offer_type)?$request->offer_type:1;
                $restaurantdet->offer_value = !empty($request->offer_value)?$request->offer_value:0;
                $restaurantdet->save();
                $response_array = array('status' => true, 'message' => "Data updated successfully");
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_discount(Request $request)
    {
        try {
            $restaurantId = $request->header('authId') ?: $request->authId;

            $restaurant = $this->restaurants->find($restaurantId);

            $data = array(
                'offer_type' => $restaurant->offer_type,
                'offer_value' => $restaurant->offer_value,
                'discount_type' => $restaurant->discount_type?:"1",
                'target_amount' => $restaurant->target_amount?:0,
                'offer_amount' => $restaurant->offer_amount?:0
            );

            $response_array = array('status' => true, 'data' => $data);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * list all the food based on vendor
     *
     * @param object $request
     *
     * @return json $response
     */
    public function get_food_list(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;

            $food_list = $this->foodlist->with('FoodQuantity', 'Restaurants')->where('restaurant_id', $restaurant_id)->where('status', '!=', 0)->get();
            $category_list = $this->category->get();

            $get_food_list = array();
            foreach ($category_list as $key) {
                $category_wise_food = array();
                foreach ($food_list as $foods) {
                    $item_count = 0;
                    $food_categories = json_decode($foods->category_id);
                    if (in_array($key->id, $food_categories)) {
                        //check food offer
                        $food_offer = $this->food_offer($foods);

                        $category_wise_food[] = array(
                            'food_id' => $foods->id,
                            'name' => $foods->name,
                            'name_ar' => $foods->name_ar,
                            'name_kur' => $foods->name_kur,
                            'image' => (!empty($foods->image)) ? SPACES_BASE_URL . $foods->image : "",
                            'is_veg' => $foods->is_veg,
                            'price' => $foods->price,
                            'description' => $foods->description,
                            'category_id' => $foods->category_id,
                            'item_count' => $item_count,
                            'food_offer' => $food_offer,
                            'discount_type' => $foods->discount_type,
                            'target_amount' => $foods->target_amount,
                            'offer_amount' => $foods->offer_amount,
                            'item_tax' => $foods->Restaurants->tax,
                            'add_ons' => $foods->Add_ons,
                            'food_quantity' => $foods->FoodQuantity,
                            'is_available' => $foods->status
                        );
                    }
                }

                if ($category_wise_food) {
                    $get_food_list[] = array(
                        'category_id' => $key->id,
                        'category_name' => $key->category_name,
                        'category_name_ar' => $key->category_name_ar,
                        'category_name_kur' => $key->category_name_kur,
                        'items' => $category_wise_food
                    );
                }
            }
            $response_array = array('status' => true, 'details' => $get_food_list);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_product(Request $request)
    {
        try {
            $rules['name'] = 'required|max:30';
            $rules['description'] = 'required|max:100';
            $rules['category'] = 'required';
            $rules['menu'] = 'required';
            $rules['price'] = 'required';
            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()) {
                $error_messages = implode(',',$validator->messages()->all());
                $response_array = array('status' => false, 'message' => $error_messages);
                $response = response()->json($response_array, 200);
                return $response;
            }else
            {
                $restaurant_id = $request->header('authId') ?: $request->authId;
                if($request->is_special==1){
                    $approve=$this->foodlist->where('restaurant_id',$restaurant_id)->update(['is_special'=>2]);
                }
                $name = $request->name;
                $description = $request->description;
                $category = $request->category;
                $menu = $request->menu;
                if(isset($request->status))
                {
                    $status = $request->status;
                    if($status == 0){
                        $status = 2;
                    }
                }
                else
                {
                    $status=2;
                }
                $price = $request->price;
                $tax = 0;
                $packaging_charge = $request->packaging_charge?$request->packaging_charge:0;
                $food_type = (int)$request->is_veg;
                $discount_type = isset($request->discount_type)?$request->discount_type:0;
                $target_amount = $request->target_amount;
                $offer_amount = $request->offer_amount;
                if($request->is_special == null){
                    $is_special = 0;
                }else{
                    $is_special = $request->is_special;
                }
                if($request->id)
                {
                    $foodlist = $this->foodlist->find($request->id);

                    if($request->hasFile('image'))
                    {
                        $path = "uploads/product";
                        $image = self::base_image_upload_product($request,'image');
                        $foodlist->image = $image;
                    }

                    $foodlist->restaurant_id = $restaurant_id;
                    $foodlist->name = $name;
                    $foodlist->description = $description;
                    $foodlist->category_id = $category;
                    $foodlist->menu_id = $menu;
                    $foodlist->price = $price;
                    $foodlist->tax = $tax;
                    $foodlist->is_special = $is_special;
                    $foodlist->packaging_charge = $packaging_charge;
                    $foodlist->is_veg = $food_type;
                    $foodlist->discount_type = $discount_type;
                    $foodlist->target_amount = $target_amount;
                    $foodlist->offer_amount = $offer_amount;
                    $foodlist->save();

                    $add_ons = $this->add_ons->find($request->add_ons);
                    //update many to many relationship data
                    $foodlist->Add_ons()->sync($add_ons);

                    $food_quantity = $this->food_quantity->find($request->food_quantity);
                    $sync_data=array();
                    for($i = 0; $i < count($food_quantity); $i++){
                        $default=0;
                        //get default based on the id passed from the default key in view
                        if((int)$request->food_quantity_default==$food_quantity[$i]->id) $default=1;

                        if($request->food_quantity_price[$i]!=''){
                            $sync_data[$food_quantity[$i]->id] = ['price' => $request->food_quantity_price[$i],'is_default'=>$default];
                        }
                    }
                    $foodlist->FoodQuantity()->sync($sync_data);
                    $trans_msg = "update_success_msg";
                }else
                {
                    if($request->hasFile('image'))
                    {
                        $image = self::base_image_upload_product($request,'image');
                        $this->foodlist->image = $image;
                    }
                    $this->foodlist->restaurant_id = $restaurant_id;
                    $this->foodlist->name = $name;
                    $this->foodlist->description = $description;
                    $this->foodlist->category_id = $category;
                    $this->foodlist->menu_id = $menu;
                    $this->foodlist->status = 1;
                    $this->foodlist->price = $price;
                    $this->foodlist->tax = $tax;
                    $this->foodlist->packaging_charge = $packaging_charge;
                    $this->foodlist->is_veg = $food_type;
                    $this->foodlist->is_special = $is_special;
                    $this->foodlist->discount_type = $discount_type;
                    $this->foodlist->target_amount = $target_amount;
                    $this->foodlist->offer_amount = $offer_amount;
                    $this->foodlist->save();

                    $add_ons = $this->add_ons->find($request->add_ons);
                    $this->foodlist->Add_ons()->attach($add_ons);

                    $food_quantity = $this->food_quantity->find($request->food_quantity);
                    $sync_data=array();
                    for($i = 0; $i < count($food_quantity); $i++){
                        $default=0;
                        if((int)$request->food_quantity_default==$food_quantity[$i]->id) $default=1;
                        $sync_data[$food_quantity[$i]->id] = ['price' => $request->food_quantity_price[$i],'is_default'=>$default];
                    }
                    $this->foodlist->FoodQuantity()->attach($sync_data);

                    $trans_msg = "add_success_msg";
                }
                $response_array = array('status' => true, 'message' => trans('constants.'.$trans_msg,[ 'param' => 'Product']));
                return response()->json($response_array, 200);
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * get details of addons, category and product quantity
     */
    public function get_product_needs(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;

            $category = $this->category->where('restaurant_id',$restaurant_id)->where('status',1)->get();
            $add_ons = $this->add_ons->where('restaurant_id',$restaurant_id)->get();
            $food_quantity = $this->food_quantity->get();
            $food_quantity_data = array();
            foreach($food_quantity as $qty){
                $food_quantity_data[] = array(
                    'id' => isset($qty->id)?$qty->id:'',
                    'name' => (isset($qty->name)?$qty->name:''),
                    'price' => "",
                    'is_default' => "",
                    'created_at' => isset($qty->created_at)?date("Y-m-d H:i:s",strtotime($qty->created_at)):'',
                    'updated_at' => isset($qty->updated_at)?date("Y-m-d H:i:s",strtotime($qty->updated_at)):'',
                );
            }
            $menu_list = $this->menu->where('restaurant_id',$restaurant_id)->get();

            $response_array = array('status' => true, 'category' => $category, 'add_ons'=>$add_ons, 'menu_list'=>$menu_list, 'food_quantity'=>$food_quantity_data);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * delete product
     *
     * @param object $request, $id
     *
     * @return json $response
     */
    public function delete_product(Request $request,$id)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $data = $this->foodrequest->with('Requestdetail')->where('restaurant_id',$restaurant_id)->wherehas('Requestdetail',function($data) use($id){
                $data->where('food_id', $id);
            })->whereIn('status',[1,2,3,4,5,6,8])->get();
            if(count($data)>0){
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.product_delete_failed_msg', $lang));
                return response()->json($response_array,200);
            }else{
                $delete =  $this->foodlist->where('id',$id)->update(['status'=>0]);
                $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.product_deleted_successfully', $lang));
                return response()->json($response_array,200);
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @param $food_id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function food_available_status_update(Request $request,$food_id ,$status)
    {
        try {
            if($request->header('authId')!="")
            {
                $restaurant_id = $request->header('authId');
            }else
            {
                $restaurant_id = $request->authId;
            }
            if($status == 0){
                $status = 2;
            }
            $lang = isset($request->lang)?$request->lang:'en';
            $food_list = $this->foodlist->find($food_id);
            if(empty($food_list))
            {
                $response_array = array('status' => false,'message' => $this->language_string_translation('constants.invalid_food_item', $lang));
                return response()->json($response_array, 200);
            }
            $food_list->status = $status;
            $food_list->save();
            $response_array = array('status' => true,'message' => $this->language_string_translation('constants.status_updated_successfully', $lang));
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * get single food details
     *
     * @param object $request, int $id
     *
     * @return json $response
     */
    public function get_food_details(Request $request, $id)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $food_list = $this->foodlist->with('FoodQuantity', 'Restaurants')->where('id', $id)->where('status', '!=', 0)->first();
            $item_count = 0;

            //check food offer
            $food_offer = $this->food_offer($food_list);
            $add_ons = array();
            if (!empty($food_list->Add_ons)) {
                foreach ($food_list->Add_ons as $addon) {
                    $add_ons[] = array(
                        'id' => $addon->id,
                        'restaurant_id' => $addon->restaurant_id,
                        'name' => $addon->name,
                        'price' => $addon->price,
                        'created_at' => date("Y-m-d H:i:s", strtotime($addon->created_at)),
                        'updated_at' => date("Y-m-d H:i:s", strtotime($addon->updated_at)),
                    );
                }
            }
            $food_quantity = array();
            if (!empty($food_list->FoodQuantity)) {
                foreach ($food_list->FoodQuantity as $qty) {
                    $food_quantity[] = array(
                        'id' => isset($qty->id) ? $qty->id : '',
                        'name' => (isset($qty->name) ? $qty->name : ''),
                        'price' => $qty->pivot->price,
                        'is_default' => $qty->pivot->is_default,
                        'created_at' => isset($qty->created_at) ? date("Y-m-d H:i:s", strtotime($qty->created_at)) : '',
                        'updated_at' => isset($qty->updated_at) ? date("Y-m-d H:i:s", strtotime($qty->updated_at)) : '',
                    );
                }
            }
            $get_food_list = array(
                'food_id' => $food_list->id,
                'name' => $food_list->name,
                'name_ar' => $food_list->name_ar,
                'name_kur' => $food_list->name_kur,
                'image' => (!empty($food_list->image)) ? SPACES_BASE_URL . $food_list->image : "",
                'is_veg' => $food_list->is_veg,
                'price' => $food_list->price,
                'description' => $food_list->description,
                'category_id' => $food_list->category_id,
                'menu_id' => $food_list->menu_id,
                'item_count' => $item_count,
                'food_offer' => $food_offer,
                'discount_type' => isset($food_list->discount_type) ? $food_list->discount_type : 0,
                'target_amount' => $food_list->target_amount,
                'offer_amount' => $food_list->offer_amount,
                'item_tax' => $food_list->Restaurants->tax,
                'add_ons' => $add_ons,
                'food_quantity' => $food_quantity,
                'is_available' => $food_list->status,
                'is_special' => $food_list->is_special,
                'packaging_charge' => $food_list->packaging_charge
            );

            $response_array = array('status' => true, 'details' => $get_food_list);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_payout_details(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $start_date = isset($request->start_date)?$request->start_date." 00:00:00":date("Y-m-d H:i:s");
            $end_date = isset($request->end_date)?$request->end_date." 23:59:59":date("Y-m-d H:i:s");
            $restaurant_detail = $this->restaurants->find($restaurant_id);
            $pending_payout = !empty($restaurant_detail->pending_payout)?$restaurant_detail->pending_payout:0;
            $pending_payout =round($pending_payout ,2);
            $total_earnings = !empty($restaurant_detail->total_earnings)?$restaurant_detail->total_earnings:0;        
            $payout_details[] = array(
                'total_orders' => 0,
                'total_amount' => $pending_payout,
                'status' => 'Pending',
                'from_date' => $start_date,
                'to_date' => $end_date
            );
            $complete_payout =  $total_earnings - $pending_payout;
            $payout_details[] = array(
                'total_orders' => 0,
                'total_amount' => $complete_payout,
                'status' => 'Completed',
                'from_date' => $start_date,
                'to_date' => $end_date
            );
            $total_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)
                ->whereBetween('ordered_time',[$start_date,$end_date])->count();
            $accept_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereNotIn('status',[0,10])
                ->whereBetween('ordered_time',[$start_date,$end_date])->count();
            $denied_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->where('status',10)
                ->whereBetween('ordered_time',[$start_date,$end_date])->count();
            $admin_paid_total_earnings = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[$start_date,$end_date])->sum('payout_amount');
            $total_earnings =round($total_earnings ,2);
            $currency_code = "IQD";
            $currency_symbol = "IQD";
            $response_array = array('status' => true, 'details' => $payout_details,'total_orders'=>$total_orders, 'accept_orders'=>$accept_orders, 'denied_orders'=>$denied_orders, 'total_earnings'=>$admin_paid_total_earnings,'currency_code'=>$currency_code,'currency_symbol'=>$currency_symbol);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * Get payout history list and pending payout details
     *
     * @param object $request
     *
     * @return json $response
     */
    public function transaction_history(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $get_data = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)->orderBy('id','desc')->get();
            if(!empty($get_data)){
                foreach ($get_data as $data){
                    if($data->status == "Success"){
                        $data->status = $this->language_string_translation('constants.success',$lang);
                    }
                }
            }
            $pending_payout = $this->restaurants->select('pending_payout')->find($restaurant_id);
            $pending_payout = !empty($pending_payout)?$pending_payout->pending_payout:0;
            return response()->json(array('status' => true, 'details' => $get_data, "pending_payout"=>$pending_payout), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * API for earnings based on daily
     *
     * @param object $request
     *
     * @return json $response
     */
    public function today_earnings(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $foodrequest = $this->foodrequest;
            $today_date = new \DateTime();
            $date = ((isset($request->filter_date) && $request->filter_date!='')) ? new \DateTime($request->filter_date) : $today_date;

            //get daily total earnings
            $today_earnings = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[$date->format('Y-m-d')." 00:00:00", $date->format('Y-m-d')." 23:59:59"])
                ->sum('payout_amount');
            $today_incentives = $foodrequest::where('restaurant_id',$restaurant_id)
                ->whereBetween('ordered_time',[$date->format('Y-m-d')." 00:00:00", $date->format('Y-m-d')." 23:59:59"])
                ->where('is_paid',1)
                ->sum('restaurant_commision');
            return response()->json(array('status'=>true,'today_earnings'=>$today_earnings,'today_incentives'=>$today_incentives), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * API for weekly earnings
     *
     * @param object $request
     *
     * @return json $response
     */
    public function weekly_earnings(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $foodrequest = $this->foodrequest;
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            $date = ((isset($request->filter_date) && $request->filter_date!='')) ? $request->filter_date : "";
            //get weekly earnings
            $weekly_earnings  = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[Carbon::parse($date)->startOfWeek(),Carbon::parse($date)->endOfWeek()])
                ->sum('payout_amount');
            $weekly_incentives  = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                ->whereBetween('ordered_time',[Carbon::parse($date)->startOfWeek(),Carbon::parse($date)->endOfWeek()])
                ->sum('restaurant_commision');
            if(empty($date)){
                $date = Carbon::now();
                $graph_data = array();        
                for($i = 0; $i<7; $i++){
                    $current_date = "";
                    $current_date = $date->startOfWeek()->addDays($i)->format('Y/m/d');
                    $current_date_graph_data = new stdClass();
                    $current_date_graph_data->total_amount = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                    ->whereBetween('created_at',[$current_date." 00:00:00",$current_date." 23:59:59"])
                    ->sum('payout_amount');
                    $current_date_graph_data->total_orders = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                    ->whereBetween('ordered_time',[$current_date." 00:00:00",$current_date." 23:59:59"])->count();
                    $current_date_graph_data->day = $current_date;
                    $graph_data[] = $current_date_graph_data;
                }
            }else{
                $current_date = $request->filter_date;
                $current_date_graph_data = new stdClass();                
                $current_date_graph_data->total_orders = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                ->whereBetween('ordered_time',[$current_date." 00:00:00",$current_date." 23:59:59"])->count();
                $current_date_graph_data->total_amount = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[$current_date." 00:00:00",$current_date." 23:59:59"])
                ->sum('payout_amount');
                $current_date_graph_data->day = $current_date;
                $graph_data = array($current_date_graph_data);
            }
            return response()->json(array('status'=>true,'weekly_earnings'=>round($weekly_earnings,2),'weekly_incentives'=> round($weekly_incentives,2),'graph_data'=>$graph_data), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * API for monthly earnings
     *
     * @param object $request
     *
     * @return json $response
     */
    public function monthly_earnings(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $foodrequest = $this->foodrequest;
            $date = ((isset($request->filter_date) && $request->filter_date!='')) ? $request->filter_date : "";

            //get weekly earnings
            $monthly_earnings  = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[Carbon::parse($date)->startOfMonth(),Carbon::parse($date)->endOfMonth()])
                ->sum('payout_amount');
            $monthly_incentives  = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                ->whereBetween('ordered_time',[Carbon::parse($date)->startOfMonth(),Carbon::parse($date)->endOfMonth()])
                ->sum('restaurant_commision');
            if(empty($date)){
                $date = Carbon::now();
                $graph_data = array();     
                $total_day = Carbon::now()->daysInMonth;
                for($i = 0; $i<$total_day; $i++){
                    $current_date = "";
                    $current_date = $date->startOfMonth()->addDays($i)->format('Y/m/d');
                    $current_date_graph_data = new stdClass();
                    $current_date_graph_data->total_amount = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                    ->whereBetween('created_at',[$current_date." 00:00:00",$current_date." 23:59:59"])
                    ->sum('payout_amount');
                    $current_date_graph_data->total_orders = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                    ->whereBetween('ordered_time',[$current_date." 00:00:00",$current_date." 23:59:59"])->count();
                    $current_date_graph_data->day = $current_date;
                    $graph_data[] = $current_date_graph_data;
                }
            }else{
                $current_date = $request->filter_date;
                $current_date_graph_data = new stdClass();                
                $current_date_graph_data->total_orders = $foodrequest->where('restaurant_id',$restaurant_id)->where('is_paid',1)
                ->whereBetween('ordered_time',[$current_date." 00:00:00",$current_date." 23:59:59"])->count();
                $current_date_graph_data->total_amount = $this->restaurant_payout_history->where('restaurant_id',$restaurant_id)
                ->whereBetween('created_at',[$current_date." 00:00:00",$current_date." 23:59:59"])
                ->sum('payout_amount');
                $current_date_graph_data->day = $current_date;
                $graph_data = array($current_date_graph_data);
            }
            return response()->json(array('status'=>true,'monthly_earnings'=>round($monthly_earnings,2),'monthly_incentives'=> round($monthly_incentives,2),'graph_data'=>$graph_data), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_restaurant_status(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;

            $restaurantdet = $this->restaurants->find($restaurant_id);
            if (empty($restaurantdet)) {
                $response_array = array('status' => false, 'message' => "Invalid authid");
                return response()->json($response_array, 200);
            }

            $status = isset($restaurantdet->status) ? $restaurantdet->status : "";

            $currency_code = "IQD";
            $currency_symbol = "IQD";

            $response_array = array('status' => true, 'details' => $status, 'currency_code' => $currency_code, 'currency_symbol' => $currency_symbol);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * Get payout summary details
     *
     * @param object $request
     *
     * @return json $response
     */
    public function get_payout_summary(Request $request)
    {
        try {
            $restaurant_id = $request->header('authId') ?: $request->authId;
            $start_date = isset($request->start_date)?$request->start_date:date("Y-m-d H:i:s");
            $end_date = isset($request->end_date)?$request->end_date:date("Y-m-d H:i:s");
            $payout_orders = $this->foodrequest->where('restaurant_settlement_status',0)->where('restaurant_id',$restaurant_id)->where('status',7)
                ->whereBetween('ordered_time',[$start_date,$end_date])->where('is_paid',1)->get();
            $order_list = array();
            foreach($payout_orders as $key)
            {
                $order_list[] = array(
                    'request_id'=>$key->id,
                    'order_id'=>$key->order_id,
                    'ordered_on'=>$key->ordered_time,
                    //'bill_amount'=>$key->bill_amount,
                    'item_total'=>$key->item_total,
                    'offer_discount'=>$key->offer_discount,
                    'restaurant_discount'=>$key->restaurant_discount,
                    'restaurant_packaging_charge'=>$key->restaurant_packaging_charge,
                    'tax'=>$key->tax,
                    'restaurant_commision' => $key->restaurant_commision
                );

            }
            $response_array = array('status' => true,'details' => $order_list);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * Change password by validate old password
     *
     * @param object $request
     *
     * @return json $response
     */
    public function change_password(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required'
            ]);
            $lang = isset($request->lang)?$request->lang:'en';
            if($validator->fails()) {
                $error_messages = implode(',',$validator->messages()->all());
                $response_array = array('status' => false, 'message' => $error_messages);
                return response()->json($response_array, 200);
            }else
            {
                $restaurant_id = $request->header('authId') ?: $request->authId;
                $check_password = $this->restaurants->where('id',$restaurant_id)->where('org_password',$request->old_password)->first();
                if(!$check_password)
                {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.old_password_does_not_match', $lang));
                    return response()->json($response_array, 200);
                }
                $check_password->org_password = $request->new_password;
                $check_password->password = Hash::make($request->new_password);
                $check_password->save();
                $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.password_updated_successfully', $lang));
                return response()->json($response_array, 200);
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function base_image_upload_product($request,$key)
    {
        $imageName = $request->file($key)->getClientOriginalName();
        $ext = $request->file($key)->getClientOriginalExtension();
        $imageName = self::generate_random_string().'.'.$ext;
        //$request->file($key)->move('public/uploads/product/',$imageName);
        $filePath = "uploads/product";
        $filetype = Storage::disk('spaces')->putFile($filePath,$request->$key,'public');
        return $filetype;
    }
    public function get_cms_pages()
    {
        try {
            $getdata = array(
                'about_us' => APP_BASE_URL . 'cms/about-us',
                'help' => APP_BASE_URL . 'cms/help',
                'faq' => APP_BASE_URL . 'cms/faq',
                'user_privacypolicy' => APP_BASE_URL . 'cms/user_privacypolicy',
                'restaurant_privacypolicy' => APP_BASE_URL . 'cms/restaurant_privacypolicy',
                'driver_privacypolicy' => APP_BASE_URL . 'cms/driver_privacypolicy',
                'user_termsandcondition' => APP_BASE_URL . 'cms/user_termsandcondition',
                'restaurant_termsandcondition' => APP_BASE_URL . 'cms/restaurant_termsandcondition',
                'driver_termsandcondition' => APP_BASE_URL . 'cms/driver_termsandcondition',
                'web_api_key' => GOOGLE_API_KEY,
                'android_api_key' => ANDROID_API_KEY,
                'ios_api_key' => IOS_API_KEY,
                'country_currency' => DEFAULT_CURRENCY_SYMBOL
            );
            $response_array = array('status' => true, 'details' => $getdata);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * import menu item data function
     * @param array item_id
     * @return json boolean value
     */
    public function importMenuItemData(Request $request){
        try {
            dispatch(new ImportMenuItemJob($request->item_id));
            return response()->json(array('status' => true ,'item_id' => $request->item_id), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
}