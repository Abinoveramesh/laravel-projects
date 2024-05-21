<?php

namespace App\Http\Controllers\api;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\api\BaseController;
use Illuminate\Support\Facades\Crypt;
use App\Model\Users;
use Log;
use DB;
use App\Model\CurrentAddress;
use App\Base\Helpers\ExceptionHandlerModel;


class LoginController extends BaseController
{
    //

    public function get_profile(Request $request)
    {
        try {
            # code...
            $user_id = $request->header('authId');
            if ($user_id == '') $user_id = ($request->authId) ? $request->authId : "";
            $data = $this->users->where('id', $user_id)->get();

            foreach ($data as $d) {
                $d->password = $this->decrypt_password($d->password);
                $d->profile_image = UPLOADS_PATH . $d->profile_image;
            }
            $response_array = array('status' => true, 'data' => $data);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
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
            if($request->auth_key) {
                $decryptionIv = 'qwertyasdfghzxcv';
                // Store the decryption key
                $decryptionKey = "truelyproducts";
                $ciphering = "AES-128-CBC";
                $options = 0;
                // Use openssl_decrypt() function to decrypt the data
                $decryption = openssl_decrypt($request->auth_key, $ciphering,
                $decryptionKey, $options, $decryptionIv);
                $finalValue = $decryption-$phone;
                if($finalValue != 0) {
                    $responseArray = array('status' => false, 'error_code' => 401, 'message' => "Unauthorized Access");
                    return response()->json($responseArray, 200);
                }
            }
            $otp = rand(10000, 99999);
            $lang = isset($request->lang)?$request->lang:"en";
            Log::info("login otp: " . $otp);
            $message = 'OTP to verify ' . APP_NAME . ' Application : ' . $otp;
            $getuser = $this->users->where('phone', $phone)->where('is_guest_user', 0)->first();
            if ($request->is_forgot_password) {
                $isForgotPassword = $request->is_forgot_password;
            } else {
                $isForgotPassword = 0;
            }
            if ($getuser) {
                if ($isForgotPassword == 1) {
                    $this->send_otp_softsms($phone, $otp);
                }
                $isNewUser = 0;
            } else {
                $this->send_otp_softsms($phone, $otp);
                $isNewUser = 1;
            }
            $responseArray = array('status' => true, 'message' => $this->language_string_translation('constants.otp_sent', $lang), 'otp' => $otp, 'is_new_user' => $isNewUser);
            return response()->json($responseArray, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function verify_otp_login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            array(
                'phone' => 'required',
                'otp' => 'required'
            ));

        if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
        } else {

            $phone = (string)$request->phone;
            $otp = (string)$request->otp;
            $verify_sms = $this->verify_otp($phone, $otp);

            if ($verify_sms) {
                // echo $verify_sms->status; exit;
                if ($verify_sms->status == "ERROR") {
                    $response_array = array('status' => false, 'message' => 'Invalid OTP');
                } else {
                    $response_array = array('status' => true, 'message' => 'OTP verified successfully');
                }
            } else {
                $response_array = array('status' => false, 'message' => 'Something went wrong. Try after sometime');
            }
        }

        $response = response()->json($response_array, 200);
        return $response;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'phone' => 'required',
                    'password' => 'required',
                    'device_token' => 'required',
                    'login_type' => 'required',
                    'device_type' => 'required|in:' . ANDROID . ',' . IOS . ',' . WEB,
                ));
            // if($request->device_token == '' || $request->device_token == 'null')
            // {
            //     $response_array = array('status' => false, 'message' => 'Please activate the VPN on your device to have full App installation');
            //     return response()->json($response_array, 200);
            // }
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:"en";
                $users = $this->users;
                $phone = $request->phone;
                $email = isset($request->email)?$request->email:"";
                $name = isset($request->name)?$request->name:"";
                $new_state_id = isset($request->state_id)?$request->state_id:0;
                $state_id = isset($request->city_id)?$request->city_id:0;
                $password = $this->encrypt_password($request->password);
                $device_type = $request->device_type;
                $device_token = $request->device_token;
                $login_type = $request->login_type;
                $authToken = $this->generateRandomString();
                $check_phone = $users::where('phone', $phone)->where('is_guest_user', 0)->get();
                $check_email = "";
                if($email!=""){
                    $check_email = $users::where('email', $email)->get();
                }
                $profile_image = "http://www.freeiconspng.com/uploads/account-profile-icon-1.png";
                if (count($check_phone) != 0) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.mobile_exist', $lang));
                } elseif ($email!="" && count($check_email) != 0) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.email_exist', $lang));
                } else {
                    $check_guest_user = $users::where('phone', $phone)->first();
                    if ($check_guest_user) {
                        $users::where('phone', $phone)->update(['phone' => $phone,
                            'name' => $name,
                            'email' => $email,
                            'new_state_id' => $new_state_id,
                            'state_id' => $state_id,
                            'authToken' => $authToken,
                            'device_type' => $device_type,
                            'password' => $password,
                            'device_token' => $device_token,
                            'profile_image' => $profile_image,
                            'login_type' => $login_type,
                            'is_guest_user' => 0
                        ]);
                    } else {
                        $new_user = array();
                        if ($device_type != WEB) {
                            $new_user[] = array(
                                'phone' => $phone,
                                'name' => $name,
                                'email' => $email,
                                'new_state_id' => $new_state_id,
                                'state_id' => $state_id,
                                'authToken' => $authToken,
                                'device_type' => $device_type,
                                'password' => $password,
                                'device_token' => $device_token,
                                'profile_image' => $profile_image,
                                'login_type' => $login_type,
                                'referral_code' => $this->generateRandomString_referral()
                            );
                        } else {
                            $new_user[] = array(
                                'name' => $request->first_name . ' ' . $request->last_name,
                                'phone' => $phone,
                                'email' => $email,
                                'new_state_id' => $new_state_id,
                                'state_id' => $state_id,
                                'authToken' => $authToken,
                                'password' => $password,
                                'device_type' => $device_type,
                                'device_token' => $device_token,
                                'profile_image' => $profile_image,
                                'login_type' => $login_type,
                                'referral_code' => $this->generateRandomString_referral()
                            );
                        }
                        $users::insert($new_user);
                    }
                    $data = $users::where('phone', '=', $phone)->with('State','City')->first();
                    if(!empty($data->State) || $data->State != ''){
                        $state = $data->State->state;
                    }else{
                        $state = '';
                    }
                    if(!empty($data->City) || $data->City != ''){
                    $city = $data->City->state;
                    }else{
                        $city = '';
                    }
                    $authToken = $data->authToken;
                    $authId = $data->id;
                    $this->user_authentication->insert(['authId' => $authId, 'authToken' => $authToken]);
                    //send email to user
                    // if (EMAIL_ENABLE == 1) {
                    //     $data->subject = "Welcome to " . APP_NAME;
                    //     // $this->send_mail($data,'user_welcome');
                    // }
                    // $message = "Thank you for registering with us. Use MKNT001 to get 1 IQD discount on your first order. Offer valid for 24 hours.";
                    // $sendSms = $this->send_otp_softsms($phone, $message);

                    $response_array = array('status' => true, 'login_type' => 0, 'authToken' => $authToken, 'authId' => $authId, 'phone' => $data->phone, 'profile_image' => $profile_image, 'email' => $email, 'name' => $data->name, 'state' => $state , 'city' => $city, 'is_guest_user' => 0);
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function register_new(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'phone' => 'required',
                    'email' => 'required',
                    'country_code' => 'required',
                    'name' => 'required',
                    'password' => 'required',
                    'confirm_password' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {

                if ($request->password != $request->confirm_password) {
                    $response_array = array('status' => false, 'message' => 'Password and Confirm Password mismatch');
                    $response = response()->json($response_array, 200);
                    return $response;
                }

                $users = $this->users;
                $phone = $request->phone;
                $email = $request->email;
                $password = $this->encrypt_password($request->password);
                $device_type = "web";
                $device_token = "NULL";
                $login_type = 0;
                $authToken = $this->generateRandomString();

                $check_phone = $users::where('phone', $phone)->get();
                $check_email = $users::where('email', $email)->get();
                $profile_image = "http://www.freeiconspng.com/uploads/account-profile-icon-1.png";

                if (count($check_phone) != 0) {
                    $response_array = array('status' => false, 'message' => 'Mobile number already exist');
                } elseif (count($check_email) != 0) {
                    $response_array = array('status' => false, 'message' => 'Email-id already exist');
                } else {
                    $new_user = array();

                    $new_user[] = array(
                        'name' => $request->name,
                        'phone' => $phone,
                        'email' => $email,
                        'authToken' => $authToken,
                        'password' => $password,
                        'device_type' => $device_type,
                        'device_token' => $device_token,
                        'profile_image' => $profile_image,
                        'login_type' => $login_type,
                        'referral_code' => $this->generateRandomString_referral(),
                        'country_code' => $request->country_code
                    );

                    $users::insert($new_user);

                    $data = $users::where('phone', '=', $phone)->first();
                    $authToken = $data->authToken;
                    $authId = $data->id;

                    //send email to user
                    if (EMAIL_ENABLE == 1) {
                        $data->subject = "Welcome to " . APP_NAME;
                        // $this->send_mail($data,'user_welcome');
                    }

                    $message = "Thank you for registering with us. Use MKNT001 to get 1 IQD discount on your first order. Offer valid for 24 hours.";

                    // $sendSms = $this->send_otp_softsms($phone, $message);

                    $response_array = array('status' => true, 'login_type' => 0, 'authToken' => $authToken, 'authId' => $authId, 'phone' => $data->phone, 'profile_image' => $profile_image, 'email' => $email);
                }
            }

            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    // 'device_token' => 'required',
                    'login_type' => 'required',
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
                $users = $this->users;
                $device_token = $request->device_token;
                $login_type = $request->login_type;
                $device_type = $request->device_type;
                $lang = isset($request->lang)?$request->lang:"en";
                if ($request->device_type == IOS) {
                    if (!$request->ios_version) {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.new_app_version_available_app_store_kindly_download_access_app', $lang));
                        return response()->json($response_array, 200);
                    }
                }
                ###############     TYPE=0 - mobile, TYPE=1 - Gmail, TYPE=2 - Facebook      ########################
                if ($login_type == 0) {
                    $phone = $request->phone;
                    $password = $this->encrypt_password($request->password);
                    $data = $users::where('phone', 'like', '%' . $phone . '%')->where('password', $password)->with('State','City')->first();
                    if ($data) {
                        if(isset($data->State)) {
                            $state = $data->State->state;
                        }else {
                            $state = '';
                        }
                        if(isset($data->City)) {
                            $city = $data->City->state;
                        }else {
                            $city = '';
                        }
                        $authId = $data->id;
                        $profile_image = $data->profile_image;
                        if ($data->name != NULL) {
                            $name = $data->name;
                        } else {
                            $name = "";
                        }
                        $email = $data->email ? $data->email : "";
                        $authToken = $this->generateRandomString();
                        if ($device_type != WEB) {
                            $users::where('id', $data->id)->update(['device_token' => $device_token, 'authToken' => $authToken, 'device_type' => $device_type, 'last_logged_in_device_type' => $data->device_type]);
                        } else {
                            $users::where('id', $data->id)->update(['authToken' => $authToken, 'last_logged_in_device_type' => $device_type]);
                        }
                        $this->user_authentication->insert(['authId' => $authId, 'authToken' => $authToken]);
                        $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.login_success',$lang) , 'authId' => $authId, 'authToken' => $authToken, 'phone' => $phone, 'profile_image' => $profile_image, 'email' => $email, 'user_name' => $name, 'state' => $state , 'city' => $city, 'is_guest_user' => 0);
                    } else {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.invalid_login',$lang));
                    }
                } elseif ($login_type == 1) {
                    // Gmail Login
                    $email = $request->email;
                    $device_token = $request->device_token;
                    $data = $users::where('email', $email)->with('State','City')->first();
                    if ($data) {
                        if(isset($data->State)) {
                            $state = $data->State->state;
                        }else {
                            $state = '';
                        }
                        if(isset($data->City)) {
                            $city = $data->City->state;
                        }else {
                            $city = '';
                        }
                        $authId = $data->id;
                        $profile_image = $data->profile_image;
                        if ($data->name != NULL) {
                            $name = $data->name;
                        } else {
                            $name = "";
                        }
                        $authToken = $this->generateRandomString();
                        $users::where('id', $data->id)->update(['device_token' => $device_token, 'authToken' => $authToken, 'device_type' => $device_type]);
                        $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.login_success',$lang), 'authId' => $authId, 'authToken' => $authToken, 'phone' => $data->phone, 'profile_image' => $profile_image, 'email' => $email, 'user_name' => $name, 'state' => $state , 'city' => $city, 'is_guest_user' => 0);
                    } else {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.new_app_version_available_app_store_kindly_download_access_app',$lang));
                    }
                } else {
                    // Facebook Login
                    $email = $request->email;
                    $device_token = $request->device_token;
                    $data = $users::where('email', $email)->with('State','City')->first();
                    if ($data) {
                        if(isset($data->State)) {
                            $state = $data->State->state;
                        }else {
                            $state = '';
                        }
                        if(isset($data->City)) {
                            $city = $data->City->state;
                        }else {
                            $city = '';
                        }
                        $authId = $data->id;
                        $profile_image = $data->profile_image;
                        if ($data->name != NULL) {
                            $name = $data->name;
                        } else {
                            $name = "";
                        }
                        $authToken = $this->generateRandomString();
                        $users::where('id', $data->id)->update(['device_token' => $device_token, 'authToken' => $authToken, 'device_type' => $device_type]);
                        $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.login_success',$lang) , 'authId' => $authId, 'authToken' => $authToken, 'phone' => $data->phone, 'profile_image' => $profile_image, 'email' => $email, 'user_name' => $name, 'state' => $state , 'city' => $city, 'is_guest_user' => 0);
                    } else {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.invalid_login',$lang));
                    }
                }
                if($response_array['status']!=false && !empty($authId)){
                    $currentAddress = CurrentAddress::where('user_id',(string)$authId)->first();
                    $response_array['data'] = !empty($currentAddress)?array($currentAddress):[];
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function guest_user_login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'device_token' => 'required',
                    'device_type' => 'required|in:' . ANDROID . ',' . IOS . ',' . WEB,
                    'phone' => 'required',
                    'name' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $name = $request->name;
                $phone = $request->phone;
                $device_token = $request->device_token;
                $device_type = $request->device_type;
                $users = $this->users;
                $profile_image = "http://www.freeiconspng.com/uploads/account-profile-icon-1.png";

                $data = $users::where('phone', $phone)->first();

                if ($data) {
                    if ($data->is_guest_user == 1) {
                        $response_array = array('status' => true, 'guest_user_id' => $data->id, 'is_guest_user' => 1, 'authToken' => $data->authToken);
                    } else {
                        $response_array = array('status' => false, 'message' => 'This number already registered as user');
                    }
                } else {
                    $authToken = $this->generateRandomString();
                    $new_user[] = array(
                        'name' => $name,
                        'phone' => $phone,
                        'email' => 'NA',
                        'authToken' => $authToken,
                        'device_type' => $device_type,
                        'password' => 'NA',
                        'device_token' => $device_token,
                        'profile_image' => $profile_image,
                        'referral_code' => $this->generateRandomString_referral(),
                        'is_guest_user' => 1
                    );
                    $users::insert($new_user);
                    $data1 = $users::where('phone', '=', $phone)->first();

                    $response_array = array('status' => true, 'guest_user_id' => $data1->id, 'is_guest_user' => 1, 'authToken' => $authToken);

                }
            }
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function verify_guest_user(Request $request)
    {
        try {
            $phone = (string)$request->phone;
            $otp = rand(10000, 99999);
            Log::info("login otp: " . $otp);
            $this->send_otp_softsms($phone, $otp);
            $response_array = array('status' => true, 'message' => 'OTP sent successfully', 'otp' => $otp);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    public function resend_otp(Request $request)
    {
        try {
            $users = $this->users;
            $otp = rand(10000, 99999);
            $this->send_otp_softsms($request->phone, $otp);
            $users::where('phone', '=', $request->phone)
                ->update(['otp' => $otp]);
            return response()->json(['status' => true, 'message' => 'OTP resend successfully']);
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
            //validation for email already exist on other users
            if(!empty($request->email)){
                if($this->users->where('id', '!=', $request->id)->where('email', $request->email)->first()){
                    $response_array = array('status' => false, 'message' => 'User Email Already Exist!');
                    return response()->json($response_array, 200);
                }
            }
            $users = $this->users;
            $custom = $this->custom;
            $update = array();
            $data = $users::where('id', $request->id)->first();
            $update['name'] = isset($request->name)?$request->name:"";
            $update['email'] = isset($request->email)?$request->email:"";
            if ($request->password) {
                $update['password'] = $this->encrypt_password($request->password);
            }
            if ($request->profile_image) {
                if ($data->profile_image != "") {
                    $custom::delete_image($data->profile_image);
                }
                $profile_pic = $custom::upload_image($request, 'profile_image');
                $update['profile_image'] = $profile_pic;
            }
            $users::where('id', $request->id)->update($update);
            $data = $users::where('id', $request->id)->first();
            if(!empty($data)){
                $data->profile_image = UPLOADS_PATH . $data->profile_image;
                $data->password = $this->decrypt_password($data->password);
            }
            $response_array = array('status' => true, 'message' => 'Profile updated successfully', 'data' => $data);
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

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
                $phone = $request->phone;

                $users = $this->users;

                $check_user = $users::where('phone', $phone)->first();

                if ($check_user) {
                    $phone = (string)$request->phone;
                    $otp = rand(10000, 99999);
                    Log::info("forgot pwd otp: " . $otp);
                    $message = 'OTP to verify Foodie Application : ' . $otp;
                    $sendSms = $this->send_otp_softsms($phone, $otp);

                    $response_array = array('status' => true, 'message' => 'OTP sent successfully', 'otp' => $otp);
                } else {
                    $response_array = array('status' => false, 'message' => 'Mobile number not registered');
                }
            }

            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

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
                $password = $this->encrypt_password($request->password);
                $phone = $request->phone;
                $users = $this->users;

                $get_user = $users::where('phone', $phone)->first();

                $users::where('phone', $phone)->update(['password' => $password]);

                $response_array = array('status' => true, 'message' => 'Password Reset Successfull');
            }

            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    public function logout(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $users = $this->users;
            $users::where('id', '=', $user_id)->update(['authToken' => 0]);

            $response_array = array('status' => true, 'message' => 'Logged Out Successfully');
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function generate_password()
    {
        try {
            $password = "more#87654$@2790";
            $new_password = Hash::make($password);
            echo $new_password;
            exit;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * get cms details
     *
     * @return json $response
     */
    public function get_cms_pages()
    {
        Log::info('check connect get cms pages apis');
        try {
            $state = $this->newState->select('id','state')->get();
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
                'country_currency' => DEFAULT_CURRENCY_SYMBOL, 
                'state' => $state
            );
            $response_array = array('status' => true, 'details' => $getdata);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * delete account api
     *
     * @return json $response
     */
    public function delete_account(Request $request)
    {
        try{
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }
            $user = Users::where('id',$user_id)->delete();
            if($user)
            {
                $response_array = array('status' => true, 'message' => 'Your Account Deleted Succesfully');
            }
            else
            {
                $response_array = array('status' => false);
            }
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
}
