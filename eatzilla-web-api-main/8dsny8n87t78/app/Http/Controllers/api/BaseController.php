<?php
namespace App\Http\Controllers\api;
use App\Model\RestaurantDeliveryCharges;
use App\Model\UserAuthentication;
use App\Model\UsersCheckoutRestaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Users;
use App\Model\Admin;
use App\Model\Deliverypartners;
use App\Model\Cuisines;
use App\Model\Restaurantcuisines;
use App\Model\Deliveryaddress;
use App\Model\Restaurants;
use App\Model\Favouritelist;
use App\Model\Popularbrands;
use App\Model\Foodlist;
use App\Model\Foodrequest;
use App\Model\Requestdetail;
use App\Model\Trackorderstatus;
use App\Model\Cart;
use App\Model\Category;
use App\Model\Menu;
use App\Model\Banner;
use App\Model\Promocode;
use App\Model\Settings;
use App\Model\Addcity;
use App\Model\Addzone;
use App\Model\Addarea;
use App\Model\Document;
use App\Model\Vehicle;
use App\Model\CancellationReason;
use App\Model\DriverList;
use App\Model\Coupon;
use App\Model\Food;
use App\Model\Add_ons;
use App\Model\FoodQuantity;
use App\Model\RequestdetailAddons;
use App\Model\RestaurantBankDetails;
use App\Model\DriverPayoutHistory;
use App\Model\RestaurantPayoutHistory;
use App\Model\OrderRatings;
use App\Model\Country;
use App\Model\State;
use App\Model\NewState;
use App\Model\Deliverypartner_detail;
use App\Model\Foodlist_Addons;
use App\Model\Cms;
use App\Model\RestaurantTimer;
use App\Model\AccessPrivilages;
use App\Model\City_geofencing;
use App\Model\Choice_category;
use App\Model\Choice;
use App\Model\FoodListAvailability;
use App\Library\Custom;
use App\Library\Validators;
use Illuminate\Support\Facades\Lang;
use Twilio\Rest\Client;
use App\Library\pointLocation;
use URL;
use Mail;
use View;
use Log;
use App\Model\DeliveryPartnerLog;
use App\Jobs\SendOrderEmailJob;

if (!defined('BASE_URL')) define('BASE_URL',URL::to('/').'/');
if (!defined('BASE_AWS')) define('BASE_AWS','https://eatzilla.nyc3.cdn.digitaloceanspaces.com/');
if (!defined('SPACES_BASE_URL')) define('SPACES_BASE_URL','https://eatzilla.nyc3.cdn.digitaloceanspaces.com/');
if (!defined('PROFILE_ICON')) define('PROFILE_ICON','profile_icon.png');
//if (!defined('RESTAURANT_UPLOADS_PATH')) define('RESTAURANT_UPLOADS_PATH','public/restaurant_uploads/');
if (!defined('RESTAURANT_UPLOADS_PATH')) define('RESTAURANT_UPLOADS_PATH',BASE_AWS.'restaurant_uploads/');
//if (!defined('VEHICLE_UPLOADS_PATH')) define('VEHICLE_UPLOADS_PATH','public/vehicles/');
if (!defined('VEHICLE_UPLOADS_PATH')) define('VEHICLE_UPLOADS_PATH',BASE_AWS.'vehicles/');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH',BASE_AWS.'uploads/');
if (!defined('UPLOADS_EMAIL_PATH')) define('UPLOADS_EMAIL_PATH','public/email/');
//if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH','public/uploads/');
//if (!defined('DRIVER_IMAGE_PATH')) define('DRIVER_IMAGE_PATH','public/uploads/Profile/');
//if (!defined('FOOD_IMAGE_PATH')) define('FOOD_IMAGE_PATH','public/uploads/product/');
if (!defined('DRIVER_IMAGE_PATH')) define('DRIVER_IMAGE_PATH',BASE_AWS.'uploads/Profile/');
if (!defined('DRIVER_LICENSE_PATH')) define('DRIVER_LICENSE_PATH',BASE_AWS.'uploads/License/');
if (!defined('FOOD_IMAGE_PATH')) define('FOOD_IMAGE_PATH',BASE_AWS.'uploads/product/');
if (!defined('PROMO_IMAGE_PATH')) define('PROMO_IMAGE_PATH','public/promo_images/');
if (!defined('WEB')) define('WEB',   'web');
if (!defined('ANDROID')) define('ANDROID',   'android');
if (!defined('IOS')) define('IOS',   'ios');
if (!defined('PAGINATION')) define('PAGINATION',   100);
if (!defined('APP_BASE_URL')) define('APP_BASE_URL',BASE_URL);

