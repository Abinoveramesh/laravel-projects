<?php

namespace App\Http\Controllers\api;

use Validator;
use Carbon\Carbon;
use DB;
use App\Model\ProviderLogDeviceToken;
use App\Model\Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use App\Model\Deliverypartners;
use GuzzleHttp\Client;
use Log;
use App\Model\DeliveryInstruction;
use App\Base\Helpers\ExceptionHandlerModel;

class Provider_LoginController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_profile(Request $request)
    {
        try {
            $partner_id = $request->header('authId');
            $data = $this->deliverypartners->where('id', $partner_id)->first();

            $ratings = $this->order_ratings->with('Foodrequest')
                ->wherehas('Foodrequest', function ($q) use ($partner_id) {
                    $q->where('delivery_boy_id', $partner_id);
                })
                ->avg('delivery_boy_rating');

            $partner_rating = round($ratings, 1);

            $response_array = array(
                'status' => true,
                'id' => $data->id,
                'partner_id' => $data->partner_id,
                'name' => $data->name,
                'phone' => $data->phone,
                'address' => isset($data->Deliverypartner_detail) ? $data->Deliverypartner_detail->address_line_1 : "",
                'profile_pic' => BASE_AWS . $data->profile_pic,
                'driving_license_no' => $data->driving_license_no,
                'service_zone' => isset($data->Deliverypartner_detail->Citylist) ? $data->Deliverypartner_detail->Citylist->city : "",
                'is_approved' => $data->status,
                'joining_date' => date("d-m-Y", strtotime($data->created_at)),
                'bank_name' => isset($data->Deliverypartner_detail) ? $data->Deliverypartner_detail->bank_name : "",
                "acc_no" => isset($data->Deliverypartner_detail) ? $data->Deliverypartner_detail->account_no : "",
                "ifsc_code" => isset($data->Deliverypartner_detail) ? $data->Deliverypartner_detail->ifsc_code : "",
                'rating' => $partner_rating,
                'city' => isset($data->Deliverypartner_detail->Citylist) ? $data->Deliverypartner_detail->Citylist->city : "",
                'earnings' => 1500.00
            );

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
    public function send_otp_login(Request $request)
    {
        try {
            $phone = (string)$request->phone;
            $otp = rand(10000, 99999);
            $message = 'OTP to verify ' . APP_NAME . ' Application : ' . $otp;
            $sendSms = $this->send_otp_softsms($phone, $otp);
            $lang = isset($request->lang)?$request->lang:'en';
            $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.otp_sent',$lang), 'otp' => $otp);
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
    public function provider_login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    // 'device_token' => 'required',
                    'phone' => 'required',
                    'password' => 'required'
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
                $deliverypartners = $this->deliverypartners;
                $device_token = $request->device_token;
                $device_type = $request->device_type;
                $phone = $request->phone;
                $lang = isset($request->lang)?$request->lang:'en';
                $password = $this->encrypt_password($request->password);
                $phone = $this->str_replace_first("+", "", $phone);
                $data = $deliverypartners::where('phone', 'like', '%' . $phone . '%')->where('password', $password)->first();
                if ($data) {
                    if ($data->is_approved == 0) {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.not_yet_approved_kindly_contact_msg',$lang));
                    } else {
                        $authId = $data->id;
                        $profile_image = BASE_AWS . $data->profile_pic;
                        if ($profile_image == NULL || $profile_image == "") {
                            $profile_image = BASE_URL . PROFILE_ICON;
                        }
                        if ($data->name != NULL) {
                            $name = $data->name;
                        } else {
                            $name = "";
                        }
                        $authToken = $this->generateRandomString();
                        $partner_id = $data->partner_id;
                        $deliverypartners::where('id', $data->id)->update(['device_type' => $device_type, 'device_token' => $device_token, 'authToken' => $authToken]);
                        $this->delivery_partner_log->insert([
                            'delivery_partner_id' => $authId,
                            'description' => "Logged In",'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $providerLogDeviceToken = ProviderLogDeviceToken::where('provider_id',(string)$authId)->first();
                        if($providerLogDeviceToken)
                        {
                            Log::info('providerLogDeviceToken  :: ' . $providerLogDeviceToken);
                            ProviderLogDeviceToken::where('provider_id',(string)$authId)->update(['auth_token' => (string)$authToken , 'status' => '1']);
                            $client = new Client();
                            $client->get(SOCKET_URL.'/provider_log_device_token/'.$authId);
                        }
                        else
                        {
                            $newProviderToken = new ProviderLogDeviceToken();
                            $newProviderToken->provider_id = (string)$authId;
                            $newProviderToken->auth_token = (string)$authToken;
                            $newProviderToken->status = '0';
                            $newProviderToken->save();
                            Log::info('newProviderToken  :: ' . $newProviderToken);
                        }
                        $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.login_success',$lang), 'authId' => $authId, 'authToken' => $authToken, 'phone' => $phone, 'profile_image' => $profile_image, 'email' => "", 'user_name' => $name, 'partner_id' => $partner_id);
                    }
                } else {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.invalid_login',$lang));
                }
            }
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
    public function update_profile(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $deliverypartners = $this->deliverypartners;
            $custom = $this->custom;
            $update = array();
            $data = $deliverypartners::where('id', $request->id)->first();
            if ($request->name) {
                $update['name'] = $request->name;
            }
            if ($request->email) {
                $update['email'] = $request->email;
            }
            if ($request->password) {
                $update['password'] = $this->encrypt_password($request->password);
            }
            $deliverypartners::where('id', $request->id)->update($update);
            $data = $deliverypartners::where('id', $request->id)->first();
            $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.profile_update',$lang), 'data' => $data);
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
    public function forgot_password(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'phone' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:'en';
                $phone = $request->phone;
                $deliverypartners = $this->deliverypartners;
                $phone = $this->str_replace_first("+", "", $phone);
                $check_user = $deliverypartners::where('phone', 'like', '%' . $phone . '%')->first();
                if ($check_user)  {
                    $phone = (string)$request->phone;
                    $otp = rand(10000, 99999);
                    $message = 'OTP to verify ' . APP_NAME . ' Application : ' . $otp;
                    Log::info('Forgot Password OTP for delivery partner' . $otp);

                    $sendSms = $this->send_otp_softsms($phone, $otp);
                    $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.otp_sent',$lang), 'otp' => $otp);
                } else {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.mobile_notregister',$lang));
                }
            }
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
    public function reset_password(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'password' => 'required',
                    'phone' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:'en';
                $password = $this->encrypt_password($request->password);
                $phone = $request->phone;
                $deliverypartners = $this->deliverypartners;
                $phone = $this->str_replace_first("+", "", $phone);
                $get_user = $deliverypartners::where('phone', 'like', '%' . $phone . '%')->first();
                $deliverypartners::where('phone', 'like', '%' . $phone . '%')->update(['password' => $password]);
                $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.pwd_reset',$lang));
            }
            return response()->json($response_array, 200);
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
            $user_id = $request->header('authId');
            $date = ((isset($request->filter_date) && $request->filter_date != '')) ? new \DateTime($request->filter_date) : new \DateTime();
            $start_date = !empty($request->start_date)?$request->start_date . " 00:00:00":$date->format('Y-m-d') . " 00:00:00";
            $end_date = !empty($request->end_date)?$request->end_date . " 23:59:59":$date->format('Y-m-d') . " 23:59:59";
            $today_earnings = $this->driver_payout_history->where('delivery_boy_id', $user_id)
                ->whereBetween('created_at', [$start_date . " 00:00:00", $end_date . " 23:59:59"])
                ->sum('payout_amount');
            $today_orders = $this->foodrequest->where('delivery_boy_id', $user_id)
                ->whereBetween('ordered_time', [$start_date . " 00:00:00", $end_date . " 23:59:59"])
                ->where('status','<=',7)
                ->where('is_paid', 1)
                ->where('paid_type', 1)
                ->count();
            return response()->json(array('status' => true, 'today_earnings' => $today_earnings, 'total_orders' => $today_orders), 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function earnings_order_detail(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $user_id = $request->header('authId');
            $foodrequest = $this->foodrequest;
            if ((isset($request->start_date)) && (isset($request->end_date))) {
                $orders = $foodrequest::where('delivery_boy_id', $user_id)
                    ->where('is_paid', 1)
                    ->whereBetween('ordered_time', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
                    ->latest()->limit(25)->get();
            } else {
                $orders = $foodrequest::where('delivery_boy_id', $user_id)
                    ->where('is_paid', 1)
                    ->whereBetween('ordered_time', [$date->format('Y-m-d') . " 00:00:00", $date->format('Y-m-d') . " 23:59:59"])
                    ->get();
            }
            $order_list = array();
            foreach ($orders as $key) {
                $order_detail = $this->requestdetail->where('request_id', $key->id)->get();
                $order_list_detail = array();
                foreach ($order_detail as $k) {
                    $food_quantity = array();
                    if (!empty($k->FoodQuantity)) {
                        $quantity_price = $k->quantity * $k->food_quantity_price;
                        $food_quantity[] = array(
                            'id' => isset($k->FoodQuantity->id) ? $k->FoodQuantity->id : '',
                            'name' => (isset($k->FoodQuantity->name) ? $k->FoodQuantity->name : ''),
                            'price' => $quantity_price,
                            'created_at' => isset($k->FoodQuantity->created_at) ? date("Y-m-d H:i:s", strtotime($k->FoodQuantity->created_at)) : '',
                            'updated_at' => isset($k->FoodQuantity->updated_at) ? date("Y-m-d H:i:s", strtotime($k->FoodQuantity->updated_at)) : '',
                        );
                    }
                    if (isset($k->FoodQuantity)) $k->FoodQuantity->price = $k->food_quantity_price;
                    $order_list_detail[] = array(
                        'food_id' => (!empty($k->Foodlist) ? $k->Foodlist->id : ""),
                        'food_name' => (!empty($k->Foodlist) ? $k->Foodlist->name : ""),
                        'food_quantity' => $k->quantity,
                        'tax' => (!empty($k->Foodlist) ? $k->Foodlist->tax : ""),
                        'item_price' => (!empty($k->Foodlist) ? $k->Foodlist->price : 0) * $k->quantity,
                        'is_veg' => (!empty($k->Foodlist) ? $k->Foodlist->is_veg : ""),
                        'food_size' => $food_quantity,
                        'add_ons' => $k->RequestdetailAddons
                    );
                }
                $restaurant_detail = $this->restaurants->find($key->restaurant_id);
                if ($restaurant_detail) {
                    $order_list[] = array(
                        'request_id' => $key->id,
                        'order_id' => $key->order_id,
                        'restaurant_id' => $restaurant_detail->id,
                        'restaurant_name' => $restaurant_detail->restaurant_name,
                        'restaurant_image' => SPACES_BASE_URL . $restaurant_detail->image,
                        'ordered_on' => $key->ordered_time,
                        'bill_amount' => $key->bill_amount,
                        'item_list' => $order_list_detail,
                        'item_total' => $key->item_total,
                        'offer_discount' => $key->offer_discount,
                        'loyalty_discount' => $key->loyalty_discount,
                        'restaurant_discount' => $key->restaurant_discount,
                        'restaurant_packaging_charge' => $key->restaurant_packaging_charge,
                        'tax' => $key->tax,
                        'delivery_charge' => $key->delivery_charge,
                        'driver_tip' => $key->driver_tip,
                        'delivery_address' => $key->delivery_address,
                        'restaurant_address' => $restaurant_detail->address
                    );
                }
            }
            if (count($order_list) != 0) {
                $response_array = array('status' => true, 'past_orders' => $order_list);
            } else {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_orders_received',$lang));
            }
            return response()->json($response_array, 200);
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
            $user_id = $request->header('authId');
            $foodrequest = $this->foodrequest;
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            $date = ((isset($request->filter_date) && $request->filter_date != '')) ? $request->filter_date : Carbon::now();

            //get weekly earnings
            $weekly_earnings = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfWeek(), Carbon::parse($date)->endOfWeek()])
                ->sum('bill_amount');
            $weekly_incentives = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfWeek(), Carbon::parse($date)->endOfWeek()])
                ->sum('delivery_boy_commision');
            $graph_data = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfWeek(), Carbon::parse($date)->endOfWeek()])
                ->select(array(DB::Raw('sum(bill_amount) as total_amount'), DB::Raw('count(id) as total_orders'), DB::Raw('DATE(ordered_time) day')))
                ->groupBy('day')
                ->get();
            $response_array = array('status' => true, 'weekly_earnings' => round($weekly_earnings, 2), 'weekly_incentives' =>
                round($weekly_incentives, 2), 'graph_data' => $graph_data);
            return response()->json($response_array, 200);
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
            $user_id = $request->header('authId');
            $foodrequest = $this->foodrequest;
            $date = ((isset($request->filter_date) && $request->filter_date != '')) ? $request->filter_date : Carbon::now();

            //get weekly earnings
            $monthly_earnings = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfMonth(), Carbon::parse($date)->endOfMonth()])
                ->sum('bill_amount');
            $monthly_incentives = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfMonth(), Carbon::parse($date)->endOfMonth()])
                ->sum('delivery_boy_commision');
            $graph_data = $foodrequest->where('delivery_boy_id', $user_id)->where('is_paid', 1)
                ->whereBetween('ordered_time', [Carbon::parse($date)->startOfMonth(), Carbon::parse($date)->endOfMonth()])
                ->select(array(DB::Raw('sum(bill_amount) as total_amount'), DB::Raw('count(id) as total_orders'), DB::Raw('DATE(ordered_time) day')))
                ->groupBy('day')
                ->get();
            $response_array = array('status' => true, 'monthly_earnings' => round($monthly_earnings, 2), 'monthly_incentives' =>
                round($monthly_incentives, 2), 'graph_data' => $graph_data);
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
    public function available_status_update(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'status' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:'en';
                $user_id = $request->header('authId');
                $available_status = $request->status; // status - 1 online, - 2 offline
                if ($available_status == 1) {
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $user_id, 'description' => "Online",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.available_status_updated_successfully',$lang));
                } else {
                    $order_count = $this->foodrequest->where('delivery_boy_id',$user_id)->whereNotIn('status',[7,9,10,2])->count();
                    if($order_count!=0){
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.available_status_updated_failed',$lang));
                    }else{
                        $this->delivery_partner_log->insert(['delivery_partner_id' => $user_id, 'description' => "Offline",'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')]);
                        $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.available_status_updated_successfully',$lang));
                    }
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * delivery boy logout
     *
     * @param object $request
     *
     * @return json $response
     */
    public function logout(Request $request)
    {
        try {
            $user_id = $request->header('authId');
            $lang = isset($request->lang)?$request->lang:'en';
            $order_count = $this->foodrequest->where('delivery_boy_id',$user_id)->whereNotIn('status',[7,9,10,2])->count();
            if($order_count!=0){
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.logout_failed',$lang));
            }else{
                $deliverypartners = $this->deliverypartners;
                $deliverypartners::where('id', '=', $user_id)->update(['authToken' => 0]);
                $this->delivery_partner_log->insert([
                    'delivery_partner_id' => $user_id,
                    'description' => "Logged Out",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                ProviderLogDeviceToken::where('provider_id',$user_id)->delete();
                $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.logout',$lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * API for driver payout
     *
     * @param object $request
     *
     * @return json $response
     */
    public function payout_details(Request $request)
    {
        try {
            $user_id = $request->header('authId');
            if ($user_id == '') {
                $error_messages = "AuthID should not be empty";
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }
            //get payout history
            $payout_history = $this->driver_payout_history->where('delivery_boy_id', $user_id)
                ->orderBy('created_at', 'desc')->limit(5)->get();
            $pending_amount = $this->deliverypartners->where('id', $user_id)
                ->first();
            $response_array = array('status' => true, 'pending_payout' => $pending_amount->pending_payout, 'payout_history' => $payout_history);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_provider_timeout()
    {
        try {
            $settings = $this->settings;
            $check = $settings->where('key_word', 'provider_timeout')->first();
            $response_array = array('status' => true, 'provider_timeout' => $check->value);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /** check provider ideal time and make then offline
     *
     */
    public function check_ideal_drivers()
    {
        // $data = file_get_contents(FIREBASE_URL . "/available_providers/.json");
        // $data = json_decode($data);

         $curl = curl_init();
        $url = FIREBASE_URL . "/available_providers/.json";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // execute and return string (this should be an empty string '')
        $data = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($data);
        print_r($data);
        if ($data != NULL && $data != "") {
            foreach ($data as $key => $value) {
                $driver_id = $key;
                $check = $this->deliverypartners->where('id', $driver_id)->where('status', 1)->count();
                $check_request = $this->foodrequest->where('delivery_boy_id', $driver_id)->whereNotIn('status', [7, 9, 10])->count();
                if ($check != 0) {
                    if ($check_request != 0) {
                        $postdata = array();
                        $postdata['status'] = 0;
                        $postdata = json_encode($postdata);
                        $this->update_firebase($postdata, 'providers_status', $driver_id);
                    } else {
                        $updated_time = isset($value->updated_at) ? $value->updated_at : date("Y-m-d H:i:s");
                        $dt = new Carbon($updated_time);
                        $last_updated_time = $dt->addMinutes(IDEAL_TIME);
                        $current_time = date("Y-m-d H:i:s");
                        if (strtotime($last_updated_time) < strtotime($current_time)) {
                            $result = $this->delete_firebase_node('available_providers', $driver_id);
                            $postdata = array();
                            $postdata['status'] = 0;
                            $postdata = json_encode($postdata);
                            $this->update_firebase($postdata, 'providers_status', $driver_id);
                        }
                    }
                } else {
                    $result = $this->delete_firebase_node('available_providers', $driver_id);
                    $postdata = array();
                    $postdata['status'] = 0;
                    $postdata = json_encode($postdata);
                    $this->update_firebase($postdata, 'providers_status', $driver_id);
                }
            }
        }

    }


    /**
     * check orders that driver not updated the status above notification time
     */
    public function check_ideal_orders()
    {
        log::info('check_ideal_orders  : ' . 'connect');
        $dt = Carbon::now();
        echo $last_updated_time = $dt->subSeconds(NOTIFICATION_TIME);
        $check_request = $this->foodrequest->where('status', 2)->where('updated_at', '<', $last_updated_time)->get();
        dd($check_request);
        if (count($check_request) != 0) {
            foreach ($check_request as $value) {
                // delete request to driver 
                $temp_driver = $provider_id = $value->delivery_boy_id;
                echo "request:" . $request_id = $value->id;
                $restaurant_id = $value->restaurant_id;
                $header = array();
                $header[] = 'Content-Type: application/json';
                $postdata = array();
                $postdata['request_id'] = $request_id;
                $postdata['user_id'] = $value->user_id;
                $postdata['status'] = 1;
                $postdata = json_encode($postdata);

                $ch = curl_init(FIREBASE_URL . "/new_request/$temp_driver.json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                $result = curl_exec($ch);
                curl_close($ch);

                //update reject drivers
                // $current_request = file_get_contents(FIREBASE_URL . "/current_request/" . $request_id . ".json");
                // $current_request = json_decode($current_request);

                 $curl = curl_init();
        $url = FIREBASE_URL . "/current_request/" . $request_id . ".json";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // execute and return string (this should be an empty string '')
        $current_request = curl_exec($curl);

        curl_close($curl);
        $current_request = json_decode($current_request);

                if (isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) {
                    $reject_drivers = explode(',', $current_request->reject_drivers);
                }
                $reject_drivers[] = $provider_id;
                $postdata = array();
                $postdata['reject_drivers'] = implode(',', $reject_drivers);
                $postdata = json_encode($postdata);
                $this->update_firebase($postdata, 'current_request', $request_id);

                $restuarant_detail = $this->restaurants::where('id', $restaurant_id)->first();
                $source_lat = $restuarant_detail->lat;
                $source_lng = $restuarant_detail->lng;

                // $data = file_get_contents(FIREBASE_URL . "/available_providers/.json");
                // $data = json_decode($data);

                 $curl = curl_init();
        $url = FIREBASE_URL . "/available_providers/.json";
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // execute and return string (this should be an empty string '')
        $data = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($data);

                print_r($data);
                $temp_driver = $old_provider = 0;
                $last_distance = 0;
                if ($data != NULL && $data != "") {
                    foreach ($data as $key => $value) {
                        # code...
                        $driver_id = $key;

                        //check previous rejected drivers    
                        // $current_request = file_get_contents(FIREBASE_URL . "/current_request/" . $request_id . ".json");
                        // $current_request = json_decode($current_request);

                         $curl = curl_init();
                        $url = FIREBASE_URL . "/current_request/" . $request_id . ".json";
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HEADER, false);

                        // execute and return string (this should be an empty string '')
                        $current_request = curl_exec($curl);

                        curl_close($curl);
                        $current_request = json_decode($current_request);


                        if (isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) {
                            $reject_drivers = explode(',', $current_request->reject_drivers);
                            if (in_array($driver_id, $reject_drivers)) {
                                continue;
                            }
                        }
                        $check = $this->deliverypartners::where('id', $driver_id)->where('status', 1)->first();
                        $check_request = $this->foodrequest->where('delivery_boy_id', $driver_id)->whereNotIn('status', [7, 9, 10])->count();
                        if ($check && $check_request == 0) {
                            if ($old_provider == 0) {
                                $old_provider = -1;
                            }
                            if ($driver_id != $old_provider && $driver_id != $provider_id) {
                                if ($value != NULL && $value != "") {
                                    $driver_lat = $value->lat;
                                    $driver_lng = $value->lng;
                                    $updated_time = isset($value->updated_at) ? $value->updated_at : date("Y-m-d H:i:s");
                                    $dt = new Carbon($updated_time);
                                    $last_updated_time = $dt->addMinutes(IDEAL_TIME);
                                    $current_time = date("Y-m-d H:i:s");
                                    if (strtotime($last_updated_time) >= strtotime($current_time)) {
                                        try {
                                            // $q = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false";
                                            $q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false&key=" . GOOGLE_API_KEY;
                                            // $json = file_get_contents($q);
                                            // $details = json_decode($json, TRUE);
                                             $curl = curl_init();
                       
                        curl_setopt($curl, CURLOPT_URL, $q);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HEADER, false);

                        // execute and return string (this should be an empty string '')
                        $json = curl_exec($curl);

                        curl_close($curl);
                        $details = json_decode($json,TRUE);

                                            // var_dump($details); exit;
                                            if ($details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS') {
                                                $current_distance = (float)$details['rows'][0]['elements'][0]['distance']['text'];

                                                if ($temp_driver == 0) {
                                                    $temp_driver = $driver_id;
                                                    $last_distance = $current_distance;
                                                } else {
                                                    if ($current_distance < $last_distance) {
                                                        $temp_driver = $driver_id;
                                                        $last_distance = $current_distance;
                                                    }
                                                }
                                            }
                                        } catch (Exception $e) {

                                        }
                                    }
                                }
                            }
                        }
                        //print_r($value->lat); exit;
                    }
                }
                //end if and forloop

                //check driver and send request
                echo "driver:" . $temp_driver;
                if ($temp_driver != 0) {
                    $user_data = $this->foodrequest->find($request_id);
                    $user_data->delivery_boy_id = $temp_driver;
                    $user_data->status = 2;
                    $user_data->save();

                    // to insert into firebase
                    $header = array();
                    $header[] = 'Content-Type: application/json';
                    $postdata = array();
                    $postdata['request_id'] = $request_id;
                    $postdata['provider_id'] = (string)$temp_driver;
                    $postdata['user_id'] = $user_data->user_id;
                    $postdata['reject_drivers'] = implode(',', $reject_drivers);
                    $postdata['status'] = 2;
                    $postdata = json_encode($postdata);

                    $ch = curl_init(FIREBASE_URL . "/current_request/$request_id.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    // sending request to driver
                    $header = array();
                    $header[] = 'Content-Type: application/json';
                    $postdata = array();
                    $postdata['request_id'] = $request_id;
                    $postdata['user_id'] = $user_data->user_id;
                    $postdata['status'] = 1;
                    $postdata = json_encode($postdata);

                    $ch = curl_init(FIREBASE_URL . "/new_request/$temp_driver.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    $result = curl_exec($ch);
                    curl_close($ch);

                } else {
                    $title = "No Providers available";

                    $user_data = DB::table('requests')
                        ->where('id', $request_id)
                        ->first();

                    DB::table('requests')->where('id', $request_id)->update(['delivery_boy_id' => 0, 'status' => 1]);

                    // to insert into firebase
                    $header = array();
                    $header[] = 'Content-Type: application/json';
                    $postdata = array();
                    $postdata['request_id'] = $request_id;
                    $postdata['provider_id'] = (string)0;
                    $postdata['user_id'] = $user_data->user_id;
                    $postdata['status'] = 1;
                    $postdata = json_encode($postdata);

                    $ch = curl_init(FIREBASE_URL . "/current_request/$request_id.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    //update in firebase for restaurant notification
                    $postdata = array();
                    $postdata['status'] = 10;
                    $postdata = json_encode($postdata);
                    $this->update_firebase($postdata, 'restaurant_request/' . $restaurant_id, $request_id);

                }
                //end condition
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_order_list(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $delivery_boy_id = $request->header('authId');
            } else {
                $delivery_boy_id = $request->authId;
            }
            $getZone = $this->deliverypartners->where('id',$delivery_boy_id)->with('Addzone')->first();
            if(!empty($getZone->Addzone->zone_geofencing)) {
                $zoneData = $getZone->Addzone->zone_geofencing->polygons;
            }else {
                $zoneData = '';
            }
            $request_detail = $this->foodrequest->where(function($query) use($delivery_boy_id){
                $query->where('delivery_boy_id',$delivery_boy_id)->orWhere(function($query) use($delivery_boy_id){
                    $query->where('delivery_boy_id',0)->where('temp_drivers',$delivery_boy_id);
                });
            })->whereIn('status', [2,3,4,5,6,8])->get();
            $assigned_order_count = $this->foodrequest->where('temp_drivers', $delivery_boy_id)->where('status',2)->count();
            $accepted_order_count = $this->foodrequest->where('delivery_boy_id', $delivery_boy_id)->whereIn('status',[3,4,5,6,8])->count();
            $order_data = array();
            $assigned_order_data = array();
            $processing_order_data = array();
            if (!empty($request_detail)) {
                foreach ($request_detail as $value) {
                    $order_id = $value->order_id;
                    $request_id = $value->id;
                    $ordered_time = $value->ordered_time;
                    $restaurant_detail = $this->restaurants->where('id', $value->restaurant_id)->where('status', 1)->first();
                    if(!empty($restaurant_detail)){
                        $restaurant_detail->image = SPACES_BASE_URL.$restaurant_detail->image;
                    }
                    $user_detail = $this->users->where('id', $value->user_id)->first();
                    if(empty($user_detail->profile_image)){
                        $profile_image = "http://www.freeiconspng.com/uploads/account-profile-icon-1.png";
                        $this->users->where('id', $value->user_id)->update(['profile_image'=>$profile_image]);
                        $user_detail = $this->users->where('id', $value->user_id)->first();
                    }
                    $address_detail = array();
                    $request_status = $value->status;
                    $address_detail [] = array(
                        'd_address' => $value->delivery_address,
                        's_address' => $restaurant_detail->address,
                        'd_lat' => $value->d_lat,
                        'd_lng' => $value->d_lng,
                        's_lat' => $restaurant_detail->lat,
                        's_lng' => $restaurant_detail->lng
                    );
                    $food_detail = array();
                    $bill_detail = array();
                    $data = $this->requestdetail->where('request_detail.request_id', $request_id)
                        ->join('food_list', 'food_list.id', '=', 'request_detail.food_id')
                        ->select('request_detail.quantity as quantity', 'food_list.name as food', 'food_list.price as price_per_quantity', 'food_list.is_veg as is_veg')
                        ->get();
                    foreach ($data as $d) {
                        $food_detail[] = array(
                            'name' => $d->food,
                            'quantity' => $d->quantity,
                            'price' => $d->quantity * $d->price_per_quantity,
                            'is_veg' => $d->is_veg
                        );
                    }
                    $bill_detail[] = array(
                        'item_total' => $value->item_total,
                        'offer_discount' => $value->offer_discount,
                        'loyalty_discount' => $value->loyalty_discount,
                        'driver_tip' => $value->driver_tip,
                        'restaurant_discount' => $value->restaurant_discount,
                        'packaging_charge' => $value->restaurant_packaging_charge,
                        'tax' => $value->tax,
                        'delivery_charge' => $value->delivery_charge,
                        'bill_amount' => $value->bill_amount,
                        'driver_comment'=>isset($value->driver_comment)?$value->driver_comment:"",
                        'paid_type'=>isset($value->paid_type)?$value->paid_type:1,
                    );
                    $date = date('Y-m-d H:i:s');
                    $updated_time = strtotime($value->updated_at);
                    $current_time = strtotime($date);
                    $notification_diff_time = $current_time - $updated_time;
                    $notification_remaining_time = (int)NOTIFICATION_TIME - $notification_diff_time;
                    if($notification_remaining_time<0){
                        $notification_remaining_time = 0;
                    }
                    $distance =  floatval($value->distance);
                    $instruction_id = !empty($value->instruction_id)?json_decode($value->instruction_id):[];
                    $instruction_list = DeliveryInstruction::withTrashed()->whereIn('id',$instruction_id)->get();
                    $order_data_detail = array(
                        'request_id' => $request_id,
                        'ordered_time' => $ordered_time,
                        'order_id' => $order_id,
                        'restaurant_detail' => $restaurant_detail,
                        'user_detail' => $user_detail,
                        'address_detail' => $address_detail,
                        'bill_detail' => $bill_detail,
                        'food_detail' => $food_detail,
                        'request_status' => $request_status,
                        'notification_time' => $notification_remaining_time,
                        'distance'=> round($distance,2),
                        'assigned_time' => isset($value->updated_at)?date("Y-m-d H:i:s", strtotime($value->updated_at)):"",
                        'delivery_instruction'=>(!empty($instruction_list) && count($instruction_list)!=0)?$instruction_list:[],
                        'image_base_url'=>SPACES_BASE_URL
                    );
                    if($value->status == 2){
                        $assigned_order_data[] = $order_data_detail;
                    }else{
                        $processing_order_data[] = $order_data_detail;
                    }
                }
                $order_data = array_merge($assigned_order_data,$processing_order_data);
            }
            $response_array = array('status' => true,'zone' => $zoneData, 'orders'=>$order_data,'assigned_order'=>$assigned_order_count,'accepted_order'=>$accepted_order_count);
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
    public function delete_account(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $delivery_boy_id = $request->header('authId');
            } else {
                $delivery_boy_id = $request->authId;
            }
            $deliveryBoy = Deliverypartners::where('id',$delivery_boy_id)->delete();
            if($deliveryBoy)
            {
                $response_array = array('status' => true, 'message' => 'Your Account Deleted Succesfully');
            }
            else
            {
                $response_array = array('status' => false);
            }
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
    public function iosDeleteAccountoption()
    {
        try {
            $data['user_key'] = USER_IOS_DELETE_KEY;
            $data['rider_key'] = RIDER_IOS_DELETE_KEY;
            $response = array('status' => true,'data'=>$data);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
}