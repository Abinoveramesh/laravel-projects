<?php

namespace App\Http\Controllers\api;
                                    
use App\Http\Requests\Api\GetAreaByLatLngRequest;
use App\Http\Requests\Api\SetDefaultAddressRequest;
use App\Http\Requests\Api\TrackOrderDetailRequest;
use App\Model\Banner;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use App\Model\CurrentAddress;
use DB;
use Carbon\Carbon;
use Log;
use Illuminate\Support\Facades\Redis;
use App\Model\DeliveryInstruction;
use App\Base\Helpers\ExceptionHandlerModel;
use DateTime;

class UserController extends BaseController
{

    public function check_version(Request $request)
    {
          $android_version = isset($request->android_version)?$request->android_version:"";
            // if($android_version=="" || ($android_version < USER_ANDROID_VERSION))
            if($android_version=="")
            {
                return response()->json(['status'=>false,'message'=>'A new app version is available in Playstore. Kindly download it to access the app']);
            }else
            {
            return response()->json(['status'=>true,'message'=>'Your App is up to date']);
            }
    }

    public function get_default_address(Request $request)
    {
        try {
            if($request->header('authId')!="") {
                $user_id = $request->header('authId');
            }else {
                $user_id = $request->authId;
            }
            $delivery_address = $this->deliveryaddress;
            $data = $delivery_address::where('user_id',$user_id)->where('is_default',1)->first();
            if($data) {
                $response_array = array('status' => true, 'data' => $data);
            }else {
                $response_array = array('status' => false, 'message' => 'No address found');
            }        

            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function set_delivery_address(Request $request)
    {
        try {
            if($request->header('authId')!="") {
                $user_id = $request->header('authId');
            }else {
                $user_id = $request->authId;
            }
            $delivery_address = $this->deliveryaddress;
            if($request->current_address)
            {
                $current_address = $request->current_address;
                $lat = $request->lat;
                $lng = $request->lng;

                if($request->address_id) {
                    $data1 =  $delivery_address::where('id',$request->address_id)->first();
                }else {
                    $data1 =  $delivery_address::where('user_id',$user_id)->where('address','like','%'.$current_address.'%')->where('lat',$request->lat)->where('lng',$request->lng)->first();
                }

                if($data1) {
                    $delivery_address::where('user_id',$user_id)->where('is_default',1)->update(['is_default'=>0]);
                    $delivery_address::where('id',$data1->id)->update(['is_default'=>1]);
                }else {
                    $delivery_address::where('user_id',$user_id)->where('is_default',1)->update(['is_default'=>0]);
                    $data = array();
                    $data[] = array(
                        'user_id'=>$user_id,
                        'address'=>$current_address,
                        'lat'=>$lat,
                        'lng'=>$lng,
                        'is_default'=>1,
                        'type'=>1
                    );
                    $delivery_address::insert($data);
                }
                $current_delivery_address = $delivery_address::where('user_id',$user_id)->where('is_default',1)->first();

            }else {
                $current_delivery_address = $delivery_address::where('user_id',$user_id)->where('is_default',1)->first();
            }

            if($current_delivery_address) {
                $response_array = array('status' => true, 'data' => $current_delivery_address);
            }else {
                $response_array = array('status' => false, 'message' => 'No address found');
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
    public function get_delivery_address(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            }else {
                $user_id = $request->authId;
            }
            $delivery_address = $this->deliveryaddress;
            $data = $delivery_address::where('user_id', $user_id)->get();
            if(count($data) != 0) {
                return response()->json(['status' => true, 'data' => $data]);    // type - 1 home, 2 work, 3 others
            }else {
                return response()->json(['status' => false, 'message' => $this->language_string_translation('constants.no_address', $lang)]);
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_delivery_address(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'address' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                    'type' => 'required',        // Type -1 Home, 2- Office, 3 -Others
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $address = $request->address;
                $address_title = $request->address_title ? $request->address_title : "";
                $road_number = isset($request->road_number)?$request->road_number:"";
                $lat = $request->lat;
                $lng = $request->lng;
                $type = $request->type;
                $flat_no = $request->flat_no ? $request->flat_no : "";
                $landmark = $request->landmark ? $request->landmark : "";
                $address_direction = $request->address_direction ? $request->address_direction : "";
                $block_number = isset($request->block_number)?$request->block_number:"";
                $building = isset($request->building)?$request->building:"";
                $delivery_address = $this->deliveryaddress;
                if ($request->customer_name) {
                    $this->users->where('id', $user_id)->update(['name' => $request->customer_name]);
                }

                $user_data = $this->users->find($user_id);
                if (!$user_data) {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => 'User session expired. Kindly logout and login.');
                    return response()->json($response_array, 200);
                }

                if ($user_data->name == "" || $user_data->name == "NULL") {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => 'Customer name is required');
                }

                $check_for_default_address = $delivery_address::where('user_id', $user_id)->where('is_default', 1)->first();

                if ($check_for_default_address) {
                    $delivery_address::where('id', $check_for_default_address->id)->update(['is_default' => 0]);
                    $is_default = 1;
                } else {
                    $is_default = 1;
                }

                $Userid = DB::table('delivery_address')->insertGetId([
                    'user_id' => $user_id,
                    'address_title' => $address_title,
                    'road_number' => $road_number,
                    'address' => $address,
                    'lat' => $lat,
                    'lng' => $lng,
                    'type' => $type,
                    'flat_no' => $flat_no,
                    'landmark' => $request->landmark ? $request->landmark : " ",
                    'address_direction' => $address_direction,
                    'block_number' => $block_number,
                    'building' => $building,
                    'is_default' => $is_default
                ]);
                Log::info('add delivery address api for user_id :' . $user_id);
                $response_array = array('status' => true, 'id'=> $Userid, 'message' => 'Address added successfully');

            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit_delivery_address(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'address_id' => 'required',
                    'address' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                    'type' => 'required',        // Type -1 Home, 2- Office, 3 -Others
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $address = $request->address;
                $address_title = $request->address_title ? $request->address_title : "NULL";
                $road_number = isset($request->road_number)?$request->road_number:"";
                $lat = $request->lat;
                $lng = $request->lng;
                $type = $request->type;
                $flat_no = $request->flat_no ? $request->flat_no : "0";
                $landmark = $request->landmark ? $request->landmark : "";
                $address_direction = $request->addressDirections ? $request->addressDirections : "NULL";
                $block_number = isset($request->block_number) ? $request->block_number:"";
                $building = isset($request->building) ? $request->building:"";
                $delivery_address = $this->deliveryaddress;
                if ($request->customer_name) {
                    $this->users->where('id', $user_id)->update(['name' => $request->customer_name]);
                }
                $user_data = $this->users->find($user_id);
                if ($user_data->name == "" || $user_data->name == "NULL") {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => 'Customer name is required');
                }
                $delivery_address::where('id', $request->address_id)->update([
                    'user_id' => $user_id,
                    'address_title' => $address_title,
                    'road_number' => $road_number,
                    'address' => $address,
                    'lat' => $lat,
                    'lng' => $lng,
                    'type' => $type,
                    'flat_no' => $flat_no,
                    'landmark' => $request->landmark ? $request->landmark : " ",
                    'address_direction' => $address_direction,
                    'block_number' => $block_number,
                    'building' => $building
                ]);
                $response_array = array('status' => true, 'message' => 'Address updated successfully');
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_filter_list($filter_type)
    {
        try {
            if($filter_type ==1)    // filter_type =1 - Cusines table else relevance table
            {
                $cuisines = $this->cuisines;
                $data = $cuisines::orderBy('name', 'ASC')->where('status',1)->get();
            }else
            {
                $data = DB::table('relevance')->get();
            } 

            if(count($data)!=0)
            {
                return response()->json(['status'=>true,'data'=>$data]);
            }else
            {
                return response()->json(['status'=>false,'message'=>'No data found']);
            }  
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_banners(Request $request)
    {
        try {
            $current_time = date('Y-m-d H:i:s');
            $currentTime = strtotime($current_time);
            $checkRedisData = 0;
            $source_lat = $request->lat;
            $source_lng = $request->lng;

            if(Redis::exists('banners-'.$source_lat.'-'.$source_lng)) {
                $checkRedisData = 1;
                $data = collect(json_decode(Redis::get('banners-'.$source_lat.'-'.$source_lng)));
            }else {
                $city_id = $this->getCurrentCityId($source_lng, $source_lat);
                $data = $this->banner->whereHas('Restaurants', function($q) use($city_id){
                    $q->where('status', 1)->where('city', 'like', '%"' . $city_id . '"%');
                })
                ->where('status', 1)->where('startdate', '<', $currentTime)->where('enddate', '>', $currentTime)
                    ->orderBy('id', 'desc')->get();
                Redis::set('banners-'.$source_lat.'-'.$source_lng , $data);
                Redis::expire('banners-'.$source_lat.'-'.$source_lng ,300);
            }
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }
            if(!empty($data)){
                if($checkRedisData == 1 && Redis::exists('banners-data-'.$source_lat.'-'.$source_lng)) {
                    $data = collect(json_decode(Redis::get('banners-data-'.$source_lat.'-'.$source_lng)));
                }else {
                    foreach ($data as $val) {
                        $res_data = $this->restaurants->find($val->restaurant_id);
                        $val->restaurant_name = $res_data->restaurant_name;
                        $val->id = $val->restaurant_id;
                        //calculate restaurant open time
                        $is_open = $this->check_restaurant_open($res_data);
        
                        if ($res_data->parent == 0) {
                            $check_sub_branches = $this->restaurants->where('parent', $res_data->id)->first();
                            if (!empty($check_sub_branches)) {
                                $val->is_main_branch = 0;
                            } else {
                                $val->is_main_branch = 1;
                            }
                        } else {
                            $val->is_main_branch = 0;
                        }
        
                        if ($res_data->status == 1) {
                            $val->is_open = $is_open;
                        } else {
                            $val->is_open = 0;
                        }
        
                        if (isset($user_id)) {
                            $user_detail = $this->users->find($user_id);
        
                            if ($user_detail->device_type == ANDROID) {
                                $val->image = $val->image1;
                            }
                        }
                    }
                    Redis::set('banners-data-'.$source_lat.'-'.$source_lng , $data);
                    Redis::expire('banners-data-'.$source_lat.'-'.$source_lng ,300);
                }
            }
            if (count($data) != 0) {
                return response()->json(['status' => true, 'data' => $data, 'base_url' => SPACES_BASE_URL]);
            } else {
                return response()->json(['status' => false, 'message' => 'No data found']);
            }
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_relevance_restaurant(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'is_pureveg' => 'required',
                    'is_offer' => 'required',
                    'lat' => 'required',
                    'lng' => 'required'

                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $restaurants = $this->restaurants;

                // $size_cuisines = sizeof($request->cuisines);
                if(isset($request->cuisines))
                {
                    $size_cuisines = 1;
                }
                else
                {
                    $size_cuisines = 0;
                }

                $source_lat = $request->lat;
                $source_lng = $request->lng;
                $cuisines = $request->cuisines;

                $query = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)->where('is_approved', 1)
                    ->groupBy('restaurant_name')
                    ->select('restaurants.*')
                    ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                * sin(radians(`lat`)))) as distance")
                    // ->having('distance','<=',DEFAULT_RADIUS)
                    ->orderBy('distance');

                $query = $query->when(($request->is_offer == 1),
                    function ($q) {
                        return $q->where('offer_amount', '!=', 0);
                    });

                $query = $query->when(($size_cuisines != 0),
                    function ($q) use ($cuisines) {
                        $q->wherehas('Cuisines', function ($q) use ($cuisines) {
                            return $q->whereIn('cuisines.id', $cuisines);
                        });
                    });

                $limit = PAGINATION;
                $page = isset($request->page) ? $request->page : 1;
                $offset = ($page - 1) * $limit;
                $query = $query->when(($limit != '-1' && isset($offset)),
                    function ($q) use ($limit, $offset) {
                        return $q->offset($offset)->limit($limit);
                    });
                // $query = $query->when(($request->is_pureveg==1),
                //             function($q){
                //                 return $q->where('offer_amount','!=',0);
                //             });

                $data = $query->get();

                $restaurant_list = array();
                $restaurant_closed_list = array();
                $restaurant_open_list = array();
                foreach ($data as $d) {
                    $rcuisines = array();
                    $i = 0;
                    foreach ($d->Cuisines as $r_cuisines) {
                        if ($i < 2) {
                            $rcuisines[] = array(
                                'name' => $r_cuisines->name,
                                'name_ar' => $r_cuisines->name_ar,
                                'name_kur' => $r_cuisines->name_kur
                            );
                            $i = $i + 1;
                        }
                    }

                    $check_favourite = DB::table('favourite_list')->where('user_id', $user_id)->where('restaurant_id', $d->id)->get();
                    if (count($check_favourite) != 0) {
                        $is_favourite = 1;
                    } else {
                        $is_favourite = 0;
                    }
                    //calculate restaurant open time
                    $is_open = $this->check_restaurant_open($d);
                    $restaurant_open_close_time = $this->get_restaurant_open_and_close_time($d);

                    //check restaurant offer
                    $restaurant_offer = "";
                    if ($d->offer_amount != '' && $d->offer_amount != 0) {
                        if ($d->discount_type == 1) {
                            $restaurant_offer = "Flat offer " . DEFAULT_CURRENCY_SYMBOL . " " . $d->offer_amount;
                        } else {
                            $restaurant_offer = $d->offer_amount . "% offer";
                        }
                        if ($d->target_amount != 0) {
                            $restaurant_offer = $restaurant_offer . " on orders above " . DEFAULT_CURRENCY_SYMBOL . " " . $d->target_amount;
                        }
                    }
                    $res_id = $d->id;
                    $rating = $this->order_ratings->with('Foodrequest')
                        ->wherehas('Foodrequest', function ($q) use ($res_id) {
                            $q->where('restaurant_id', $res_id);
                        })
                        ->avg('restaurant_rating');

                    if ($rating < 1) {
                        $rating = 5;
                    }

                    if ($d->parent == 0) {
                        $check_sub_branches = $this->restaurants->where('parent', $d->id)->first();
                        if ($check_sub_branches) {
                            $is_main_branch = 0;
                        } else {
                            $is_main_branch = 1;
                        }
                    } else {
                        $is_main_branch = 0;
                    }

                    if (sizeof($rcuisines) > 0) {
                        if ($is_open == 1) {
                            $restaurant_open_list[] = array(
                                'id' => $d->id,
                                'name' => $d->restaurant_name,
                                'name_ar' => $d->restaurant_name_ar,
                                'name_kur' => $d->restaurant_name_kur,
                                'image' => SPACES_BASE_URL . $d->image,
                                'discount' => $d->discount,
                                'rating' => round($rating, 1),
                                'is_open' => $is_open,     // 1- Open , 0 - Close
                                'cuisines' => $rcuisines,
                                // 'travel_time' => $d->estimated_delivery_time,
                                'price' => $restaurant_offer,
                                'discount_type' => $d->discount_type,
                                'target_amount' => $d->target_amount,
                                'offer_amount' => $d->offer_amount,
                                'is_favourite' => $is_favourite,
                                'delivery_type' => $d->delivery_type,
                                'address' => $d->address,
                                'is_main_branch' => $is_main_branch,
                                'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                                'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                            );
                        } else {
                            $restaurant_closed_list[] = array(
                                'id' => $d->id,
                                'name' => $d->restaurant_name,
                                'name_ar' => $d->restaurant_name_ar,
                                'name_kur' => $d->restaurant_name_kur,
                                'image' => SPACES_BASE_URL . $d->image,
                                'discount' => $d->discount,
                                'rating' => round($rating, 1),
                                'is_open' => $is_open,     // 1- Open , 0 - Close
                                'cuisines' => $rcuisines,
                                // 'travel_time' => $d->estimated_delivery_time,
                                'price' => $restaurant_offer,
                                'discount_type' => $d->discount_type,
                                'target_amount' => $d->target_amount,
                                'offer_amount' => $d->offer_amount,
                                'is_favourite' => $is_favourite,
                                'delivery_type' => $d->delivery_type,
                                'address' => $d->address,
                                'is_main_branch' => $is_main_branch,
                                'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                                'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                            );
                        }
                    }

                }

                if ((sizeof($restaurant_open_list) > 0) || (sizeof($restaurant_closed_list) > 0)) {
                    $restaurant_list = array_merge($restaurant_open_list, $restaurant_closed_list);
                    $response_array = array('status' => true, 'restaurants' => $restaurant_list);
                } else {
                    $response_array = array('status' => false, 'message' => 'No Data Found');
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
    public function get_menu(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $menu = $this->menu;
                $foodlist = $this->foodlist;
                $restaurant_id = $request->restaurant_id;
                $menu_list = array();

                $check = $menu::where('restaurant_id', $restaurant_id)->where('status', 1)->get();

                foreach ($check as $c) {
                    $food_count = $foodlist::where('menu_id', $c->id)->where('status', 1)->count();

                    $menu_list[] = array(
                        'menu_id' => $c->id,
                        'menu_name' => $c->menu_name,
                        'food_count' => $food_count,
                    );
                }

                $response_array = array('status' => true, 'menus' => $menu_list);
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
    public function get_nearby_restaurant(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'lat' => 'required',
                    'lng' => 'required',
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $source_lat = $request->lat;
                $source_lng = $request->lng;
                $restaurants = $this->restaurants;
                $isHomeScreen = $request->is_home_screen;
                $size_cuisines = isset($request->cuisines) ? sizeof($request->cuisines) : 0;
                $cuisines = isset($request->cuisines) ? $request->cuisines : '';
                $is_pureveg = 0;
                $is_filter = $request->is_filter;
                if(isset($request->is_pureveg)) {
                    switch($request->is_pureveg) {
                        case 1:
                            $is_pureveg = [1];
                            break;
                        case 2:
                            $is_pureveg = [2,3];
                            break;
                        case 3:
                            $is_pureveg = [1,2,3];
                            break;
                    }
                }
                // future reference

                // if ($request->is_home_screen) {
                //     $query = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)->where('is_approved', 1)
                //         ->select('restaurants.*')
                //         ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                //                     * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                //                     * sin(radians(`lat`)))) as distance")
                //         ->groupBy('restaurant_name')
                //         ->orderBy('distance');
                //     Log::info('home screen get_nearby_restaurant api');
                // } else {
                    //check location availability
                    if ($request->address) {
                        $request->address = trim($request->address, '"');;
                        $delivery_charge_details = $this->addcity
                            ->where('city', 'LIKE', '%' . $request->address . '%')
                            ->select('add_city.*', 'city_geofencing.polygons')
                            ->leftJoin('city_geofencing', function ($join) {
                                $join->on('city_geofencing.city_id', '=', 'add_city.id');
                            })->get();
                    } else {
                        $delivery_charge_details = $this->addcity
                            ->select('add_city.*', 'city_geofencing.polygons')
                            ->leftJoin('city_geofencing', function ($join) {
                                $join->on('city_geofencing.city_id', '=', 'add_city.id');
                            })->get();
                    }
                    if (count($delivery_charge_details) == 0) {
                        $delivery_charge_details = $this->addcity
                            ->select('add_city.*', 'city_geofencing.polygons')
                            ->leftJoin('city_geofencing', function ($join) {
                                $join->on('city_geofencing.city_id', '=', 'add_city.id');
                            })
                            ->get();
                    }
                    $delivery_charge_data = "";
                    foreach ($delivery_charge_details as $value) {
                        $polygon = json_decode($value->polygons);
                        $ponits = array($source_lng, $source_lat);
                        $is_avail = $this->contains($ponits, $polygon[0]);
                        if ($is_avail == 1) {
                            $delivery_charge_data = $value;
                            $check_restaurant = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('city', 'like', '%"' . $delivery_charge_data->id . '"%')->where('status', 1)->where('is_approved', 1)->count();
                            if ($check_restaurant != 0) {
                                break;
                            }
                        }
                    }
                    if ($delivery_charge_data != "") {
                        $checkData = 0;
                        if($is_filter != 1 && $cuisines == '' && Redis::exists('nearby_restaurant-'.$source_lat.'-'.$source_lng)) {
                            $checkData = 1;
                            $data = collect(json_decode(Redis::get('nearby_restaurant-'.$source_lat.'-'.$source_lng)));
                        }else {
                            $query = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('city', 'like', '%"' . $delivery_charge_data->id . '"%')->where('status', 1)->where('is_approved', 1)
                            ->select('restaurants.*')
                            ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                * sin(radians(`lat`)))) as distance");
                            $query = $query->when(($request->is_offer == 1),
                                function ($q) {
                                    return $q->where('offer_amount', '!=', 0);
                                });
                            $query = $query->when(($size_cuisines != 0),
                                function ($q) use ($cuisines) {
                                    $q->wherehas('Cuisines', function ($q) use ($cuisines) {
                                        return $q->whereIn('cuisines.id', $cuisines);
                                    });
                                });
                            $query = $query->when(($is_pureveg != 0),
                                function ($q) use ($is_pureveg) {
                                    return $q->whereIn('restaurant_type', $is_pureveg);
                                });
                            
                            if($request->sort_type != 0) {
                                // Sorting
                                switch ($request->sort_type) {
                                    case 2:
                                        // Rating
                                        $query = $query->orderBy('rating','DESC');
                                        break;
                                    case 3:
                                        // Cost for high to low
                                        $query = $query->orderBy('cost_for_two','ASC');
                                        break;
                                    case 4:
                                        // Cost for low to high
                                        $query = $query->orderBy('cost_for_two','DESC');
                                        break;
                                }
                            }else {
                                $query = $query->orderBy('sort', 'ASC');
                            }

                            $limit = PAGINATION;
                            $page = isset($request->page) ? $request->page : 1;
                            $offset = ($page - 1) * $limit;
                            $query = $query->when(($limit != '-1' && isset($offset)),
                                function ($q) use ($limit, $offset) {
                                    return $q->offset($offset)->limit($limit);
                                });
                            $data = $query->get();

                            foreach($data as $d) {
                                $res_lat = $d->lat;
                                $res_lng = $d->lng;

                                $minutes = $this->get_travel_time($source_lat ,$source_lng ,$res_lat ,$res_lng);
                                $str = strstr($d->estimated_food_preparation_time, '-');

                                if($str != false) {
                                    $d->estimated_food_preparation_time = trim(substr($str, 1));
                                }

                                $travel_time = $d->estimated_food_preparation_time + $minutes + 5;
                                $d->distance_mins = $minutes + 5;
                                $d->total_delivery_time = round($travel_time);
                            }
                            if($is_filter != 1 && $cuisines == '') {
                                Redis::set('nearby_restaurant-'.$source_lat.'-'.$source_lng , $query->get());
                                Redis::expire('nearby_restaurant-'.$source_lat.'-'.$source_lng , 300);
                            }
                        }
                    } 
                    else {
                        $data = '';
                    }
                // }
                $restaurant_list = array();
                $restaurant_closed_list = array();
                $restaurant_open_list = array();
                $j = 0;
                $sorted_data = array();
                if($request->sort_type == 1) {
                    $data = collect($data);
                    $data = $data->sortBy('total_delivery_time');
                }
                if($data != '')
                {
                    if($is_filter != 1 && $cuisines == '' && $checkData == 1 && Redis::exists('nearby_restaurant-data-'.$source_lat.'-'.$source_lng)) {
                        $restaurant_list = collect(json_decode(Redis::get('nearby_restaurant-data-'.$source_lat.'-'.$source_lng)));
                    }else {
                        foreach ($data as $d) {
                            if($isHomeScreen == '1' && $d->total_delivery_time > '45' ) {
                                continue;
                            }
                            // To display only two restaurants
                            $rcuisines = array();
                            $i = 0;
                            if(isset($d->cuisines)){
                                $cuisine = $d->cuisines;
                            }else {
                                $cuisine = $d->Cuisines;
                            }
                            foreach ($cuisine as $r_cuisines) {
                                if ($i < 3) // To display only two cuisines
                                {
                                    $rcuisines[] = array(
                                        'name' => ucwords($r_cuisines->name),
                                        'name_ar' => $r_cuisines->name_ar,
                                        'name_kur' => $r_cuisines->name_kur
                                    );
                                    $i = $i + 1;
                                }
                            }

                            $check_favourite = $this->favouritelist->where('user_id', $user_id)->where('restaurant_id', $d->id)->get();
                            if (count($check_favourite) != 0) {
                                $is_favourite = 1;
                            } else {
                                $is_favourite = 0;
                            }

                            //calculate restaurant open time
                            $is_open = $this->check_restaurant_open($d);
                            $restaurant_open_close_time = $this->get_restaurant_open_and_close_time($d);

                            //check restaurant offer
                            $restaurant_offer = "";
                            if ($d->offer_amount != '' && $d->offer_amount != 0 && $d->discount != 0) {
                                if ($d->discount_type == 1) {
                                    $restaurant_offer = "Flat offer " . DEFAULT_CURRENCY_SYMBOL . " " . $d->offer_amount;
                                } else {
                                    $restaurant_offer = $d->offer_amount . "% offer";
                                }
                                if ($d->target_amount != 0) {
                                    $restaurant_offer = $restaurant_offer . " on orders above " . DEFAULT_CURRENCY_SYMBOL . " " . $d->target_amount;
                                }
                            }
                            if ($d->parent == 0) {
                                $check_offer_for_restaurant = $this->banner->where('status', 1)->where('restaurant_id', $d->id)->get();
                            } else {
                                $check_offer_for_restaurant = $this->banner->where('status', 1)->where('restaurant_id', $d->parent)->get();
                            }

                            if (count($check_offer_for_restaurant) != 0) {
                                $is_food_offer_exist = 1;
                            } else {
                                $is_food_offer_exist = 0;
                            }

                            $rating = $d->rating;

                            if ($d->discount == 0) {
                                $d->discount = "";
                            }

                            // To calculate delivery charge based on distance

                            $delivery_distance = $this->calculate_distance($source_lat, $source_lng, $d->lat, $d->lng);
                            $get_delivery_charge = DB::table('restaurant_delivery_charges')->where('restaurant_id', $d->id)->where('min_distance', '<', $delivery_distance)->where('max_distance', '>=', $delivery_distance)->first();
                            $check_admin_charge = $d->delivery_charge_status;
                            if (!empty($get_delivery_charge && $check_admin_charge == 1)) {
                                $d->restaurant_delivery_charge = $get_delivery_charge->delivery_charge;
                            }else{
                                $d->restaurant_delivery_charge = 0;
                            }


                            if ($is_open == 1 && $d->is_busy!=1) {
                                $restaurant_open_list[] = array(
                                    'id' => $d->id,
                                    'name' => ucwords(strtolower($d->restaurant_name)),
                                    'name_ar' => $d->restaurant_name_ar,
                                    'name_kur' => $d->restaurant_name_kur,
                                    'image' => SPACES_BASE_URL . $d->image,
                                    'discount' => $d->discount,
                                    'rating' => round($rating, 1),
                                    'is_open' => $is_open,     // 1- Open , 0 - Close
                                    'is_busy' => $d->is_busy,
                                    'cuisines' => $rcuisines,
                                    'travel_time' => $d->total_delivery_time,
                                    // 'distance' => isset($d->distance_mins) ? $d->distance_mins : '',
                                    'price' => $restaurant_offer,
                                    'discount_type' => $d->discount_type,
                                    'target_amount' => $d->target_amount,
                                    'offer_amount' => $d->offer_amount ? $d->offer_amount : "0",
                                    'offer_type'=> $d->offer_type,
                                    'offer_value'=> $d->offer_value,
                                    'is_food_offer_exist' => $is_food_offer_exist,
                                    'is_favourite' => $is_favourite,
                                    'delivery_type' => $d->delivery_type,
                                    'address' => $d->address,
                                    'min_order_value' => $d->min_order_value,
                                    'restaurant_delivery_charge' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                                    'restaurant_delivery_charge_ios' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                                    'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                                    'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                                );
                            } else {
                                $restaurant_closed_list[] = array(
                                    'id' => $d->id,
                                    'name' => ucwords(strtolower($d->restaurant_name)),
                                    'name_ar' => $d->restaurant_name_ar,
                                    'name_kur' => $d->restaurant_name_kur,
                                    'image' => SPACES_BASE_URL . $d->image,
                                    'discount' => $d->discount,
                                    'rating' => round($rating, 1),
                                    'is_open' => $is_open,     // 1- Open , 0 - Close
                                    'is_busy' => $d->is_busy,
                                    'cuisines' => $rcuisines,
                                    'travel_time' => $d->total_delivery_time,
                                    // 'distance' => isset($d->distance_mins) ? $d->distance_mins : '',
                                    'price' => $restaurant_offer,
                                    'discount_type' => $d->discount_type,
                                    'offer_type'=> $d->offer_type,
                                    'offer_value'=> $d->offer_value,
                                    'target_amount' => $d->target_amount,
                                    'offer_amount' => $d->offer_amount ? $d->offer_amount : "0",
                                    'is_food_offer_exist' => $is_food_offer_exist,
                                    'is_favourite' => $is_favourite,
                                    'delivery_type' => $d->delivery_type,
                                    'address' => $d->address,
                                    'min_order_value' => $d->min_order_value,
                                    'restaurant_delivery_charge' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                                    'restaurant_delivery_charge_ios' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                                    'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                                    'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                                );
                            }
                            $j++;
                        }
                        $restaurant_list = array_merge($restaurant_open_list, $restaurant_closed_list);
                        if($is_filter != 1 && $cuisines == '') {
                            Redis::set('nearby_restaurant-data-'.$source_lat.'-'.$source_lng , json_encode($restaurant_list));
                            Redis::expire('nearby_restaurant-data-'.$source_lat.'-'.$source_lng , 300);
                        }
                    }
                }
                else
                {
                    $restaurant_list = [];
                }
                    if (count($restaurant_list) != 0) {
                        $response_array = array('status' => true, 'restaurants' => $restaurant_list);
                    } else {
                        $response_array = array('status' => false, 'message' => __('constants.no_restaurant'));
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
    public function newly_added_restaurant(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $restaurants = $this->restaurants;
            $query = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)->where('is_approved', 1)->where('is_newly_added', 1)
                ->select('restaurants.*')
                ->groupBy('restaurant_name');

            $query = $query->when(($request->is_offer == 1),
                function ($q) {
                    return $q->where('offer_amount', '!=', 0);
                });

            $limit = PAGINATION;
            $page = isset($request->page) ? $request->page : 1;
            $offset = ($page - 1) * $limit;
            $query = $query->when(($limit != '-1' && isset($offset)),
                function ($q) use ($limit, $offset) {
                    return $q->offset($offset)->limit($limit);
                });

            $data = $query->orderBy('id', 'desc')->get();

            $restaurant_list = array();
            $restaurant_closed_list = array();
            $restaurant_open_list = array();
            $j = 0;

            $sorted_data = array();

            foreach ($data as $d1) {
                // if($j<2)  // To display only two restaurants
                // {
                $sorted_data[] = $d1->id;
                $rcuisines = array();
                $i = 0;
                foreach ($d1->Cuisines as $r_cuisines) {
                    if ($i < 3) // To display only two cuisines
                    {
                        $rcuisines[] = array(
                            'name' => ucwords($r_cuisines->name),
                            'name_ar' => $r_cuisines->name_ar,
                            'name_kur' => $r_cuisines->name_kur
                        );
                        $i = $i + 1;
                    }
                }

                $check_favourite = DB::table('favourite_list')->where('user_id', $user_id)->where('restaurant_id', $d1->id)->get();
                if (count($check_favourite) != 0) {
                    $is_favourite = 1;
                } else {
                    $is_favourite = 0;
                }

                //calculate restaurant open time
                $is_open = $this->check_restaurant_open($d1);
                $restaurant_open_close_time = $this->get_restaurant_open_and_close_time($d1);

                //check restaurant offer
                $restaurant_offer = "";
                if ($d1->offer_amount != '' && $d1->offer_amount != 0 && $d1->discount != 0) {
                    if ($d1->discount_type == 1) {
                        $restaurant_offer = "Flat offer " . DEFAULT_CURRENCY_SYMBOL . " " . $d1->offer_amount;
                    } else {
                        $restaurant_offer = $d1->offer_amount . "% offer";
                    }
                    if ($d1->target_amount != 0) {
                        $restaurant_offer = $restaurant_offer . " on orders above " . DEFAULT_CURRENCY_SYMBOL . " " . $d1->target_amount;
                    }
                }
                if ($d1->parent == 0) {
                    $check_offer_for_restaurant = DB::table('offers_banner')->where('status', 1)->where('restaurant_id', $d1->id)->get();
                } else {
                    $check_offer_for_restaurant = DB::table('offers_banner')->where('status', 1)->where('restaurant_id', $d1->parent)->get();
                }

                if (count($check_offer_for_restaurant) != 0) {
                    $is_food_offer_exist = 1;
                } else {
                    $is_food_offer_exist = 0;
                }

                $res_id = $d1->id;
                $rating = $this->order_ratings->with('Foodrequest')
                    ->wherehas('Foodrequest', function ($q) use ($res_id) {
                        $q->where('restaurant_id', $res_id);
                    })
                    ->avg('restaurant_rating');

                if ($rating < 1) {
                    $rating = 5;
                }

                if ($d1->discount == 0) {
                    $d1->discount = "";
                }

                // To calculate delivery charge based on distance
                if (isset($request->lat) && isset($request->lng)) {
                    $delivery_distance = $this->calculate_distance($request->lat, $request->lng, $d1->lat, $d1->lng);
                    $get_delivery_charge = DB::table('restaurant_delivery_charges')->where('restaurant_id', $d1->id)->where('min_distance', '<', $delivery_distance)->where('max_distance', '>=', $delivery_distance)->first();

                    if ($get_delivery_charge) {
                        $d1->restaurant_delivery_charge = $get_delivery_charge->delivery_charge;
                    }
                } else {
                    $check_delivery_charge = DB::table('restaurant_delivery_charges')->where('restaurant_id', $d1->id)->where('delivery_charge', '!=', 0)->min('delivery_charge');

                    if (isset($check_delivery_charge)) {
                        $d1->restaurant_delivery_charge = $check_delivery_charge;
                    }
                }

                if ($is_open == 1) {
                    $restaurant_open_list[] = array(
                        'id' => $d1->id,
                        'name' => ucwords(strtolower($d1->restaurant_name)),
                        'name_ar' => $d1->restaurant_name_ar,
                        'name_kur' => $d1->restaurant_name_kur,
                        'image' => SPACES_BASE_URL . $d1->image,
                        'discount' => $d1->discount,
                        'rating' => round($rating, 1),
                        'is_open' => $is_open,     // 1- Open , 0 - Close
                        'is_busy' => $d1->is_busy,
                        'cuisines' => $rcuisines,
                        // 'travel_time' => $d1->estimated_delivery_time,
                        'price' => $restaurant_offer,
                        'discount_type' => $d1->discount_type,
                        'target_amount' => $d1->target_amount,
                        'offer_amount' => $d1->offer_amount,
                        'is_food_offer_exist' => $is_food_offer_exist,
                        'is_favourite' => $is_favourite,
                        'delivery_type' => $d1->delivery_type,
                        'address' => $d1->address,
                        'min_order_value' => $d1->min_order_value,
                        'restaurant_delivery_charge' => $d1->restaurant_delivery_charge ? $d1->restaurant_delivery_charge : 0,
                        'restaurant_delivery_charge_ios' => $d1->restaurant_delivery_charge ? $d1->restaurant_delivery_charge : 0,
                        'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                        'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                    );
                } else {
                    $restaurant_closed_list[] = array(
                        'id' => $d1->id,
                        'name' => ucwords(strtolower($d1->restaurant_name)),
                        'name_ar' => $d1->restaurant_name_ar,
                        'name_kur' => $d1->restaurant_name_kur,
                        'image' => SPACES_BASE_URL . $d1->image,
                        'discount' => $d1->discount,
                        'rating' => round($rating, 1),
                        'is_open' => $is_open,     // 1- Open , 0 - Close
                        'is_busy' => $d1->is_busy,
                        'cuisines' => $rcuisines,
                        // 'travel_time' => $d1->estimated_delivery_time,
                        'price' => $restaurant_offer,
                        'discount_type' => $d1->discount_type,
                        'target_amount' => $d1->target_amount,
                        'offer_amount' => $d1->offer_amount,
                        'is_food_offer_exist' => $is_food_offer_exist,
                        'is_favourite' => $is_favourite,
                        'delivery_type' => $d1->delivery_type,
                        'address' => $d1->address,
                        'min_order_value' => $d1->min_order_value,
                        'restaurant_delivery_charge' => $d1->restaurant_delivery_charge ? $d1->restaurant_delivery_charge : 0,
                        'restaurant_delivery_charge_ios' => $d1->restaurant_delivery_charge ? $d1->restaurant_delivery_charge : 0,
                        'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                        'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                    );
                }
                $j++;
                // }
            }

            $restaurant_list = array_merge($restaurant_open_list, $restaurant_closed_list);

            if (count($restaurant_list) != 0) {
                $response_array = array('status' => true, 'restaurants' => $restaurant_list);
            } else {
                $response_array = array('status' => false, 'message' => __('constants.no_restaurant'));
            }

            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_location_suburb(Request $request)
    {
        try {
            $validator = Validator::make(
                    $request->all(),
                    array(
                        'lat' => 'required',
                        'lng' => 'required'
                    ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                if($request->header('authId')!="")
                {
                    $user_id = $request->header('authId');
                }else
                {
                    $user_id = $request->authId;
                }

            
                $source_lat = $request->lat;
                $source_lng = $request->lng;
                
                //check location availability

                if($request->address)
                {
                    $delivery_charge_details = DB::table('add_city')
                                    ->where('add_city.city','like','%'.$request->address.'%')
                                    ->select('add_city.*','city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($source_lat) ) *
                                                        cos( radians( city_geofencing.latitude ) )
                                                        * cos( radians( city_geofencing.longitude ) - radians($source_lng)
                                                        ) + sin( radians($source_lat) ) *
                                                        sin( radians( city_geofencing.latitude ) ) )
                                                    ) AS distance"))
                                    ->leftJoin('city_geofencing', function($join)
                                    {
                                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                                    })
                                    ->get();
                }else
                {
                    $delivery_charge_details = DB::table('add_city')
                                    ->select('add_city.*','city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($source_lat) ) *
                                                        cos( radians( city_geofencing.latitude ) )
                                                        * cos( radians( city_geofencing.longitude ) - radians($source_lng)
                                                        ) + sin( radians($source_lat) ) *
                                                        sin( radians( city_geofencing.latitude ) ) )
                                                    ) AS distance"))
                                    ->leftJoin('city_geofencing', function($join)
                                    {
                                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                                    })
                                    ->get();
                }
                
                if(count($delivery_charge_details)==0)
                {
                    $delivery_charge_details = DB::table('add_city')
                                    ->select('add_city.*','city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($source_lat) ) *
                                                        cos( radians( city_geofencing.latitude ) )
                                                        * cos( radians( city_geofencing.longitude ) - radians($source_lng)
                                                        ) + sin( radians($source_lat) ) *
                                                        sin( radians( city_geofencing.latitude ) ) )
                                                    ) AS distance"))
                                    ->leftJoin('city_geofencing', function($join)
                                    {
                                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                                    })
                                    ->get();
                }
                    
                // dd($delivery_charge_details);
                $delivery_charge_data = "";
                foreach($delivery_charge_details as $value)
                {
                    $polygon = json_decode($value->polygons);
                    // dd($polygon[0]);
                    $ponits = array($source_lng, $source_lat);
                    $is_avail = $this->contains($ponits, $polygon[0]);
                
                    if($is_avail==1)
                    {
                        $delivery_charge_data = $value;
                        break;
                    }
                }
                if($delivery_charge_data=="")
                {
                    $response_array = array('status'=>false,'message'=>"Your location not in deliverable area");
                
                }elseif($delivery_charge_data)
                {
                    $response_array = array('status'=>true,'data'=>$delivery_charge_data);
                }
                $response = response()->json($response_array, 200);
                return $response;
            }
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_area_by_latlng(GetAreaByLatLngRequest $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $source_lat = $request->lat;
            $source_lng = $request->lng;

            //check location availability
            $delivery_charge_details = $this->addcity
                ->select('add_city.*', 'city_geofencing.polygons')
                ->leftJoin('city_geofencing', function ($join) {
                    $join->on('city_geofencing.city_id', '=', 'add_city.id');
                })
                ->get();

            if (!empty($delivery_charge_details)) {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_area_found',$lang));
                return response()->json($response_array, 200);
            }
            foreach ($delivery_charge_details as $value) {
                $polygon = json_decode($value->polygons);
                $ponits = array($source_lng, $source_lat);
                $is_avail = $this->contains($ponits, $polygon[0]);
                if ($is_avail == 1) {
                    $response_array = array('status' => true, 'area' => $value->city);
                    return response()->json($response_array, 200);
                }
            }
            $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_area_found',$lang));
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
    public function get_popular_brands(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'lat' => 'required',
                    'lng' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $popularbrands = $this->popularbrands;
                $source_lat = $request->lat;
                $source_lng = $request->lng;
                $checkData = 0;
                if(Redis::exists('popular_brands-'.$source_lat.'-'.$source_lng)) {
                    $checkData = 1;
                    $data = collect(json_decode(Redis::get('popular_brands-'.$source_lat.'-'.$source_lng)));
                }else {
                    $city_id = $this->getCurrentCityId($source_lng, $source_lat);
                    
                    //geofencing for restaurant
                    $data = $popularbrands->leftJoin('restaurants', 'popular_brands_list.name', '=', 'restaurants.id')
                            ->where('city', 'like', '%"' . $city_id . '"%')
                            ->where('popular_brands_list.status', 1)
                            ->where('restaurants.status', 1)
                            ->select('popular_brands_list.*', 'restaurants.restaurant_name as name', 'restaurants.id as restaurant_id')
                            ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                        * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                        * sin(radians(`lat`)))) as distance")
                            ->having('distance', '<=', DEFAULT_RADIUS)
                            ->orderBy('distance')
                            ->get();
                    Redis::set('popular_brands-'.$source_lat.'-'.$source_lng , $data);
                    Redis::expire('popular_brands-'.$source_lat.'-'.$source_lng , 300);
                }
                
                if (count($data) != 0) {
                    if($checkData == 1 && Redis::exists('popular_brands-data-'.$source_lat.'-'.$source_lng)) {
                        $data = collect(json_decode(Redis::get('popular_brands-data-'.$source_lat.'-'.$source_lng)));
                    }else {
                        foreach ($data as $d) {
                            //calculate ratings
                            $res_id = $d->restaurant_id;
                            $d->id = $d->restaurant_id;
                            $rating = $this->order_ratings->with('Foodrequest')
                                ->wherehas('Foodrequest', function ($q) use ($res_id) {
                                    $q->where('restaurant_id', $res_id);
                                })
                                ->avg('restaurant_rating');
                            $d->rating = round($rating, 1);

                            $res_data = $this->restaurants->find($res_id);
                            //calculate restaurant open time
                            $is_open = $this->check_restaurant_open($res_data);
                            $d->is_open = $is_open;
                            if ($res_data->parent == 0) {
                                $check_sub_branches = $this->restaurants->where('parent', $res_data->id)->first();
                                if ($check_sub_branches) {
                                    $d->is_main_branch = 0;
                                } else {
                                    $d->is_main_branch = 1;
                                }
                            } else {
                                $d->is_main_branch = 0;
                            }
                        }
                        Redis::set('popular_brands-data-'.$source_lat.'-'.$source_lng , $data);
                        Redis::expire('popular_brands-data-'.$source_lat.'-'.$source_lng , 300);
                    }
                    $response_array = array('status' => true, 'data' => $data, 'base_url' => SPACES_BASE_URL);
                } else {
                    $response_array = array('status' => false, 'message' => 'No Data Found');
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_cuisines(Request $request)
    {
        try {
            $source_lat = $request->lat;
            $source_lng = $request->lng;
            if(Redis::exists('cuisines-'.$source_lat.'-'.$source_lng)) {
                $data = collect(json_decode(Redis::get('cuisines-'.$source_lat.'-'.$source_lng)));
            }else {
                $city_id = $this->getCurrentCityId($source_lng, $source_lat);
                $data = $this->restaurants->leftJoin('restaurant_cuisines','restaurants.id','restaurant_cuisines.restaurant_id')
                ->leftjoin('cuisines', 'cuisines.id', '=', 'restaurant_cuisines.cuisine_id')->select('cuisines.*')
                ->where('restaurants.status', 1)->where('restaurants.is_busy',0)
                ->where('restaurants.city', 'like', '%"' . $city_id . '"%')
                ->where('cuisines.status',1)->where('cuisines.is_displayed', 1)
                ->distinct('cuisines.id')
                ->get();
                Redis::set('cuisines-'.$source_lat.'-'.$source_lng , $data);
                Redis::expire('cuisines-'.$source_lat.'-'.$source_lng , 300);
            }
            if (count($data) != 0) {
                $response_array = array('status' => true, 'data' => $data, 'base_url' => SPACES_BASE_URL);
            } else {
                $response_array = array('status' => false, 'message' => 'No Data Found');
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_favourite_list(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $favouritelist = $this->favouritelist;
            $restaurants = $this->restaurants;
            $data = $favouritelist::where('user_id', $user_id)->get();
            $restaurant_list = array();
            if (count($data) != 0) {
                foreach ($data as $key) {

                    $restaurant_detail = $restaurants::with(['Cuisines', 'RestaurantTimer'])->where('id', $key->restaurant_id)->where('status', 1)->first();

                    $restaurant_cuisines = $this->restaurantcuisines->join('cuisines', 'cuisines.id', '=', 'restaurant_cuisines.cuisine_id')
                        ->join('restaurants', 'restaurants.id', '=', 'restaurant_cuisines.restaurant_id')
                        ->select('restaurant_cuisines.restaurant_id as restaurant_id', 'cuisines.name as cuisine_name','cuisines.name_ar as cuisine_name_ar','cuisines.name_kur as cuisine_name_kur', 'restaurants.restaurant_name as restaurant_name')
                        ->where('restaurants.id', '=', $key->restaurant_id)
                        ->where('restaurants.status', 1)
                        ->get();

                    $rcuisines = array();
                    $i = 0;
                    foreach ($restaurant_cuisines as $r_cuisines) {

                        if ($restaurant_detail->restaurant_name == $r_cuisines->restaurant_name && $i < 2) // To display only two cuisines
                        {
                            $rcuisines[] = array(
                                'name' => $r_cuisines->cuisine_name,
                                'name_ar' => $r_cuisines->cuisine_name_ar,
                                'name_kur' => $r_cuisines->cuisine_name_kur
                            );
                            $i = $i + 1;
                        }

                    }

                    if (isset($restaurant_detail->id)) {
                        //calculate restaurant open time
                        $is_open = $this->check_restaurant_open($restaurant_detail);

                        //check restaurant offer
                        $restaurant_offer = "";
                        if ($restaurant_detail->offer_amount != '' && $restaurant_detail->offer_amount != 0) {
                            if ($restaurant_detail->discount_type == 1) {
                                $restaurant_offer = "Flat offer " . DEFAULT_CURRENCY_SYMBOL . " " . $restaurant_detail->offer_amount;
                            } else {
                                $restaurant_offer = $restaurant_detail->offer_amount . "% offer";
                            }
                            if ($restaurant_detail->target_amount != 0) {
                                $restaurant_offer = $restaurant_offer . " on orders above " . DEFAULT_CURRENCY_SYMBOL . " " . $restaurant_detail->target_amount;
                            }
                        }
                        $res_id = $key->restaurant_id;
                        $rating = $this->order_ratings->with('Foodrequest')
                            ->wherehas('Foodrequest', function ($q) use ($res_id) {
                                $q->where('restaurant_id', $res_id);
                            })
                            ->avg('restaurant_rating');
                        if ($rating < 1) {
                            $rating = 5;
                        }
                        $restaurant_list[] = array(
                            'restaurant_id' => $key->restaurant_id,
                            'name' => $restaurant_detail->restaurant_name,
                            'name_ar' => $restaurant_detail->restaurant_name_ar,
                            'name_kur' => $restaurant_detail->restaurant_name_kur,
                            'image' => SPACES_BASE_URL . $restaurant_detail->image,
                            'discount' => $restaurant_detail->discount,
                            'rating' => round($rating, 1),
                            'is_open' => $is_open,     // 1- Open , 0 - Close
                            // 'travel_time' => $restaurant_detail->estimated_delivery_time,
                            'price' => $restaurant_offer,
                            'discount_type' => $restaurant_detail->discount_type,
                            'target_amount' => $restaurant_detail->target_amount,
                            'offer_amount' => $restaurant_detail->offer_amount,
                            'address' => $restaurant_detail->address,
                            'is_favourite' => 1,
                            'delivery_type' => $restaurant_detail->delivery_type,
                            'cuisines' => $rcuisines
                        );
                    }
                }

                $response_array = array('status' => true, 'favourite_list' => $restaurant_list);
            } else {
                $response_array = array('status' => false, 'message' => 'No favourite restaurants found');
            }

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_favourite(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);

            } else {
                $restaurant_id = $request->restaurant_id;

                $favouritelist = $this->favouritelist;

                $check = $favouritelist::where('user_id', $user_id)->where('restaurant_id', $restaurant_id)->first();

                if ($check) {
                    $favouritelist::where('id', $check->id)->delete();

                    $response_array = array('status' => true, 'message' => 'Removed from Favourites');
                } else {
                    $data = array();
                    $data['user_id'] = $user_id;
                    $data['restaurant_id'] = $restaurant_id;
                    $favouritelist::insert($data);

                    $response_array = array('status' => true, 'message' => 'Added to Favourites');
                }

            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_current_order_status(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            }else {
                $user_id = $request->authId;
            }
            $check_request = $this->foodrequest->where('user_id', $user_id)->where('status', '!=', 10)->where('is_rated', '=', 0)->orderBy('id', 'desc')->get();
            if (count($check_request) != 0) {
                $order_status = array();
                foreach ($check_request as $key) {

                    $restaurant_detail = $this->restaurants->where('id', $key->restaurant_id)->where('status', 1)->first();

                    if ($restaurant_detail) {
                        $item_list = $this->requestdetail->where('request_id', $key->id)->get();
                        $item_count = 0;

                        $delivery_boy_detail = $this->deliverypartners->find($key->delivery_boy_id);

                        if ($delivery_boy_detail) {
                            $delivery_boy_id = $delivery_boy_detail->id;
                            $delivery_boy_name = $delivery_boy_detail->name;
                            $delivery_boy_image = $delivery_boy_detail->profile_pic;
                            $delivery_boy_phone = $delivery_boy_detail->phone;
                        } else {
                            $delivery_boy_id = 0;
                            $delivery_boy_name = "";
                            $delivery_boy_image = "";
                            $delivery_boy_phone = "";
                        }

                        $get_item_lists = array();

                        foreach ($item_list as $list) {
                            $item_count = $item_count + $list->quantity;
                            $food_detail = $this->foodlist->where('id', $list->food_id)->where('status', 1)->first();
                            $get_item_lists[] = array(
                                'item_name' => (!empty($food_detail)) ? $food_detail->name : "",
                                'item_name_ar' => (!empty($food_detail)) ? $food_detail->name_ar : "",
                                'item_name_kur' => (!empty($food_detail)) ? $food_detail->name_kur : "",
                                'item_quantity' => $list->quantity,
                                'price' => ((!empty($food_detail)) ? $food_detail->price : 0) * $list->quantity
                            );
                        }

                        $order_status[] = array(
                            'request_id' => $key->id,
                            'order_id' => $key->order_id,
                            'ordered_time' => $key->ordered_time,
                            'restaurant_name' => $restaurant_detail->restaurant_name,
                            'restaurant_name_ar' => $restaurant_detail->restaurant_name_ar,
                            'restaurant_name_kur' => $restaurant_detail->restaurant_name_kur,
                            'restaurant_image' => $restaurant_detail->image,
                            'item_count' => $item_count,
                            'bill_amount' => $key->bill_amount,
                            'offer_discount' => $key->offer_discount,
                            'loyalty_discount' => $key->loyalty_discount,
                            'restaurant_discount' => $key->restaurant_discount,
                            'status' => $key->status,
                            'delivery_boy_id' => $delivery_boy_id,
                            'delivery_boy_image' => $delivery_boy_image,
                            'delivery_boy_phone' => $delivery_boy_phone,
                            'item_list' => $get_item_lists,
                            'delivery_type' => $key->delivery_type,
                            'total_members' => $key->total_members,
                            'pickup_dining_time' => $key->pickup_dining_time,
                        );
                    }
                }

                $response_array = array('status' => true, 'order_status' => $order_status);
            }else {
                $response_array = array('status' => false, 'message' => 'No orders in processing');
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
    public function track_order_detail(TrackOrderDetailRequest $request)
    {
        try {
            $request_id = $request->request_id;
            $trackorderstatus = $this->trackorderstatus;
            $check_request = $this->foodrequest->where('id', $request_id)->first();
            Log::info('check_request api order data' . $check_request);
            $restaurant_detail = $this->restaurants->where('id', $check_request->restaurant_id)->first();
            $order_status = array();
            $item_list = $this->requestdetail->where('request_id', $request_id)->get();
            $item_count = 0;
            $delivery_boy_detail = $this->deliverypartners->find($check_request->delivery_boy_id);
            if ($delivery_boy_detail) {
                $delivery_boy_id = $delivery_boy_detail->id;
                $delivery_boy_name = $delivery_boy_detail->name;
                $delivery_boy_image = $delivery_boy_detail->profile_pic;
                $delivery_boy_phone = $delivery_boy_detail->phone;
            } else {
                $delivery_boy_id = 0;
                $delivery_boy_name = "";
                $delivery_boy_image = "";
                $delivery_boy_phone = "";
            }
            foreach ($item_list as $list) {
                $item_count = $item_count + $list->quantity;
            }

            // Delivery Time in Mins Logics 
            $order_distance_time = $check_request->distance_travel_time;
            $food_prepared_time_seconds = $check_request->food_preparation_time * 60;
            $differ_time = strtotime($order_distance_time) + $food_prepared_time_seconds;
            $date = date('Y-m-d H:i:s');
            $d1 = new DateTime(date('Y-m-d H:i:s',$differ_time));
            $d2 = new DateTime($date);
            $interval = $d1->diff($d2);
            if($d1 > $d2) {
                $overall_food_delivery_time_mins = $interval->i;
            }else {
                $overall_food_delivery_time_mins = 0;
            }

            $order_detail = $this->requestdetail->where('request_id', $check_request->id)->get();
            $order_list_detail = array();
            foreach ($order_detail as $k) {
                $add_ons = array();
                if (!empty($k->Addons)) {
                    foreach ($k->Addons as $addon) {
                        $add_ons[] = array(
                            'id' => $addon->id,
                            'restaurant_id' => $addon->restaurant_id,
                            'name' => $addon->name,
                            'name_ar' => $addon->name_ar,
                            'name_kur' => $addon->name_kur,
                            'price' => $addon->price,
                            'created_at' => date("Y-m-d H:i:s", strtotime($addon->created_at)),
                            'updated_at' => date("Y-m-d H:i:s", strtotime($addon->updated_at)),
                        );
                    }
                }
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
                    'add_ons' => $k->RequestdetailAddons,
                );
            }
            $instruction_id = !empty($check_request->instruction_id)?json_decode($check_request->instruction_id):[];
            $instruction_list = DeliveryInstruction::withTrashed()->whereIn('id',$instruction_id)->get();
            $order_status[] = array(
                'request_id' => $request_id,
                'order_id' => $check_request->order_id,
                'ordered_time' => $check_request->ordered_time,
                'delivered_time' => $overall_food_delivery_time_mins,
                'current_time' => date('Y-m-d H:i:s'),
                'restaurant_name' => $restaurant_detail->restaurant_name,
                'restaurant_name_ar' => $restaurant_detail->restaurant_name_ar ? $restaurant_detail->restaurant_name_ar : "",
                'restaurant_name_kur' => $restaurant_detail->restaurant_name_kur ? $restaurant_detail->restaurant_name_kur : "",
                'restaurant_image' => SPACES_BASE_URL . $restaurant_detail->image,
                'item_count' => $item_count,
                'item_total' => $check_request->item_total,
                'item_list' => $order_list_detail,
                'offer_discount' => $check_request->offer_discount,
                'loyalty_discount' => $check_request->loyalty_discount,
                'restaurant_packaging_charge' => $check_request->restaurant_packaging_charge,
                'tax' => $check_request->tax,
                'delivery_charge' => $check_request->delivery_charge,
                'bill_amount' => $check_request->bill_amount,
                'restaurant_discount' => $check_request->restaurant_discount,
                'status' => $check_request->status,
                'delivery_boy_id' => $delivery_boy_id,
                'delivery_boy_name' => $delivery_boy_name,
                'delivery_boy_image' => $delivery_boy_image,
                'delivery_boy_phone' => $delivery_boy_phone,
                'restaurant_lat' => $restaurant_detail->lat,
                'restaurant_lng' => $restaurant_detail->lng,
                'restaurant_address' => $restaurant_detail->address,
                'delivery_address' => $check_request->delivery_address,
                'restaurant_phone' => $restaurant_detail->phone,
                'user_lat' => $check_request->d_lat,
                'user_lng' => $check_request->d_lng,
                'delivery_type' => $check_request->delivery_type,
                'payment_type' => $check_request->paid_type,
                'total_members' => $check_request->total_members,
                'pickup_dining_time' => $check_request->pickup_dining_time,
                'delivery_address_title' => $check_request->delivery_address_title ? $check_request->delivery_address_title : "Home",
                'delivery_instruction'=>(!empty($instruction_list) && count($instruction_list)!=0)?$instruction_list:[],
                'image_base_url'=>SPACES_BASE_URL
            );
            $tracking_detail = $trackorderstatus::where('request_id', $request_id)->get();
            $response_array = array('status' => true, 'order_status' => $order_status, 'tracking_detail' => $tracking_detail);
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function order_history(Request $request)
    {
        try{
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }
            $foodRequest = $this->foodrequest;
            $lang = isset($request->lang)?$request->lang:'en';
            $orders = $foodRequest->where('user_id', $user_id)->where('status', 7)->latest()->limit(10)->get();
            $orderList = $this->getOrderData($orders);
            $upcomingOrders = $foodRequest->where('user_id', $user_id)->where('status', '!=', 10)->where('status', '!=', 7)->latest()->limit(10)->get();
            $upcomingOrdersList = $this->getOrderData($upcomingOrders);
            $cancelledOrders = $foodRequest->where('user_id', $user_id)->where('status', 10)->latest()->limit(10)->get();
            $cancelOrdersList = $this->getOrderData($cancelledOrders);
            if(count($upcomingOrdersList) != 0 || count($orderList) != 0 || count($cancelOrdersList) != 0) {
                $response_array = array('status' => true, 'base_url' => SPACES_BASE_URL, 'past_orders' => $orderList, 'upcoming_orders' => $upcomingOrdersList, 'cancel_orders' => $cancelOrdersList);
            }else {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_orders', $lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * Get Order Details
     * @param $orders
     * @return array
     */

    public function getOrderData($orders) {
        $orderList = array();
        foreach ($orders as $key) {
            $orderDetail = $key->Requestdetail;
            $order_list_detail = array();
            if(!empty($orderDetail)) {
                foreach ($orderDetail as $k) {
                    $add_ons = array();
                    if (!empty($k->Addons)) {
                        foreach ($k->Addons as $addon) {
                            $add_ons[] = array(
                                'id' => $addon->id,
                                'restaurant_id' => $addon->restaurant_id,
                                'name' => $addon->name,
                                'name_ar' => $addon->name_ar,
                                'name_kur' => $addon->name_kur,
                                'price' => $addon->price,
                                'created_at' => date("Y-m-d H:i:s", strtotime($addon->created_at)),
                                'updated_at' => date("Y-m-d H:i:s", strtotime($addon->updated_at)),
                            );
                        }
                    }
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
                    foreach ($k->RequestdetailAddons as $addon_detail) {
                        $choice_detail = $this->choice->find($addon_detail->addons_id);
                        if ($choice_detail) {
                            $choice_food_detail = $this->choice_category->find($choice_detail->choice_category_id);
                            if ($choice_food_detail) {
                                if ($choice_food_detail->restaurant_id == $key->restaurant_id) {
                                    $addon_detail->original_choice_price = $choice_detail->price;
                                }
                            }
                        }
                    }
                    $current_time = date('Y-m-d H:i:s');
                    $current_time = strtotime($current_time);
                    if (!empty($k->Foodlist)) {
                        if (($k->Foodlist->startfrom < $current_time) && ($current_time < $k->Foodlist->endfrom)) {
                            $food_percentage_offer = $k->Foodlist->discount;
                        } elseif (($k->Foodlist->startfrom == 0) && ($k->Foodlist->endfrom == 0)) {
                            $food_percentage_offer = $k->Foodlist->discount;
                        } else {
                            $food_percentage_offer = 0;
                        }
                    } else {
                        $food_percentage_offer = 0;
                    }

                    if (!empty($k->Foodlist)) {
                        if ($k->Foodlist->restaurant_id == $key->restaurant_id) {
                            $original_item_price = $k->Foodlist->price;
                        } else {
                            $original_item_price = "";
                        }
                    } else {
                        $original_item_price = "";
                    }

                    $order_list_detail[] = array(
                        'food_id' => (!empty($k->Foodlist) ? $k->Foodlist->id : ""),
                        'food_name' => (!empty($k->Foodlist) ? $k->Foodlist->name : ""),
                        'food_name_ar' => (!empty($k->Foodlist) ? $k->Foodlist->name_ar : ""),
                        'food_name_kur' => (!empty($k->Foodlist) ? $k->Foodlist->name_kur : ""),
                        'image' => (!empty($k->Foodlist->image) ? $k->Foodlist->image : ""),
                        'is_imported_image' => (!empty($k->Foodlist->is_imported_image) ? $k->Foodlist->is_imported_image : 0),
                        'food_percentage_offer' => $food_percentage_offer,
                        'food_quantity' => $k->quantity,
                        'tax' => (!empty($k->Foodlist) ? $k->Foodlist->tax : ""),
                        'item_price' => (!empty($k->Foodlist) ? $k->Foodlist->price : 0) * $k->quantity,
                        'original_item_price' => (int)$original_item_price,
                        'is_veg' => (!empty($k->Foodlist) ? $k->Foodlist->is_veg : ""),
                        'food_size' => $food_quantity,
                        'add_ons' => $k->RequestdetailAddons,
                    );
                }
            }

            $orderList[] = array(
                'request_id' => $key->id,
                'order_id' => $key->order_id,
                'restaurant_id' => isset($key->Restaurants->id) ? $key->Restaurants->id : $key->restaurant_id,
                'restaurant_name' => isset($key->Restaurants->restaurant_name) ? $key->Restaurants->restaurant_name : "",
                'restaurant_name_ar' => isset($key->Restaurants->restaurant_name_ar) ? $key->Restaurants->restaurant_name_ar : "",
                'restaurant_name_kur' => isset($key->Restaurants->restaurant_name_kur) ? $key->Restaurants->restaurant_name_kur : "",
                'restaurant_image' => isset($key->Restaurants->image) ? SPACES_BASE_URL . $key->Restaurants->image : "",
                'driver_id' => isset($key->Deliverypartners->id) ? $key->Deliverypartners->id : 0,
                'driver_name' => isset($key->Deliverypartners->name) ? $key->Deliverypartners->name : "",
                'driver_phone' => isset($key->Deliverypartners->phone) ? $key->Deliverypartners->phone : "",
                'driver_image' => isset($key->Deliverypartners->profile_pic) ? SPACES_BASE_URL.$key->Deliverypartners->profile_pic : "",
                'ordered_on' => !empty($key->ordered_time) ? $key->ordered_time : "",
                'delivered_on' => !empty($key->delivered_time) ? $key->delivered_time : "",
                'canceled_on' => !empty($key->canceled_time) ? $key->canceled_time : "",
                'cancel_type' => $key->is_canceled_by_user,
                'bill_amount' => $key->bill_amount,
                'deducted_bill_amount' => $key->deducted_bill_amount,
                'item_list' => $order_list_detail,
                'item_total' => $key->item_total,
                'offer_discount' => $key->offer_discount,
                'loyalty_discount' => $key->loyalty_discount,
                'restaurant_discount' => $key->restaurant_discount,
                'restaurant_packaging_charge' => $key->restaurant_packaging_charge,
                'tax' => $key->tax,
                'status' => $key->status,
                'delivery_charge' => $key->delivery_charge,
                'driver_tip' => $key->driver_tip,
                'payment_type' => $key->paid_type,
                'delivery_address' => $key->delivery_address,
                'restaurant_address' => isset($key->Restaurants->address) ? $key->Restaurants->address : "",
                'delivery_type' => $key->delivery_type,
                'total_members' => $key->total_members,
                'pickup_dining_time' => $key->pickup_dining_time,
                'is_rated' => $key->is_rated
            );
        }
        return $orderList;
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_order_status(Request $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $request_detail = $this->foodrequest->where('user_id', $user_id)
                ->where('status', '!=', 10)
                ->where('status', '!=', 7)
                ->first();

            $response_array = array('status' => true, 'message' => 'No Orders Available');

            if ($request_detail) {

                $order_id = $request_detail->order_id;

                $request_id = $request_detail->id;

                $ordered_time = $request_detail->ordered_time;

                $restaurant_detail = $this->restaurants->where('id', $request_detail->restaurant_id)->where('status', 1)->first();

                $user_detail = $this->users->where('id', $request_detail->user_id)->first();

                $address_detail = array();

                $request_status = $request_detail->status;

                $address_detail [] = array(
                    'd_address' => $request_detail->delivery_address,
                    's_address' => $restaurant_detail->address,
                    'd_lat' => $request_detail->d_lat,
                    'd_lng' => $request_detail->d_lng,
                    's_lat' => $restaurant_detail->lat,
                    's_lng' => $restaurant_detail->lng
                );

                $food_detail = array();
                $bill_detail = array();

                $data = $this->requestdetail->where('request_detail.request_id', $request_id)
                    ->join('food_list', 'food_list.id', '=', 'request_detail.food_id')
                    ->select('request_detail.quantity as quantity', 'food_list.name as food_name', 'food_list.name_ar as food_name_ar', 'food_list.name_kur as food_name_kur', 'food_list.price as price_per_quantity', 'food_list.is_veg as is_veg')
                    ->get();

                foreach ($data as $d) {
                    $food_detail[] = array(
                        'name' => $d->food_name ?: "",
                        'name_ar' => $d->food_name_ar ?: "",
                        'name_kur' => $d->food_name_kur ?: "",
                        'quantity' => $d->quantity,
                        'price' => $d->quantity * $d->price_per_quantity,
                        'is_veg' => $d->is_veg
                    );
                }

                $bill_detail[] = array(
                    'item_total' => $request_detail->item_total,
                    'offer_discount' => $request_detail->offer_discount,
                    'loyalty_discount' => $request_detail->loyalty_discount,
                    'restaurant_discount' => $request_detail->restaurant_discount,
                    'packaging_charge' => $request_detail->restaurant_packaging_charge,
                    'tax' => $request_detail->tax,
                    'delivery_charge' => $request_detail->delivery_charge,
                    'bill_amount' => $request_detail->bill_amount
                );

                $response_array = array('status' => true, 'request_id' => $request_id, 'ordered_time' => $ordered_time, 'order_id' => $order_id, 'restaurant_detail' => $restaurant_detail, 'user_detail' => $user_detail, 'address_detail' => $address_detail, 'bill_detail' => $bill_detail, 'food_detail' => $food_detail, 'request_status' => $request_status);
            }

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }



    /**
     * to list all promocode details
     * 
     * @param object $request
     * 
     * @return json $response
     */
    public function get_promo_list(Request $request)
    {
        try {
            $get_promo = $this->promocode->where('status',1)
                            ->whereDate('available_from','<=',Carbon::now())
                            ->whereDate('valid_till','>=',Carbon::now())
                            ->get();
            $response_array = array('status'=>true,'promo_list'=>$get_promo);
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
    * to check the availability of restaurant during checkout
    *
    * @param ogject $request
    * 
    * @return json $response
    */
    public function check_restaurant_availability(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'lat' => 'required',
                    'lng' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $restaurant_id = $request->restaurant_id;
                $source_lat = $request->lat;
                $source_lng = $request->lng;
                $lang = isset($request->lang)?$request->lang:'en';
                $chek_restaurant_status = $this->restaurants->where('status', 0)->where('id',$restaurant_id)->first();
                if(!empty($chek_restaurant_status)) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.restaurant_closed',$lang));
                    return response()->json($response_array, 200);
                }
                $chek_restaurant_busy_status = $this->restaurants->where('is_busy', 1)->where('id',$restaurant_id)->first();
                if(!empty($chek_restaurant_busy_status)) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.restaurant_is_busy',$lang));
                    return response()->json($response_array, 200);
                }
                $data = $this->restaurants->where('status', 1)
                    ->where('id', $restaurant_id)
                    ->select('restaurants.*')
                    ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                * sin(radians(`lat`)))) as distance")
                    ->having('distance', '<=', DEFAULT_RADIUS)
                    ->orderBy('distance')
                    ->first();

                //check location availability
                $delivery_charge_details = DB::table('add_city')
                    ->select('add_city.*', 'city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($source_lat) ) *
                                                        cos( radians( city_geofencing.latitude ) )
                                                        * cos( radians( city_geofencing.longitude ) - radians($source_lng)
                                                        ) + sin( radians($source_lat) ) *
                                                        sin( radians( city_geofencing.latitude ) ) )
                                                    ) AS distance"))
                    ->leftJoin('city_geofencing', function ($join) {
                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                    })
                    // ->having("distance", "<", DEFAULT_RADIUS)
                    ->get();
                $delivery_charge_data = "";
                foreach ($delivery_charge_details as $value) {
                    $polygon = json_decode($value->polygons);
                    $ponits = array($source_lng, $source_lat);
                    $is_avail = $this->contains($ponits, $polygon[0]);
                    if ($is_avail == 1) {
                        $delivery_charge_data = $value;
                        $check_restaurant = $this->restaurants->where('city', 'like', '%"' . $delivery_charge_data->id . '"%')->where('status', 1)->where('is_approved', 1)->select('restaurants.*')->where('id', $restaurant_id)->first();
                        if (!empty($check_restaurant))
                            break;
                        if (!empty($check_restaurant) == 0) {
                            $delivery_charge_data = "";
                        }
                    }
                }
                if ($delivery_charge_data == "") {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.your_location_not_in_deliverable_area',$lang));
                    return response()->json($response_array, 200);
                }
                if ($data) {
                    if(empty($data->banners) || $data->banners == '[]') {
                        $data->banners = null;
                    }else {
                        $data->banners = json_decode($data->banners);
                    }
                    if($request->is_reorder == 1 && !empty($request->food_id)) {
                        $unAvailableFoods = $this->food_availability_check($request->food_id , $restaurant_id);
                    }else {
                        $unAvailableFoods = [];
                    }
                    $response_array = array('status' => true, 'restaurant' => $data , 'unavailable_foods' => $unAvailableFoods);
                } else {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_data',$lang));
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_restaurant_availability_by_parent(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'lat' => 'required',
                    'lng' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }

                $restaurant_id = $request->restaurant_id;
                $source_lat = $request->lat;
                $source_lng = $request->lng;

                $check_parent_restaurant = $this->restaurants->where('id', $restaurant_id)->first();

                if ($check_parent_restaurant) {
                    if ($check_parent_restaurant->parent != 0) {
                        $restaurant_id = $check_parent_restaurant->parent;
                    }
                }

                $data = $this->restaurants->where('status', 1)
                    ->where('id', $restaurant_id)
                    ->select('restaurants.*')
                    ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                * sin(radians(`lat`)))) as distance")
                    // ->having('distance','<=',DEFAULT_RADIUS)
                    ->orderBy('distance')
                    ->first();

                //check location availability
                $radius = DEFAULT_RADIUS;
                $delivery_charge_details = DB::table('add_city')
                    ->select('add_city.*', 'city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($source_lat) ) *
                                                        cos( radians( city_geofencing.latitude ) )
                                                        * cos( radians( city_geofencing.longitude ) - radians($source_lng)
                                                        ) + sin( radians($source_lat) ) *
                                                        sin( radians( city_geofencing.latitude ) ) )
                                                    ) AS distance"))
                    ->leftJoin('city_geofencing', function ($join) {
                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                    })
                    // ->having("distance", "<", $radius)
                    ->get();

                $delivery_charge_data = "";
                foreach ($delivery_charge_details as $value) {
                    $polygon = json_decode($value->polygons);
                    // dd($polygon[0]);
                    $ponits = array($source_lng, $source_lat);
                    $is_avail = $this->contains($ponits, $polygon[0]);
                    // echo $is_avail;
                    if ($is_avail == 1) {
                        $delivery_charge_data = $value;
                        $check_restaurant = $this->restaurants->where('city', 'like', '%"' . $delivery_charge_data->id . '"%')->where('status', 1)->where('is_approved', 1)->select('restaurants.*')->where('id', $restaurant_id)->first();
                        if ($check_restaurant)
                            break;
                        if (!($check_restaurant)) {
                            $delivery_charge_data = "";
                        }
                    }
                }
                if ($delivery_charge_data == "") {
                    $data1 = $this->restaurants->where('status', 1)
                        ->where('parent', $restaurant_id)
                        ->select('restaurants.*')
                        ->get();

                    if (count($data1) != 0) {
                        $delivery_charge_data = "";
                        foreach ($delivery_charge_details as $value) {
                            $polygon = json_decode($value->polygons);
                            $ponits = array($source_lng, $source_lat);
                            $is_avail = $this->contains($ponits, $polygon[0]);

                            if ($is_avail == 1) {
                                $delivery_charge_data = $value;
                                foreach ($data1 as $key1) {
                                    # code...
                                    // echo $delivery_charge_data->id;
                                    $check_restaurant = $this->restaurants->where('city', 'like', '%"' . $delivery_charge_data->id . '"%')->where('status', 1)->where('is_approved', 1)->select('restaurants.*')->where('id', $key1->id)->first();
                                    if ($check_restaurant) {
                                        Log::info('check restaurant availability parent restaurat id: ' . $check_restaurant->id);
                                        $response_array = array('status' => true, 'restaurant' => $key1);
                                        $response = response()->json($response_array, 200);
                                        return $response;
                                    }
                                }
                                if (!($check_restaurant)) {
                                    $delivery_charge_data = "";
                                }
                            }
                        }
                    }

                    $response_array = array('status' => false, 'message' => "Your location not in deliverable area");
                    return response()->json($response_array, 200);
                }

                if ($data) {
                    Log::info('check restaurant availability restaurat id: ' . $check_restaurant->id);
                    $response_array = array('status' => true, 'restaurant' => $data);
                } else {
                    $response_array = array('status' => false, 'message' => __('constants.no_data'));
                }

            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * get loyalty points based on user
     * 
     * @return json $response
     */
    public function get_loyaltypoints(Request $request)
    {
        try {
            if($request->header('authId')!="") {
                $user_id = $request->header('authId');
            }else {
                $user_id = $request->authId;
            }
            $data = $this->users->find($user_id);
            return response()->json(['status'=>true,'data'=>$data]);    // type - 1 home, 2 work, 3 others
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * delete address of user
     * 
     * @param object $request, int $id
     * 
     * @return json $response
     */
    public function delete_delivery_address(Request $request, $id)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            if($request->header('authId')!="")
            {
                $user_id = $request->header('authId');
            }else
            {
                $user_id = $request->authId;
            }
            $data = $this->deliveryaddress->where('id',$id)->where('is_default',1)->first();
            if(empty($data)){
                $this->deliveryaddress->where('id',$id)->delete();
                $response_array = array('status'=>true,'message'=>$this->language_string_translation('constants.address_deleted_successfully',$lang));
            }else{
                $response_array = array('status'=>false,'message'=>$this->language_string_translation('constants.default_address_not_deletable',$lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function benifit_payment_status(Request $request)
    {
         if($request->header('authId')!="")
        {
            $user_id = $request->header('authId');
        }else
        {
            $user_id = $request->authId;
        }

         Log::info('benifit_payment_status api - user_id :'.$user_id);

        $data = DB::table('benefit')->where('UDF2',$user_id)->where('is_order_created',0)->first();

        if(!$data)
        {
             $paid_status = 0;
        }else
        {
            if($data->result == "CAPTURED")
            {
                DB::table('benefit')->where('UDF2',$user_id)->where('is_order_created',0)->update(['is_order_created'=>1]);
                $paid_status = 1;
            }else
            {
                DB::table('benefit')->where('UDF2',$user_id)->where('is_order_created',0)->update(['is_order_created'=>2]);
                $paid_status = 2;
            }

            $response_array = array('status'=>true,'is_paid'=>$paid_status);
            $response = response()->json($response_array, 200);
            return $response;
        }

          $response_array = array('status'=>true,'is_paid'=>$paid_status);
        $response = response()->json($response_array, 200);
        return $response;
    }

    public function pay_by_credit(Request $request)
    {
         $validator = Validator::make(
                $request->all(),
                array(
                    // 'order_id' => 'required',
                    // 'transaction_id' => 'required',
                    'card_number' => 'required',
                    'expiry_month' => 'required',
                    'expiry_year' => 'required',
                    'security_code' =>'required',
                    'amount' => 'required'
                ));

        if ($validator->fails())
        {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
        }else
        {
              if($request->header('authId')!="")
            {
                $user_id = $request->header('authId');
            }else
            {
                $user_id = $request->authId;
            }

            $order_id = $user_id.rand(1000,100000000000);
            $transaction_id = rand(1000,100000000000);
            $card_number = $request->card_number;
            $expiry_month = $request->expiry_month;
            $expiry_year = $request->expiry_year;
            $security_code = $request->security_code;
            $amount = $request->amount;
            $version="34";
            $method="PUT";
            $api_operation = "AUTHORIZE";
            $source_type = "CARD";
            $currency = "IQD";

            // $post_data = array();
            $post_data = array(
                'version' => $version,
                'orderId' => $order_id,
                'transactionId' => $transaction_id,
                'method' => $method,
                'apiOperation' => $api_operation,
                'sourceOfFunds[type]' => $source_type,
                'sourceOfFunds[provided][card][number]' => $card_number,
                'sourceOfFunds[provided][card][expiry][month]' => $expiry_month,
                'sourceOfFunds[provided][card][expiry][year]' => $expiry_year,
                'sourceOfFunds[provided][card][securityCode]' => $security_code,
                'order[amount]' => $amount,
                'order[currency]' => $currency
            );

            // print_r($post_data); exit;

            $url = "http://64.225.17.45/paymentgateway/process.php";

             // $fields = json_encode($fields);
                // $headers = array(
                //     'Content-Type: application/json'
                // );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $result = curl_exec($ch);

                //print_r($result);
                curl_close($ch);
                $result = json_decode($result);
                // print_r($result); exit;
                // echo $result->response->acquirerMessage; exit;
                // echo $result->result; exit;


                if(!isset($result))
                {
                    $reason = 'Something went wrong. Try again later';
                    $response_array = array('status'=>false,'message'=>'Something went wrong. Try again later.');
                }elseif($result->result == "ERROR")
                {
                    $reason = json_encode($result);
                    $response_array = array('status'=>false, 'message'=>$result->error->explanation);
                }elseif($result->result == "FAILURE")
                {
                    $reason =json_encode($result);
                    $response_array = array('status'=>false,'message'=>$result->response->gatewayCode);
                }else
                {
                    $reason = json_encode($result);
                    $response_array = array('status'=>true,'message'=>'Paid successfully.');
                }

                 try
                {
                     $credimax_transaction_data = DB::table('credimax_transaction_log')->insert(['user_id'=>$user_id, 'amount'=>$amount,'reason'=>$reason]);
                }catch(\Exception $e)
                {
                    Log::info('Credimax Log insert error');
                }
            
        }

          $response = response()->json($response_array, 200);
          return $response;

    }

    public function create_credimax_session(Request $request)
    {
        $validator = Validator::make(
                $request->all(),
                array(
                    'transaction_id' => 'required',
                    'amount' => 'required',
                    'currency' => 'required',
                    'return_url' =>'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {

            if($request->header('authId')!="")
            {
                $user_id = $request->header('authId');
            }else
            {
                $user_id = $request->authId;
            }

            $amount = round($request->amount,2);
            $amount = number_format($amount, 3);

                $post['apiOperation'] = 'CREATE_CHECKOUT_SESSION';
            $post['interaction']['returnUrl'] = $request->return_url;
            $post['interaction']['operation'] = 'PURCHASE';
            $post['order']['id'] = $request->transaction_id;
            $post['order']['amount'] = $amount;
            $post['order']['currency'] = $request->currency;
            $postjson = json_encode($post,JSON_UNESCAPED_SLASHES);
            //echo $postjson.'<br>';
            
            $url = "https://credimax.gateway.mastercard.com/api/rest/version/56/merchant/E11742950/session";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "merchant.E11742950:3d64693c62a08580a5780f28a94ca2ff");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postjson),
                'Authorization: Basic '. base64_encode("merchant.E11742950:3d64693c62a08580a5780f28a94ca2ff")
                )
            );
            $result=curl_exec ($ch);
            curl_close ($ch);
            if(isset($result))
            {
                $store_result = json_decode($result); 
                if($store_result->result == "SUCCESS")
                {
                    DB::table('credimax_transaction_log')->insert(['user_id'=>$user_id,'transaction_id'=>$request->transaction_id,'currency'=>$request->currency, 'amount'=>$request->amount,'reason'=>$result]);
                }
            }
            
             $response_array = array('status' => true, 'result' => json_decode($result,true));
        }
        $response = response()->json($response_array, 200);
          return $response;
    }

     public function create_credimax_session_mobile(Request $request)
    {
        $validator = Validator::make(
                $request->all(),
                array(
                    'transaction_id' => 'required',
                    'amount' => 'required',
                    'currency' => 'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {

            if($request->header('authId')!="")
            {
                $user_id = $request->header('authId');
            }else
            {
                $user_id = $request->authId;
            }

            $amount = round($request->amount,2);
            $amount = number_format($amount, 3);

            $return_url = "http://64.225.17.45/notlob/api/update_credimax_payment_status/".$request->transaction_id;

                $post['apiOperation'] = 'CREATE_CHECKOUT_SESSION';
            $post['interaction']['returnUrl'] = $return_url;
            $post['interaction']['operation'] = 'PURCHASE';
            $post['order']['id'] = $request->transaction_id;
            $post['order']['amount'] = $amount;
            $post['order']['currency'] = $request->currency;
            $postjson = json_encode($post,JSON_UNESCAPED_SLASHES);
            //echo $postjson.'<br>';
            
            $url = "https://credimax.gateway.mastercard.com/api/rest/version/56/merchant/E11742950/session";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, "merchant.E11742950:3d64693c62a08580a5780f28a94ca2ff");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postjson),
                'Authorization: Basic '. base64_encode("merchant.E11742950:3d64693c62a08580a5780f28a94ca2ff")
                )
            );
            $result=curl_exec ($ch);
            curl_close ($ch);
            if(isset($result))
            {
                $store_result = json_decode($result); 
                if($store_result->result == "SUCCESS")
                {
                    DB::table('credimax_transaction_log')->insert(['user_id'=>$user_id,'transaction_id'=>$request->transaction_id,'currency'=>$request->currency, 'amount'=>$request->amount,'reason'=>$result]);
                }
            }
            
             $response_array = array('status' => true, 'result' => json_decode($result,true));
        }
        $response = response()->json($response_array, 200);
          return $response;
    }

    public function credimax_gateway_redirect_mobile(Request $request)
    {
        if($request->header('authId')!="")
        {
            $user_id = $request->header('authId');
        }else
        {
            $user_id = $request->authId;
        }
        $sessionid = $request->session_id;
        $total = $request->bill_amount;
        $transactionid = $request->transaction_id;
        $delivery_address_detail = $this->deliveryaddress->where('user_id',$user_id)->where('is_default',1)->first();

        if(isset($delivery_address_detail))
        {
            $block_number=$delivery_address_detail->block_number;
            $address = $delivery_address_detail->address;
        }else
        {
            $block_number = "Block 317";
            $address = "Block 317";
        }

        return view('credimax_pay')->with('sessionid',$sessionid)->with('total',$total)->with('block_number',$block_number)->with('address',$address)->with('transactionid',$transactionid);
    }

    public function update_credimax_payment_status($transaction_id,Request $request)
    {
        $data = DB::table('credimax_check_log')->where('transaction_id',$transaction_id)->get();

        if(count($data)==0)
        {
            DB::table('credimax_check_log')->insert(['transaction_id'=>$transaction_id, 'request_data'=>$request,'result_indicator'=>$request->resultIndicator]);
        
            if(!empty($request->resultIndicator))
            {
                DB::table('credimax_transaction_log')->where('transaction_id',$transaction_id)->update(['payment_status_mobile'=>1]);
            }else
            {
                DB::table('credimax_transaction_log')->where('transaction_id',$transaction_id)->update(['payment_status_mobile'=>2]);
            }

        }       
        
        return 1;
    }

    public function validate_credimax_transaction(Request $request)
    {
         $validator = Validator::make(
                $request->all(),
                array(
                    'transaction_id' => 'required',
                    'amount' => 'required',
                    'currency' => 'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                if($request->header('authId')!="")
                {
                    $user_id = $request->header('authId');
                }else
                {
                    $user_id = $request->authId;
                }

                $data = DB::table('credimax_transaction_log')->where('user_id',$user_id)->where('transaction_id',$request->transaction_id)
                ->where('currency',$request->currency)->first();
                Log::info('validate_credimax_transaction_api for user id :'.$user_id);
                if($data)
                {
                    Log::info('validate_credimax_transaction_api transaction completed for user id :'.$user_id.'with data->amount '.$data->amount.' with request->amount '.$request->amount);
                    if($data->amount == $request->amount)
                    {
                         $response_array = array('status' => true, 'message' => "Valid Transaction",'paid_status_mobile'=>$data->payment_status_mobile);
                    }else
                    {
                        $response_array = array('status' => false, 'message' => "Invalid Transaction",'paid_status_mobile'=>$data->payment_status_mobile);
                    }
                }else
                {
                    $response_array = array('status' => false, 'message' => "Invalid Transaction");
                }
            }

        $response = response()->json($response_array, 200);
          return $response;
    }

    public function validate_benifit_transaction(Request $request)
    {
        $validator = Validator::make(
                $request->all(),
                array(
                    'transaction_id' => 'required',
                    'user_id' => 'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                if($request->amount)
                {
                $data = DB::table('benefit')->where('UDF2',$request->user_id)->where('UDF3',$request->transaction_id)->where('UDF4','like','%'.$request->amount.'%')->first();
                }else
                {
                    $data = DB::table('benefit')->where('UDF2',$request->user_id)->where('UDF3',$request->transaction_id)->first();
                }

                if($data)
                {
                    $current_time = date("Y-m-d H:i:s");
                    $current_time = strtotime($current_time);
                    $transaction_time = strtotime($data->created_at);
                    $diff = round(abs($current_time - $transaction_time) / 60);

                    Log::info('Validate transaction api for user id'.$request->user_id.' diff '.$diff);

                    // To check whether transaction is done within 3 minutes from current time
                    if($diff<3)
                    {
                        $response_array = array('status' => true, 'message' => "Valid Transaction");
                    }else
                    {
                        $response_array = array('status' => false, 'message' => "Invalid Transaction");
                    } 
                   
                }else
                {
                    $response_array = array('status' => false, 'message' => "Invalid Transaction");
                }
            }

        $response = response()->json($response_array, 200);
          return $response;
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function set_default_address(SetDefaultAddressRequest $request)
    {
        try {
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }
            $id = isset($request->id)?$request->id:"";
            $this->deliveryaddress->where('user_id', $user_id)->update(['is_default' => 0]);
            $this->deliveryaddress->where('user_id', $user_id)->where('id', $id)->update(['is_default' => 1]);
            $response_array = array('status' => true, 'message' => "Updated as a default address", 'default_address_id' => $id);
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */

    public function addCurrentAddress(Request $request)
    {
        try {
            if($request->header('authId') != "") {
                $userId = $request->header('authId');
            }else {
                $userId = $request->authId;
            }
            $addressId = $request->id;
            $addressArea = $request->address_area;
            $addressTitle = $request->address_title;
            $city = $request->city;
            $currentAddress = $request->current_address;
            $isFromSaved = $request->is_from_saved;
            $lat = $request->lat;
            $lng = $request->lng;

            $existingCurrentAddress = CurrentAddress::where('user_id',$userId)->first();
            if($existingCurrentAddress) {
                CurrentAddress::where('user_id',$userId)->update(['address_id' => $addressId ,'address_area' => $addressArea , 'address_title' => $addressTitle , 
                'city' => $city , 'current_address' => $currentAddress , 'is_from_saved' => $isFromSaved , 'lat' => $lat , 'lng' => $lng]);

                $response_array = array('status' => true, 'message' => "Address Updated Successfully");
            }else {
                $newCurrentAddress = new CurrentAddress();
                $newCurrentAddress->user_id = $userId;
                $newCurrentAddress->address_id = $addressId;
                $newCurrentAddress->address_area = $addressArea;
                $newCurrentAddress->address_title = $addressTitle;
                $newCurrentAddress->city = $city;
                $newCurrentAddress->current_address = $currentAddress;
                $newCurrentAddress->is_from_saved = $isFromSaved;
                $newCurrentAddress->lat = $lat;
                $newCurrentAddress->lng = $lng;
                $newCurrentAddress->save();

                $response_array = array('status' => true, 'message' => "Address Added Successfully");
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */

    public function getCurrentAddress(Request $request)
    {
        try {
            if($request->header('authId') != "") {
                $userId = $request->header('authId');
            }else {
                $userId = $request->authId;
            }

            $currentAddress = CurrentAddress::where('user_id',$userId)->first();
            if($currentAddress) {
                $response_array = array('status' => true, 'data' => $currentAddress);
            }else {
                $response_array = array('status' => false, 'message' => "No Address Available");
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

     public function ccavenuePayment(Request $request) {    
        try {
            $fields = array(
                'access_code' => config('app.access_code'),
                'order_id' => $request->order_id
            );
            $postvars = '';
            $sep = '';
            foreach ($fields as $key => $value) {
                $postvars .= $sep . urlencode($key) . '=' . urlencode($value);
                $sep = '&';
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, config('app.ccavenue_url'));
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_CAINFO, asset('cacert.pem'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);

            return $result;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
}