class BaseController extends Controller
{

	 public function __construct(Request $request, Admin $admin, Users $users,Custom $custom,Cuisines $cuisines,Deliveryaddress $deliveryaddress,Restaurants $restaurants,Favouritelist $favouritelist,Popularbrands $popularbrands,Foodlist $foodlist,Category $category,Menu $menu,Cart $cart,Foodrequest $foodrequest,Requestdetail $requestdetail,Deliverypartners $deliverypartners,Trackorderstatus $trackorderstatus,Promocode $promocode,Banner $banner,Settings $settings,Restaurantcuisines $restaurantcuisines,Addcity $addcity,Addzone $addzone,Addarea $addarea,Document $document,Vehicle $vehicle,CancellationReason $cancellation_reason,DriverList $driver_list,Coupon $coupon,Food $food, Add_ons $add_ons, FoodQuantity $food_quantity, RequestdetailAddons $requestdetail_addons, RestaurantBankDetails $restaurant_bank_details, DriverPayoutHistory $driver_payout_history, RestaurantPayoutHistory $restaurant_payout_history, OrderRatings $order_ratings, Validators $validators,Country $country,State $state,Deliverypartner_detail $delivery_partner_details, Foodlist_Addons $foodlist_addons, Cms $cms, RestaurantTimer $restaurant_timer, AccessPrivilages $access_privilages,City_geofencing $city_geofencing,Choice_category $choice_category,Choice $choice,DeliveryPartnerLog $delivery_partner_log,UsersCheckoutRestaurant $users_checkout_restaurant,RestaurantDeliveryCharges $restaurant_delivery_charges,UserAuthentication $user_authentication ,FoodListAvailability $foodListAvailability ,NewState $newState)
    {
        // $this->validateArrays = $ValidateArrays;
        $this->admin = $admin;
        $this->users = $users;
        $this->custom = $custom;
        $this->cuisines = $cuisines;
        $this->deliveryaddress = $deliveryaddress;
        $this->restaurants = $restaurants;
        $this->favouritelist = $favouritelist;
        $this->popularbrands = $popularbrands;
        $this->foodlist = $foodlist;
        $this->category = $category;
        $this->menu = $menu;
        $this->cart = $cart;
        $this->banner = $banner;
        $this->promocode = $promocode;
        $this->foodrequest = $foodrequest;
        $this->requestdetail = $requestdetail;
        $this->deliverypartners = $deliverypartners;
        $this->trackorderstatus = $trackorderstatus;
        $this->settings = $settings;
        $this->restaurantcuisines = $restaurantcuisines;
        $this->addcity = $addcity;
        $this->addzone = $addzone;
        $this->addarea = $addarea;
        $this->document = $document;
        $this->vehicle = $vehicle;
        $this->cancellation_reason = $cancellation_reason;
        $this->driver_partner_details = $driver_list;
        $this->coupon = $coupon;
        $this->food = $food;
        $this->add_ons = $add_ons;
        $this->food_quantity = $food_quantity;
        $this->requestdetail_addons = $requestdetail_addons;
        $this->restaurant_bank_details = $restaurant_bank_details;
        $this->driver_payout_history = $driver_payout_history;
        $this->restaurant_payout_history = $restaurant_payout_history;
        $this->order_ratings = $order_ratings;
        $this->validators = $validators;
        $this->country = $country;
        $this->state = $state;
        $this->delivery_partner_details = $delivery_partner_details;
        $this->foodlist_addons = $foodlist_addons;
        $this->cms = $cms;
        $this->restaurant_timer = $restaurant_timer;
        $this->access_privilages = $access_privilages;
        $this->city_geofencing = $city_geofencing;
        $this->choice_category = $choice_category;
        $this->choice = $choice;
        $this->delivery_partner_log = $delivery_partner_log;
        $this->users_checkout_restaurant = $users_checkout_restaurant;
        $this->restaurant_delivery_charges = $restaurant_delivery_charges;
        $this->user_authentication = $user_authentication;
        $this->foodListAvailability = $foodListAvailability;
        $this->newState = $newState;

        //get site info
        $site_info = $this->settings->get();
        //dd($site_info);
        
        //check language
        $this->lang = isset($request->lang)?$request->lang:'';
        // print_r($this->lang);exit();
        if($this->lang!='')
            app()->setLocale($this->lang);
       
    }

