<?php

namespace App\Http\Controllers\api;
                                    
use App\Service\MultiOrderAssign;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use DB;
use Hash;
use App\Model\Requestdetail;
use App\Model\RequestdetailAddons;
use App\Model\Deliverypartners;
use App\Model\CurrentRequest;
use App\Model\CurrentAddress;
use App\Model\Restaurants;
use App\Model\AvailableProviders;
use App\Model\CCAvenueTransaction;
use App\Model\Refund;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Log;
use Mail;
use App\Library\Payment;
use App\Model\Cards;
use App\Model\Transactions;
use App\Model\Zone_geofencing;
use App\Model\DeliveryInstruction;
use App\Model\ViewCurrentAvailableFoods;
use App\Jobs\FoodPreparationJob;
use App\Service\CCaveEncrypt;
use App\Library\pointLocation;
use App\Base\Helpers\ExceptionHandlerModel;
use App\Base\Helpers\HelperTools;

class RestaurantController extends BaseController
{
   
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function single_restaurant(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'veg_only' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $restaurant_id = $request->restaurant_id;
                $veg_only = $request->veg_only;
                $restaurants = $this->restaurants;
                $foodlist = $this->foodlist;
                $menu = $this->menu;
                $cart = $this->cart;
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $lang = isset($request->lang)?$request->lang:'en';
                $data = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('id', $restaurant_id)->where('status', 1)->first();

                if (!$data) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.restaurant_not_available', $lang));
                    return response()->json($response_array, 200);
                }

                $food_filter = $foodlist->with(['Category', 'Choice_category'])->where('restaurant_id', $restaurant_id)->where('status', 1)->get();

                $restaurant_list = array();
                $rcuisines = array();
                $i = 0;
                foreach ($data->Cuisines as $r_cuisines) {
                    if ($i < 3) // To display only two cuisines
                    {
                        $rcuisines[] = array(
                            'name' => $r_cuisines->name,
                            'name_ar' => $r_cuisines->name_ar,
                            'name_kur' => $r_cuisines->name_kur
                        );
                        $i = $i + 1;
                    }
                }

                $foods = array();
                $j = 0;
                foreach ($food_filter as $f) {
                    $cart_count = $cart::where('user_id', $user_id)->where('food_id', $f->id)->first();
                    // For Cart item quantity

                    if ($cart_count) {
                        $count = $cart_count->quantity;
                    } else {
                        $count = 0;
                    }
                    //check food offer
                    $food_offer = $this->food_offer($f);

                    if ($j < 4) {
                        if($veg_only == 0) {
                            $foods[] = array(
                                'food_id' => $f->id,
                                'name' => $f->name,
                                'name_ar' => $f->name_ar,
                                'name_kur' => $f->name_kur,
                                'food_image' => (!empty($f->image)) ? SPACES_BASE_URL . $f->image : "",
                                'price' => $f->price,
                                'description' => $f->description,
                                'item_description' => $f->description,
                                'is_veg' => $f->is_veg,
                                'category' => $f->Category,
                                'choice_category' => $f->Choice_category,
                                'item_count' => $count,
                                'food_offer' => $food_offer,
                                'discount_type' => $f->discount_type ? $f->discount_type : "0",
                                'target_amount' => $f->target_amount ? $f->target_amount : "0",
                                'offer_amount' => (int)($f->offer_amount ? $f->offer_amount : "0"),
                            );
                            $j = $j + 1;
                        }else {
                            if ($f->is_veg == 1) {
                                $foods[] = array(
                                    'food_id' => $f->id,
                                    'name' => $f->name,
                                    'name_ar' => $f->name_ar,
                                    'name_kur' => $f->name_kur,
                                    'food_image' => (!empty($f->image)) ? SPACES_BASE_URL . $f->image : "",
                                    'price' => $f->price,
                                    'description' => $f->description,
                                    'item_description' => $f->description,
                                    'is_veg' => $f->is_veg,
                                    'category' => $f->Category,
                                    'choice_category' => $f->Choice_category,
                                    'item_count' => $count,
                                    'food_offer' => $food_offer,
                                    'discount_type' => $f->discount_type ? $f->discount_type : "0",
                                    'target_amount' => $f->target_amount ? $f->target_amount : "0",
                                    'offer_amount' => (int)($f->offer_amount ? $f->offer_amount : "0"),
                                );
                                $j = $j + 1;
                            }
                        }
                    }
                }

                $check_favourite = $this->favouritelist->where('user_id', $user_id)->where('restaurant_id', $data->id)->get();
                if (count($check_favourite) != 0) {
                    $is_favourite = 1;
                } else {
                    $is_favourite = 0;
                }
                //calculate restaurant open time
                $is_open = $this->check_restaurant_open($data);

                //check restaurant offer
                $restaurant_offer = "";
                if ($data->offer_amount != '' && $data->offer_amount != 0) {
                    if ($data->discount_type == 1) {
                        $restaurant_offer = "Flat offer " . DEFAULT_CURRENCY_SYMBOL . " " . $data->offer_amount;
                    } else {
                        $restaurant_offer = $data->offer_amount . "% offer";
                    }
                    if ($data->target_amount != 0) {
                        $restaurant_offer = $restaurant_offer . " on orders above " . DEFAULT_CURRENCY_SYMBOL . " " . $data->target_amount;
                    }
                }
                if ($data->parent == 0) {
                    $check_offer_for_restaurant = $this->banner->where('status', 1)->where('restaurant_id', $data->id)->get();
                } else {
                    $check_offer_for_restaurant = $this->banner->where('status', 1)->where('restaurant_id', $data->parent)->get();
                }

                // $check_offer_for_restaurant = DB::table('offers_banner')->where('status',1)->where('restaurant_id',$data->id)->get();
                if (count($check_offer_for_restaurant) != 0) {
                    $is_food_offer_exist = 1;
                } else {
                    $is_food_offer_exist = 0;
                }

                $minutes = $this->get_travel_time($request->lat , $request->lng , $data->lat , $data->lng);
                $str = strstr($data->estimated_food_preparation_time, '-');

                if($str != false) {
                    $data->estimated_food_preparation_time = trim(substr($str, 1));
                }

                $travel_time = $data->estimated_food_preparation_time + $minutes + 5;
                $data->total_delivery_time = round($travel_time);
                
                $rating = $data->rating;

                // To calculate delivery charge based on distance
                if (isset($request->lat) && isset($request->lng)) {
                    $delivery_distance = $this->calculate_distance($data->lat, $data->lng, $request->lat, $request->lng);
                    $get_delivery_charge = DB::table('restaurant_delivery_charges')->where('restaurant_id', $restaurant_id)->where('min_distance', '<', $delivery_distance)->where('max_distance', '>=', $delivery_distance)->first();
                    $check_admin_charge = $data->delivery_charge_status;
                    if ($get_delivery_charge && $check_admin_charge == 1) {
                        $data->restaurant_delivery_charge = $get_delivery_charge->delivery_charge;
                    }else{
                        $data->restaurant_delivery_charge = 0;
                    }

                }
                $restaurantDetail = $restaurants::where('id',$restaurant_id)->first();
                if($restaurantDetail->app_color == Null)
                {
                    $restaurantDetail->app_color = '#24336a';
                    $restaurants::where('id',$restaurant_id)->update(['app_color' => $restaurantDetail->app_color]);
                }
                if($restaurantDetail->secondary_color == Null)
                {
                    $restaurantDetail->secondary_color = '#24336a';
                    $restaurants::where('id',$restaurant_id)->update(['secondary_color' => $restaurantDetail->secondary_color]);
                }
                $restaurant_list[] = array(
                    'id' => $data->id,
                    'name' => ucwords(strtolower($data->restaurant_name)),
                    'name_ar' => $data->restaurant_name_ar,
                    'name_kur' => $data->restaurant_name_kur,
                    'image' => SPACES_BASE_URL . $data->image,
                    'address' => $data->address,
                    'discount' => $data->discount,
                    'rating' => round($rating, 1),
                    'is_open' => $is_open,     // 1- Open , 0 - Close
                    'is_busy' => $data->is_busy,
                    'cuisines' => $rcuisines,
                    'travel_time' => $data->total_delivery_time,
                    'price' => $restaurant_offer,
                    'discount_type' => $data->discount_type ? $data->discount_type : "0",
                    'target_amount' => $data->target_amount ? $data->target_amount : "0",
                    'offer_amount' => (int)($data->offer_amount ? $data->offer_amount : "0"),
                    'offer_type'=> $data->offer_type,
                    'offer_value'=> $data->offer_value,
                    'is_food_offer_exist' => $is_food_offer_exist,
                    'is_favourite' => $is_favourite,
                    'delivery_type' => $data->delivery_type,
                    'shop_description' => $data->shop_description,
                    'fssai_license' => $data->fssai_license,
                    'food_list' => $foods,
                    'min_order_value' => $data->min_order_value,
                    'restaurant_delivery_charge' => (float)$data->restaurant_delivery_charge,
                    'restaurant_delivery_charge_ios' => $data->restaurant_delivery_charge ? $data->restaurant_delivery_charge : "0",
                    'restaurant_timing' => $data->RestaurantTimer,
                    'app_color' => $restaurantDetail->app_color,
                    'secondary_color' => $restaurantDetail->secondary_color,
                    'banners' => !empty($restaurantDetail->banners)?json_decode($restaurantDetail->banners):[],
                );

                $food_cart = array();

                $check_for_cart = $cart::where('user_id', $user_id)->get();
                $amount = 0;
                $quantity = 0;
                foreach ($check_for_cart as $key) {
                    $food_detail = $foodlist::where('id', $key->food_id)->first();
                    $amount = $amount + ($food_detail->price * $key->quantity);
                    $quantity = $quantity + $key->quantity;
                }

                $food_cart[] = array(
                    'amount' => $amount,
                    'quantity' => $quantity,
                );

                $response_array = array('status' => true, 'restaurants' => $restaurant_list, 'cart' => $food_cart);
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
    public function add_to_cart(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'food_id' => 'required',
                    'quantity' => 'required',
                    'restaurant_id' => 'required',
                    'force_insert' => 'required'  // To Overwrite previous restaurant cart if exist - 1
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $cart = $this->cart;
                $foodlist = $this->foodlist;
                $restaurants = $this->restaurants;
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }

                $food_id = $request->food_id;
                $quantity = $request->quantity;
                $force_insert = $request->force_insert;
                $restaurant_id = $request->restaurant_id;

                $check = $cart::where('user_id', $user_id)->where('food_id', $food_id)->first();

                if ($force_insert == 0) {
                    if ($check) {
                        $cart::where('id', $check->id)->update(['quantity' => $quantity]);
                    } else {
                        $last_data = $cart::where('user_id', $user_id)->first();
                        if ($last_data) {
                            $check_restaurant = $foodlist::where('id', $last_data->food_id)->first();

                            if ($check_restaurant->restaurant_id == $restaurant_id) {
                                $is_same_restaurant = 1;
                            } else {
                                $existing_restaurant = $restaurants::where('id', $check_restaurant->restaurant_id)->first();
                                $new_restaurant = $restaurants::where('id', $restaurant_id)->first();

                                $message = 'Your cart contains dishes from ' . $existing_restaurant->restaurant_name . ' . Do you want to discard the selection and add dishes from ' . $new_restaurant->restaurant_name . ' ?';

                                $response_array = array('status' => false, 'error_code' => 102, 'message' => $message);
                                return response()->json($response_array, 200);
                            }
                        }

                        $insert_data = array();
                        $insert_data[] = array(
                            'user_id' => $user_id,
                            'food_id' => $food_id,
                            'quantity' => $quantity
                        );

                        $cart::insert($insert_data);
                    }
                } else {
                    $cart::where('user_id', $user_id)->delete();

                    $insert_data = array();
                    $insert_data[] = array(
                        'user_id' => $user_id,
                        'food_id' => $food_id,
                        'quantity' => $quantity
                    );

                    $cart::insert($insert_data);
                }