        public static function generate_booking_id($cityCode)
    {
        $booking = Foodrequest::orderBy('id','DESC')->first();
        if (!$booking) {
            $booking_code = ORDER_ID_PREFIX.'-'.$cityCode->city_code.'-'.str_pad(1, 3, "0", STR_PAD_LEFT);
        } else {
            $new_id = $booking->id + 1;
            $booking_code = ORDER_ID_PREFIX.'-'.$cityCode->city_code.'-'.str_pad($new_id, 3, "0", STR_PAD_LEFT);
        }
        return $booking_code;
    }

           public static function generate_partner_id()
    {
        $booking =Deliverypartners::orderBy('id','DESC')->first();
        if (!$booking) {
            $booking_code = 'PAT'.str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $new_id = $booking->id + 1;
            $booking_code = 'PAT'.str_pad($new_id, 5, "0", STR_PAD_LEFT);
        }
        return $booking_code;
    }
    
    public static function send_otp_softsms($NUMBER,$OTP)
    {
        try{
            $otp = urlencode($OTP.' is the OTP to login to your TRUELY account. Bon Appetit ! sms service');
            Log::info('send_otp_softsms');
       }
       catch(\Exception $e)
       {
           Log::error('Send otp SoftSms Error:: ' . $e->getMessage());
       }
       return true;
    }

    public static function check_null($data)
    {
        # code...
        array_walk_recursive($data, function (&$item, $key) {
            $item = null === $item ? '' : $item;
        });
        return $data;
    }

    public static function is_near($pickup_lat,$pickup_lng,$user_id)
    {
        $user = Users::where('id',$user_id)->first();
        $distance = Common::get_distance($pickup_lat,$pickup_lng,$user->lat,$user->lng);
        if ($distance < Common::$radius) {
            return $distance;
        } else {
            return false;
        }
        
    }

    public function generateRandomString($length = 16) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	 public function generateRandomString_referral($length = 8) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

    public function encrypt_password($password) {
        $key = hash('sha256', 'sparkout');
        $iv = substr(hash('sha256', 'developer'), 0, 16);
        $output = openssl_encrypt($password, "AES-256-CBC", $key, 0, $iv);
        $output2 = base64_encode($output);
        return $output2;
    }

    public function decrypt_password($encrypted_password) {

        $key = hash('sha256', 'sparkout');
        $iv = substr(hash('sha256', 'developer'), 0, 16);
        $output1 = openssl_decrypt(base64_decode($encrypted_password), "AES-256-CBC", $key, 0, $iv);
        return $output1;
    }

    /**
     * to send push notifications
     * 
     * @param array $params
     * 
     */
    public function user_send_push_notification($params) 
    {
        // $device_token = "f_ZXeVPxK5k:APA91bE3FxmrPDQAeTc17j17CHyliLQ3D0iOhnQfsQz4coqyBfeHPYF6zMeJKDfX1wrwLWzp6bAkGCYRQ3Z_VUv0Z6xyUBKurpfXAT4-vJLO_X6PtlIyHE4UtKdZwdsy1ua8c_3V4zRZ";
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
                        'image' => isset($params['image'])?$params['image']:"",
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

        // var_dump($fields);
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

        //print_r($result);
        curl_close($ch);
        Log::info('USER_NOTIFICATION_KEY:'.USER_NOTIFICATION_KEY);
        Log::info('push notification result :'.$result);

    }

   
    /**
     * to update firebase db in common
     * 
     * @param array $postdata, string $node, string $key
     * 
     */
    public function update_firebase($postdata, $node, $key)
    {
        $header = array();
        $header[] = 'Content-Type: application/json';

        $ch = curl_init(FIREBASE_URL."/".$node."/$key.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec($ch); 
        curl_close($ch); 

    }

    public function calculate_distance($s_lat,$s_lng,$d_lat,$d_lng)
    {
        $theta = $s_lng - $d_lng;
        $distance = (sin(deg2rad($s_lat)) * sin(deg2rad($d_lat))) + (cos(deg2rad($s_lat)) * cos(deg2rad($d_lat)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515 * 1.609344 * 1.7;
        return $distance;
    }


    /**
     * check whether the given date is weekend or not
     * 
     * @param string $date
     * 
     * @return boolean 
     */
    public function isWeekend($date) {
        return (date('N', strtotime($date)) == 5);
    }

      function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $content, 1);
    }

    /**
    * send email notification
    *
    * @param object $requestdata
    *
    */
    public function send_mail($requestdata, $type='')
    {
        $sender_email = isset($requestdata->email)?$requestdata->email:"";
        $sender_name = isset($requestdata->name)?$requestdata->name:"";
        $subject = isset($requestdata->subject)?$requestdata->subject:"";
            
        if($sender_email!=''){

                try
               {
                    $details= new SendOrderEmailJob($sender_email , $sender_name , $subject,$requestdata, $type='');
                    dispatch($details);
                }catch(\Exception $e)
               {
                   Log::error('Mail error:: ' . $e->getMessage());
               }
                
        }
    }

    

    /**
     * generate random string
     * 
     * @return string
     */
    public function generate_random_string()
    {
        return rand(11111111,99999999);
    }



    /**
     * Check food offer based on food item
     * 
     * @param object $data
     * 
     * @return string $food_offer
     */
    public function food_offer($data)
    {
        //check food offer
        $food_offer = "";
        if(!empty($data->offer_amount) && $data->offer_amount!='' && $data->offer_amount!=0){
            if($data->discount_type==1){
                 $food_offer = "Flat offer ".DEFAULT_CURRENCY_SYMBOL." ".$data->offer_amount;
            }else{
                 $food_offer = $data->offer_amount."% offer";
            }
            if($data->target_amount!=0){
                 $food_offer = $food_offer." on orders above ".DEFAULT_CURRENCY_SYMBOL." ".$data->target_amount;
            }
        }
        return $food_offer;
    }



    /**
     * check whether the restaurant opend or closed
     * 
     * @param object $data
     * 
     * @return boolean 
     */
    public function check_restaurant_open($data)
    {
        $is_open = 0;
        $current_time = date('Y-m-d H:i:s');
        $date = date("Y-m-d");
        // $is_weekend = $this->isWeekend($date);
        $todays_day = date('N', strtotime($date));
        switch ($todays_day) {
            case '1':
                $todays_day = "Monday";
                break;
            case '2':
                $todays_day = "Tuesday";
                break;
            case '3':
                $todays_day = "Wednesday";
                break;
            case '4':
                $todays_day = "Thursday";
                break;
            case '5':
                $todays_day = "Friday";
                break;
            case '6':
                $todays_day = "Saturday";
                break;
            case '7':
                $todays_day = "Sunday";
                break;                          
            default:
                # code...
                break;
        }
       // return $todays_day;
        if(!empty($data->RestaurantTimer))
        {
            foreach($data->RestaurantTimer as $value)
            {
                // if($is_weekend==true){ 
                //     if($value->is_weekend==1)
                //     {
                //         $opening_time = date("Y-m-d ".$value->opening_time);
                //         $closing_time = date("Y-m-d ".$value->closing_time);
                //         if(strtotime($value->opening_time) > strtotime($value->closing_time))
                //         {        
                //             $closing_time = date("Y-m-d ".$value->closing_time);    
                //             $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' +1 day'));
                //         }
                //         if((strtotime($opening_time)<=strtotime($current_time)) && (strtotime($closing_time)>=strtotime($current_time))){
                //          $is_open = 1;
                //         }
                //     }  
                   
                // }else{ 
                    if($value->day==$todays_day)
                    {
                        // return $current_time;
                        // $current_time = "2020-07-08 06:22:32";
                        $opening_time = date("Y-m-d ".$value->opening_time); 
                        $closing_time = date("Y-m-d ".$value->closing_time);
                        // return $closing_time;
                        if(strtotime($value->opening_time) > strtotime($value->closing_time))
                        {
                            $closing_time = date("Y-m-d ".$value->closing_time);
                            $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' +1 day'));

                            if((strtotime($current_time)>=strtotime($opening_time)) && (strtotime($current_time)<=strtotime($closing_time)))
                            {
                                $is_open = 1;  
                            }else
                            {
                                $opening_time = date("Y-m-d H:i:s", strtotime($opening_time. ' -1 day'));
                                $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' -1 day'));

                                if((strtotime($current_time)>=strtotime($opening_time)) && (strtotime($current_time)<=strtotime($closing_time)))
                                {
                                    $is_open = 1;  
                                }
                            }
                        }else
                        {
                            if((strtotime($opening_time)<=strtotime($current_time)) && (strtotime($closing_time)>=strtotime($current_time))){
                            $is_open = 1;  
                            }
                        }
                        
                    }elseif($value->day == 'all')
                    {
                        $opening_time = date("Y-m-d ".$value->opening_time); 
                        $closing_time = date("Y-m-d ".$value->closing_time);

                        if((strtotime($opening_time)<=strtotime($current_time)) && (strtotime($closing_time)>=strtotime($current_time))){
                            $is_open = 1;  
                            }
                    }
                // }
            }
        }
        return $is_open;
    }

    public function get_restaurant_open_and_close_time($data)
    {

        $current_time = date('Y-m-d H:i:s');
        $date = date("Y-m-d");
        // $is_weekend = $this->isWeekend($date);
        $todays_day = date('N', strtotime($date));
        switch ($todays_day) {
            case '1':
                $todays_day = "Monday";
                break;
            case '2':
                $todays_day = "Tuesday";
                break;
            case '3':
                $todays_day = "Wednesday";
                break;
            case '4':
                $todays_day = "Thursday";
                break;
            case '5':
                $todays_day = "Friday";
                break;
            case '6':
                $todays_day = "Saturday";
                break;
            case '7':
                $todays_day = "Sunday";
                break;                          
            default:
                # code...
                break;
        }
        if(isset($data->RestaurantTimer)) {
            $restaurantTimer = $data->RestaurantTimer;
        } else{
            $restaurantTimer = $data->restaurant_timer;
        }
        if(!empty($restaurantTimer))
        {
            foreach($restaurantTimer as $value)
            {
                // if($is_weekend==true){ 
                //     if($value->is_weekend==1)
                //     {
                //         $opening_time = date("Y-m-d ".$value->opening_time);
                //         $closing_time = date("Y-m-d ".$value->closing_time);
                //         if(strtotime($value->opening_time) > strtotime($value->closing_time))
                //         {        
                //             $closing_time = date("Y-m-d ".$value->closing_time);    
                //             $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' +1 day'));
                //         }
                //         if((strtotime($opening_time)<=strtotime($current_time)) && (strtotime($closing_time)>=strtotime($current_time))){
                //          $is_open = 1;
                //         }
                //     }  
                   
                // }else{ 
                    if($value->day==$todays_day)
                    {
                        // return $current_time;
                        // $current_time = "2020-07-08 06:22:32";
                        $opening_time = date("Y-m-d ".$value->opening_time); 
                        $closing_time = date("Y-m-d ".$value->closing_time);
                        // return $closing_time;
                        if(strtotime($value->opening_time) > strtotime($value->closing_time))
                        {
                            $closing_time = date("Y-m-d ".$value->closing_time);
                            $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' +1 day'));

                            if((strtotime($current_time)>=strtotime($opening_time)) && (strtotime($current_time)<=strtotime($closing_time)))
                            {
                                $is_open = 1;  
                            }else
                            {
                                $opening_time = date("Y-m-d H:i:s", strtotime($opening_time. ' -1 day'));
                                $closing_time = date("Y-m-d H:i:s", strtotime($closing_time. ' -1 day'));

                                if((strtotime($current_time)>=strtotime($opening_time)) && (strtotime($current_time)<=strtotime($closing_time)))
                                {
                                    $is_open = 1;  
                                }
                            }
                        }else
                        {
                            if((strtotime($opening_time)<=strtotime($current_time)) && (strtotime($closing_time)>=strtotime($current_time))){
                            $is_open = 1;  
                            }
                        }
                        
                    }
                // }
                if(isset($opening_time))
                {
                    if((strtotime($opening_time)>strtotime($current_time)) )
                    {
                        if(isset($opening_time_show))
                        {
                            if($opening_time_show > $opening_time)
                            {
                                $opening_time_show = $opening_time;
                            }
                        }else
                        {
                            $opening_time_show = $opening_time;
                        }      
                    }
                }
                
            }

            if(!isset($opening_time))
            {
                $opening_time = date('Y-m-d H:i:s');
                $opening_time_show = date('Y-m-d H:i:s');
            }
            if(!isset($opening_time_show))
            {
                $opening_time_show = $opening_time;
            }
            
            if(!isset($closing_time))
            {
                $closing_time = date('Y-m-d H:i:s');
            }

            $restaurant_and_close_time = array();
            $restaurant_and_close_time = array(
                'opening_time' => $opening_time_show,
                'closing_time' => $closing_time,
            );
        }
        return $restaurant_and_close_time;
    }



    /**
     * get distance and time based on user source and restaurant
     * 
     * @param string $source, string $destination
     * 
     * @return array $getdata
     */
    public function getGoogleDistance($source, $destination,$type=0)
    {
        $key = GOOGLE_API_KEY;
        
        // $start = implode(',', $start);
        // $finish = implode(',', $finish);
        # API hit function
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$source."&destination=".$destination."&sensor=false&mode=driving&alternatives=true&language=null&key=".$key;

         $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // execute and return string (this should be an empty string '')
        $file = curl_exec($curl);

        curl_close($curl);
        $arr_conversion = json_decode($file);
            
        // $file = file_get_contents($url);
        // $arr_conversion = json_decode($file);

        if(isset($arr_conversion->status) && $arr_conversion->status=='OK'){
            $data = $arr_conversion->routes[0]->legs[0];
            //$data[0] = round(($data->distance->value)/1000, 2);
            if($type==1){
                $getdata[0] = round(($data->distance->value)/1000, 2);
                $getdata[1] = $data->duration->value;
            }else{
                $getdata[0] = $data->distance->text;
                $getdata[1] = $data->duration->text;
            }
        }else{
            $distance = $this->find_Haversine($source, $destination);
            if($type==1)
                $getdata[0] = $distance;
            else
                $getdata[0] = $distance." km";

            $getdata[1]="";
        }
        return $getdata;
    }
    
    
    /**
     * calculate driver commission
     * 
     * @param object $data, string $source, string $destination
     * 
     * @return array $result
     */
    public function calculate_driver_commission($data, $source, $destination)
    {
        if(isset($data) && $data->driver_base_price!=0)
        {
            $base_price = $data->driver_base_price;
            $base_distance = $data->min_dist_base_price;
            $extra_fee = $data->extra_fee_amount;
            //get distance based on goole api
            $getdistance = $this->getGoogleDistance($source, $destination,1);
            $distance = $getdistance[0];
            $extra_charge=0;
            if($distance!='' && $distance>$base_distance){
                $extra_charge = ($distance-$base_distance)*$extra_fee;
            }
            $delivery_boy_commission = $base_price + $extra_charge;
        }else
        {
            $delivery_boy_commission = 0;
            $distance = 0;
        }
        $result = array(
                'distance' => $distance,
                'delivery_boy_commission' => $delivery_boy_commission
            );
        return $result;
    }


    /**
     * calculate delivery charge
     * 
     * @param object $data, string $source, string $destination
     * 
     * @return float $delivery_boy_commission
     */
    public function calculate_deliver_charge($data, $source, $destination)
    {
        if(isset($data) && $data->default_delivery_amount!=0)
        {
            $base_price = $data->default_delivery_amount;
            $base_distance = $data->min_dist_delivery_price;
            $extra_fee = $data->extra_fee_deliveryamount;
            //get distance based on goole api
            $distance = $this->getGoogleDistance($source, $destination,1);
            $extra_charge=0;
            // return $distance; 
            if($distance[0]!='' && $distance[0]>$base_distance){
                $extra_charge = ($distance[0]-$base_distance)*$extra_fee;
            }
            $delivery_charge = $base_price + $extra_charge;
        }else
        {
            $delivery_charge = 0;
        }
        return $delivery_charge;
    }


    /**
     * Find distance from source and destination based on havesine method
     * 
     * @param string $source, string $destination
     * 
     * @return decimal $distance
     */
    public function find_Haversine($source, $destination)
    {
        $start = explode(',',$source);
        $finish = explode(',',$destination);
        $theta    = $start[1] - $finish[1];
        $distance = (sin(deg2rad($start[0])) * sin(deg2rad($finish[0]))) + (cos(deg2rad($start[0])) * cos(deg2rad($finish[0])) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        return round($distance, 2);
    }


    public function generateChecksum(Request $request)
    {
        if($request->header('authId')!="")
        {
            $user_id = $request->header('authId');
        }else
        {
            $user_id = $request->authId;
        }

        $user_detail = $this->users::where('id',$user_id)->first();

        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $amount = $request->amount;

        if(isset($user_detail) && $user_detail->device_type==WEB)
        {
            $food_id = array();
            $food_qty = $food_quantity = $food_quantity_price = array();
            $food_ids = str_replace('"','', (string) $request->food_id);
            $food_id = explode(',', $food_ids);
            $food_qtys = str_replace('"','', (string) $request->food_qty);
            $food_qty = explode(',', $food_qtys);
            $food_quantitys = str_replace('"','', (string) $request->food_quantity);
            $food_quantity = explode(',', $food_quantitys);
            $food_quantity_prices = str_replace('"','', (string) $request->food_quantity_price);
            $food_quantity_price = explode(',', $food_quantity_prices);
        }else
        {
            $food_id = $request->food_id;
            $food_qty = $request->food_qty;
            $food_quantity = $request->food_quantity;
            $food_quantity_price = $request->food_quantity_price;
        }
        $food_id_size = sizeof($food_id);
        $food_qty_size = sizeof($food_qty);
        for($i=0;$i<$food_id_size;$i++)
        {   
            $prouct[] = '{"id":'.$food_id[$i].',"qty":'.$food_qty[$i].',"food_size":'.$food_quantity[$i].',"food_size_price":'.$food_quantity_price[$i].'}';
            
            if($request->add_ons[$i]!=''&&$request->add_ons[$i]!=0)
            {
                $prouct['addon_ids'] = $request->add_ons[$i];
            }

        }
        $productinfo = json_encode($prouct);

        $firstname = $user_detail->name;
        $email = $user_detail->email;
        $hashSequence = env('MERCHANT_KEY').'|'.$txnid.'|'.$amount.'|'.$productinfo.'|'.$firstname.'|'.$email.'|||||||||||'.env('MERCHANT_SALT');
        //$hash=hash('sha512', $data->key.'|'.$data->txnid.'|'.$data->amount.'|'.$data->pinfo.'|'.$data->fname.'|'.$data->email.'|||||'.$data->udf5.'||||||'.$data->salt);
        $hash = hash("sha512", $hashSequence);

        $response_array = array('status'=>true,'hash_key'=> $hash);
        $response = response()->json($response_array, 200);
        return $response;
    }


    public function contains($point, $polygon)
    {
        $pointLocation = new pointLocation();
        $is_avail = 0;
        // The last point's coordinates must be the same as the first one's, to "close the loop"
        
        $is_avail = $pointLocation->pointInPolygon($point, $polygon[0]);
        

        return $is_avail;

    }



    /**
     * delete node in firebase
     * 
     * @param string $node, int $id
     * 
     * @return json $result
     */
    public function delete_firebase_node($node, $id)
    {
        
        $ch = curl_init(FIREBASE_URL."/".$node."/".$id.".json");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $result = curl_exec($ch); 
        print_r("firebase:".$result);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $key
     * @param $lang
     * @return mixed
     */
    public function language_string_translation($key,$lang,$param=0){
        if($param==0){
            $string = Lang::choice($key,1,[],$lang);
        }else{
            $string = Lang::choice($key,1,$param,$lang);
        }
        return $string;
    }

    public function get_travel_time($source_lat , $source_lng , $res_lat , $res_lng) {
        $theta = $source_lng - $res_lng;
        $dist = sin(deg2rad($source_lat)) * sin(deg2rad($res_lat)) +  cos(deg2rad($source_lat)) * cos(deg2rad($res_lat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $distance = round($miles * 1.609344);
        $minutes = ($distance / 40) * 60;

        return $minutes;
    }

    public function get_cuisines_detils($restaurantId) {
        $getRestaurants = $this->restaurants->with(['Cuisines', 'RestaurantTimer'])->where('id',$restaurantId)->first();
        return [$getRestaurants['Cuisines'], $getRestaurants['RestaurantTimer']];
    }

    public function getIndianCurrency(float $number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'one', 2 => 'two',
            3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
            7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred','thousand','lakh', 'crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? 'and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees.'Rupees ' : '') . $paise;
    }

    public function getCurrentCityId($source_lng ,$source_lat) {
        $city_details = $this->addcity
                        ->select('add_city.*', 'city_geofencing.polygons')
                        ->leftJoin('city_geofencing', function ($join) {
                            $join->on('city_geofencing.city_id', '=', 'add_city.id');
                        })->get();

        $city_id = "";
        foreach ($city_details as $value) {
            $polygon = json_decode($value->polygons);
            $ponits = array($source_lng, $source_lat);
            $is_avail = $this->contains($ponits, $polygon[0]);
            if ($is_avail == 1) {
                $city_id = $value->id;
                break;
            }
        }

        return $city_id;
    }

    /**
     * check food availability
     * 
     * @param string $food_list, restaurant_id
     * 
     * @return json $result
     */

    public function food_availability_check($food_list , $restaurant_id) {
        $current_time_for_food = date("H:i:s");
        $unAvailableFoods = [];
        foreach ($food_list as $foods) {
            // To check Food timing
            $getFood = $this->foodlist->where('id',$foods)->where('status', 1)->where('restaurant_id',$restaurant_id)->first();
            if(!empty($getFood)) {
                $check_food_time = $this->foodListAvailability->where('food_list_id', $getFood->id)->get();
                if (count($check_food_time) == 0) {
                    $show_food = 1;
                    $is_food_available = 1;
                }else {
                    $show_food = 0;
                    foreach ($check_food_time as $food_time) {
                        if ($food_time->item_days == "allday") {
                            if ($food_time->item_finish_time < $food_time->item_start_time) {
                                if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                    $is_food_available = 1;
                                } else {
                                    $is_food_available = 0;
                                }
                            } else {
                                if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                    $is_food_available = 1;
                                } else {
                                    $is_food_available = 0;
                                }
                            }
                        } else {
                            $timestamp = strtotime(date("Y-m-d"));
                            $day = date('D', $timestamp);
                            if (($day == "Mon") && ($food_time->item_days == "mon")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Tue") && ($food_time->item_days == "tue")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Wed") && ($food_time->item_days == "wed")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Thu") && ($food_time->item_days == "thu")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Fri") && ($food_time->item_days == "fri")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Sat") && ($food_time->item_days == "sat")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } elseif (($day == "Sun") && ($food_time->item_days == "sun")) {
                                if ($food_time->item_finish_time < $food_time->item_start_time) {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food > $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                } else {
                                    if (($food_time->item_start_time < $current_time_for_food) && ($current_time_for_food < $food_time->item_finish_time)) {
                                        $is_food_available = 1;
                                    } else {
                                        $is_food_available = 0;
                                    }
                                }
                            } else {
                                $is_food_available = 0;
                            }
                        }
                        if ($is_food_available == 1) {
                            $show_food = 1;
                        }
                    }
                }
                if($show_food == 0) {
                    array_push($unAvailableFoods , strval($getFood->id));
                }
            }else {
                array_push($unAvailableFoods , strval($foods));
            }
        }
        return $unAvailableFoods;
    }
}