                $response_array = array('status' => true, 'message' => 'Item quantity added to cart');
            }

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function reduce_from_cart(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'food_id' => 'required',
                    'quantity' => 'required'
                ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                $cart = $this->cart;
                if($request->header('authId')!="")
                {
                    $user_id = $request->header('authId');
                }else
                {
                    $user_id = $request->authId;
                }
                // $user_id = $request->header('authId');
                $food_id = $request->food_id;
                $quantity = $request->quantity;

                $check = $cart::where('user_id',$user_id)->where('food_id',$food_id)->first();
                if($check && $quantity!=0)
                {
                    $cart::where('id',$check->id)->update(['quantity'=>$quantity]);
                }elseif($check && $quantity==0)
                {

                    $cart::where('id',$check->id)->delete();                
                }
                $response_array = array('status'=>true,'message'=>'Item quantity removed from cart');
            }
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
    public function check_cart(Request $request)
    {
        try {
            $cart = $this->cart;
            $foodlist = $this->foodlist;
            if ($request->header('authId') != "") {
                $user_id = $request->header('authId');
            } else {
                $user_id = $request->authId;
            }

            $user_table = $this->users->where('id', $user_id)->first();
            $restaurants = $this->restaurants;

            $food_cart = array();

            // if($user_table->device_type!=WEB)
            // {

            //         $check_for_cart = $cart::where('user_id',$user_id)->get();
            //         $amount = 0;
            //         $quantity = 0;
            //         foreach ($check_for_cart as $key) {

            //           $food_detail = $foodlist::where('id',$key->food_id)->first();

            //             $quantity = $quantity + $key->quantity;
            //             $amount = $amount+($food_detail->price* $key->quantity);
            //         }

            //          $food_cart[] = array(
            //                 'amount'     => $amount,
            //                 'quantity'   => $quantity,
            //             );

            //          $response_array = array('status'=>true,'cart'=>$food_cart);
            // }else
            // {

            $check_for_cart = $cart::where('user_id', $user_id)->get();
            $amount = 0;
            $quantity = 0;
            $item_list = array();
            foreach ($check_for_cart as $key) {

                $food_detail = $foodlist::where('id', $key->food_id)->where('status', 1)->first();

                $quantity = $quantity + $key->quantity;
                $amount = $amount + ($food_detail->price * $key->quantity);
                $item_list[] = array(
                    'item_id' => $key->food_id,
                    'item_name' => $food_detail->name,
                    'quantity' => $key->quantity,
                    'price' => $key->quantity * $food_detail->price
                );

                $restaurant_id = $food_detail->restaurant_id;
                $order_on = $key->created_at;
            }

            if (isset($restaurant_id)) {
                $restaurant_detail = $restaurants::where('id', $restaurant_id)->where('status', 1)->first();
                $restaurant_name = $restaurant_detail->restaurant_name;
                $restaurant_image = $restaurant_detail->image;
                $order_on = $order_on;
                $restaurant_address = $restaurant_detail->address;
            } else {
                $restaurant_name = "";
                $restaurant_image = "";
                $order_on = "";
                $restaurant_address = "";
            }

            $food_cart[] = array(
                'item_list' => $item_list,
                'amount' => $amount,
                'quantity' => $quantity,
                'restaurant_name' => $restaurant_name,
                'restaurant_image' => $restaurant_image,
                'order_on' => $order_on,
                'restaurant_address' => $restaurant_address
            );

            $response_array = array('status' => true, 'cart' => $food_cart);

            // }

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function get_category($restaurant_id, Request $request)
    {
        try {
            $foodlist = $this->foodlist;
            $category = $this->category;
            $menu = $this->menu;

            $category_list = $foodlist->where('restaurant_id', $restaurant_id)->where('status', 1)
                ->groupBy('category_id')
                ->pluck('category_id');

            $data = $category->where('restaurant_id', $restaurant_id)->where('status', 1)->get();

            if (count($data) != 0) {

                $food_category = array();
                $i = 1;
                foreach ($data as $d) {
                    $food_category[] = array(
                        'category_id' => $d->id,
                        'name' => $d->category_name,
                        'name_ar' => $d->category_name_ar,
                        'name_kur' => $d->category_name_kur,
                        'position' => $i
                    );

                    $i = $i + 1;
                }

                $response_array = array('status' => true, 'category' => $food_category);
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
    public function get_food_list(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'is_veg' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:'en';
                $restaurant_id = $request->restaurant_id;
                $restaurant_detail = $this->restaurants->where('id', $restaurant_id)->where('status', 1)->select('restaurant_name', 'image', 'address', 'discount_type', 'target_amount', 'offer_amount', 'tax', 'min_order_value','packaging_charge_enable_value')->first();

                if (!$restaurant_detail) {
                    $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.restaurant_closed', $lang));
                    return response()->json($response_array, 200);
                }
                $getFoodListwiseCategory = DB::select("call get_food_list_json('$restaurant_id')");
                $foodListwiseCategory  = json_decode($getFoodListwiseCategory[0]->food_list_json, true, JSON_UNESCAPED_SLASHES);           
                $response_array = array('status' => true, 'food_list' => $foodListwiseCategory,'image_base_url' => SPACES_BASE_URL,  'restaurant_detail' => $restaurant_detail);
                return response()->json($response_array, 200);
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
    public function get_category_wise_food_list(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'category_id' => 'required',
                    'veg_only' => 'required'
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $restaurant_id = $request->restaurant_id;
                $category_id = $request->category_id;
                $foodlist = $this->foodlist;
                $cart = $this->cart;
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }

                $veg_only = $request->veg_only;

                $food_list = $foodlist::where('restaurant_id', $restaurant_id)->where('category_id', $category_id)->where('status', 1)->get();

                $check_food_for_null = 0;
                if (count($food_list) != 0) {
                    foreach ($food_list as $f) {
                        $cart_count = $cart::where('user_id', $user_id)->where('food_id', $f->id)->first();  // For Cart item quantity
                        if ($cart_count) {
                            $count = $cart_count->quantity;
                        } else {
                            $count = 0;
                        }
                        if ($veg_only == 0) {
                            $check_food_for_null += 1;
                            $foods[] = array(
                                'food_id' => $f->id,
                                'name' => $f->name,
                                'name_ar' => $f->name_ar,
                                'name_kur' => $f->name_kur,
                                'price' => $f->price,
                                'description' => $f->description,
                                'is_veg' => $f->is_veg,
                                'item_count' => $count,
                            );

                        } else {
                            if ($f->is_veg == 1) {
                                $check_food_for_null += 1;

                                $foods[] = array(
                                    'food_id' => $f->id,
                                    'name' => $f->name,
                                    'name_ar' => $f->name_ar,
                                    'name_kur' => $f->name_kur,
                                    'price' => $f->price,
                                    'description' => $f->description,
                                    'is_veg' => $f->is_veg,
                                    'item_count' => $count,
                                );
                            }
                        }
                    }

                    $check_favourite = $this->favouritelist->where('user_id', $user_id)->where('restaurant_id', $restaurant_id)->get();

                    if (count($check_favourite) != 0) {
                        $is_favourite = 1;
                    } else {
                        $is_favourite = 0;
                    }

                    if ($check_food_for_null != 0) {
                        $response_array = array('status' => true, 'food_list' => $foods, 'is_favourite' => $is_favourite);
                    } else {
                        $response_array = array('status' => false, 'message' => 'No Data Found');
                    }
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

    public function add_to_cart_log(Request $request)
    {
        try {
            if($request->header('authId')!="")
            {
                $user_id = $request->header('authId');
            }else
            {
                $user_id = $request->authId;
            }

            $check_cart = DB::table('cart_log')->where('user_id',$user_id)->orderBy('id','desc')->first();

            $cart_detail =  json_encode($request->all());

            if($check_cart)
            {
                DB::table('cart_log')->where('user_id',$user_id)->update(['request_params'=>$cart_detail,'is_order_created'=>0]);
            }else
            {
                DB::table('cart_log')->insert(['user_id'=>$user_id,'request_params'=>$cart_detail]);
            }

            $response = response()->json(array('status'=>true,'message'=>'Cart updated'), 200);
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
    public function checkout(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'bill_amount' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                    'food_id' => 'required',
                    'food_qty' => 'required'
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
                $restaurant_discount = isset($request->offer_amount) ? $request->offer_amount : 0;
                $coupon_code_discount = isset($request->coupon_code_discount) ? $request->coupon_code_discount : 0;
                $lang = isset($request->lang)?$request->lang:'en';
                if ($user_id == 2492 || $user_id == 3273 || $user_id == 4615) {
                    return response()->json(array('status' => false, 'message' => $this->language_string_translation('constants.contact_admin_to_process_the_order',$lang)), 200);
                }
                $restaurants = $this->restaurants;
                $restaurant = $restaurants::with('RestaurantTimer')->where('id', $request->restaurant_id)->where('status', 1)->first();

                //calculate restaurant open time
                $is_open = $this->check_restaurant_open($restaurant);

                if ($is_open != 1) {
                    return response()->json(array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.restaurant_not_available',$lang)), 200);
                }

                $check_already_exist_checkout = $this->users_checkout_restaurant->where('user_id', $user_id)->get();

                if (count($check_already_exist_checkout) == 0) {
                    $this->users_checkout_restaurant->insert(['user_id' => $user_id, 'restaurant_id' => $request->restaurant_id]);
                } else {
                    $this->users_checkout_restaurant->where('user_id', $user_id)->update(['restaurant_id' => $request->restaurant_id]);
                }

                if ($restaurant->is_busy == 1) {
                    return response()->json(array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.restaurant_not_available',$lang)), 200);
                }

                if (empty($restaurant)) {
                    return response()->json(array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.restaurant_not_available',$lang)), 200);
                }

                $restaurant_detail = array();

                $restaurant_detail[] = array(
                    'restaurant_id' => $restaurant->id,
                    'name' => $restaurant->restaurant_name,
                    'name_ar' => $restaurant->restaurant_name_ar,
                    'name_kur' => $restaurant->restaurant_name_kur,
                    'image' => SPACES_BASE_URL . $restaurant->image,
                    'address' => $restaurant->address,
                    'delivery_type' => $restaurant->delivery_type,
                    // 'estimated_delivery_time' => $restaurant->estimated_delivery_time,
                    'weekday_opening_time' => $restaurant->opening_time,
                    'weekday_closing_time' => $restaurant->closing_time,
                    'weekend_opening_time' => $restaurant->weekend_opening_time,
                    'weekend_closing_time' => $restaurant->weekend_closing_time,
                    'max_dining_count' => $restaurant->max_dining_count,
                    'restaurant_timing' => $restaurant->RestaurantTimer,
                    'min_order_value' => $restaurant->min_order_value ? $restaurant->min_order_value : 0
                );

                // FOR COUPON CODE
                if ($request->coupon_code != "") {
                    $get_restaurant_offer = DB::table('restaurant_coupon_code')->where('code', $request->coupon_code)->first();
                    $get_offer = !empty($get_restaurant_offer)?$get_restaurant_offer:DB::table('sub_admin_coupon_code')->where('code', $request->coupon_code)->first();
                    $get_offer = !empty($get_offer)?$get_offer:$this->promocode->where('code', $request->coupon_code)->first();
                    if ($get_offer) {
                        $coupon_code = $request->coupon_code;
                        $offer_type = $get_offer->offer_type;
                        if ($offer_type == 0) // For % offer
                        {
                            $offer = $get_offer->value;
                            $coupon_discount = $offer;
                        } else {
                            // For price offer
                            $coupon_discount = $get_offer->value;
                        }
                    } else {
                        return response()->json(array('status' => false, 'message' => $this->language_string_translation('constants.invalid_coupon',$lang)), 200);
                    }
                } else {
                    $coupon_code = "NA";
                    $coupon_discount = 0;
                }

                $restaurant_packaging_charge = $restaurant->packaging_charge;
                $user_detail = $this->users->where('id', $user_id)->first();

                if (empty($user_detail)) {
                    return response()->json(array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.restaurant_not_available',$lang)), 200);
                }

                if ($user_detail->device_type != WEB) {
                    // $data = file_get_contents(FIREBASE_URL . "/current_address/$user_id.json");
                    // $data = json_decode($data);
                    // $url = FIREBASE_URL . "/current_address/$user_id.json";
                    //  $curl = curl_init();
                
                    // curl_setopt($curl, CURLOPT_URL, $url);
                    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    // curl_setopt($curl, CURLOPT_HEADER, false);

                    // // execute and return string (this should be an empty string '')
                    // $result = curl_exec($curl);

                    // curl_close($curl);

                    // $data = json_decode($result);
                    // Log::info('checkout api :'.$result);
                    $currentAddress = CurrentAddress::where('user_id',$user_id)->first();

                    $d_lat = isset($currentAddress->lat) ? $currentAddress->lat : "";
                    $d_lng = isset($currentAddress->lng) ? $currentAddress->lng : "";
                    $city = isset($currentAddress->city) ? $currentAddress->city : "Erbil";
                } else {
                    $delivery_address_detail = $this->deliveryaddress->where('user_id', $user_id)->where('is_default', 1)->first();
                    $d_lat = $request->d_lat ? $request->d_lat : $delivery_address_detail->lat;
                    $d_lng = $request->d_lng ? $request->d_lng : $delivery_address_detail->lng;
                    $city = isset($data->city) ? $data->city : "Erbil";
                }

                $restaurant->default_delivery_amount = $restaurant->restaurant_delivery_charge;
                $delivery_charge = $restaurant->restaurant_delivery_charge;

                // To calculate delivery distance and it's charge
                $d_lat = (float)$d_lat;
                $d_lng = (float)$d_lng;
                $delivery_distance = $this->calculate_distance($restaurant->lat, $restaurant->lng, $d_lat, $d_lng);
                $get_delivery_charge = $this->restaurant_delivery_charges->where('restaurant_id', $request->restaurant_id)->where('min_distance', '<', $delivery_distance)->where('max_distance', '>=', $delivery_distance)->where('min_amount','<=',$request->bill_amount)->first();
                $check_admin_charge = $restaurant->delivery_charge_status;
                Log::info('checkout api - delivery distance '.$delivery_distance);
                Log::info('checkout api -get_delivery_charge '.$get_delivery_charge);
                if ($get_delivery_charge && $check_admin_charge == 1) {
                    $delivery_charge = $get_delivery_charge->delivery_charge;
                }

                $loyalty_discount = 0;
                $user_loyalty_points = $user_detail->loyalty_points;
                $total_amount = $request->bill_amount + $restaurant_packaging_charge + $delivery_charge;
                if ($user_loyalty_points >= MAXIMUM_LOYALTY_POINTS && $total_amount >= LOYALTY_AMOUNT) {
                    $loyalty_discount = LOYALTY_AMOUNT;
                }

                $suburb_city = json_decode($restaurant->city);
                $lat = $request->lat;
                $lng = $request->lng;
                $get_city = "";

                foreach($suburb_city as $suburb) {
                    $city = $this->addcity->where('add_city.id',$suburb)->select('add_city.*', 'city_geofencing.polygons')
                    ->leftJoin('city_geofencing', function ($join) {
                        $join->on('city_geofencing.city_id', '=', 'add_city.id');
                    })->first();
                    $polygon = json_decode($city->polygons);
                    $ponits = array($lng, $lat);
                    $is_avail = $this->contains($ponits, $polygon[0]);
                    if ($is_avail == 1) {
                        $get_city = $city;
                        $check_restaurant = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('city', 'like', '%"' . $city->id . '"%')->where('status', 1)->where('is_approved', 1)->count();
                        if ($check_restaurant != 0) {
                            break;
                        }
                    }else {
                        $get_city = $city;
                    }
                }


                // Get distance from Google map
                $deliveryLocation = $lat . ',' . $lng;
                $restaurantLocation = $restaurant->lat . ',' . $restaurant->lng;
                $distance = $this->getGoogleDistance($deliveryLocation, $restaurantLocation,1);
                $distance = round($distance[0]);


                // Delivery charge calculations
                $overall_delivery_charge = 0;
                if($distance != 0) {
                    // First 3 KM Charge
                    $delivery_charge_first_3_km = $get_city->delivery_charge_first_2_km;
                    if($distance > 3) {
                        $minus_3km_from_distance = $distance - 3;
                    }else {
                        $minus_3km_from_distance = 0;
                    }
                    $delivery_charge_remaining_each_km = $get_city->delivery_charge_remaining_each_km * $minus_3km_from_distance;
                    $overall_delivery_charge = $delivery_charge_first_3_km + $delivery_charge_remaining_each_km;
                }else {
                    $overall_delivery_charge = $get_city->delivery_charge_first_2_km;
                }

                // Item Total Amount
                $item_total = $request->bill_amount;
                
                // Restaurant Packing Charge
                $overall_restaurant_packing_charge = 0;
                if($restaurant->packaging_charge_enable_value == 1) {
                    $food_id = $request->food_id;
                    $food_qty = $request->food_qty;
                    if(count($food_id) != 0) {
                        $charge = [];
                        foreach($food_id as $key => $food) {
                            $get_food = $this->foodlist->where('id',$food)->select('packaging_charge')->first();
                            $food_charge = $get_food->packaging_charge * $food_qty[$key];
                            array_push($charge,$food_charge);
                        }
                        $charge = array_sum($charge);
                        $overall_restaurant_packing_charge = $charge;
                    }
                }

                //  Restaurant Commission         
                $all_discounts = $restaurant_discount + $coupon_code_discount;   
                $check_restaurant_commission = ($item_total - $all_discounts) + $overall_restaurant_packing_charge;
                $restaurant_commission  =  (NEW_RESTAURANT_COMMISSION / 100) * $check_restaurant_commission;

                // delivery instruction list
                $delivery_instruction = DeliveryInstruction::get();
                $delivery_instruction = (!empty($delivery_instruction) && count($delivery_instruction)!=0)?$delivery_instruction:[];
                $invoice = array();
                $invoice[] = array(
                    'offer_discount' => $coupon_discount,
                    'restaurant_packaging_charge' => $overall_restaurant_packing_charge,
                    'delivery_charge' => $overall_delivery_charge,
                    'restaurant_commission' => $restaurant_commission,
                    'item_total' => $item_total,
                    'coupon_code' => $coupon_code,
                    'loyalty_discount' => $loyalty_discount,                
                );
                $response_array = array('status' => true, 'restaurant_detail' => $restaurant_detail, 'invoice' => $invoice,'delivery_instruction' => $delivery_instruction,'image_base_url'=>SPACES_BASE_URL);
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
    public function paynow(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'restaurant_id' => 'required',
                    'item_total' => 'required',
                    'offer_discount' => 'required',
                    'restaurant_packaging_charge' => 'required',
                    'gst' => 'required',
                    'delivery_charge' => 'required',
                    'restaurant_commission' => 'required',
                    'bill_amount' => 'required',
                    'coupon_code' => 'required',
                    'food_id' => 'required',
                    'food_qty' => 'required',
                    'paid_type' => 'required',
                ));

            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                // count(Redis::keys('popular_brands*')) != 0 ? Redis::del(Redis::keys('popular_brands*')) : '';
                // count(Redis::keys('nearby_restaurant*')) != 0 ? Redis::del(Redis::keys('nearby_restaurant*')) : '';
                $foodrequest = $this->foodrequest;
                $cart = $this->cart;
                $trackorderstatus = $this->trackorderstatus;
                $settings = $this->settings;
                if ($request->header('authId') != "") {
                    $user_id = $request->header('authId');
                } else {
                    $user_id = $request->authId;
                }
                $restaurant_id = $request->restaurant_id;
                $item_total = $request->item_total;
                $offer_discount = $request->offer_discount;
                $restaurant_packaging_charge = $request->restaurant_packaging_charge;
                $restaurant_commission = $request->restaurant_commission;
                $gst = $request->gst;
                $delivery_charge = $request->delivery_charge;
                $bill_amount = $request->bill_amount;
                $coupon_code = $request->coupon_code;
                $delivery_type = isset($request->delivery_type) ? $request->delivery_type : 1;
                $restaurant_discount = isset($request->restaurant_discount) ? $request->restaurant_discount : 0;
                $coupon_code_discount = isset($request->coupon_code_discount) ? $request->coupon_code_discount : 0;
                $user_detail = $this->users::where('id', $user_id)->first();
                if ($request->device_type) {
                    $user_detail->device_type = $request->device_type; // For Web -  to handle if user logged in web first, then with mobile and the trying to create order in web
                    $device_type = $request->device_type;
                } else {
                    $device_type = "NULL";
                }
                if (isset($user_detail) && $user_detail->device_type == WEB) {
                    try {
                        $food_id = array();
                        $food_qty = $food_quantity = $food_quantity_price = array();
                        $food_ids = str_replace('"', '', (string)$request->food_id);
                        $food_id = explode(',', $food_ids);
                        $food_qtys = str_replace('"', '', (string)$request->food_qty);
                        $food_qty = explode(',', $food_qtys);
                        $food_quantitys = str_replace('"', '', (string)$request->food_quantity);
                        $food_quantity = explode(',', $food_quantitys);
                        $food_quantity_prices = str_replace('"', '', (string)$request->food_quantity_price);
                        $food_quantity_price = explode(',', $food_quantity_prices);
                    } catch (\Exception $e) {
                        $food_id = $request->food_id;
                        $food_qty = $request->food_qty;
                        $food_quantity = $request->food_quantity;
                        $food_quantity_price = $request->food_quantity_price;
                    }
                } else {
                    try {
                        $food_id = $request->food_id;
                        $food_qty = $request->food_qty;
                        $food_quantity = $request->food_quantity;
                        $food_quantity_price = $request->food_quantity_price;
                    } catch (\Exception $e) {
                        $food_id = array();
                        $food_qty = $food_quantity = $food_quantity_price = array();
                        $food_ids = str_replace('"', '', (string)$request->food_id);
                        $food_id = explode(',', $food_ids);
                        $food_qtys = str_replace('"', '', (string)$request->food_qty);
                        $food_qty = explode(',', $food_qtys);
                        $food_quantitys = str_replace('"', '', (string)$request->food_quantity);
                        $food_quantity = explode(',', $food_quantitys);
                        $food_quantity_prices = str_replace('"', '', (string)$request->food_quantity_price);
                        $food_quantity_price = explode(',', $food_quantity_prices);
                    }
                }
                $food_id_size = sizeof((array)$food_id);
                $food_qty_size = sizeof((array)$food_qty);
                $paid_type = $request->paid_type;

                if ($paid_type != 1) {
                    if ($paid_type == 4) {
                        $paid_type = 3;
                    }
                    $is_paid = 1;
                } else {
                    $is_paid = 0;
                }
                if ($request->paid_type == 2) {
                    $benefit_data = DB::table('benefit')->where('UDF2', $user_id)->where('is_order_created', 1)->orderBy('id', 'desc')->first();

                    if ($benefit_data)  {
                        if ($benefit_data->UDF4 != $bill_amount) {
                            $response_array = array('status' => false, 'message' => 'Order amount invalid');
                            $response = response()->json($response_array, 200);
                        }
                    }
                }
                if ($paid_type != 1) {
                    $check_cart = DB::table('cart_log')->where('user_id', $user_id)->orderBy('id', 'desc')->first();
                    if ($check_cart)  {
                        if ($check_cart->is_order_created == 0) {
                            $cart_id = $check_cart->id;
                            DB::table('cart_log')->where('user_id', $user_id)->update(['is_order_created' => 1]);
                        } else {
                            $cart_id = 0;
                        }
                    } else {
                        $cart_id = 0;
                    }
                } else {
                    $cart_id = 0;
                }
                if ($user_detail->device_type != WEB) {

                    // $data = file_get_contents(FIREBASE_URL . "/current_address/$user_id.json");
                    // $url = FIREBASE_URL . "/current_address/$user_id.json";

                    // $curl = curl_init();
                
                    // curl_setopt($curl, CURLOPT_URL, $url);
                    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    // curl_setopt($curl, CURLOPT_HEADER, false);

                    // // execute and return string (this should be an empty string '')
                    // $data = curl_exec($curl);

                    // curl_close($curl);
                    // $data = json_decode($data);
                    // Log::info('Paynow api body in address section for mobile user id ' . $user_id);
                    $currentAddress = CurrentAddress::where('user_id',(string)$user_id)->first();
                    $currentAddressId = isset($currentAddress->address_id) ? $currentAddress->address_id : 0;
                    $d_lat = isset($currentAddress->lat) ? $currentAddress->lat : "0.0";
                    $d_lng = isset($currentAddress->lng) ? $currentAddress->lng : "0.0";
                    $delivery_address = isset($currentAddress->current_address) ? $currentAddress->current_address : "";
                    $city = isset($currentAddress->city) ? $currentAddress->city : "";
                    $address_title = isset($currentAddress->address_title) ? $currentAddress->address_title : "";
                } else {
                    Log::info('Paynow api body in address section for web user id ' . $user_id);
                    $delivery_address_detail = $this->deliveryaddress::where('user_id', $user_id)->where('is_default', 1)->first();

                    $d_lat = $request->lat ? $request->lat : $delivery_address_detail->lat;
                    $d_lng = $request->lng ? $request->lng : $delivery_address_detail->lng;
                    $delivery_address = $request->address ? $request->address : $delivery_address_detail->address;
                    $city = isset($data->city) ? $data->city : "coimbatore";
                    $address_title = "Home";
                }
                $restaurant_details = $this->restaurants->find($restaurant_id);


                $getCityId = DB::table('add_city')
                        ->select('add_city.*', 'city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($restaurant_details->lat) ) *
                                                            cos( radians( city_geofencing.latitude ) )
                                                            * cos( radians( city_geofencing.longitude ) - radians($restaurant_details->lng)
                                                            ) + sin( radians($restaurant_details->lat) ) *
                                                            sin( radians( city_geofencing.latitude ) ) )
                                                        ) AS distance"))
                        ->leftJoin('city_geofencing', function ($join) {
                            $join->on('city_geofencing.city_id', '=', 'add_city.id');
                        })
                        ->orderBy('distance', 'asc')
                        ->get();
                    $getCityData = 0;
                    foreach ($getCityId as $value) {
                        $polygon = json_decode($value->polygons);
                        $ponits = array($restaurant_details->lng,$restaurant_details->lat);
                        $city_id = $value->id;
                        $is_avail = $this->contains($ponits, $polygon[0]);
                        if ($is_avail == 1) {
                            $getCityData = $value;
                            break;
                        }
                    }
                $cityCode = $this->state->where('id',$getCityData->state_id)->select('city_code')->first();




                $order_id = $this->generate_booking_id($cityCode);
                $city_id = $distance = 0;
                //get restaurant based commission
                // if (!empty($restaurant_details->admin_commision)) {
                //     $admin_commision_setting = $restaurant_details->admin_commision;
                // } else {
                //     $admin_commision_details = DB::table('add_city')
                //         ->select('add_city.*', 'city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($d_lat) ) *
                //                                             cos( radians( city_geofencing.latitude ) )
                //                                             * cos( radians( city_geofencing.longitude ) - radians($d_lng)
                //                                             ) + sin( radians($d_lat) ) *
                //                                             sin( radians( city_geofencing.latitude ) ) )
                //                                         ) AS distance"))
                //         ->leftJoin('city_geofencing', function ($join) {
                //             $join->on('city_geofencing.city_id', '=', 'add_city.id');
                //         })
                //         ->orderBy('distance', 'asc')
                //         // ->having("distance", "<", $radius)
                //         ->get();

                //     $admin_commision_setting = 0;
                //     $source = $d_lat . ',' . $d_lng;
                //     foreach ($admin_commision_details as $value) {
                //         $polygon = json_decode($value->polygons);
                //         $ponits = array($d_lng, $d_lat);
                //         $is_avail = $this->contains($ponits, $polygon[0]);
                //         if ($is_avail == 1) {
                //             $admin_commision_setting = $value->admin_commision;
                //             $city_id = $value->id;
                //             break;
                //         }
                //     }
                // }
                // if (!empty($restaurant_details->driver_base_price) && $restaurant_details->driver_base_price!=0) {
                //     $source = $d_lat . ',' . $d_lng;
                //     $destination = $restaurant_details->lat . ',' . $restaurant_details->lng;
                //     $delivery_boy_commission_data = $this->calculate_driver_commission($restaurant_details, $source, $destination);
                //     $delivery_boy_commission = isset($delivery_boy_commission_data['delivery_boy_commission'])?$delivery_boy_commission_data['delivery_boy_commission']:0;
                //     $distance = isset($delivery_boy_commission_data['distance'])?$delivery_boy_commission_data['distance']:0;
                // } else {
                //     $source = $d_lat . ',' . $d_lng;
                //     $radius = DEFAULT_RADIUS;
                //     $delivery_boy_commision_details = DB::table('add_city')
                //         ->select('add_city.*', 'city_geofencing.polygons', DB::raw("( 6371 * acos( cos( radians($d_lat) ) *
                //                                             cos( radians( city_geofencing.latitude ) )
                //                                             * cos( radians( city_geofencing.longitude ) - radians($d_lng)
                //                                             ) + sin( radians($d_lat) ) *
                //                                             sin( radians( city_geofencing.latitude ) ) )
                //                                         ) AS distance"))
                //         ->leftJoin('city_geofencing', function ($join) {
                //             $join->on('city_geofencing.city_id', '=', 'add_city.id');
                //         })
                //         ->orderBy('distance', 'asc')
                //         ->get();
                //     $delivery_boy_commision_data = 0;
                //     $source = $d_lat . ',' . $d_lng;
                //     foreach ($delivery_boy_commision_details as $value) {
                //         $polygon = json_decode($value->polygons);
                //         $ponits = array($d_lng, $d_lat);
                //         $city_id = $value->id;
                //         $is_avail = $this->contains($ponits, $polygon[0]);
                //         if ($is_avail == 1) {
                //             $delivery_boy_commision_data = $value;
                //             break;
                //         }
                //     }
                //     if (!empty($delivery_boy_commision_data)) {
                //         $destination = $restaurant_details->lat . ',' . $restaurant_details->lng;
                //         $delivery_boy_commission_data = $this->calculate_driver_commission($delivery_boy_commision_data, $source, $destination);
                //         $delivery_boy_commission = $delivery_boy_commission_data['delivery_boy_commission'];
                //         $distance = $delivery_boy_commission_data['distance'];
                //     } else {
                //         $delivery_boy_commission = 0;
                //     }

                // }


                // Distance KM
                $source = $d_lat . ',' . $d_lng;
                $destination = $restaurant_details->lat . ',' . $restaurant_details->lng;
                $getdistance = $this->getGoogleDistance($source, $destination,1);
                $distance = $getdistance[0];
                
                // Delivery Time
                $date = date('Y-m-d H:i:s');
                $current_time = strtotime($date);
                $diff_time = $current_time + $getdistance[1];
                $delivered_time = date('Y-m-d H:i:s', $diff_time);

                //check delivery type

                // if ($delivery_type != 1)
                //     $delivery_boy_commission = $delivery_charge = 0;

                $loyalty_discount = isset($request->loyalty_discount) ? $request->loyalty_discount : 0;

                // $admin_calculation_amount = ($item_total + $gst + $restaurant_packaging_charge);
                // $admin_commission_total = ($admin_calculation_amount / 100) * $admin_commision_setting;
                // $admin_commission = ($admin_commission_total + $delivery_charge) - $delivery_boy_commission - $offer_discount - $loyalty_discount;
                // $restaurant_commission = (($item_total + $gst + $restaurant_packaging_charge) - $restaurant_discount) - $admin_commission_total;

                if ($user_detail->is_guest_user == 0) {
                    if(!empty($currentAddressId)){
                        $delivery_address_detail = $this->deliveryaddress->where('id', $currentAddressId)->where('user_id', $user_id)->first();
                    }
                    if (!empty($delivery_address_detail)) {
                        $delivery_address_detail = $this->deliveryaddress->where('user_id', $user_id)->where('is_default', 1)->first();
                    }
                    if (isset($delivery_address_detail)) {
                        Log::info('Paynow api body in delivery_address_detail section for user id ' . $user_id);
                        if ($delivery_address_detail->address_direction == "NULL") {
                            $delivery_address_detail->address_direction = "";
                        }
                        if ($delivery_address_detail->address_title == "NULL") {
                            $delivery_address_detail->address_title = "";
                        }
                        if ($delivery_address_detail->road_number == "NULL") {
                            $delivery_address_detail->road_number = "";
                        }
                        if ($delivery_address_detail->address == "NULL") {
                            $delivery_address_detail->address = "";
                        }
                        $delivery_address_1 = $delivery_address;
                        $delivery_address = '';
                        $delivery_address .= ($delivery_address_detail->flat_no) ? $delivery_address_detail->flat_no .',': '';
                        $delivery_address .= ($delivery_address_detail->block_number) ? $delivery_address_detail->block_number.',' : '';
                        $delivery_address .= ($delivery_address_detail->road_number) ? $delivery_address_detail->road_number.',': '';
                        $delivery_address .= ($delivery_address_detail->building) ? $delivery_address_detail->building.',' : '';
                        $delivery_address .= ($delivery_address_detail->address) ? $delivery_address_detail->address.',' : '';
                        $delivery_address .= ($delivery_address_detail->address_direction) ? $delivery_address_detail->address_direction: '';

                    }
                } else {
                    if ($request->flat_no) {
                        $flat_no = $request->flat_no;
                    } else {
                        $flat_no = "";
                    }

                    if ($request->block_number) {
                        $block_number = $request->block_number;
                    } else {
                        $block_number = "";
                    }

                    if ($request->road_number) {       
                        $road_number = $request->road_number;
                    } else {
                        $road_number = "";
                    }

                    if ($request->building) {
                        $building = $request->building;
                    } else {
                        $building = "";
                    }

                    if ($request->address_direction) {
                        $address_direction = $request->address_direction;
                    } else {
                        $address_direction = "";
                    }

                    $delivery_address_1 = $delivery_address;
                    $delivery_address = '';
                    $delivery_address .= ($flat_no) ? $flat_no .',': '';
                    $delivery_address .= ($block_number) ? $block_number.',' : '';
                    $delivery_address .= ($road_number) ? $road_number.',': '';
                    $delivery_address .= ($building) ? $building.',' : '';
                    $delivery_address .= ($address_direction) ? $address_direction: '';
                }
            
                // Admin Commission Calculations 
                $admin_gst_percentage = (NEW_ADMIN_GST / 100) * NEW_ADMIN_COMMISSION; 
                $admin_commision_percentage = NEW_ADMIN_COMMISSION + $admin_gst_percentage;
                $all_discounts = $restaurant_discount + $coupon_code_discount;
                $item_totals = $item_total - $all_discounts;
                $total_value = $item_totals + $restaurant_packaging_charge;
                $overall_admin_commision = ($admin_commision_percentage / 100) * $total_value;
                
                $order_data = array();

                $order_data[] = array(
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                    'restaurant_id' => $restaurant_id,
                    'delivery_type' => $delivery_type,
                    'device_type' => $device_type,
                    'total_members' => isset($request->total_members) ? $request->total_members : 0,
                    'pickup_dining_time' => isset($request->pickup_dining_time) ? date("Y-m-d H:i:s", strtotime($request->pickup_dining_time)) : date('Y-m-d H:i:s'),
                    'item_total' => $item_total,
                    // offer_discount = coupon code discount
                    'offer_discount' => $coupon_code_discount,
                    'loyalty_discount' => $loyalty_discount,
                    // restaurant_discount = restaurant discount
                    'restaurant_discount' => $restaurant_discount,
                    'restaurant_packaging_charge' => $restaurant_packaging_charge,
                    'tax' => $restaurant_commission,
                    'delivery_charge' => $delivery_charge,
                    'bill_amount' => $bill_amount,
                    'admin_commision' => $overall_admin_commision,
                    'restaurant_commision' => 0,
                    'delivery_boy_commision' => 0,
                    'coupon_code' => $coupon_code,
                    'is_confirmed' => 0,
                    'is_paid' => $is_paid,
                    'paid_type' => $paid_type,
                    'delivery_address' => !empty($delivery_address) ? $delivery_address : $request->address,
                    'delivery_address' => $delivery_address !== '' ? $delivery_address : $request->address,
                    'delivery_address_title' => $address_title,
                    'delivery_address_1' => isset($delivery_address_1) ? $delivery_address_1 : 'NA',
                    'delivery_address_id' => isset($delivery_address_detail->id) ? $delivery_address_detail->id : 0,
                    'd_lat' => $d_lat,
                    'd_lng' => $d_lng,
                    'distance' => $distance,
                    'city_id' => $city_id,
                    'ordered_time' => date('Y-m-d H:i:s'),
                    'distance_travel_time' => $delivered_time,
                    'comments' => $request->comment ? $request->comment : NULL,
                    'cart_id' => $cart_id,
                    'instruction_id'=>!empty($request->instruction_id)?json_encode($request->instruction_id):NULL,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $foodrequest::insert($order_data);
                Log::info('Paynow api order created id' . $order_id);
                $last_id = $foodrequest::where('user_id', $user_id)->where('restaurant_id', $restaurant_id)->orderBy('id', 'desc')->first();
                $request_id = (string)$last_id->id;
                $requestId = $last_id->id;
                for ($i = 0; $i < $food_id_size; $i++) {
                    $request_detail = new Requestdetail;
                    $request_detail->request_id = $last_id->id;
                    $request_detail->restaurant_id = $restaurant_id;
                    $request_detail->food_id = $food_id[$i];
                    $request_detail->quantity = $food_qty[$i];
                    $request_detail->addon_list = '0';
                    $request_detail->food_quantity_price = '0';
                    $request_detail->food_quantity = '0';
                    $request_detail->save();
                    $request_detail_id = $request_detail->id;
                    if (isset($request->choice) && isset($request->choice[$i])) {
                        if ($request->choice[$i] != '' && $request->choice[$i] != 0) {
                            $get_choice_id = explode(',',$request->choice[$i]);
                            for ($j = 0; $j < count($get_choice_id); $j++) {
                                $choices = $this->choice->find($get_choice_id[$j]);
                                Log::info('Paynow api choice id' . $get_choice_id[$j]);
                                $requestdetail_addons = new RequestdetailAddons;
                                $requestdetail_addons->requestdetail_id = $request_detail_id;
                                $requestdetail_addons->addons_id = $choices->id;
                                $requestdetail_addons->name = $choices->name;
                                $requestdetail_addons->name_ar = $choices->name_ar;
                                $requestdetail_addons->name_kur = $choices->name_kur;
                                $requestdetail_addons->price = $choices->price;
                                $requestdetail_addons->save();
                            }
                        }else{
                            Log::info('Paynow api choice no founded1');
                        }
                    }else{
                        Log::info('Paynow api choice no founded');
                    }
                }

                // Food Preparation Time Calculations
                try
                {
                    $details= new FoodPreparationJob($request->food_id , $request->food_qty , $requestId);
                    dispatch($details);
                }catch(\Exception $e)
                {
                    Log::error('Mail error:: ' . $e->getMessage());
                }

                //loyalty points calculation
                if ($loyalty_discount == 0 && $bill_amount != 0) {
                    $loyalty_point = LOYALTY_POINT;
                    $user_loyalty_points = $loyalty_point * $bill_amount;
                    $this->users->find($user_id)->increment('loyalty_points', $user_loyalty_points);
                }
                if ($loyalty_discount != 0) {
                    $this->users->where('id', $user_id)->update(['loyalty_points' => 0]);
                }

                //insert into firebase only when the delivery type is home delivery
                // if ($delivery_type == 1) {
                //     MultiOrderAssign::request_driver_commission($request_id, $distance, $delivery_boy_commission);
                // }
                    // $header = array();
                    // $header[] = 'Content-Type: application/json';
                    // $postdata = array();
                    // $postdata['user_id'] = $user_id;
                    // $postdata['request_id'] = $request_id;
                    // $postdata = json_encode($postdata);

                    // $ch = curl_init(FIREBASE_URL . "/new_user_request/$user_id.json");
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    // $result = curl_exec($ch);
                    // curl_close($ch);

                    // store firebase

                    // $header = array();
                    // $header[] = 'Content-Type: application/json';
                    // $postdata = array();
                    // $postdata['request_id'] = (string)$request_id;
                    // $postdata['user_id'] = (float)$user_id;
                    // $postdata['provider_id'] = "0";
                    // $postdata['status'] = 0;
                    // $postdata = json_encode($postdata);

                    // $ch = curl_init(FIREBASE_URL . "/current_request/$request_id.json");
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    // $result = curl_exec($ch);
                    // curl_close($ch);

                    // Store Mongodb
                    $currentRequest = CurrentRequest::where('request_id',$request_id)->first();
                    if($currentRequest)
                    {
                        CurrentRequest::where('request_id',$request_id)
                            ->update(['request_id' => $request_id , 'user_id' => $user_id , 'provider_id' => '0' , 'status' => 0]); 
                    }
                    else
                    {
                        $currentRequest = new CurrentRequest();
                        $currentRequest->request_id = (string)$request_id;
                        $currentRequest->user_id = (string)$user_id;
                        $currentRequest->provider_id = "0";
                        $currentRequest->status = "0";        
                        $currentRequest->save();
                    }
                // }

                //update in firebase for restaurant notification
                // $postdata = array();
                // $postdata['status'] = 0;
                // $postdata = json_encode($postdata);
                // $this->update_firebase($postdata, 'restaurant_request/' . $restaurant_id, $request_id);

                $client = new Client();
                $client->get(SOCKET_URL.'/restaurant_request/'.$restaurant_id);
                $cart::where('user_id', $user_id)->delete();

                $trackorderstatus->request_id = $request_id;
                $trackorderstatus->status = 0;
                $trackorderstatus->detail = "Order Placed";
                $trackorderstatus->save();

                //send push notification to restaurant
                if (isset($restaurant_details->device_token) && $restaurant_details->device_token != '') {
                    $title = $message = trans('constants.new_order');
                    $data = array(
                        'device_token' => $restaurant_details->device_token,
                        'device_type' => $restaurant_details->device_type,
                        'title' => $title,
                        'message' => $message,
                        'request_id' => $request_id,
                        'delivery_type' => $delivery_type
                    );
                    $this->user_send_push_notification($data);
                }
                // file_get_contents('http://'.$_SERVER['HTTP_HOST'].':8880/new-request?id='.$restaurant_id);

                $url = NOTIFICATION_URL.'new-request?id='.$restaurant_id;
                info('NOTIFICATION_URL'.$url);
                    $curl = curl_init();
                
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HEADER, false);

                    // execute and return string (this should be an empty string '')
                    $data = curl_exec($curl);

                    curl_close($curl);

                // if (EMAIL_ENABLE == 1) {
                //     $order_details = $foodrequest->find($request_id);
                //     $order_details->subject = 'Your Order From ' . $order_details->Restaurants->restaurant_name;
                //     $order_details->email = $user_detail->email;
                //     $order_details->name = 'User';
                //     $this->send_mail($order_details, 'user_order');
                // }

                // if (SMS_ENABLE == 1) {
                //     $message = "Your order " . $order_id . " with Fastbee has been created";
                //     $sendSms = $this->send_otp_softsms($user_detail->phone, $message);
                // }
                // $result = CCAvenueTransaction::where('user_id', $user_id)
                // ->orderBy('id','desc')
                // ->take(1)
                // ->update(['request_id' => $last_id->id]);

                $response_array = array('status' => true, 'message' => 'Order Placed Successfully', 'request_id' => $requestId);
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
    public function encrypt(Request $request)
    {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['ccavenue_payment'] == 1){
                $merchant_id = Config('app.test_merchant_id');
                $key = Config('app.test_working_key');
                $access_code = Config('app.test_access_code');
                info('Test-ccavenue-payment');
            }elseif($data['ccavenue_payment'] == 2){
                $merchant_id = Config('app.merchant_id');  
                $key = Config('app.working_key');
                $access_code = Config('app.access_code');
                info('Live-ccavenue-payment');
            }
            $encryptkey = [];
            $encryptkey['merchant_id'] = $merchant_id;
            $encryptkey['order_id'] = $request->order_id;
            $encryptkey['redirect_url'] = url('ccavResponseHandler.php');
            $encryptkey['cancel_url'] = url('ccavResponseHandler.php');
            $encryptkey['language'] = 'EN';
            $encryptkey['currency'] = 'INR';
            $encryptkey['amount'] = $request->amount;
            $encryptkey['billing_name'] = $request->billing_name;
            $encryptkey['user_country'] = $request->user_country;
            $encryptkey['user_address'] = $request->user_address;
            $encryptkey['user_postal_code'] = $request->user_postal_code;
            $encryptkey['user_city'] = $request->user_city;
            $encryptkey['user_state'] = $request->user_state;
            $encryptkey['user_phone_number'] = $request->user_phone_number;
            $encryptkey['user_email'] = $request->user_email;
            info($encryptkey);
            $user_id = $request->user_id;
            $encryptmerchant_id = 'merchant_id' . "=" .$encryptkey['merchant_id'];
            $encryptorder_id = 'order_id' . "=" . $encryptkey['order_id'];
            $encryptredirecturl = 'redirect_url' . "=" . $encryptkey['redirect_url'];
            $encryptcancelurl = 'cancel_url' . "=" . $encryptkey['cancel_url'];
            $encryptamount = 'amount' . "=" . $encryptkey['amount'];
            $encryptlanguage = 'language' . "=" . $encryptkey['language'];
            $encryptcurrency = 'currency' . "=" . $encryptkey['currency'];
            $encryptbilling_name = 'billing_name' . "=" . $encryptkey['billing_name'];
            $encryptuser_country = 'billing_country' . "=" . $encryptkey['user_country'];
            $encryptuser_address = 'billing_address' . "=" . $encryptkey['user_address'];
            $encryptuser_postal_code = 'billing_zip' . "=" . $encryptkey['user_postal_code'];
            $encryptuser_city = 'billing_city' . "=" . $encryptkey['user_city'];
            $encryptuser_state = 'billing_state' . "=" . $encryptkey['user_state'];
            $encryptuser_phone_number = 'billing_tel' . "=" . $encryptkey['user_phone_number'];
            $encryptuser_email = 'billing_email' . "=" . $encryptkey['user_email'];
            $plainText = $encryptmerchant_id . "&" . $encryptorder_id . "&" . $encryptredirecturl . "&" . $encryptcancelurl. "&" . $encryptamount . "&" . $encryptlanguage . "&" . $encryptcurrency . "&" . $encryptbilling_name . "&" . $encryptuser_country . "&" . $encryptuser_address . "&" . $encryptuser_postal_code . "&" . $encryptuser_city . "&" . $encryptuser_state  . "&" . $encryptuser_phone_number . "&" . $encryptuser_email;
            info($plainText);
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt = [];
            $encrypt['order_id'] = $encryptkey['order_id'];
            $encrypt['access_code'] = $access_code;
            $encrypt['redirect_url'] = url('ccavResponseHandler.php');
            $encrypt['cancel_url'] = url('ccavResponseHandler.php');
            $encrypt['enc_val'] = $ccaveEncrypt->encrypt($plainText,$key);
            info($encrypt);
            $ccavenuetransation = new CCAvenueTransaction();
            $ccavenuetransation->user_id = $request->user_id;
            $ccavenuetransation->order_id = $request->order_id;
            $ccavenuetransation->status = 0;
            $ccavenuetransation->save();
            return response (array('status' => true, 'encrypt' => $encrypt));
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

     public function ccAvenuesuccess($id,$order_id)
     {
        try {
            CCAvenueTransaction::where('id',$id)->where('order_id',$order_id)->update(['status' => 1]);
            return response (array('status' => true,'message' => 'Purchased successfull', 'success_status' => 1));
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }
     public function ccAvenuefailed($id,$order_id)
     {
        try {
            CCAvenueTransaction::where('id',$id)->where('order_id',$order_id)->update(['status' => 2]);
            return response (array('status' => true, 'message' => 'Purchased Failed' ,'success_status' => 2));
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }
     public function ccAvenuestatus(Request $request)
     {
        try {
            $status = CCAvenueTransaction::where('user_id',$request->user_id)->orderBy('id', 'DESC')->first();
            return response (array('status' => true, 'data' => $status));
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }
     public function ccAvenuerefund($request_id,$refund_amount)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['ccavenue_refund'] == 1){
                $key = Config('app.test_working_key');
                $access_code = Config('app.test_access_code');
                $refund_url = Config('app.test_ccavenue_refund_url');
                info('Test-ccavenue-refund');
            }elseif($data['ccavenue_refund'] == 2){
                $key = Config('app.working_key');
                $access_code = Config('app.access_code');
                $refund_url = Config('app.ccavenue_refund_url');
                info('Live-ccavenue-refund');
            }
            info($key);
            info($access_code);
            info($refund_url);
            $ccavenuedetails = CCAvenueTransaction::where('request_id', $request_id)->first();
            info($ccavenuedetails);
            $encrypt['command'] = "refundOrder";
            $encrypt['reference_no'] = $ccavenuedetails->transaction_id;
            $encrypt['refund_amount'] = (float)$refund_amount;
            $randomNumber = mt_rand(1000, 9999);  
            $encrypt['refund_ref_no'] = 'API'.$randomNumber;
            $encrypt['access_code'] = $access_code;
            $encrypt_details['reference_no']  = $ccavenuedetails->transaction_id;
            $encrypt_details['refund_amount'] = (float)$encrypt['refund_amount'];
            $encrypt_details['refund_ref_no'] = 'API'.$randomNumber;
            info($encrypt_details);
            $plainText = json_encode($encrypt_details);
            info($plainText);
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_request'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encrypt['enc_request'] =  trim(preg_replace('/\s\s+/', ' ',$encrypt['enc_request']));
            info($encrypt['enc_request']);
            $request_params = array(
                'command' => 'refundOrder',
                'access_code' => $encrypt['access_code'],
                'request_type' => 'JSON',
                'enc_request' => $encrypt['enc_request'],
                'response_type' =>'JSON',
                'version' =>'1.1'
            );
            info($request_params);
            $request_param = json_encode($request_params);
            info($request_param);
            info($refund_url);
            $ch = curl_init($refund_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $arrayResponse = explode('&', $response);
            $status = explode('=', $arrayResponse[0]);
            $encrypted = explode('=', $arrayResponse[1]);
            info($encrypted[1]);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypted[1]));
            // $encryptedText = $encrypted[1];
            if($status[1] == 0){
                $return_response = $ccaveEncrypt->decrypt($encryptedText,$key);
                info($return_response);
                $response = json_decode($return_response);
            }else{
                $response = '';
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueRefundstatus(Request $request)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['ccavenue_refund'] == 1){
                $key = Config('app.test_working_key');
                $access_code = Config('app.test_access_code');
                $refund_url = Config('app.test_ccavenue_refund_url');
                info('Test-ccavenue-refund');
            }elseif($data['ccavenue_refund'] == 2){
                $key = Config('app.working_key');
                $access_code = Config('app.access_code');
                $refund_url = Config('app.ccavenue_refund_url');
                info('Live-ccavenue-refund');
            }
            $request_id = $request->requestid;
            $ccavenuedetails = CCAvenueTransaction::where('request_id', $request_id)->first();
            $encrypt['access_code'] = $access_code;
            $encrypt_details['reference_no']  = $ccavenuedetails->transaction_id;
            $plainText = json_encode($encrypt_details);
            info($plainText);
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_request'] = $ccaveEncrypt->encrypt($plainText,$key);
            $request_params = array(
                'command' => 'getRefundDetails',
                'access_code' => $encrypt['access_code'],
                'request_type' => 'JSON',
                'enc_request' => $encrypt['enc_request'],
                'response_type' =>'JSON',
                'version' =>'1.1'
            );
            $ch = curl_init($refund_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $arrayResponse = explode('&', $response);
            $status = explode('=', $arrayResponse[0]);
            $encrypted = explode('=', $arrayResponse[1]);
            info($encrypted[1]);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypted[1]));
            if($status[1] == 0){
                $return_response = json_decode($ccaveEncrypt->decrypt($encryptedText,$key));
                if($return_response->error_code == ''){
                    $refundvalue = $return_response->refund_list['0']->refund_status;
                    $refund_status = HelperTools::refundStatus($refundvalue);
                    Refund::where('request_id',$request_id)
                    ->update(['refund_status' => $refund_status]); 
                    $response = $return_response;
                }else{
                    $response = 1;
                }
            }else{
                $response = 1;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
     public function ccAvenueCreatevendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $restaurants = $this->restaurants;
            $data = $restaurants->with('RestaurantBankDetails')->where('id',$id)->first();
            if($data->RestaurantBankDetails->account_type == 1){
                $account_type = 'Current';
            }elseif($data->RestaurantBankDetails->account_type == 2){
                $account_type = 'Saving';
            }
            $encryptDetails['vendorName'] = $data->restaurant_name;
            $encryptDetails['vendorContactName'] = $data->RestaurantBankDetails->account_name;
            $encryptDetails['vendorPhone'] = $data->phone;
            $encryptDetails['vendorEmail'] = $data->email;
            $encryptDetails['vendorAddress'] = preg_replace('/([^a-zA-Z0-9\s])\1+/', '\1',$data->RestaurantBankDetails->branch_address);
            $encryptDetails['vendorZipCode'] = (string)$data->pin_code;
            $encryptDetails['vendorRefNo'] = $data->vendor_reference_no;
            $encryptBankdetail = array(
                'accountNo' => $data->RestaurantBankDetails->account_no,
                'accountType' => $account_type,
                'beneficiaryName' => $data->RestaurantBankDetails->account_name,
                'ifscCode' => trim(preg_replace('/\s\s+/', ' ', $data->RestaurantBankDetails->ifsc)),
            );
            $vendorBankDetail = $encryptBankdetail;
            $encryptDetails['vendorBankDetails'] = $vendorBankDetail;
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);        
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/createNewVendorApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            info($data['messageDesc']);
            if($data['code'] == 0){
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['messageDesc']));
            $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
            info('dec_response'.$dec_response);
            $data = json_decode($dec_response, true);
                $vendorId = $data['vendorId'];
                $regId = $data['regId'];
                $vendorVirtualAccNo = $data['vendorVirtualAccNo'];
                $restaurants = $this->restaurants;
                $restaurants->where('id',$id)->where('id',$id)->update(['vendor_id' => $vendorId,'vendor_reg_id' => $regId ,'vendor_virtual_accno' => $vendorVirtualAccNo]);    
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueEditvendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $restaurants = $this->restaurants;
            $data = $restaurants->with('RestaurantBankDetails')->where('id',$id)->first();
            if($data->RestaurantBankDetails->account_type == 1){
                $account_type = 'Current';
            }elseif($data->RestaurantBankDetails->account_type == 2){
                $account_type = 'Saving';
            }
            $encryptDetails['vendorId'] = $data->vendor_id;
            $encryptDetails['vendorName'] = $data->restaurant_name;
            $encryptDetails['vendorContactName'] = $data->RestaurantBankDetails->account_name;
            $encryptDetails['vendorPhone'] = $data->phone;
            $encryptDetails['vendorEmail'] = $data->email;
            $encryptDetails['vendorAddress'] = preg_replace('/([^a-zA-Z0-9\s])\1+/', '\1',$data->RestaurantBankDetails->branch_address);
            $encryptDetails['vendorZipCode'] = (string)$data->pin_code;
            $encryptDetails['vendorRefNo'] = $data->vendor_reference_no;
            $encryptBankdetail = array(
                'accountNo' => $data->RestaurantBankDetails->account_no,
                'accountType' => $account_type,
                'beneficiaryName' => $data->RestaurantBankDetails->account_name,
                'ifscCode' => trim(preg_replace('/\s\s+/', ' ', $data->RestaurantBankDetails->ifsc)),
            );
            $vendorBankDetail = $encryptBankdetail;
            $encryptDetails['vendorBankDetails'] = $vendorBankDetail;
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);        
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/editVendorApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            info($data['messageDesc']);
            if($data['code'] == 0){
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['messageDesc']));
            $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
            info('dec_response'.$dec_response);
            $data = json_decode($dec_response, true);
                $vendorId = $data['vendorId'];
                $regId = $data['regId'];
                $vendorVirtualAccNo = $data['vendorVirtualAccNo'];
                $restaurants = $this->restaurants;
                $restaurants->where('id',$id)->where('id',$id)->update(['vendor_id' => $vendorId,'vendor_reg_id' => $regId ,'vendor_virtual_accno' => $vendorVirtualAccNo]);    
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueDeletevendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $restaurants = $this->restaurants;
            $data = $restaurants->with('RestaurantBankDetails')->where('id',$id)->first();     
            $encryptBankdetail = $data->vendor_id;
            $vendorsToDelete = $encryptBankdetail;
            $encryptDetails['vendorsToDelete'] = array($vendorsToDelete);
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);        
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/deleteSelectedVendorsApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            if($data['code'] == 0){
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueRiderCreatevendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $deliverypartners = $this->deliverypartners;
            $driver_partner_details = $this->driver_partner_details;
            $data = $deliverypartners->with('Deliverypartner_detail')->where('id',$id)->first();
            $encryptDetails['vendorName'] = $data->name;
            $encryptDetails['vendorContactName'] = $data->Deliverypartner_detail->account_name;
            $encryptDetails['vendorPhone'] =  substr($data->phone,3);
            $encryptDetails['vendorEmail'] = $data->email;
            $encryptDetails['vendorAddress'] = preg_replace('/([^a-zA-Z0-9\s])\1+/', '\1',$data->Deliverypartner_detail->branch_address);
            $encryptDetails['vendorZipCode'] = (string)$data->Deliverypartner_detail->pin_code;
            $encryptDetails['vendorRefNo'] = $data->Deliverypartner_detail->rider_vendor_reference_no;
            $encryptBankdetail = array(
                'accountNo' => $data->Deliverypartner_detail->account_no,
                'accountType' => $data->Deliverypartner_detail->account_type,
                'beneficiaryName' => $data->Deliverypartner_detail->account_name,
                'ifscCode' => trim(preg_replace('/\s\s+/', ' ', $data->Deliverypartner_detail->swift_code)),
            );
            $vendorBankDetail = $encryptBankdetail;
            $encryptDetails['vendorBankDetails'] = $vendorBankDetail;
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);   
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/createNewVendorApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            info($data['messageDesc']);
            if($data['code'] == 0){
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['messageDesc']));
            $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
            info('dec_response'.$dec_response);
            $data = json_decode($dec_response, true);
                $vendorId = $data['vendorId'];
                $regId = $data['regId'];
                $vendorVirtualAccNo = $data['vendorVirtualAccNo'];
                $driver_partner_details->where('delivery_partners_id',$id)->update(['rider_vendor_id' => $vendorId,'rider_vendor_reg_id' => $regId,'rider_vendor_virtual_accno' => $vendorVirtualAccNo]);    
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueRiderEditvendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $deliverypartners = $this->deliverypartners;
            $driver_partner_details = $this->driver_partner_details;
            $data = $deliverypartners->with('Deliverypartner_detail')->where('id',$id)->first();
            $encryptDetails['vendorId'] = $data->Deliverypartner_detail->rider_vendor_id;
            $encryptDetails['vendorName'] = $data->name;
            $encryptDetails['vendorContactName'] = $data->Deliverypartner_detail->account_name;
            $encryptDetails['vendorPhone'] =  substr($data->phone,3);
            $encryptDetails['vendorEmail'] = $data->email;
            $encryptDetails['vendorAddress'] = preg_replace('/([^a-zA-Z0-9\s])\1+/', '\1',$data->Deliverypartner_detail->branch_address);
            $encryptDetails['vendorZipCode'] = (string)$data->Deliverypartner_detail->pin_code;
            $encryptDetails['vendorRefNo'] = $data->Deliverypartner_detail->rider_vendor_reference_no;
            $encryptBankdetail = array(
                'accountNo' => $data->Deliverypartner_detail->account_no,
                'accountType' => $data->Deliverypartner_detail->account_type,
                'beneficiaryName' => $data->Deliverypartner_detail->account_name,
                'ifscCode' => trim(preg_replace('/\s\s+/', ' ', $data->Deliverypartner_detail->swift_code)),
            );
            $vendorBankDetail = $encryptBankdetail;
            $encryptDetails['vendorBankDetails'] = $vendorBankDetail;
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);   
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/editVendorApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            info($data['messageDesc']);
            if($data['code'] == 0){
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['messageDesc']));
            $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
            info('dec_response'.$dec_response);
            $data = json_decode($dec_response, true);
                $vendorId = $data['vendorId'];
                $regId = $data['regId'];
                $vendorVirtualAccNo = $data['vendorVirtualAccNo'];
                $driver_partner_details->where('delivery_partners_id',$id)->update(['rider_vendor_id' => $vendorId,'rider_vendor_reg_id' => $regId,'rider_vendor_virtual_accno' => $vendorVirtualAccNo]);    
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

     public function ccAvenueRiderDeletevendor($id)
     {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $deliverypartners = $this->deliverypartners;
            $driver_partner_details = $this->driver_partner_details;
            $data = $deliverypartners->with('Deliverypartner_detail')->where('id',$id)->first();
            $encryptBankdetail = $data->Deliverypartner_detail->rider_vendor_id;
            $vendorsToDelete = $encryptBankdetail;
            $encryptDetails['vendorsToDelete'] = array($vendorsToDelete);
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails);        
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_req'],
                'version' =>'1.0'
            );
            info($plainText);
            info($request_params);
            info($encrypt['enc_req']);
            info($encrypt['dec_req']);
            $payout_url = $url.'/services/vendorApi/deleteSelectedVendorsApi';
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            if($data['code'] == 0){
                $response = $data; 
            } else {
                $response = $data;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
     }

    public function ccAvenueRiderpayout($id,$amount,$description)
    {
        try {
            $randomNumber = mt_rand(100000000, 9999999999);  
            $amount = (float)$amount;
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            info($key);
            info($access_code);
            info($url);
            $data = $this->driver_partner_details->where('delivery_partners_id',$id)->first();
            if(empty($data->rider_vendor_id) || $data->rider_vendor_id == Null){
                $curl = curl_init();
                            curl_setopt_array($curl, array(
                            CURLOPT_URL => $url."cc_avenue_create_vendor/".$id,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                            ),
                        ));
                        $response = curl_exec($curl);
                        curl_close($curl);
            }
            $encryptDetails['vendor_id'] = $data->rider_vendor_id;
            $encryptDetails['vendor_ref_id'] = '';
            $encryptDetails['merchant_ref'] = $randomNumber;
            $encryptDetails['amount'] = $amount;
            $encryptDetails['currency'] = 'INR';
            $encryptDetails['payment_mode'] = 'NEFT';
            $encryptDetails['bene_account_no'] = '';
            $encryptDetails['bene_ifsc_code'] = '';
            $encryptDetails['bene_name'] = '';
            $encryptDetails['remarks'] = $description;
            info($encryptDetails);
            $encrypt['access_code'] = $access_code;
            $plainText = json_encode($encryptDetails); 
            info($plainText);       
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_req'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypt['enc_req']));
            $encrypt['dec_req'] = $ccaveEncrypt->decrypt($encryptedText,$key);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'enc_req' => $encrypt['enc_req'],
            );
            info($request_params);
            $payout_url = $url.'/services/makeB2BPayment/processReq';
            info($payout_url);
            $ch = curl_init($payout_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data['status_code']);
                if($data['status_code'] != 1){
                    $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['enc_response']));
                    $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
                    $data = json_decode($dec_response);
                }
            return response()->json($data, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
    public function payoutStatus(Request $request)
    {
        try {
            $data = $this->settings->pluck('value','key_word')->toArray();
            if($data['b2biz_payment'] == 1){
                $key = Config('app.test_payout_working_key');
                $access_code = Config('app.test_payout_access_code');
                $url = Config('app.test_payout_url');
                info('Test-b2biz-payment');
            }elseif($data['b2biz_payment'] == 2){
                $key = Config('app.payout_working_key');
                $access_code = Config('app.payout_access_code');
                $url = Config('app.payout_url');
                info('Live-b2biz-payment');
            }
            $payout_id = $request->payout_id;
            $merchant_ref_no = $request->merchant_ref_no;
            $payout_trans_id = $request->payout_trans_id;
            if($payout_trans_id != ''){
            $encrypt['access_code'] = $access_code;
            $encrypt_details['transId'] = $payout_trans_id;
            $encrypt_details['payRefNo'] = '';
            info($encrypt_details);
            $plainText = json_encode($encrypt_details);
            info($plainText);
            $ccaveEncrypt = new CCaveEncrypt;
            $encrypt['enc_request'] = $ccaveEncrypt->encrypt($plainText,$key);
            $encrypt['enc_request'] =  trim(preg_replace('/\s\s+/', ' ',$encrypt['enc_request']));
            info($encrypt['enc_request']);
            $request_params = array(
                'access_code' => $encrypt['access_code'],
                'req_type' => 'JSON',
                'enc_req' => $encrypt['enc_request'],
                'version' =>'1.1'
            );
            info($request_params);
            $request_param = json_encode($request_params);
            info($request_param);
            $refund_url = $url.'/services/depositAccountApi/transactionStatusApi';
            $ch = curl_init($refund_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
            $response = curl_exec($ch);
            curl_close($ch);
            info($response);
            $data = json_decode($response, true);
            info($data);
            info($data['messageDesc']);
            if($data['code'] != '-1'){
                $encryptedText = trim(preg_replace('/\s\s+/', ' ', $data['messageDesc']));
                $dec_response = $ccaveEncrypt->decrypt($encryptedText,$key);
                info('dec_response'.$dec_response);
                $data = json_decode($dec_response, true);
                $response = $data; 
            }else{
                $response = 0;
            }
            }else{
                $response = 0;
            }
            return response()->json($response, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_restaurants_old(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array(
                'key_word' => 'required',
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
            $lang = isset($request->lang) ? $request->lang : 'en';
            $restaurants = $this->restaurants;
            $key_word = $request->key_word;
            $source_lat = $request->lat;
            $source_lng = $request->lng;
            if ($lang == 'ar') {
                $data = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)
                    ->where(function ($queryFilter) use ($key_word) {
                        $queryFilter->Where('restaurant_name_ar', 'like', '%' . $key_word . '%');
                    });
            } elseif ($lang == 'ku') {
                $data = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)
                    ->where(function ($queryFilter) use ($key_word) {
                        $queryFilter->Where('restaurant_name_kur', 'like', '%' . $key_word . '%');
                    });
            } else {
                $data = $restaurants->with(['Cuisines', 'RestaurantTimer'])->where('status', 1)
                    ->where(function ($queryFilter) use ($key_word) {
                        $queryFilter->Where('restaurant_name', 'like', '%' . $key_word . '%');
                    });
            }
            $data = $data->groupBy('restaurant_name')
                ->select('restaurants.*')
                ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                            * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                            * sin(radians(`lat`)))) as distance")
                ->having('distance', '<=', DEFAULT_RADIUS)
                ->orderBy('distance')
                ->get();

            $restaurant_list = array();
            $j = 0;
            foreach ($data as $d) {
                $rcuisines = array();
                $i = 0;
                foreach ($d->Cuisines as $r_cuisines) {
                    if ($i < 2) // To display only two cuisines
                    {
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
                if ($d->parent == 0) {
                    $check_offer_for_restaurant = DB::table('offers_banner')->where('status', 1)->where('restaurant_id', $d->id)->get();
                } else {
                    $check_offer_for_restaurant = DB::table('offers_banner')->where('status', 1)->where('restaurant_id', $d->parent)->get();
                }

                $check_offer_for_restaurant = DB::table('offers_banner')->where('status', 1)->where('restaurant_id', $d->id)->get();
                if (count($check_offer_for_restaurant) != 0) {
                    $is_food_offer_exist = 1;
                } else {
                    $is_food_offer_exist = 0;
                }

                $minutes = $this->get_travel_time($source_lat , $source_lng , $d->lat , $d->lng);
                $str = strstr($d->estimated_food_preparation_time, '-');

                if($str != false) {
                    $d->estimated_food_preparation_time = trim(substr($str, 1));
                }

                $travel_time = $d->estimated_food_preparation_time + $minutes + 5;
                $d->total_delivery_time = round($travel_time);

                $rating = $d->rating;

                $restaurant_list[] = array(
                    'id' => $d->id,
                    'name' => $d->restaurant_name,
                    'name_ar' => $d->restaurant_name_ar,
                    'name_kur' => $d->restaurant_name_kur,
                    'image' => SPACES_BASE_URL . $d->image,
                    'discount' => $d->discount,
                    'rating' => round($rating, 1),
                    'is_open' => $is_open,     // 1- Open , 0 - Close
                    'is_busy' => $d->is_busy,
                    'cuisines' => $rcuisines,
                    'travel_time' => $d->total_delivery_time,
                    'price' => $restaurant_offer,
                    'discount_type' => $d->discount_type,
                    'target_amount' => $d->target_amount,
                    'offer_amount' => $d->offer_amount,
                    'offer_type'=> $d->offer_type,
                    'offer_value'=> $d->offer_value,
                    'is_food_offer_exist' => $is_food_offer_exist,
                    'is_favourite' => $is_favourite,
                    'delivery_type' => $d->delivery_type,
                    'address' => $d->address,
                    'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                    'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                );
                $j++;
            }
            if (count($data) != 0) {
                $response_array = array('status' => true, 'restaurants' => $restaurant_list);
            } else {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_restaurant', $lang));
            }
        }
        return response()->json($response_array, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_restaurants(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'key_word' => 'required',
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
                $source_lat = $request->lat;
                $source_lng = $request->lng;
                $keyWord = $request->key_word;

                $restaurants = $this->restaurants;
                $size_cuisines = isset($request->cuisines) ? sizeof($request->cuisines) : 0;
                $cuisines = isset($request->cuisines) ? $request->cuisines : '';
                $is_pureveg = 0;
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
                // dd($delivery_charge_data->id);
                if ($delivery_charge_data != "") {
                    $checkData = 0;

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
                        $datas = $query->get();
                        $subHubId = (string) $delivery_charge_data->id;
                        $restaurantFullIdlist = implode(",", array_column($datas->toArray(), 'id'));
                        $data = DB::select("call get_similar_foods_restaurants('$restaurantFullIdlist','$keyWord','$subHubId')");
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
                            $getCuisinesRestaurants = $this->get_cuisines_detils($d->id);
                            $d->cuisines = $getCuisinesRestaurants[0];
                            $d->RestaurantTimer = $getCuisinesRestaurants[1];
                            $d->is_boosted = $d->boosted;
                        }

                } else {
                    $data = '';
                }
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
                    foreach ($data as $d) {
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


                        // if ($is_open == 1 && $d->is_busy!=1) {
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
                                'is_boosted' => $d->is_boosted,
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
                        // } else {
                        //     $restaurant_closed_list[] = array(
                        //         'id' => $d->id,
                        //         'name' => ucwords(strtolower($d->restaurant_name)),
                        //         'name_ar' => $d->restaurant_name_ar,
                        //         'name_kur' => $d->restaurant_name_kur,
                        //         'image' => SPACES_BASE_URL . $d->image,
                        //         'discount' => $d->discount,
                        //         'rating' => round($rating, 1),
                        //         'is_open' => $is_open,     // 1- Open , 0 - Close
                        //         'is_busy' => $d->is_busy,
                        //         'cuisines' => $rcuisines,
                        //         'travel_time' => $d->total_delivery_time,
                        //         'is_boosted' => $d->is_boosted,
                        //         // 'distance' => isset($d->distance_mins) ? $d->distance_mins : '',
                        //         'price' => $restaurant_offer,
                        //         'discount_type' => $d->discount_type,
                        //         'offer_type'=> $d->offer_type,
                        //         'offer_value'=> $d->offer_value,
                        //         'target_amount' => $d->target_amount,
                        //         'offer_amount' => $d->offer_amount ? $d->offer_amount : "0",
                        //         'is_food_offer_exist' => $is_food_offer_exist,
                        //         'is_favourite' => $is_favourite,
                        //         'delivery_type' => $d->delivery_type,
                        //         'address' => $d->address,
                        //         'min_order_value' => $d->min_order_value,
                        //         'restaurant_delivery_charge' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                        //         'restaurant_delivery_charge_ios' => $d->restaurant_delivery_charge ? $d->restaurant_delivery_charge : 0,
                        //         'restaurant_open_time' => $restaurant_open_close_time['opening_time'],
                        //         'restaurant_close_time' => $restaurant_open_close_time['closing_time']
                        //     );
                        // }
                        $j++;
                    }
                    // $restaurant_list = array_merge($restaurant_open_list, $restaurant_closed_list);
                    $restaurant_list = $restaurant_open_list;
                } else {
                    $restaurant_list = [];
                }
                            
                if (count($restaurant_list) != 0) {
                    $response_array = array('status' => true, 'restaurants' => $restaurant_list);
                } else {
                    $response_array = array('status' => false, 'message' => __('constants.no_restaurant'));
                }
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * get dining restaurants
     * 
     * @param object $request
     * 
     * @return json $response
     */
    public function get_dining_restaurant(Request $request)
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
                $restaurants = $this->restaurants;

                $query = $restaurants->with(['Cuisines','RestaurantTimer'])->Where(function($q)
                        {
                            $q->where("status",1)->where("delivery_type",'["3"]');
                        })
                        ->orWhere(function($q)
                        {
                            $q->where("status",1)->where("delivery_type",'["1","2","3"]');
                        })
                        ->orWhere(function($q)
                        {
                            $q->where("status",1)->where("delivery_type",'[2","3"]');
                        })
                        ->orWhere(function($q)
                        {
                            $q->where("status",1)->where("delivery_type",'["1","3"]');
                        })
                        ->select('restaurants.*')
                        ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                * sin(radians(`lat`)))) as distance")
                        ->having('distance','<=',DEFAULT_RADIUS)
                        ->orderBy('distance');
                    
            
                $limit = PAGINATION;
                $page = isset($request->page)?$request->page:1;
                $offset = ($page - 1) * $limit;
                $query = $query->when(($limit!='-1' && isset($offset)), 
                            function($q) use($limit, $offset){
                                return $q->offset($offset)->limit($limit);
                            });
                        
                $data = $query->get();
                $restaurant_list = array();
                foreach($data as $d)
                {
                
                    $rcuisines = array();
                    $i=0;
                    foreach($d->Cuisines as $r_cuisines)
                    {
                        if($i<2)
                        {
                            $rcuisines[] = array(
                                'name' => $r_cuisines->name
                            );  
                            $i =$i+1;
                        }
                    }
                    
                    $check_favourite = DB::table('favourite_list')->where('user_id',$user_id)->where('restaurant_id',$d->id)->get();
                    if(count($check_favourite)!=0)
                    {
                        $is_favourite = 1;
                    }else
                    {
                        $is_favourite = 0;
                    }
                //calculate restaurant open time
                $is_open = $this->check_restaurant_open($d);

                    //check restaurant offer
                    $restaurant_offer = "";
                    if($d->offer_amount!='' && $d->offer_amount!=0){
                        if($d->discount_type==1){
                            $restaurant_offer = "Flat offer ".DEFAULT_CURRENCY_SYMBOL." ".$d->offer_amount;
                        }else{
                            $restaurant_offer = $d->offer_amount."% offer";
                        }
                        if($d->target_amount!=0){
                            $restaurant_offer = $restaurant_offer." on orders above ".DEFAULT_CURRENCY_SYMBOL." ".$d->target_amount;
                        }
                    }
                    $res_id = $d->id;
                    $rating = $this->order_ratings->with('Foodrequest')
                                ->wherehas('Foodrequest',function($q) use($res_id){
                                    $q->where('restaurant_id', $res_id);
                                    })
                                ->avg('restaurant_rating');

                        if($rating<1)
                                {
                                    $rating = 5;
                                }
                    if(sizeof($rcuisines)>0)
                    {
                        $restaurant_list[] = array(
                            'id'        =>$d->id,
                            'name'      => $d->restaurant_name,
                            'image'     => SPACES_BASE_URL.$d->image,
                            'discount'  => $d->discount,
                            'rating'    => round($rating,1),
                            'is_open'   => $is_open,     // 1- Open , 0 - Close
                            'cuisines'  => $rcuisines,
                            // 'travel_time' => $d->estimated_delivery_time,
                            'price'     => $restaurant_offer,
                            'discount_type' => $d->discount_type,
                            'target_amount' => $d->target_amount,
                            'offer_amount'  => $d->offer_amount,
                            'is_favourite'=>$is_favourite,
                            'delivery_type' => $d->delivery_type,
                            'weekday_opening_time' => $d->opening_time,
                            'weekday_closing_time' => $d->closing_time,
                            'weekend_opening_time' => $d->weekend_opening_time,
                            'weekend_closing_time' => $d->weekend_closing_time,
                            'max_dining_count' => $d->max_dining_count,
                            'restaurant_timing' => $d->RestaurantTimer
                            );
                    }

                }

                if(sizeof($restaurant_list)>0)
                {
                    $response_array = array('status'=>true,'restaurants'=>$restaurant_list);
                }else
                {
                    $response_array = array('status'=>false,'message'=>__('constants.no_data'));
                }
            }
            $response = response()->json($response_array, 200);
            return $response;
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function paynow_dining(Request $request)
    {
        try {
            $validator = Validator::make(
                    $request->all(),
                    array(
                        'restaurant_id' => 'required',
                        'total_members' => 'required',
                        'pickup_dining_time' => 'required'
                    ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {

                $foodrequest = $this->foodrequest;
                $trackorderstatus = $this->trackorderstatus;
                $settings = $this->settings;
                if($request->header('authId')!="")
                {
                    $user_id = $request->header('authId');
                }else
                {
                    $user_id = $request->authId;
                }
                // $user_id = $request->header('authId');
                $restaurant_id = $request->restaurant_id;
                
                $delivery_type = isset($request->delivery_type)?$request->delivery_type:3;

                $user_detail = $this->users::where('id',$user_id)->first();

                $order_id =$this->generate_booking_id();

                $order_data = array();

                $order_data[] = array(
                    'order_id'=>$order_id,
                    'user_id'=>$user_id,
                    'restaurant_id'=>$restaurant_id,
                    'delivery_type' => $delivery_type,
                    'total_members' => isset($request->total_members)?$request->total_members:0,
                    'pickup_dining_time' => isset($request->pickup_dining_time)?date("Y-m-d H:i:s",strtotime($request->pickup_dining_time)):"0000-00-00 00:00:00",
                    'is_confirmed'=>0,
                    'is_paid'=>0,
                    'ordered_time'=>date('Y-m-d H:i:s'),
                );
                $foodrequest::insert($order_data);
                $last_id = $foodrequest::where('user_id',$user_id)->where('restaurant_id',$restaurant_id)->orderBy('id','desc')->first();
                $request_id = $last_id->id;

                // $status_entry[] = array(
                //     'request_id'=>$request_id,
                //     'status'=>0,
                //     'detail'=>"Order Placed",
                // );

                // $trackorderstatus::insert($status_entry);

                $trackorderstatus->request_id = $request_id;
                $trackorderstatus->status = 0;
                $trackorderstatus->detail = "Order Placed";
                $trackorderstatus->save();

                //sesnd email to user
                if(EMAIL_ENABLE==1)
                {
                    //$order_details = $foodrequest->find($request_id);
                }
                //send push notification to restaurant
                $restaurant_details = $this->restaurants->find($restaurant_id);
                if(isset($restaurant_details->device_token) && $restaurant_details->device_token!='')
                {
                    $title = $message = trans('constants.new_order');
                    $data = array(
                        'device_token' => $restaurant_details->device_token,
                        'device_type' => $restaurant_details->device_type,
                        'title' => $title,
                        'message' => $message,
                        'request_id' => $request_id,
                        'delivery_type' => $delivery_type
                    );
                    $this->user_send_push_notification($data);
                }

                $response_array = array('status'=>true,'message'=>__('constants.order_place'));
                //file_get_contents("http://".$_SERVER['HTTP_HOST'].":8081/new-request?id=".$restaurant_id);

            }

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
    public function todays_special(Request $request)
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
                $source_lat = $request->lat;
                $source_lng = $request->lng;

                $food_list = $this->foodlist->leftJoin('restaurants', 'food_list.restaurant_id', '=', 'restaurants.id')
                    ->select('food_list.*', 'restaurants.restaurant_name as name')
                    ->selectRaw("(6371 * acos(cos(radians(" . $source_lat . "))* cos(radians(`lat`)) 
                                        * cos(radians(`lng`) - radians(" . $source_lng . ")) + sin(radians(" . $source_lat . ")) 
                                        * sin(radians(`lat`)))) as distance")
                    ->having('distance', '<=', DEFAULT_RADIUS)
                    ->orderBy('distance')->where('food_list.is_special', 1)
                    ->where('food_list.status', 1)->get();

                foreach ($food_list as $key) {
                    $key->image = FOOD_IMAGE_PATH . $key->image;
                }

                $response_array = array('status' => true, 'food_list' => $food_list);
            }

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
    *
    * for to add the restaurant
    *
    */

    public function add_to_restaurants(Request $request)
    {
     //dd($request->all());
       $rules = array(
            'name' => 'required|max:50',
            'password' => 'required',
            'city' => 'required',
            // 'area' => 'required',
            //'status' => 'required',
            //'opening_time' => 'required',
            //'closing_time' => 'required',
            //'weekend_opening_time' => 'required',
            //'weekend_closing_time' => 'required',
            'weekdays' => 'required',
            'weekenddays' => 'required',
            // 'estimated_delivery_time' => 'required',
            // 'fssai_license' => 'required',
            'address' => 'required',
            // 'packaging_charge' => 'required',
            // 'tax' => 'required',
            'delivery_type' => 'required|array',
            'cuisines' => 'required|array',
        );
        if($request->id!='')
        {
            $rules['email'] = 'required|unique:restaurants,email,'.$request->id;
            $rules['phone'] = 'required|numeric|unique:restaurants,phone,'.$request->id;
        }else
        {
            $rules['image'] = 'required|max:2048|mimes:jpeg,jpg,bmp,png';
            $rules['email'] = 'required|unique:restaurants,email';
            $rules['phone'] = 'required|numeric|unique:restaurants,phone';
        //    $rules['status'] = 'required';
        }
        // foreach($request->document as $key=>$value){
        //     $rules['document.document.*'] = 'max:2048';
        // }
       // dd($rules);
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());
            return response()->json(['error' => $error_messages]);

        }else
        {
            //dd($request->document);
                $restaurants = $this->restaurants;
                $custom = $this->custom;
                $name = $request->name;
                $email = $request->email;
                $phone = $request->phone;
                $city = $request->city;
                $tax = $request->tax?$request->tax:0;
                $area = $request->area?$request->area:"NULL";
                $discount_type = $request->discount_type;
                $target_amount = $request->target_amount;
                $offer_amount = $request->offer_amount;
                $admin_commision =  $request->admin_commision;
                $dining_count = isset($request->dining_count)?$request->dining_count:0;
                $min_dist_delivery_price = $request->min_dist_delivery_price;
                $extra_fee_deliveryamount = $request->extra_fee_deliveryamount;
                $driver_base_price = $request->driver_base_price;
                $min_dist_base_price = $request->min_dist_base_price;
                $extra_fee_amount = $request->extra_fee_amount;
                 


                $opening_time = 0;
                $closing_time = 0;
                $weekend_opening_time = 0;
                $weekend_closing_time = 0;
                $image = "";
                if($request->image)
                {
                    $image = $custom->restaurant_upload_image($request,'image');
                    //$url = 'https://s3.eu-west-2.amazonaws.com/boxfood-imageupload/';
                }
                // else
                // {
                //  $image=PROFILE_ICON;
                // }
                $packaging_charge = $request->packaging_charge?$request->packaging_charge:0;
                //$offer_percentage = $request->offer_percentage;
                if($request->shop_description)
                {
                $shop_description = $request->shop_description;
                }else
                {
                    $shop_description = "";
                }
                // $estimated_delivery_time = $request->estimated_delivery_time;
                $address = $request->address;

                if($request->id)
                {

                    $restaurants_det = $restaurants->find($request->id);
                    if(!$request->image)
                    {
                        $image = $restaurants_det->image;
                    }
                                                                
                    $restaurants_det->restaurant_name = $name;
                    $restaurants_det->image = $image;
                    $restaurants_det->email = $email;
                    $restaurants_det->org_password = $request->password;
                    $restaurants_det->password = Hash::make($request->password);
                    $restaurants_det->phone = $phone;
                    $restaurants_det->city = $city;
                    $restaurants_det->max_dining_count = $dining_count;
                    $restaurants_det->area = $area;
                    $restaurants_det->tax = $tax;
                    $restaurants_det->discount_type = $discount_type;
                    $restaurants_det->target_amount = $target_amount;
                    $restaurants_det->offer_amount = $offer_amount;
                    $restaurants_det->admin_commision = $admin_commision;
                    $restaurants_det->restaurant_delivery_charge = $request->restaurant_delivery_charge;
                    $restaurants_det->min_dist_delivery_price = $min_dist_delivery_price;
                    $restaurants_det->extra_fee_deliveryamount = $extra_fee_deliveryamount;
                    $restaurants_det->driver_commision = 0;
                    $restaurants_det->driver_base_price = $driver_base_price;
                    $restaurants_det->min_dist_base_price = $min_dist_base_price;
                    $restaurants_det->extra_fee_amount = $extra_fee_amount;
                    //$restaurants_det->discount = $offer_percentage;
                    $restaurants_det->shop_description = $shop_description;
                    $restaurants_det->is_open = 0;
                    $restaurants_det->lat = $request->latitude;
                    $restaurants_det->lng = $request->longitude;
                    // $restaurants_det->estimated_delivery_time = $estimated_delivery_time;
                    $restaurants_det->packaging_charge = $packaging_charge;
                    $restaurants_det->address = $address;
                    $restaurants_det->opening_time = $opening_time;
                    $restaurants_det->closing_time = $closing_time;
                    $restaurants_det->weekend_opening_time = $weekend_opening_time;
                    $restaurants_det->weekend_closing_time = $weekend_closing_time;
                    $restaurants_det->status = 1;
                    $restaurants_det->fssai_license = $request->fssai_license?$request->fssai_license:'0';
                    $restaurants_det->delivery_type = json_encode($request->delivery_type);
                    $restaurants_det->resturant_website = $request->resturant_website;
                    $restaurants_det->save();

                    $this->restaurant_timer->where('restaurant_id',$request->id)->delete();

                    for ($i=0; $i < count($request->weekdays['opening_time']); $i++) { 
                        # code...
                        if($request->weekdays['opening_time'][$i]!='00:00' && $request->weekdays['closing_time'][$i]!='00:00')
                        {
                            $restaurant_timer                 = new $this->restaurant_timer;
                            $restaurant_timer->restaurant_id  = $request->id;
                            $restaurant_timer->opening_time   = date("H:i:s",strtotime($request->weekdays['opening_time'][$i]));
                            $restaurant_timer->closing_time   = date("H:i:s",strtotime($request->weekdays['closing_time'][$i]));
                            $restaurant_timer->save(); 
                        }
                      
                    }
                 
                
                    for ($j=0; $j < count($request->weekenddays['opening_time']); $j++) { 
                        if($request->weekenddays['opening_time'][$j]!='00:00' && $request->weekenddays['closing_time'][$j]!='00:00')
                        {
                            $restaurant_timer1                 = new $this->restaurant_timer;
                            $restaurant_timer1->restaurant_id  = $request->id;
                            $restaurant_timer1->opening_time   = date("H:i:s",strtotime($request->weekenddays['opening_time'][$j]));
                            $restaurant_timer1->closing_time   = date("H:i:s",strtotime($request->weekenddays['closing_time'][$j]));
                            $restaurant_timer1->is_weekend     = 1; 
                            //dd($restaurant_timer1);
                            $restaurant_timer1->save();
                        }
                    }
                                
                    $cuisines = $this->cuisines->find($request->cuisines);
                    //update many to many relationship data
                    $restaurants_det->Cuisines()->sync($cuisines);

                    //data insert into document many to many
                    $sync_data=array();
                    if(!empty($request->document)){
                        foreach($request->document as $key=>$value){
                            if($_FILES['document']['name'][$key]['document']!='')
                            {
                                $expiry_date='';
                                if(isset($value['date']) && $value['date']!=null) $expiry_date=date("Y-m-d",strtotime($value['date']));

                                $filename = strtotime(date("Y-m-d")).basename($_FILES['document']['name'][$key]['document']);
                                //move_uploaded_file($_FILES["document"]["tmp_name"][$key]['document'], 'public/uploads/Restaurant Document/'.$filename);                                
                                $imageName = $_FILES["document"]["name"][$key]['document'];       
                                $imageName = self::generate_random_string().$imageName;        
                                $filePath = "uploads/restaurant_document/".$imageName;
                                $filetype = Storage::disk('s3')->getDriver()->getAdapter()->getClient()->putObject(array(
                                    'Bucket'        => env('AWS_BUCKET'),
                                    'Key'           => $filePath,
                                    'Body'          => file_get_contents($_FILES["document"]["tmp_name"][$key]['document']),
                                    'ACL'           => 'public-read',
                                    'Expires'       => '',
                                    'CacheControl'  => 'max-age'
                                ));
                                $sync_data[$key] = ['document' => $imageName,'expiry_date'=>$expiry_date];
                            }
                        }
                        $restaurants_det->Document()->sync($sync_data);
                        //dd($sync_data);
                    }
                    $restaurant_bank_details = $this->restaurant_bank_details->where('restaurant_id',$request->id)->first();
                    if(empty($restaurant_bank_details)) $restaurant_bank_details = $this->restaurant_bank_details;
                    $restaurant_bank_details->account_name = $request->account_name;
                    $restaurant_bank_details->account_address = $request->account_address;
                    $restaurant_bank_details->account_no = $request->account_no;
                    $restaurant_bank_details->bank_name = $request->bank_name;
                    $restaurant_bank_details->branch_name = $request->branch_name;
                    $restaurant_bank_details->branch_address = $request->branch_address;
                    $restaurant_bank_details->swift_code = $request->swift_code;
                    $restaurant_bank_details->routing_no = $request->routing_no;
                    $restaurants_det->RestaurantBankDetails()->save($restaurant_bank_details);
                    $msg = "update_success_msg";

                }else
                {

                    $check_email_phone = $restaurants->where('email',$request->email)->orwhere('phone',$request->phone)->first();
                    if($check_email_phone){
                        return response()->json(['error' => 'Email/Phone already exists']);
                    }
                    $restaurants->restaurant_name = $name;
                    $restaurants->image = $image;
                    $restaurants->email = $email;
                    $restaurants->org_password = $request->password;
                    $restaurants->password = Hash::make($request->password);
                    $restaurants->phone = $phone;
                    $restaurants->city = $city;
                    $restaurants->max_dining_count = $dining_count;
                    $restaurants->area = $area;
                    $restaurants->tax = $tax;
                    $restaurants->discount_type = $discount_type;
                    $restaurants->target_amount = $target_amount;
                    $restaurants->offer_amount = $offer_amount;
                    $restaurants->admin_commision = $admin_commision;
                    $restaurants->restaurant_delivery_charge = $request->restaurant_delivery_charge;
                    $restaurants->min_dist_delivery_price = $min_dist_delivery_price;
                    $restaurants->extra_fee_deliveryamount = $extra_fee_deliveryamount;
                    $restaurants->driver_commision = $request->driver_commision;
                    //$restaurants->discount = $offer_percentage;
                    $restaurants->shop_description = $shop_description;
                    $restaurants->is_open = 0;
                    // $restaurants->estimated_delivery_time = $estimated_delivery_time;
                    $restaurants->packaging_charge = $packaging_charge;
                    $restaurants->address = $address;
                    $restaurants->lat = $request->latitude;
                    $restaurants->lng = $request->longitude;
                    $restaurants->opening_time = $opening_time;
                    $restaurants->closing_time = $closing_time;
                    $restaurants->weekend_opening_time = $weekend_opening_time;
                    $restaurants->weekend_closing_time = $weekend_closing_time;
                    $restaurants->status = 1;
                    $restaurants->fssai_license = $request->fssai_license?$request->fssai_license:'0';
                    $restaurants->delivery_type = json_encode($request->delivery_type);
                    $restaurants->is_approved = 0;
                    $restaurants->resturant_website = $request->resturant_website;
                    $restaurants->save();

                   // dd($request->weekdays['closing_time']);

                    for ($i=0; $i < count($request->weekdays['opening_time']); $i++) { 
                        if($request->weekdays['opening_time'][$i]!='00:00' && $request->weekdays['closing_time'][$i]!='00:00')
                        {
                            $restaurant_timer                 = new $this->restaurant_timer;
                            $restaurant_timer->restaurant_id  = $restaurants->id;
                            $restaurant_timer->opening_time   = date("H:i:s",strtotime($request->weekdays['opening_time'][$i]));
                            $restaurant_timer->closing_time   = date("H:i:s",strtotime($request->weekdays['closing_time'][$i]));
                            $restaurant_timer->save(); 
                        }
                   }
                     
                
                    for ($j=0; $j < count($request->weekenddays['opening_time']); $j++) { 
                        if($request->weekenddays['opening_time'][$j]!='00:00' && $request->weekenddays['closing_time'][$j]!='00:00')
                        {
                            $restaurant_timer1                 = new $this->restaurant_timer;
                            $restaurant_timer1->restaurant_id  = $restaurants->id;
                            $restaurant_timer1->opening_time   = date("H:i:s",strtotime($request->weekenddays['opening_time'][$j]));
                            $restaurant_timer1->closing_time   = date("H:i:s",strtotime($request->weekenddays['closing_time'][$j]));
                            $restaurant_timer1->is_weekend     = 1; 
                            $restaurant_timer1->save();
                        }
                    }
                 
                    $cuisines = $this->cuisines->find($request->cuisines);
                    $restaurants->Cuisines()->attach($cuisines);

                    //$food_quantity = $this->document->find($request->food_quantity);
                    $sync_data=array();
                    if(!empty($request->document)){
                        foreach($request->document as $key=>$value){
                            if($_FILES['document']['name'][$key]['document']!='')
                            {
                                $expiry_date='';
                                if(isset($value['date'])) $expiry_date=date("Y-m-d",strtotime($value['date']));

                                $filename = strtotime(date("Y-m-d")).basename($_FILES['document']['name'][$key]['document']);
                               // move_uploaded_file($_FILES["document"]["tmp_name"][$key]['document'], 'public/uploads/Restaurant Document/'.$filename);
                                $imageName = $_FILES["document"]["name"][$key]['document'];       
                                $imageName = self::generate_random_string().$imageName;        
                                $filePath = "uploads/restaurant_document/".$imageName;
                                $filetype = Storage::disk('s3')->getDriver()->getAdapter()->getClient()->putObject(array(
                                    'Bucket'        => env('AWS_BUCKET'),
                                    'Key'           => $filePath,
                                    'Body'          => file_get_contents($_FILES["document"]["tmp_name"][$key]['document']),
                                    'ACL'           => 'public-read',
                                    'Expires'       => '',
                                    'CacheControl'  => 'max-age'
                                ));
                                
                                $sync_data[$key] = ['document' => $imageName,'expiry_date'=>$expiry_date];
                            }
                        }
                        //dd($sync_data);
                        $restaurants->Document()->attach($sync_data);
                    }

                    $this->restaurant_bank_details->account_name = $request->account_name;
                    $this->restaurant_bank_details->account_address = $request->account_address;
                    $this->restaurant_bank_details->account_no = $request->account_no;
                    $this->restaurant_bank_details->bank_name = $request->bank_name;
                    $this->restaurant_bank_details->branch_name = $request->branch_name;
                    $this->restaurant_bank_details->branch_address = $request->branch_address;
                    $this->restaurant_bank_details->swift_code = $request->swift_code;
                    $this->restaurant_bank_details->routing_no = $request->routing_no;
                    $restaurants->RestaurantBankDetails()->save($this->restaurant_bank_details);

                    $msg = "add_success_msg";

                    //sesnd email to user
                    if(EMAIL_ENABLE==1)
                    {
                        $restaurants->name = isset($restaurants->restaurant_name)?$restaurants->restaurant_name:"";
                        $restaurants->subject = "Welcome to ".APP_NAME;
                        // $this->send_mail($restaurants,'restaurant_welcome');
                   }
                }                  
        }
            return response()->json(['success' => 'The Restaurant is added Successfully']);
    }

    // To send email to admin for the restaurants signingup in new website

    public function restaurant_signup_request(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            array(
                'name' => 'required',
                'email' => 'required',
                'restaurant_name' => 'required',
                'phone' => 'required'
            ));

        if ($validator->fails())
        {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
        }else
        {



           //send email to Admin
            if(EMAIL_ENABLE==1)
            {
                $sender_email = 'support@gonotlob.com';
                $subject = "Restaurant Contact Request";
                $sender_name = $request->name;

                  try
               {
                    Mail::send('email.restaurant_signup', array('name'=>$request->name,'email'=>$request->email,'restaurant_name'=>$request->restaurant_name, 'phone'=>$request->phone), function($message) use($sender_email, $subject, $sender_name){
                        $message->to($sender_email, $sender_name)->subject
                            ($subject);
                        $message->from(EMAIL_USER_NAME,APP_NAME);
                    });
                }catch(\Exception $e)
               {
                   Log::error('Mail error:: ' . $e->getMessage());
               }
           }
            $response_array = array('status' => true, 'message' => 'Form submitted');
        }

        $response = response()->json($response_array, 200);
        return $response;
    }
    

    /**
    * Add the driver 
    *
    * @param object $request
    *
    * @return json $response
    */
    public function add_driver(Request $request)
    {
        $lang = isset($request->lang)?$request->lang:'en';
        if(!$request->id){
            if($request->delivery_mode==1)
            {
                //$rules['right_to_work_doc'] = 'required';
            }else
            {
//                $rules['license'] = 'required|max:2048';
                //$rules['right_to_work_doc'] = 'required';
//                $rules['registration_doc'] = 'required';
//                $rules['ctp_doc'] = 'required';
            }
            //$rules['profile_pic'] = 'required|max:2048';
            $rules['password'] = 'required';
            $rules['phone_no'] = 'required|numeric|unique:delivery_partners,phone,'.$request->id;
        }else
        {
            $rules['phone_no'] = 'required|numeric|unique:delivery_partners,phone';
        }
        if($request->delivery_mode==1)
        {
            //$rules['right_to_work_expiry_date'] = 'required';
            //$rules['right_to_work'] = 'required';
        }else
        {
           // $rules['right_to_work_expiry_date'] = 'required';
           // $rules['right_to_work'] = 'required';
//            $rules['ctp_expiry_date'] = 'required';
//            $rules['registration_expiry_date'] = 'required';
//            $rules['ctp_number'] = 'required';
//            $rules['registration_number'] = 'required';
//            $rules['license_expiry'] = 'required';
        }
//        $rules['city'] = 'required';
        //$rules['area'] = 'required';
        $rules['driver_name'] = 'required';
        $rules['email'] = 'required';
        $rules['delivery_mode'] = 'required';
//        $rules['address_line_1'] = 'required';
//        $rules['state_province'] = 'required';
//        $rules['zip_code'] = 'required';
//        $rules['country'] = 'required';

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            $error_messages = implode(',',$validator->messages()->all());
            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);

        }else
        {
//            return response()->json(['status'=>false,'message' => "Contact Admin"]);
            
            if($request->id){
                $insert1 = Deliverypartners::find($request->id);
                $msg = $this->language_string_translation('constants.update_driver_success',$lang);
            }else{
                $insert1 = new Deliverypartners();
                $insert1->password=$this->encrypt_password($request->password);
                $msg = $this->language_string_translation('constants.add_driver_success',$lang);
            }
            
//            if($request->license){
//                $license = self::base_image_upload_license($request,'license');
//                $insert1->license=$license;
//            }
            if($request->profile_pic){
                $profile_picture = self::base_image_upload_profile($request,'profile_pic');
                $insert1->profile_pic=$profile_picture;
            }
            
            if(!$request->id){
                $insert1->partner_id=$this->generate_partner_id();
            }
            $insert1->name=$request->driver_name;
            $insert1->email=$request->email;
            $insert1->phone=$request->country_code.$request->phone_no;
            $insert1->country_code=isset($request->country_code)?$request->country_code:"61";
//            $insert1->expiry_date=date("Y-m-d",strtotime($request->license_expiry));
            $insert1->status=1;
            $insert1->is_approved=0;
            $insert1->delivery_mode = $request->delivery_mode;
            $insert1->save();

            $partner_id = $insert1->id;
            
            if($request->id){
                $insert = $this->delivery_partner_details->where('delivery_partners_id',$request->id)->first();
                if(empty($insert)) $insert = $this->delivery_partner_details;
            }else{
                $insert = $this->delivery_partner_details;
            }

            $insert->delivery_partners_id=$partner_id;
//            $insert->city=$request->city;
//            $insert->area=$request->area;
            $insert->vehicle_name=$request->vehicle_no;
//            $insert->address_line_1=$request->address_line_1;
//            $insert->address_line_2=$request->address_line_2;
//            $insert->address_city=$request->address_city;
//            $insert->state_province=$request->state_province;
//            $insert->country=$request->country;
//            $insert->zip_code=$request->zip_code;
//            $insert->about=$request->about;
//            $insert->account_name=$request->account_name;
//            $insert->account_address=$request->account_address;
//            $insert->account_no=$request->account_no;
//            $insert->bank_name=$request->bank_name;
//            $insert->branch_name= $request->branch_name;
//            $insert->branch_address=$request->branch_address;
//            $insert->swift_code=$request->swift_code;
//            $insert->routing_no=$request->routing_no;

            $insert->save();


            if($request->id){
                $vehicle = $this->vehicle->where('delivery_partners_id',$request->id)->first();
                if(empty($vehicle)) $vehicle = $this->vehicle;
            }else{
                $vehicle = $this->vehicle;
            }
//            if($request->registration_doc){
//                $registration_doc = self::base_image_upload_profile($request,'registration_doc');
//                $vehicle->insurance_image=$registration_doc;
//            }
//            if($request->ctp_doc){
//                $ctp_doc = self::base_image_upload_profile($request,'ctp_doc');
//                $vehicle->rc_image=$ctp_doc;
//            }
//            if($request->right_to_work_doc){
//                $right_to_work_doc = self::base_image_upload_profile($request,'right_to_work_doc');
//                $vehicle->right_to_work_doc=$right_to_work_doc;
//            }

            $vehicle->delivery_partners_id=$partner_id;
//            $vehicle->vehicle_name=$request->registration_number;
//            $vehicle->vehicle_no=$request->ctp_number;
//            $vehicle->right_to_work_img=isset($request->right_to_work)?$request->right_to_work:"";
               
//            if($request->registration_expiry_date!='')
//                $vehicle->registration_expiry_date=date("Y-m-d",strtotime($request->registration_expiry_date));
//
//            if($request->ctp_expiry_date!='')
//                $vehicle->rc_expiry_date=date("Y-m-d",strtotime($request->ctp_expiry_date));
//
//            if($request->delivery_mode==1)
//                $vehicle->right_to_work_expiry_date= (isset($request->right_to_work_expiry_date1))?date("Y-m-d",strtotime($request->right_to_work_expiry_date1)):"0000-00-00";
//            else
//                $vehicle->right_to_work_expiry_date= (isset($request->right_to_work_expiry_date))?date("Y-m-d",strtotime($request->right_to_work_expiry_date)):"0000-00-00";
                
            $vehicle->save();

            if(!$request->id){
                //send email to user
                if(EMAIL_ENABLE==1)
                {
                    $insert1->subject = "Welcome to ".APP_NAME;
                    // $this->send_mail($insert1,'driver_welcome');
                }
            }

            $response_array = array('status' => true, 'message' => $msg);

        }

        $response = response()->json($response_array, 200);
        return $response;
    }



    /**
    *
    * base image upload license
    *
    * @param $request and $key
    */

    public function base_image_upload_license($request,$key)    
    {        
        $imageName = $request->file($key)->getClientOriginalName();       
        $ext = $request->file($key)->getClientOriginalExtension();
        $imageName = self::generate_random_string().'.'.$ext;        
            //$request->file($key)->move('public/uploads/License/',$imageName);   
        $filePath = "uploads/License";
        $filetype = Storage::disk('spaces')->putFile($filePath,$request->$key,'public');
        return $filetype;
    }



    /**
    *
    * base image upload profile
    *
    * @param $request and $key
    */


        public function base_image_upload_profile($request,$key)    
    {        
        $imageName = $request->file($key)->getClientOriginalName();       
        $ext = $request->file($key)->getClientOriginalExtension();
        $imageName = self::generate_random_string().'.'.$ext;        
        $filePath = "uploads/Profile";
        $filetype = Storage::disk('spaces')->putFile($filePath,$request->$key,'public');
        return $filetype;
    }


    /**
    *
    * get document
    *
    * @param $id
    */
    public function get_restaurant_document()
    {
        try {
            $docs = $this->document->where('document_for',2)->get();
            return response()->json(['document'=>$docs]);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


    /**
     * Add cuisines by restaurant
     * 
     * @param object $request
     * 
     * @return json $response
     */
    public function add_cuisines(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cuisine_name' => 'required|max:30',
            ]);

            if($validator->fails()) 
            {
                $error_messages = implode(',',$validator->messages()->all());
                return back()->with('error', $error_messages);
            }else
            {
                $cuisine_name = $request->cuisine_name;
                $get_data = $this->cuisines->where('name','like','%'.$cuisine_name.'%')->count();
                if($get_data>0)
                {
                    return response()->json(['status'=>false,'message'=>"Cuisines already exist"]);
                }else
                {
                    $this->cuisines->name = $cuisine_name;
                    $this->cuisines->save();
                }
                return response()->json(['status'=>true,'message'=>"Cuisines added successfully"]);
            }
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

    public function update_choices_name(Request $request)
    {
        $data = DB::table('choice')->get();
        foreach ($data as $key) {
            # code...
            $updated_choice_name = str_replace('&amp;', '&', $key->name);
            $updated_choice_name = str_replace('&#039;s', '&', $updated_choice_name);
            // $updated_choice_name = preg_replace('/\s\s+/', ' ', $updated_choice_name);
            $choice_data = DB::table('choice')->where('id',$key->id)->update(['category_name'=>$updated_choice_name]);
        }

        return response()->json(['status'=>true,'message'=>"Choices updated successfully"]);
    }

    public function checkRiderStatus(Request $request){

        $riderId = $request->header('authId');
        $deliverypartner = $this->deliverypartners->find($riderId);

        $riderStatus = 0;

        $statuses = array('7', '9', '10');
        $tripProgress = '';
        $foodRequestCount = $this->foodrequest->where('delivery_boy_id', $riderId)->whereNotIn('status', $statuses)->count();
        if ($foodRequestCount == 0) {
            $tripProgress = 'Available';
            $zoneData = Zone_geofencing::select('polygons')->where('zone_id',$deliverypartner->zone_id)->get()->toArray();
            $polygon = (!empty($zoneData))?json_decode($zoneData[0]['polygons']):[];

            $deliveryPartnerLocation = AvailableProviders::select('lng','lat')->where('provider_id',(string)$riderId)->get()->toArray();
            $riderLocation = array($deliveryPartnerLocation[0]['lng']??'', $deliveryPartnerLocation[0]['lat']??'');
            if(!empty($polygon[0])){
                $riderStatus = $this->contains($riderLocation, $polygon[0]);
            }

        }

        if($riderStatus == 0 && $tripProgress=='Available'){
            if (isset($deliverypartner->device_token) && $deliverypartner->device_token != '') {
                $data = array(
                    'device_token' => $deliverypartner->device_token,
                    'device_type' => $deliverypartner->device_type,
                    'title' => 'Zone status',
                    'message' => 'You are out of zone',
                    'delivery_type' => $deliverypartner->delivery_type
                );
                //here we send push notification to rider if he is out of zone
                $this->user_send_push_notification($data);
            }
        }
        $responseData = ['status' => true,'rider_id' => $riderId, 'is_available' => $riderStatus];
        return response()->json($responseData,200);
    }

    public function get_availbale_providers($restaurant_id,$type)
    {
        info('restaurant_id :'.$restaurant_id);
        info('type :'.$type);
        //if Type 1 is admin
        //if Type 2 is restaurant
        $availableProviderList =[];
        if($restaurant_id != 0 && $type == 2) {
            info(1);
            $deliveryType = $type;
            $deliveryPartnerIds = Deliverypartners::select('id')->where('restaurant_id',$restaurant_id)->where('delivery_type',$deliveryType)->get()->toArray();
            $providerIds = array_map(function ($ar) {return $ar['id'];}, $deliveryPartnerIds); 
            if(!empty($providerIds)){
                foreach($providerIds as $i => $providerId){
                    $availableProviderData = AvailableProviders::select('_id','lat','lng','provider_id','status','updated_at')->where('provider_id',(string)$providerId)->where('status','1')->get()->toArray();
                    $deliverypartner = $this->deliverypartners->select('id','zone_id')->find($providerId);
                    $zoneData = Zone_geofencing::select('polygons')->where('zone_id',$deliverypartner->zone_id)->get()->toArray();
                    $is_avail = 0;
                    if(!empty($zoneData[0]['polygons'])){
                        $polygon = json_decode($zoneData[0]['polygons']);
                        $driverLocation = array($availableProviderData[0]['lng']??'', $availableProviderData[0]['lat']??'');
                        $is_avail = $this->contains($driverLocation, $polygon[0]);
                    }
                
                    if(!empty($availableProviderData) && $is_avail ==1){
                        $availableProviderList[$i]['_id'] = $availableProviderData[0]['_id'];
                        $availableProviderList[$i]['lat'] = $availableProviderData[0]['lat'];
                        $availableProviderList[$i]['lng'] = $availableProviderData[0]['lng'];
                        $availableProviderList[$i]['provider_id'] = $availableProviderData[0]['provider_id'];
                        $availableProviderList[$i]['status'] = $availableProviderData[0]['status'];
                        $availableProviderList[$i]['updated_at'] = $availableProviderData[0]['updated_at'];
                    }
                }
            }
        }elseif($restaurant_id != 0 && $type == 1) {
            info(2);
            $restaurant = $this->restaurants->where('id',$restaurant_id)->where('status', 1)->select('id','lat','lng')->first();
            $pointLocation = new pointLocation();
            $get_zone = $pointLocation->getZone($restaurant->lng, $restaurant->lat);
            if(!empty($get_zone)) {
                $currentAvailableProviderList = AvailableProviders::where('status','1')->get();
                foreach($currentAvailableProviderList as $i => $provider) {
                    $checkRider = $this->deliverypartners->where('id',$provider->provider_id)->where('delivery_type',$type)->where('zone_id',$get_zone->id)->first();
                    if(!empty($checkRider)) {
                        if(!empty($get_zone->polygons)){
                            $polygon = json_decode($get_zone->polygons);
                            $ponits = array($provider->lng, $provider->lat);
                            $pointLocation = new pointLocation();
                            $is_avail = $pointLocation->pointInPolygon($ponits, $polygon[0][0]);
                            if($is_avail == 1) {
                                $availableProviderList[$i]['_id'] = $provider->_id;
                                $availableProviderList[$i]['lat'] = $provider->lat;
                                $availableProviderList[$i]['lng'] = $provider->lng;
                                $availableProviderList[$i]['provider_id'] = $provider->provider_id;
                                $availableProviderList[$i]['status'] = $provider->status;
                                $availableProviderList[$i]['updated_at'] = $provider->updated_at;
                            }
                        }
                    }
                }
            }
        }else {
            info(3);
            $availableProviderList = AvailableProviders::where('status','1')->get();
        }
        return response()->json($availableProviderList, 200);
    }
    public function get_available_riders($type){
        info('show_all_riders');
        $availableProviderList = AvailableProviders::where('status',$type)->get();
        return response()->json($availableProviderList, 200);
    }
    public function get_provider($id)
    {
        $provider = AvailableProviders::where('provider_id',$id)->first();
        if($provider)
        {
            $response_array =  $provider;
        }
        else
        {
            $response_array = null;
        }
        return response()->json($response_array, 200);
    }
    public function getRestaurant($id)
    {
        $restaurants = $this->restaurants;
        $restaurantDetail = $restaurants::where('id',$id)->first();
        return response()->json(['status'=>true,'message'=>" successfully",'name' => $restaurantDetail->restaurant_name]);
    }

}
