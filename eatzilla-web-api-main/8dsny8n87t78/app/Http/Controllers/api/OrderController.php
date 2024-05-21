<?php

namespace App\Http\Controllers\api;
                                    
use App\Http\Requests\Api\CancelOrderByUserRequest;
use App\Http\Requests\Api\CheckPromocodeRequest;
use App\Http\Requests\Api\UserOrderRatingsRequest;
use App\Service\MultiOrderAssign;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use App\Jobs\OrderCompleteMail;
use App\Model\CurrentRequest;
use App\Model\NewRequest;
use App\Service\queueDriverAssign;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use DB;
use Carbon\Carbon;
use Log;
use DateTime;
use Exception;
use App\Model\PayoutSetting;
use stdClass;
use App\Model\DeliveryInstruction;
use App\Jobs\BroadcastDelayJob;
use App\Model\OrderRejectedDriver;
use App\Base\Helpers\ExceptionHandlerModel;

class OrderController extends BaseController
{
	// public function get_address_detail(Request $request)
	// {

	// 	  $validator = Validator::make(
 //                $request->all(),
 //                array(
 //                    'request_id' => 'required'
 //                ));

 //        if ($validator->fails())
 //        {
 //            $error_messages = implode(',', $validator->messages()->all());
 //            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
 //        }else
 //        {
	// 	$request_id = $request->request_id;

	// 	$request_detail = DB::table('requests')->where('id',$request_id)->first();

	// 	$order_id = $request_detail->order_id;

	// 	$ordered_time = $request_detail->ordered_time;

	// 	$restaurant_detail = $this->restaurants::where('id',$request_detail->restaurant_id)->first();

	// 	$user_detail = $this->users::where('id',$request_detail->user_id)->first();

	// 	$address_detail = array();

	// 	$request_status = $request_detail->status;

	// 	$address_detail [] = array(
	// 		'd_address'=>$request_detail->delivery_address,
	// 		's_address'=>$restaurant_detail->address,
	// 		'd_lat'=>$request_detail->d_lat,
	// 		'd_lng'=>$request_detail->d_lng,
	// 		's_lat'=>$restaurant_detail->lat,
	// 		's_lng'=>$restaurant_detail->lng
	// 	);

	// 	$food_detail = array();
	// 	$bill_detail = array();
	// 	$data = $this->requestdetail->where('request_id',$request_id)->get();

	// 			foreach($data as $d)
	// 			{
	// 				$add_ons=array();
	// 					if(!empty($d->Addons))
	// 					{
	// 						foreach($d->Addons as $addon){
	// 							$add_ons[] = array(
	// 								'id' => $addon->id,
	// 								'restaurant_id' => $addon->restaurant_id,
	// 								'name' =>$addon->name,
	// 								'price' => $addon->price,
	// 								'created_at' => date("Y-m-d H:i:s",strtotime($addon->created_at)),
	// 								'updated_at' => date("Y-m-d H:i:s",strtotime($addon->updated_at)),
	// 							);
	// 						}
	// 					}
	// 				$food_quantity=array();
	// 					if(!empty($d->FoodQuantity))
	// 					{
	// 						//foreach($list->FoodQuantity as $qty){
	// 							$food_quantity[] = array(
	// 								'id' => isset($d->FoodQuantity->id)?$d->FoodQuantity->id:"",
	// 								'name' => $d->FoodQuantity->name?$d->FoodQuantity->name:"",
	// 								'price' => $d->food_quantity_price,
	// 								'created_at' => isset($d->FoodQuantity->created_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->created_at)):"",
	// 								'updated_at' => isset($d->FoodQuantity->updated_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->updated_at)):"",
	// 							);
	// 						//}
	// 					}
	// 				$food_detail[] = array(
	// 					'name'=>(!empty($d->Foodlist)?$d->Foodlist->name:""),
	// 					'quantity'=>$d->quantity,
	// 					'price'=>$d->quantity * $d->price_per_quantity,
	// 					'is_veg'=>(!empty($d->Foodlist)?$d->Foodlist->is_veg:""),
	// 					'food_size'=>$food_quantity,
	//                 	'add_ons' => $add_ons
	// 				);
	// 			}

	// 	$bill_detail[] = array(
	// 		'item_total'=>$request_detail->item_total,
	// 		'offer_discount'=>$request_detail->offer_discount,
	// 		'loyalty_discount'=>$request_detail->loyalty_discount,
	// 		// 'driver_tip'=>$request_detail->driver_tip,
	// 		'restaurant_discount'=>$request_detail->restaurant_discount,
	// 		'packaging_charge'=>$request_detail->restaurant_packaging_charge,
	// 		'tax'=>$request_detail->tax,
	// 		'delivery_charge'=>$request_detail->delivery_charge,
	// 		'bill_amount'=>$request_detail->bill_amount,
	// 		'paid_type' => $request_detail->paid_type
	// 	);

	// 	$response_array = array('status'=>true,'request_id'=>$request_id,'ordered_time'=>$ordered_time,'order_id'=>$order_id,'restaurant_detail'=>$restaurant_detail,'user_detail'=>$user_detail,'address_detail'=>$address_detail,'bill_detail'=>$bill_detail,'food_detail'=>$food_detail,'request_status'=>$request_status);
	// 	}

	// 	 $response = response()->json($response_array, 200);
 //        return $response;
	// }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
	public function get_address_detail(Request $request)
	{
        try {
            $validator = Validator::make(
                    $request->all(),
                    array(
                        'request_id' => 'required'
                    ));

            if ($validator->fails())
            {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            }else
            {
                $request_id = $request->request_id;
                $request_detail = $this->foodrequest->where('id',$request_id)->first();
                $order_id = $request_detail->order_id;
                $ordered_time = $request_detail->ordered_time;
                $restaurant_detail = $this->restaurants->where('id',$request_detail->restaurant_id)->first();
                $restaurant_detail->image = SPACES_BASE_URL.$restaurant_detail->image;
                $user_detail = $this->users->where('id',$request_detail->user_id)->first();
                $address_detail = array();
                $request_status = $request_detail->status;
                $address_detail [] = array(
                    'd_address'=>$request_detail->delivery_address,
                    's_address'=>$restaurant_detail->address,
                    'd_lat'=>$request_detail->d_lat,
                    'd_lng'=>$request_detail->d_lng,
                    's_lat'=>$restaurant_detail->lat,
                    's_lng'=>$restaurant_detail->lng
                );
                $food_detail = array();
                $bill_detail = array();
                $data = $this->requestdetail->where('request_id',$request_id)->get();
                foreach($data as $d)
                {
                    $price = ($d->food_quantity==0)?$d->price_per_quantity:$d->food_quantity_price;
                    $add_ons=array();
                    if(!empty($d->RequestdetailAddons)){
                        foreach($d->RequestdetailAddons as $addon){
                            $add_ons[] = array(
                                'id' => $addon->id,
                                'restaurant_id' => isset($request_detail->restaurant_id)?$request_detail->restaurant_id:"",
                                'name' => isset($addon->name)?$addon->name:"",
                                'name_ar' => isset($addon->name_ar)?$addon->name_ar:"",
                                'name_kur' => isset($addon->name_kur)?$addon->name_kur:"",
                                'price' => $addon->price,
                                'created_at' => date("Y-m-d H:i:s",strtotime($addon->created_at)),
                                'updated_at' => date("Y-m-d H:i:s",strtotime($addon->updated_at)),
                            );
                        }
                    }
                    $food_quantity=array();
                    if(!empty($d->FoodQuantity)){
                        $quantity_price = $d->food_quantity_price;
                            $food_quantity[] = array(
                                'id' => isset($d->FoodQuantity->id)?$d->FoodQuantity->id:'',
                                'name' => (isset($d->FoodQuantity->name)?$d->FoodQuantity->name:''),
                                'price' => $quantity_price,
                                'status' => isset($d->FoodQuantity->status)?$d->FoodQuantity->status:0,
                                'created_at' => isset($d->FoodQuantity->created_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->created_at)):'',
                                'updated_at' => isset($d->FoodQuantity->updated_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->updated_at)):'',
                            );
                    }
                    if(isset($d->FoodQuantity)) $d->FoodQuantity->price = $d->food_quantity_price;
                    $food_detail[] = array(
                        'food_id'=>(!empty($d->Foodlist)?$d->Foodlist->id:""),
                        'food_name'=>(!empty($d->Foodlist->name)?$d->Foodlist->name:""),
                        'food_name_ar'=>(!empty($d->Foodlist->name_ar)?$d->Foodlist->name_ar:""),
                        'food_name_kur'=>(!empty($d->Foodlist->name_kur)?$d->Foodlist->name_kur:""),
                        'food_quantity'=>$d->quantity,
                        'tax' => (!empty($d->Foodlist)?$d->Foodlist->tax:""),
                        'item_price'=>(!empty($d->Foodlist)?$d->Foodlist->price:0) * $d->quantity,
                        'is_veg'=>(!empty($d->Foodlist)?$d->Foodlist->is_veg:""),
                        'food_size'=>(!empty($d->FoodQuantity)?$food_quantity:$d->FoodQuantity),
                        'add_ons' => $add_ons,
                    );
                }
                $bill_detail[] = array(
                    'item_total'=>$request_detail->item_total,
                    'offer_discount'=>$request_detail->offer_discount,
                    'loyalty_discount'=>$request_detail->loyalty_discount,
                    'restaurant_discount'=>$request_detail->restaurant_discount,
                    'packaging_charge'=>$request_detail->restaurant_packaging_charge,
                    'driver_tip'=>$request_detail->driver_tip,
                    'tax'=>$request_detail->tax,
                    'delivery_charge'=>$request_detail->delivery_charge,
                    'bill_amount'=>$request_detail->bill_amount,
                    'deducted_bill_amount'=>$request_detail->deducted_bill_amount,
                    'paid_type' => $request_detail->paid_type
                );
                $instruction_id = !empty($request_detail->instruction_id)?json_decode($request_detail->instruction_id):[];
                $instruction_list = DeliveryInstruction::withTrashed()->whereIn('id',$instruction_id)->get();
                $instruction_list = !empty($instruction_list)?$instruction_list:[];
                $response_array = array('status'=>true,'request_id'=>$request_id,'ordered_time'=>$ordered_time,'order_id'=>$order_id,'restaurant_detail'=>$restaurant_detail,'user_detail'=>$user_detail,'address_detail'=>$address_detail,'bill_detail'=>$bill_detail,'food_detail'=>$food_detail,'request_status'=>$request_status,'is_ondemand_delivery'=>$request_detail->ondemanddelivery,'delivery_instruction'=>$instruction_list,'image_base_url'=>SPACES_BASE_URL);
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
        
	}


	// 	public function get_order_status(Request $request)
	// {

	// 	// $request_id = $request->request_id;

	// 	$delivery_boy_id = $request->header('authId');

	// 	$request_detail = DB::table('requests')->where('delivery_boy_id',$delivery_boy_id)
	// 										   ->where('status','!=',10)
	// 										   ->where('status','!=',7)
	// 										   ->first();

	// 	if(count($request_detail)!=0)
	// 	{
	// 			$order_id = $request_detail->order_id;

	// 			$request_id = $request_detail->id;

	// 			$ordered_time = $request_detail->ordered_time;

	// 			$restaurant_detail = $this->restaurants::where('id',$request_detail->restaurant_id)->first();
	// 			if(isset($restaurant_detail->image)) $restaurant_detail->image = SPACES_BASE_URL.$restaurant_detail->image;
		


	// 				$user_detail = DB::table('users')->where('id',$request_detail->user_id)->first();

	// 				$address_detail = array();

	// 				$request_status = $request_detail->status;

	// 				$address_detail [] = array(
	// 					'd_address'=>$request_detail->delivery_address,
	// 					's_address'=>$restaurant_detail->address,
	// 					'd_lat'=>$request_detail->d_lat,
	// 					'd_lng'=>$request_detail->d_lng,
	// 					's_lat'=>$restaurant_detail->lat,
	// 					's_lng'=>$restaurant_detail->lng
	// 				);

	// 				$food_detail = array();
	// 				$bill_detail = array();

	// 				// $data = DB::table('request_detail')->where('request_detail.request_id',$request_id)
	// 				// 									->join('food_list','food_list.id','=','request_detail.food_id')
	// 				// 									->select('request_detail.quantity as quantity','food_list.name as food','food_list.price as price_per_quantity','food_list.is_veg as is_veg')
	// 				// 									->get();

	// 				$data = $this->requestdetail->where('request_id',$request_id)->get();

	// 				// 		foreach($data as $d)
	// 				// 		{
	// 				// 			$food_detail[] = array(
	// 				// 				'name'=>$d->food,
	// 				// 				'quantity'=>$d->quantity,
	// 				// 				'price'=>$d->quantity * $d->price_per_quantity,
	// 				// 				'is_veg'=>$d->is_veg
	// 				// 			);
	// 				// 		}

	// 				// $bill_detail[] = array(
	// 				// 	'item_total'=>$request_detail->item_total,
	// 				// 	'offer_discount'=>$request_detail->offer_discount,
	// 				// 	'packaging_charge'=>$request_detail->restaurant_packaging_charge,
	// 				// 	'tax'=>$request_detail->tax,
	// 				// 	'delivery_charge'=>$request_detail->delivery_charge,
	// 				// 	'bill_amount'=>$request_detail->bill_amount,
	// 				// 	'paid_type' => $request_detail->paid_type
	// 				// );

	// 				// $response_array = array('status'=>true,'request_id'=>$request_id,'ordered_time'=>$ordered_time,'order_id'=>$order_id,'restaurant_detail'=>$restaurant_detail,'user_detail'=>$user_detail,'address_detail'=>$address_detail,'bill_detail'=>$bill_detail,'food_detail'=>$food_detail,'request_status'=>$request_status,'assigned_time'=>$request_detail->updated_at, 'notification_time'=>NOTIFICATION_TIME);

	// 					foreach($data as $d)
	// 				{
	// 					$add_ons=array();
	// 					if(!empty($d->Addons))
	// 					{
	// 						foreach($d->Addons as $addon){
	// 							$add_ons[] = array(
	// 								'id' => $addon->id,
	// 								'restaurant_id' => $addon->restaurant_id,
	// 								'name' => ($this->lang=='ar')?$addon->name_arabic:$addon->name,
	// 								'price' => $addon->price,
	// 								'created_at' => date("Y-m-d H:i:s",strtotime($addon->created_at)),
	// 								'updated_at' => date("Y-m-d H:i:s",strtotime($addon->updated_at)),
	// 							);
	// 						}
	// 					}
	// 					$food_quantity=array();
	// 					if(!empty($d->FoodQuantity))
	// 					{
	// 						//foreach($list->FoodQuantity as $qty){
	// 							$food_quantity[] = array(
	// 								'id' => isset($d->FoodQuantity->id)?$d->FoodQuantity->id:"",
	// 								'name' => $d->FoodQuantity->name?$d->FoodQuantity->name:"",
	// 								'pivot' => array('price'=>isset($d->FoodQuantity->pivot->price)?$d->FoodQuantity->pivot->price:$d->food_quantity_price,'is_default'=>isset($d->FoodQuantity->pivot->is_default)?$d->FoodQuantity->pivot->is_default:""),
	// 								'price' => $d->food_quantity_price,
	// 								'created_at' => isset($d->FoodQuantity->created_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->created_at)):"",
	// 								'updated_at' => isset($d->FoodQuantity->updated_at)?date("Y-m-d H:i:s",strtotime($d->FoodQuantity->updated_at)):"",
	// 							);
	// 						//}
	// 					}
	// 					$food_detail[] = array(
	// 						'name'=>$d->Foodlist->name?$d->Foodlist->name:"",
	// 						'quantity'=>$d->quantity,
	// 						'price'=>(!empty($d->Foodlist)?$d->Foodlist->price:0) * $d->quantity,
	// 						'is_veg'=>(!empty($d->Foodlist)?$d->Foodlist->is_veg:""),
	// 						'food_size'=>$food_quantity,
 //                    		'add_ons' => $add_ons
	// 					);
	// 				}

	// 				$bill_detail[] = array(
	// 					'item_total'=>$request_detail->item_total,
	// 					'offer_discount'=>$request_detail->offer_discount,
	// 					'packaging_charge'=>$request_detail->restaurant_packaging_charge,
	// 					'tax'=>$request_detail->tax,
	// 					'delivery_charge'=>$request_detail->delivery_charge,
	// 					'bill_amount'=>$request_detail->bill_amount,
	// 					'payment_type'=>$request_detail->paid_type
	// 				);

	// 				$response_array = array('status'=>true,'request_id'=>$request_id,'ordered_time'=>$ordered_time,'order_id'=>$order_id,'restaurant_detail'=>$restaurant_detail,'user_detail'=>$user_detail,'address_detail'=>$address_detail,'bill_detail'=>$bill_detail,'food_detail'=>$food_detail,'request_status'=>$request_status,'assigned_time'=>$request_detail->updated_at, 'notification_time'=>NOTIFICATION_TIME);

	// 	}else
	// 	{
	// 		$response_array = array('status'=>false,'message'=>'No orders available');
	// 	}

	// 	 $response = response()->json($response_array, 200);
 //        return $response;
	// }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_order_status(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $delivery_boy_id = $request->header('authId');
            $request_detail = $this->foodrequest->where('delivery_boy_id', $delivery_boy_id)
                ->whereNotIn('status', [10, 7, 0])
                ->first();
            if (count($request_detail) == 0) {
                $check_request_detail = $this->foodrequest->whereNotIn('status', [10, 7, 0])->get();
                foreach ($check_request_detail as $check_temp_driver) {
                    $temp_drivers_list = explode(",", $check_temp_driver->temp_drivers);
                    if (in_array($delivery_boy_id, $temp_drivers_list)) {
                        $current_request_id = $check_temp_driver->id;
                        $request_detail = $this->foodrequest->where('id', $current_request_id)
                            ->first();
                        break;
                    }
                }
            }
            if (count($request_detail) != 0) {
                $order_id = $request_detail->order_id;
                $request_id = $request_detail->id;
                $ordered_time = $request_detail->ordered_time;
                $restaurant_detail = $this->restaurants->where('id', $request_detail->restaurant_id)->first();
                if (isset($restaurant_detail->image)) $restaurant_detail->image = SPACES_BASE_URL . $restaurant_detail->image;
                $user_detail = $this->users->where('id', $request_detail->user_id)->first();
                $address_detail = array();
                $request_status = $request_detail->status;
                $is_accepted_by_driver = $request_detail->is_confirmed;
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
                    'item_total' => $request_detail->item_total,
                    'offer_discount' => $request_detail->offer_discount,
                    'packaging_charge' => $request_detail->restaurant_packaging_charge,
                    'tax' => $request_detail->tax,
                    'delivery_charge' => $request_detail->delivery_charge,
                    'bill_amount' => $request_detail->bill_amount,
                    'paid_type' => $request_detail->paid_type
                );
                $response_array = array('status' => true, 'request_id' => $request_id, 'ordered_time' => $ordered_time, 'order_id' => $order_id, 'restaurant_detail' => $restaurant_detail, 'user_detail' => $user_detail, 'address_detail' => $address_detail, 'bill_detail' => $bill_detail, 'food_detail' => $food_detail, 'request_status' => $request_status, 'assigned_time' => $request_detail->updated_at, 'notification_time' => NOTIFICATION_TIME, 'is_accepted_by_driver' => $is_accepted_by_driver, 'is_ondemand_delivery' => $request_detail->ondemanddelivery, 'is_driver_reached_restaurant' => $request_detail->is_driver_reached_restaurant);
            } else {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.no_order_avail',$lang));
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
    public function update_request(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'request_id' => 'required',
                    'status' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang) ? $request->lang : 'en';
                log::info('langnew   ::'  . $lang);
                $message_lang = "";
                $request_id = $request->request_id;
                $providerId = CurrentRequest::where('request_id',(string)$request_id)->first();
                $provider_id = $providerId->provider_id;
                $status = $request->status;
                $trackorderstatus = $this->trackorderstatus;
                $deliverypartners = $this->deliverypartners;
                $client = new Client();
                $partner_id = $request->header('authId');
                $request_detail = $this->foodrequest->find($request_id);
                $this->foodrequest->where('id', $request_id)->update(['status' => $status]);

                /*

                        Started towards restaurant -> 3 -> (On the way)
                        Reached restaurant -> 4 -> (Food received)
                        Started towards Customer -> 5 -> (On the way)
                        Food delivered -> 6 -> (delivered)
                        cash received - >  7 ->(order completed)
                        cancelled - > 10 -> (Order canceled)
                */
                $current_date_time = Carbon::now();

                if ($status == 3) {
                    if ($request_detail->delivery_boy_id == 0 || $request_detail->delivery_boy_id == 99) //99 is notlob delivery
                    {   
                        //making temp driver null when driver accept the order
                        $request_detail->delivery_boy_id = $partner_id;
                        $request_detail->temp_drivers = NULL;
                        $request_detail->save();
                    } elseif ($request_detail->delivery_boy_id != 0 && $request_detail->temp_drivers == NULL) {
                        $request_detail->delivery_boy_id = $partner_id;
                        $request_detail->save();
                    } else {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.request_already_accepted_another_rider', $lang));
                        return response()->json($response_array, 200);
                    }


                    $this->foodrequest->where('id', $request_id)->update(['is_confirmed' => 1]);
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $partner_id, 'request_id' => $request_id, 'description' => "Approved Order",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    $message = "Your food is prepared. Our Rider is on its way";
                    $message_lang = $this->language_string_translation('constants.your_food_is_prepared_our_rider_its_way', $lang);
                    $admin_driver_list_data = $this->deliverypartners->where('restaurant_id', 0)->get();
                    foreach ($admin_driver_list_data as $driver_detail) {
                        if ($driver_detail->id != $partner_id) {
                            $temp_driver = $driver_detail->id;
                            // $header = array();
                            // $header[] = 'Content-Type: application/json';
                            // $postdata = array();
                            // $postdata['request_id'] = $request_id;
                            // $postdata['user_id'] = $request_detail->user_id;
                            // $postdata['status'] = 1;
                            // $postdata = json_encode($postdata);

                            // $ch = curl_init(FIREBASE_URL . "/new_request/$temp_driver/$request_id.json");
                            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                            // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                            // curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                            // $result = curl_exec($ch);
                            // curl_close($ch);

                            NewRequest::where('request_id',$request_id)->where('provider_id',$temp_driver)->delete();
                        }
                    }
                    // MultiOrderAssign::request_driver_commission_comparison($request_detail->delivery_boy_id, $request_id, $request_detail->distance);

                    $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['status' => $status]); 

                
                    
                    if($request->r_lat && $request->r_lng){
                        
                        //get rider location from request
                        $riderLocation = $request->r_lat . ',' . $request->r_lng;

                        //get distance from restaurant to delivery location
                        $distanceRestaurantToDeliveryLocation = $request_detail->distance;

                        //get restaurant location from restaurants table
                        $restaurantDetail = $this->restaurants->find($request_detail->restaurant_id);
                        $restaurantLocation = $restaurantDetail->lat . ',' . $restaurantDetail->lng;

                        //calculate distance between driver location and restaurant location
                        $getdistance = $this->getGoogleDistance($riderLocation, $restaurantLocation,1);
                        $distanceRiderToRestaurant = $getdistance[0];

                        //calculate total distance from driver location to delivery location through restaurant
                        $distance = $distanceRiderToRestaurant + $distanceRestaurantToDeliveryLocation;

                        $this->foodrequest->where('id', $request_id)->update(['distance' => $distance]);
                    }

                    //based on delivery type, deducted admin commission from bill amount
                    //admin delivery_type =1 , restaurant delivery_type =2
                    // $billAmount = $request_detail->bill_amount;
                    // $restaurantData = $this->restaurants->find($request_detail->restaurant_id);
                    // $deliveryPartner = $this->deliverypartners->find($partner_id);
                    // $deductedAmount = ($deliveryPartner->delivery_type==1) ? $billAmount * ($restaurantData->admin_commision/100) : 0;
                    // $deductedAdminCommission = $billAmount - $deductedAmount ;
                    // $finalBillAmount = ($deliveryPartner->delivery_type==1) ? $deductedAdminCommission : $billAmount;
                    // $this->foodrequest->where('id', $request_id)->update(['bill_amount' => $finalBillAmount,'deducted_bill_amount' => $deductedAmount]);

                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);

                } elseif ($status == 4) {
                    $this->foodrequest->where('id', $request_id)->update(['is_driver_reached_restaurant' => 1]);
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $partner_id, 'request_id' => $request_id, 'description' => "Reached Restaurant",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    $message = "Rider Reached restaurant";
                    $message_lang = $this->language_string_translation('constants.rider_reached_restaurant',$lang);
                    $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['status' => $status]); 

                    $this->foodrequest->where('id', $request_id)->update(['rider_reached_to_restaurant' => $current_date_time->toDateTimeString()]);

                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                } elseif ($status == 5) {
                    $check_restaurant_accepted = $this->foodrequest->where('id', $request_id)->first();
                    if ($check_restaurant_accepted->status == 0) {
                        $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.restaurant_not_yet_accepted_order',$lang), 'is_restaurant_accepted' => 0);
                        return response()->json($response_array, 200);
                    }
                    $this->foodrequest->where('id', $request_id)->update(['status' => $status, 'is_confirmed' => 1]);
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $partner_id, 'request_id' => $request_id, 'description' => "Started towards Customer",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    $message = "Started towards Customer";
                    $message_lang = $this->language_string_translation('constants.started_towards_customer',$lang);
                    $currentRequest = CurrentRequest::where('request_id',$request_id)
                            ->update(['status' => $status]); 

                
                    $getMinutesFromWaitingTime = $current_date_time->diffInMinutes($check_restaurant_accepted->rider_reached_to_restaurant);

                    $waitingTimesInRestaurant = 0; // minutes
                    if ($getMinutesFromWaitingTime >= 3) {
                        $waitingTimesInRestaurant = $getMinutesFromWaitingTime - 3;
                    }
                    $this->foodrequest->where('id', $request_id)->update(['rider_started_from_restaurant' => $current_date_time->toDateTimeString(),'waiting_time' => $waitingTimesInRestaurant]);
                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                }elseif ($status == 8){
                    $this->foodrequest->where('id', $request_id)->update(['status' => $status, 'is_confirmed' => 1]);
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $partner_id, 'request_id' => $request_id, 'description' => "Reached Customer Location",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    $message = "Reached Customer Location";
                    $message_lang = $this->language_string_translation('constants.reached_customer_location',$lang);
                    CurrentRequest::where('request_id',$request_id)
                            ->update(['status' => $status]);
                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                }

                // Temporarily Hide Food Delivered status(6)
                
                // elseif ($status == 6) {
                //     $driver_commission = 0;
                //     $payout_setting = PayoutSetting::where('id',1)->first();
                //     if(!empty($payout_setting)){
                //         //delivery boy commission calculation
                //         $driver_travelled_distance = $request_detail->distance;                    
                //         $driver_waiting_time = $request_detail->waiting_time;
                //         $extra_distance_amount = ($driver_travelled_distance-3)*$payout_setting->rider_payout_remaining_each_KM_charge;
                //         $waiting_charge = $driver_waiting_time*$payout_setting->rider_payout_waiting_time_charge;                    
                //         $driver_commission = $payout_setting->rider_payout_first_2_KM_charge + $extra_distance_amount + $waiting_charge;
                //         //updating driver commission amount in delivery boy table 
                //         $partner_detail = $deliverypartners::where('id', $partner_id)->first();
                //         $partner_earnings = $partner_detail->total_earnings + $driver_commission;
                //         $partner_detail->total_earnings = $partner_earnings;
                //         $partner_detail->pending_payout = $partner_detail->pending_payout + $driver_commission;
                //         $partner_detail->save();
                //     }

                //     $this->foodrequest->where('id', $request_id)->update(['status' => $status, 'is_confirmed' => 1, 'delivery_boy_commision'=>$driver_commission]);
                //     $message = "Food delivered";
                //     $message_lang = $this->language_string_translation('constants.food_delivered',$lang);                

                //     //commission update in admin
                //     if($request_detail->order_accepted_type != 1) {
                //         $admin_commision = $request_detail->admin_commision + $request_detail->delivery_charge;
                //     }else {
                //         $admin_commision = $request_detail->admin_commision;
                //     }
                //     $this->admin->find(1)->increment('total_earnings', $admin_commision);

                //     //earnings update in restaurant
                //     $all_discounts = $request_detail->restaurant_discount + $request_detail->offer_discount;
                //     $item_total = $request_detail->item_total - $all_discounts;
                //     if($request_detail->order_accepted_type == 1) {
                //         $total_value = $item_total + $request_detail->restaurant_packaging_charge + $request_detail->delivery_charge;
                //     }else {
                //         $total_value = $item_total + $request_detail->restaurant_packaging_charge;
                //     }
                //     $restaurant_payout = $total_value - $request_detail->admin_commision;
                //     $this->foodrequest->where('id', $request_id)->update(['restaurant_commision' => $restaurant_payout]);
                //     $this->restaurants->find($request_detail->restaurant_id)->increment('total_earnings', $restaurant_payout);
                //     $this->restaurants->find($request_detail->restaurant_id)->increment('pending_payout', $restaurant_payout);
                //     $currentRequest = CurrentRequest::where('request_id',$request_id)
                //             ->update(['status' => $status]); 
                //     $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                // }
                
                if ($status == 7) {
                    $this->foodrequest->where('id', $request_id)->update(['is_paid' => 1, 'temp_drivers' => 0, 'status' => $status, 'is_confirmed' => 1, 'delivered_time'=>date('Y-m-d H:i:s')]);
                    $message = "Order Completed";
                    $title = $this->language_string_translation('constants.common_title','en');
                    log::info('title  ::' . $title);
                    $message_lang = $this->language_string_translation('constants.order_completed',$lang);
                    // delete request to driver
                    $temp_driver = $request_detail->delivery_boy_id;
                    NewRequest::where('request_id',(string)$request_id)->where('provider_id',(string)$temp_driver)->delete();

                    $driver_commission = 0;
                    $payout_setting = PayoutSetting::where('id',1)->first();
                    if(!empty($payout_setting)){
                        //delivery boy commission calculation
                        $driver_travelled_distance = $request_detail->distance;                    
                        $driver_waiting_time = $request_detail->waiting_time;
                        if($driver_travelled_distance > 3) {
                            $extra_distance_amount = ($driver_travelled_distance-3) * $payout_setting->rider_payout_remaining_each_KM_charge;
                        }else {
                            $extra_distance_amount = 0;
                        }
                        $waiting_charge = $driver_waiting_time * $payout_setting->rider_payout_waiting_time_charge;  
                        $driver_commission = $payout_setting->rider_payout_first_2_KM_charge + $extra_distance_amount + $waiting_charge;
                        //updating driver commission amount in delivery boy table 
                        $partner_detail = $deliverypartners::where('id', $partner_id)->first();
                        $partner_earnings = $partner_detail->total_earnings + $driver_commission;
                        $partner_detail->total_earnings = $partner_earnings;
                        $partner_detail->pending_payout = $partner_detail->pending_payout + $driver_commission;
                        $partner_detail->save();
                    }

                    $this->foodrequest->where('id', $request_id)->update(['status' => $status, 'is_confirmed' => 1, 'delivery_boy_commision'=>$driver_commission]);
                    //commission update in admin
                    if($request_detail->order_accepted_type != 1) {
                        $admin_commision = $request_detail->admin_commision + $request_detail->delivery_charge;
                    }else {
                        $admin_commision = $request_detail->admin_commision;
                    }
                    $this->admin->find(1)->increment('total_earnings', $admin_commision);

                    //earnings update in restaurant
                    $all_discounts = $request_detail->restaurant_discount + $request_detail->offer_discount;
                    $item_total = $request_detail->item_total - $all_discounts;
                    if($request_detail->order_accepted_type == 1) {
                        $total_value = $item_total + $request_detail->restaurant_packaging_charge + $request_detail->delivery_charge;
                    }else {
                        $total_value = $item_total + $request_detail->restaurant_packaging_charge;
                    }
                    $restaurant_payout = $total_value - $request_detail->admin_commision;
                    $this->foodrequest->where('id', $request_id)->update(['restaurant_commision' => $restaurant_payout]);
                    $this->restaurants->find($request_detail->restaurant_id)->increment('total_earnings', $restaurant_payout);
                    $this->restaurants->find($request_detail->restaurant_id)->increment('pending_payout', $restaurant_payout);

                    $data = array(
                        'device_token' => $request_detail->Users->device_token,
                        'device_type' => $request_detail->Users->device_type,
                        'title' => $title,
                        'message' => $message,
                        'request_id' => $request_id,
                        'delivery_type' => $request_detail->delivery_type
                    );
                    $this->user_send_push_notification($data);
                    $this->delivery_partner_log->insert(['delivery_partner_id' => $partner_id, 'request_id' => $request_id, 'description' => "Order Delivered",'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')]);
                    CurrentRequest::where('request_id',$request_id)
                            ->update(['status' => $status]); 
                    $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
                    
                    // Order Completed mail
                    $requestCompleted = $request_detail;
                    $restaurantDetail = $this->restaurants->where('id', $requestCompleted->restaurant_id)->first();
                    $userDetails = $this->users->where('id',$request_detail->user_id)->first();
                    $foodDetail = $this->requestdetail->where('request_id', $request_id)->get();
                    $invoiceValue = $request_detail->tax + $request_detail->restaurant_packaging_charge + $request_detail->item_total;
                    $invoiceAmount = $this->getIndianCurrency($invoiceValue);

                    if(!empty($userDetails) && $userDetails->is_guest_user == 0 && !empty($userDetails->email)) {
                        $orderCompleted = new OrderCompleteMail($requestCompleted,$restaurantDetail,$userDetails,$foodDetail,$invoiceAmount);
                        dispatch($orderCompleted);
                    }
                }
                $check_trackorder_status = $trackorderstatus::where('request_id', $request_id)->where('status', $status)->count();
                if ($check_trackorder_status == 0) {
                    $trackorderstatus->request_id = $request_id;
                    $trackorderstatus->status = $status;
                    $trackorderstatus->detail = $message;
                    $trackorderstatus->save();
                }

                //send push notification to user
                if (isset($request_detail->Users->device_token) && ($request_detail->Users->device_token != '') && ($status == 5)) {
                    $restaurant_detail = $this->restaurants->find($request_detail->restaurant_id);
                    $message = "Your order from " . $restaurant_detail->restaurant_name . " is on its way";
                    $message_lang = $this->language_string_translation('constants.your_order_from',$lang) ." ". $restaurant_detail->restaurant_name . " " .$this->language_string_translation('constants.is_on_its_way',$lang);
                    $title = $this->language_string_translation('constants.common_title','en');
                    $data = array(
                        'device_token' => $request_detail->Users->device_token,
                        'device_type' => $request_detail->Users->device_type,
                        'title' => $title,
                        'message' => $message,
                        'request_id' => $request_id,
                        'delivery_type' => $request_detail->delivery_type
                    );
                    $this->user_send_push_notification($data);
                }
                $response_array = array('status' => true, 'request_id' => $request_id, 'order_id' => $request_detail->order_id, 'message' => $message_lang);
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

	// public function cancel_request(Request $request)
	// {
	// 	  $validator = Validator::make(
 //                $request->all(),
 //                array(
 //                    'request_id' => 'required'
 //                ));

 //        if ($validator->fails())
 //        {
 //            $error_messages = implode(',', $validator->messages()->all());
 //            $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
 //        }else
 //        {
	// 		$request_id = $request->request_id;
	// 		$provider_id = $request->header('authId');
	// 		$old_provider=0;

	// 		$request_data = DB::table('requests')
	// 							->where('id',$request_id)->first();

	// 		// delete request to driver 
	// 		$temp_driver = $provider_id;
	// 		$header = array();
	// 		$header[] = 'Content-Type: application/json';
	// 		$postdata = array();
	// 		$postdata['request_id'] = $request_id;
	// 		$postdata['user_id'] = $request_data->user_id;
	// 		$postdata['status'] = 1;
	// 		$postdata = json_encode($postdata);
					
	// 		$ch = curl_init(FIREBASE_URL."/new_request/$temp_driver.json");
	// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// 		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	// 		$result = curl_exec($ch); 
	// 		curl_close($ch); 
				
	// 		//update reject drivers
	// 		$current_request = file_get_contents(FIREBASE_URL."/current_request/".$request_id.".json");
	// 		$current_request = json_decode($current_request);
	// 		if(isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) 
	// 		{
	// 			$reject_drivers = explode(',',$current_request->reject_drivers);
	// 		}
	// 		$reject_drivers[] = $provider_id;
	// 		$postdata = array();
	// 		$postdata['reject_drivers'] = implode(',',$reject_drivers);
	// 		$postdata = json_encode($postdata);
	// 		$this->update_firebase($postdata, 'current_request', $request_id);

	// 		$restuarant_detail = $this->restaurants::where('id',$request_data->restaurant_id)->first();

	// 		$source_lat = $restuarant_detail->lat;
	// 		$source_lng = $restuarant_detail->lng;
			

	// 		$data = file_get_contents(FIREBASE_URL."/available_providers/.json");
	// 		$data = json_decode($data);

	// 		$temp_driver = 0;
	// 		$last_distance = 0;
	// 		if($data != NULL && $data !="")
	// 		{
	// 			foreach ($data as $key => $value) 
	// 			{
	// 				# code...
	// 				$driver_id = $key;

	// 				//check previous rejected drivers    
	// 				$current_request = file_get_contents(FIREBASE_URL."/current_request/".$request_id.".json");
	// 				$current_request = json_decode($current_request);
	// 				if(isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) 
	// 				{
	// 					$reject_drivers = explode(',',$current_request->reject_drivers);
	// 					if(in_array($driver_id, $reject_drivers))
	// 					{
	// 						continue;
	// 					}
	// 				}
	// 				$check = $this->deliverypartners::where('id',$driver_id)->where('status',1)->first();
	// 				$check_request = $this->foodrequest->where('delivery_boy_id',$driver_id)->whereNotIn('status',[7,9,10])->count();
	// 				if(count($check)!=0 && $check_request==0)
	// 				{
	// 					if($old_provider==0){
	// 						$old_provider = -1;
	// 					}
	// 					if($driver_id != $old_provider && $driver_id!=$provider_id)
	// 					{
	// 						if ($value != NULL && $value != "")
	// 						{
	// 						$driver_lat = $value->lat;
	// 						$driver_lng = $value->lng;
	// 						$updated_time = isset($value->updated_at)?$value->updated_at:date("Y-m-d H:i:s");
	// 						$dt = new Carbon($updated_time);
	// 						$last_updated_time = $dt->addMinutes(IDEAL_TIME); 
	// 						$current_time = date("Y-m-d H:i:s");
	// 						if(strtotime($last_updated_time) >= strtotime($current_time))
	// 						{

	// 							try 
	// 							{
	// 								// $q = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false";
	// 								$q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false&key=".GOOGLE_API_KEY;
	// 								$json = file_get_contents($q); 
	// 								$details = json_decode($json, TRUE);

	// 								// var_dump($details); exit;
	// 								if(isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS'){
	// 									$current_distance_with_unit = $details['rows'][0]['elements'][0]['distance']['text'];
	// 									$current_distance = (float)$details['rows'][0]['elements'][0]['distance']['value'];
	// 									$unit =str_after($current_distance_with_unit, ' ');
	// 									// if($unit == 'm')
	// 									// {
	// 									// 	$current_distance = $current_distance/1000;
	// 									// }
	// 									$current_distance = $current_distance/1000;
	// 									if($current_distance<=DEFAULT_RADIUS){
	// 										if($temp_driver == 0){
	// 											$temp_driver = $driver_id;
	// 											$last_distance = $current_distance;
	// 										}else{
	// 											if($current_distance < $last_distance){
	// 												$temp_driver = $driver_id;
	// 												$last_distance = $current_distance;
	// 											}
	// 										}
	// 									}
	// 								}

	// 								} catch (Exception $e) {
										
	// 								}
	// 							}
	// 						}
	// 					}
	// 				}
	// 				//print_r($value->lat); exit;
	// 			}
	// 		}
				
	// 			if ($temp_driver != 0 ) {
	// 				# code...
	// 				$user_data = $this->foodrequest->find($request_id);
	// 				$user_data->delivery_boy_id = $temp_driver;
	// 				$user_data->status = 2;
	// 				$user_data->save();

	// 				//DB::table('requests')->where('id',$request_id)->update(['delivery_boy_id'=>$temp_driver,'status'=>2]);
				
	// 				// to insert into firebase
	// 				$header = array();
	// 				$header[] = 'Content-Type: application/json';
	// 				$postdata = array();
	// 				// $postdata['id'] = $request_id;
	// 				$postdata['request_id'] = (String)$request_id;
	// 				$postdata['provider_id'] = (String)$temp_driver;
	// 				$postdata['user_id'] = $user_data->user_id;
	// 				$postdata['reject_drivers'] = implode(',',$reject_drivers);
	// 				$postdata['status'] = 2;
	// 				$postdata = json_encode($postdata);
					
	// 				$ch = curl_init(FIREBASE_URL."/current_request/$request_id.json");
	// 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// 				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
	// 				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	// 				$result = curl_exec($ch); 
	// 				curl_close($ch); 

	// 				// sending request to driver 
	// 				$header = array();
	// 				$header[] = 'Content-Type: application/json';
	// 				$postdata = array();
	// 				$postdata['request_id'] = $request_id;
	// 				$postdata['user_id'] = $user_data->user_id;
	// 				$postdata['status'] = 1;
	// 				$postdata = json_encode($postdata);
					
	// 				$ch = curl_init(FIREBASE_URL."/new_request/$temp_driver.json");
	// 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// 				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	// 				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	// 				$result = curl_exec($ch); 
	// 				curl_close($ch); 

	// 				  $provider = $this->deliverypartners->find($temp_driver);
	// 				if(isset($provider->device_token) && $provider->device_token!='')
	// 				{
	// 					$title = $message = trans('constants.new_order');
	// 					$data = array(
	// 						'device_token' => $provider->device_token,
	// 						'device_type' => $provider->device_type,
	// 						'title' => $title,
	// 						'message' => $message,
	// 						'request_id' => $request_id,
	// 						'delivery_type' => $request_data->delivery_type
	// 					);
	// 					$this->user_send_push_notification($data);
	// 				}
				
	// 			} else {
	// 					# code...
	// 				$title = "No Providers available";

	// 				$user_data = DB::table('requests')
	// 							->where('id',$request_id)
	// 							->first();

	// 				DB::table('requests')->where('id',$request_id)->update(['delivery_boy_id'=>0,'status'=>1]);

	// 				//delete in track order status table
	// 				$this->trackorderstatus->where('request_id',$request_id)->where('status',2)->delete();

	// 				// to insert into firebase
	// 				$header = array();
	// 				$header[] = 'Content-Type: application/json';
	// 				$postdata = array();
	// 				// $postdata['id'] = $request_id;
	// 				$postdata['request_id'] = (String)$request_id;
	// 				$postdata['provider_id'] = (String)0;
	// 				$postdata['user_id'] = $user_data->user_id;
	// 				$postdata['status'] = 1;
	// 				$postdata = json_encode($postdata);
					
	// 				$ch = curl_init(FIREBASE_URL."/current_request/$request_id.json");
	// 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// 				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
	// 				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	// 				$result = curl_exec($ch); 
	// 				curl_close($ch); 
					
	// 				//update in firebase for restaurant notification
	// 				$postdata = array();
	// 				$postdata['status'] = 10;
	// 				$postdata = json_encode($postdata);
	// 				$this->update_firebase($postdata, 'restaurant_request/'.$request_data->restaurant_id, $request_id);

	// 		     //         $message = array();
	// 				   // $message['title'] = "Taxi Request";
	// 		     //        $message['body'] = "No Providers available";


	// 	      //           $this->send_push_notification($request->header('authId'), $title, $message);

	// 				}

	// 			$response_array = array('status'=>true,'message'=>'Request Cancelled Successfully');

 //        }

 //        $response = response()->json($response_array, 200);
 //        return $response;
	// }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel_request(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'request_id' => 'required'
                ));
            if ($validator->fails()) {
                $error_messages = implode(',', $validator->messages()->all());
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $error_messages);
            } else {
                $lang = isset($request->lang)?$request->lang:'en';
                $request_id = $request->request_id;
                $provider_id = $request->header('authId');
                $request_data = $this->foodrequest->where('id', $request_id)->first();
                if(!empty($request_data->reject_drivers)) {
                    $exist_reject_drivers = json_decode($request_data->reject_drivers);
                    array_push($exist_reject_drivers , $provider_id);
                    $new_reject_drivers = json_encode($exist_reject_drivers);
                }else {
                    $rider = array($provider_id);
                    $new_reject_drivers = json_encode($rider);
                }
                $this->foodrequest->where('id', $request_id)->update(['delivery_boy_id' => 0,'temp_drivers' => null, 'status' => 1, 'reject_drivers' => $new_reject_drivers]);
                // delete request to driver
                $temp_driver = $provider_id;
                NewRequest::where('request_id',$request_id)->where('provider_id',$temp_driver)->delete();

                if($request_data->order_accepted_type == 2) {
                    $type = 2;
                    try
                    {
                        $job = (new BroadcastDelayJob($request_id , $type));
                        dispatch($job);
                    }catch(\Exception $e)
                    {
                        Log::error('BroadcastDelyJob Mail error:: ' . $e->getMessage());
                    }
                }

                // Old broadcast flow logic
                    // $queue_drivers = json_decode($request_data->queue_drivers);
                    // if(!empty($queue_drivers) && $request_data->order_accepted_type == 2) {
                    //     $search = array_search($provider_id , $queue_drivers);
                    //     $remaining_drivers = null;
                    //     if($search !== false) {
                    //         // replace rider queue first into last
                    //         $first_element = array_shift($queue_drivers);
                    //         if($provider_id == $first_element) {
                    //             $queue_drivers[count($queue_drivers) + 1] = $first_element;
                    //             // unset($queue_drivers[$search]);
                    //             $remaining_drivers = array_values($queue_drivers);
                    //             $remaining_drivers_list = json_encode($remaining_drivers);
                    //             log::info('Next driver ::'.$remaining_drivers[0]);
                    //             log::info('Remainng drivers List ::'.json_encode($remaining_drivers));
                    //             $this->foodrequest->where('id', $request_id)->update(['queue_drivers' => $remaining_drivers_list]);
                    //             if(!empty($remaining_drivers)) {
                    //                 queueDriverAssign::broadCastAssignDriver($request_id);
                    //             }
                    //         }
                    //     }
                    // }
                    OrderRejectedDriver::create([
                        'driver_id' => $provider_id,
                        'order_id' => $request_id,
                        'restaurant_id' => $request_data->restaurant_id,
                        'status' => '1',
                    ]);

                $this->trackorderstatus->where('request_id',$request_id)->whereNotIn('status',[0,1])->delete();
                $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.req_canceld',$lang));
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
    public function order_history(Request $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $delivery_boy_id = $request->header('authId');
            $orders = $this->foodrequest->where('requests.delivery_boy_id', $delivery_boy_id)->where('requests.is_paid', 1)->latest()->limit(15)->get();
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
                        'food_name_ar' => (!empty($k->Foodlist) ? $k->Foodlist->name_ar : ""),
                        'food_name_kur' => (!empty($k->Foodlist) ? $k->Foodlist->name_kur : ""),
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
                        'restaurant_name_ar' => $restaurant_detail->restaurant_name_ar,
                        'restaurant_name_kur' => $restaurant_detail->restaurant_name_kur,
                        'restaurant_image' => SPACES_BASE_URL . $restaurant_detail->image,
                        'ordered_on' => $key->delivered_time,
                        'bill_amount' => $key->bill_amount,
                        'deducted_bill_amount' => $key->deducted_bill_amount,
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

            // $upcoming_orders = DB::table('requests')->where('requests.delivery_boy_id', $delivery_boy_id)->where('requests.status', '!=', 10)->where('requests.status', '!=', 7)->orderBy('requests.id', 'desc')->get();
            // $upcoming_order_list = array();
            // foreach ($upcoming_orders as $key) {
            //     $upcoming_order_detail = $this->requestdetail->where('request_id', $key->id)->get();
            //     $upcoming_order_list_detail = array();
            //     foreach ($upcoming_order_detail as $k) {
            //         if (isset($k->FoodQuantity)) $k->FoodQuantity->price = $k->food_quantity_price;
            //         $upcoming_order_list_detail[] = array(
            //             'food_id' => (!empty($k->Foodlist) ? $k->Foodlist->id : ""),
            //             'food_name' => (!empty($k->Foodlist) ? $k->Foodlist->name : ""),
            //             'food_name_ar' => (!empty($k->Foodlist) ? $k->Foodlist->name_ar : ""),
            //             'food_name_kur' => (!empty($k->Foodlist) ? $k->Foodlist->name_kur : ""),
            //             'food_quantity' => $k->quantity,
            //             'tax' => (!empty($k->Foodlist) ? $k->Foodlist->tax : ""),
            //             'item_price' => (!empty($k->Foodlist) ? $k->Foodlist->price : 0) * $k->quantity,
            //             'is_veg' => (!empty($k->Foodlist) ? $k->Foodlist->is_veg : ""),
            //             'food_size' => $k->FoodQuantity,
            //             'add_ons' => $k->RequestdetailAddons
            //         );
            //     }

            //     $restaurant_details = $this->restaurants->find($key->restaurant_id);

            //     if ($restaurant_details) {
            //         $upcoming_order_list[] = array(
            //             'request_id' => $key->id,
            //             'order_id' => $key->order_id,
            //             'restaurant_id' => $restaurant_details->id,
            //             'restaurant_name' => $restaurant_details->restaurant_name,
            //             'restaurant_name_ar' => $restaurant_details->restaurant_name_ar,
            //             'restaurant_name_kur' => $restaurant_details->restaurant_name_kur,
            //             'restaurant_image' => SPACES_BASE_URL . $restaurant_details->image,
            //             'ordered_on' => $key->ordered_time,
            //             'bill_amount' => $key->bill_amount,
            //             'deducted_bill_amount' => $key->deducted_bill_amount,
            //             'item_list' => $upcoming_order_list_detail,
            //             'item_total' => $key->item_total,
            //             'offer_discount' => $key->offer_discount,
            //             'loyalty_discount' => $key->loyalty_discount,
            //             'restaurant_discount' => $key->restaurant_discount,
            //             'restaurant_packaging_charge' => $key->restaurant_packaging_charge,
            //             'tax' => $key->tax,
            //             'delivery_charge' => $key->delivery_charge,
            //             'driver_tip' => $key->driver_tip,
            //             'delivery_address' => $key->delivery_address,
            //             'restaurant_address' => $restaurant_details->address
            //         );
            //     }

            // }

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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function cancel_order_by_user(CancelOrderByUserRequest $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $order_det = $this->foodrequest->find($request->request_id);
            if ($order_det->status >= 1) {
                $response_array = array('status' => false, 'message' => $this->language_string_translation('constants.order_cancellation_time_exceeded',$lang));
                return response()->json($response_array, 200);
            }
            $order_det->status = 10;
            $order_det->is_canceled_by_user = 1;
            $order_det->canceled_time = date('Y-m-d H:i:s');
            $order_det->save();
            
            // Send SMS Temporary hide
            // if (SMS_ENABLE == 1) {
            //     $user_detail = $this->users->where('id', $order_det->user_id)->first();
            //     $message = $this->language_string_translation('constants.you_have_successfully_cancelled_your_order',$lang) . $order_det->order_id;
            //     $sendSms = $this->send_otp_softsms($user_detail->phone, $message);
            // }
            $request_id = $request->request_id;
            $restaurantId = $order_det->restaurant_id;
            NewRequest::where('request_id',$request_id)->delete();
            CurrentRequest::where('request_id',$request_id)->delete();
            $client = new Client();
            $client->get(SOCKET_URL.'/cancel_order_by_user/'.$restaurantId);
            $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.order_canceled_successfully',$lang));
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
	

	/**
	 * update order ratings from user
	 * 
	 * @param object $request
	 * 
	 * @return json $response
	 */
    public function order_ratings(UserOrderRatingsRequest $request)
    {
        try {
            $lang = isset($request->lang)?$request->lang:'en';
            $order_det = $this->foodrequest->find($request->request_id);
            if (!empty($order_det)) {
                if ($order_det->status == 7) {
                    $feedback = (isset($request->restaurant_feedback) ? $request->restaurant_feedback : "");

                    //insert ratings into table
                    $this->order_ratings->request_id = $request->request_id;
                    $this->order_ratings->restaurant_rating = $request->restaurant_rating;
                    $this->order_ratings->restaurant_feedback = $feedback;
                    $this->order_ratings->delivery_boy_rating = $request->delivery_boy_rating;
                    $this->order_ratings->save();

                    $order_det->is_rated = 1;
                    $order_det->save();

                    $res_id = $order_det->restaurant_id;
                    $rating = $this->order_ratings->with('Foodrequest')
                    ->wherehas('Foodrequest', function ($q) use ($res_id) {
                        $q->where('restaurant_id', $res_id);
                    })
                    ->avg('restaurant_rating');
                    $this->restaurants->where('id',$res_id)->update(['rating' => round($rating)]);

                    count(Redis::keys('popular_brands*')) != 0 ? Redis::del(Redis::keys('popular_brands*')) : '';
                    count(Redis::keys('nearby_restaurant*')) != 0 ? Redis::del(Redis::keys('nearby_restaurant*')) : '';

                    $response_array = array('status' => true, 'message' => $this->language_string_translation('constants.rate_msg',$lang));
                } else {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.order_not_complete',$lang));
                }
            } else {
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.invalid_order',$lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e) {
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }


	/**
	 * validate promocode
	 * 
	 * @param object $request
	 * 
	 * @return json $response
	 * 
	 */
    public function check_promocode(CheckPromocodeRequest $request)
    {
        try {
            $user_id = ($request->header('authId') != "")?$request->header('authId'):$request->authId;
            $lang = isset($request->lang) ? $request->lang : 'en';
            $get_checkout_restaurant_id = DB::table('users_checkout_restaurant')->where('user_id', $user_id)->first();       
            $delivery_type = isset($request->delivery_type) ? $request->delivery_type : 0;
            $restaurant_id = $request->restaurant_id;
            //check restaurant promocode
            $get_promocode = DB::table('restaurant_coupon_code')->where('code', $request->promocode)
            ->where('restaurant_id', $restaurant_id)
            ->where('status', 1)
            ->where('is_approve',1)
            ->whereDate('available_from', '<=', Carbon::now()->toDateString())
            ->whereDate('valid_till', '>=', Carbon::now()->toDateString())->first();

            if(empty($get_promocode)){
                //check restaurant super admin promocode
                $get_promocode = DB::table('sub_admin_coupon_code')->where('code', $request->promocode)
                ->join('admin', 'admin.id', '=', 'sub_admin_coupon_code.admin_id')
                ->whereRaw('json_contains(admin.restaurant_id, \'["' .$restaurant_id.'"]\')')
                ->where('sub_admin_coupon_code.status', 1)
                ->where(function($q) use($restaurant_id){
                    $q->whereRaw('json_contains(sub_admin_coupon_code.restaurant_id, \'["' .$restaurant_id.'"]\')')
                    ->orWhereRaw('json_contains(sub_admin_coupon_code.restaurant_id, \'["0"]\')');
                })
                ->whereDate('sub_admin_coupon_code.available_from', '<=', Carbon::now()->toDateString())
                ->whereDate('sub_admin_coupon_code.valid_till', '>=', Carbon::now()->toDateString())->first();

                //check admin promocode
                if(empty($get_promocode)){            
                    $get_promocode = $this->promocode->where('code', $request->promocode)
                    ->where('status', 1)
                    ->where(function($q) use($restaurant_id){
                        $q->whereRaw('json_contains(restaurant_id, \'["' .$restaurant_id.'"]\')')
                        ->orWhereRaw('json_contains(restaurant_id, \'["0"]\')');
                    })->whereDate('available_from', '<=', Carbon::now()->toDateString())
                    ->whereDate('valid_till', '>=', Carbon::now()->toDateString())->first();
                }
            }        
            if (!empty($get_promocode)) {
                //check total usage of promocode
                $total_usage = $this->foodrequest->where('coupon_code', $request->promocode)->where('status', '!=', 10)
                    ->count();
                //check the promocode usage by user
                $user_usage = $this->foodrequest->where('coupon_code', $request->promocode)
                    ->where('status', '!=', 10)->where('user_id', $user_id)
                    ->count();
                if ($total_usage >= $get_promocode->total_use) {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.promocode_used', $lang));
                } elseif ($get_promocode->coupon_type == 2 && $get_promocode->coupon_value > $request->bill_amount) {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.min_order_val', $lang, ['param' => $get_promocode->coupon_value]));
                } elseif ($user_usage >= $get_promocode->use_per_customer) {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.promocode_used', $lang));
                } elseif ($get_promocode->delivery_type != 0 && ($get_promocode->delivery_type != $delivery_type)) {
                    $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.deliverytype_notsupports', $lang));
                } else {
                    if ($get_promocode->offer_type == 1) {
                        $offer_amount = $get_promocode->value;
                    } else {
                        $offer_amount = $request->bill_amount * ($get_promocode->value / 100);
                        if (isset($get_promocode->max_amount) && ($offer_amount > $get_promocode->max_amount)) {
                            $offer_amount = $get_promocode->max_amount;
                        }
                    }
                    $response_array = array('status' => true, 'offer_amount' => $offer_amount, 'offer_type' => $get_promocode->offer_type, 'max_amount' => $get_promocode->max_amount, 'percent_offer' => $get_promocode->value, 'coupon_value' => $get_promocode->coupon_value);
                }
            } else {
                $response_array = array('status' => false, 'error_code' => 101, 'message' => $this->language_string_translation('constants.invalid_promo', $lang));
            }
            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
	/**
	 * gave driver tip based on order
	 * 
	 * @param object $request
	 * 
	 * @return json $response
	 */
	public function driver_tip(Request $request)
	{
        try {
            $validator = Validator::make(
                $request->all(),
                array(
                    'tip' => 'required',
                    'request_id' => 'required'
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

                $data = $this->foodrequest->find($request->request_id);
                $tip_amount = $request->tip;
                //$tip_amount = $data->bill_amount*($tip/100);

                $data->driver_tip = $tip_amount;
                $data->save();

                $response_array = array('status' => true,'message'=>"Tips added successfully");
                $response = response()->json($response_array, 200);
                return $response;
            }
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
	}

    public function update_current_request($request_id , $provider_id , $user_id)
    {
        $statusUpdate = CurrentRequest::where('request_id',$request_id)->update(['provider_id' => $provider_id , 'status' => '2']);
        if($statusUpdate == 1)
        {
            $client = new Client();
            $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$provider_id);
            
            $checkNewRequest = NewRequest::where('request_id',$request_id)->where('provider_id',$provider_id)->first();
            if($checkNewRequest == null)
            {
                $newRequest = new NewRequest();
                $newRequest->request_id = (string)$request_id;
                $newRequest->user_id = (string)$user_id;
                $newRequest->provider_id = (string)$provider_id;
                $newRequest->status = "1";        
                $newRequest->save();
            }
            $client->get(SOCKET_URL.'/new_request_status/'.$request_id.'/'.$provider_id);

        }
        $response = response()->json($statusUpdate, 200);
		return $response;
    }

    public function admin_update_current_request($request_id , $status)
    {
        $statusUpdate = CurrentRequest::where('request_id',$request_id)->update(['status' => $status]);
        if($statusUpdate == 1)
        {
            $currentRequest = CurrentRequest::where('request_id',$request_id)->first();
            $client = new Client();
            $client->get(SOCKET_URL.'/current_request_status/'.$request_id.'/'.$currentRequest->provider_id);
            $status = $currentRequest->status;
            if($status == 0 || $status == 1 || $status == 7)
            {
                $request = $this->foodrequest->where('id',$request_id)->first();
                $requestId = $request->restaurant_id;
                log::info('requestId : ' . $requestId);
                $client->get(SOCKET_URL.'/request_status_restaurant/'.$requestId);
            }
            if($status == 7)
            {
                $requestCompleted = $this->foodrequest->where('id',$request_id)->first();
                if(!empty($requestCompleted)) {
                    $restaurantDetail = $this->restaurants->where('id', $requestCompleted->restaurant_id)->first();
                    $userDetails = $this->users->where('id',$requestCompleted->user_id)->first();
                    $foodDetail = $this->requestdetail->where('request_id', $request_id)->get();
                    $invoiceValue = $requestCompleted->tax + $requestCompleted->restaurant_packaging_charge + $requestCompleted->item_total;
                    $invoiceAmount = $this->getIndianCurrency($invoiceValue);

                    if(!empty($userDetails) && $userDetails->is_guest_user == 0 && !empty($userDetails->email)) {
                        log::info('connect delivered by admin : ' );
                        $orderCompleted = new OrderCompleteMail($requestCompleted,$restaurantDetail,$userDetails,$foodDetail,$invoiceAmount);
                        dispatch($orderCompleted);
                    }
                }
            }
        }
        $response = response()->json($statusUpdate, 200);
		return $response;
    }
    
    public function admin_cancel_new_request($request_id,$type)
    {
        Log::info('admin_cancel_new_request request_id :'.$request_id);
        Log::info('admin_cancel_new_request type  :'.$type);
        $newRequest = NewRequest::where('request_id',$request_id)->delete();
        $client = new Client();
        $rider = $this->foodrequest->where('id',$request_id)->first();
        $riderId = $rider->delivery_boy_id;
        $tempDriverId = $rider->temp_drivers;
        if($rider->temp_drivers == null)
        {
            $tempDriverId = 0;
        }
        $userId = $rider->user_id;
        $client->get(SOCKET_URL.'/admin_cancel_request/'.$request_id.'/'.$riderId.'/'.$tempDriverId.'/'.$userId.'/'.$type);
        $response = response()->json($newRequest, 200);
        return $response;
    }

    public function get_distance($restaurant_location , $delivery_location) {
        $getdistance = $this->getGoogleDistance($restaurant_location, $delivery_location,1);
        Log::info('getdistance  :'.$getdistance[0]);
        return $getdistance[0];
    }

    public function pushover_test()
    {
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                "token" => "add91z5g8ywojvri58nr7casfnsmpg",
                "user" => "u21wb723agxyapxghgup52gosctbk5",
                "message" => "New Order Received for EatZilla testing",
            ),
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function pushy_test(Request $request)
    {
        $validator = Validator::make(
			$request->all(),
			array(
				'device_token' => 'required'
			));

		if ($validator->fails())
		{
			$error_messages = implode(',', $validator->messages()->all());
			$response_array = array('status' => false, 'message' => $error_messages);
		}
        else
        {
            $data = array('message' => 'Eatzilla Test Notification welcome!');

            // The recipient device tokens
            $to = array($request->device_token);

            // Optionally, send to a publish/subscribe topic instead
            // $to = '/topics/news';

            // Optional push notification options (such as iOS notification fields)
            $options = array(
                'notification' => array(
                    'badge' => 1,
                    'sound' => 'ping.aiff',
                    'title' => 'Eatzilla Test Notification',
                    'body'  => 'Eatzilla Test Notification welcome!'
                )
            );

            // Insert your Secret API Key here
            $apiKey = '9cfd5bfd63bb919de14510fe024ebe147fc1c7c28252b68c3b916e9aebba1d54';

            // Default post data to provided options or empty array
            $post = $options ?: array();

            // Set notification payload and recipients
            $post['to'] = $to;
            $post['data'] = $data;

            // Set Content-Type header since we're sending JSON
            $headers = array(
                'Content-Type: application/json'
            );

            // Initialize curl handle
            $ch = curl_init();

            // Set URL to Pushy endpoint
            curl_setopt($ch, CURLOPT_URL, 'https://api.pushy.me/push?api_key=' . $apiKey);
            // dd('https://api.pushy.me/push?api_key=' . $apiKey);

            // Set request method to POST
            curl_setopt($ch, CURLOPT_POST, true);

            // Set our custom headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Get the response back as string instead of printing it
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Set post data as JSON
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_UNESCAPED_UNICODE));

            // Actually send the push
            $result = curl_exec($ch);
            // dd($result);

            // Display errors
            if (curl_errno($ch)) {
                echo curl_error($ch);
            }

            // Close curl handle
            curl_close($ch);

            // Attempt to parse JSON response
            $response = @json_decode($result);

            // Throw if JSON error returned
            if (isset($response) && isset($response->error)) {
                // throw new Exception('Pushy API returned an error: ' . $response->error);
                $response_array = array('status' => false, 'message' => 'No devices matched the specified Device Token');
                return $response_array;
            }

            $response_array = array('status' => true, 'message' => 'Notification Send Successfully');
        }
        return $response_array;
    }

    public function call_masking(Request $request) {
        log::info('call_masking connect');
        log::info('Request uuid :' . $request->uuid);
        log::info('Request call_id :' . $request->call_id);
        log::info('Request call_to_number :' . $request->call_to_number);
        log::info('Request caller_id_number :' . $request->caller_id_number);
        log::info('Request start_stamp :' . $request->start_stamp);
        $number = 0;
        if($request->call_to_number == USER_TOLL_NUMBER) {
            $check_number = $request->caller_id_number;
            $search = '-';
            $trimmed = str_replace($search, '', $check_number);
            $search = '+';
            $finalValue = str_replace($search, '', $trimmed);
            $getUser = $this->users->where('phone',$finalValue)->select('id')->first();
            if(!empty($getUser)) {
                $order_details = $this->foodrequest->where('user_id',$getUser->id)->whereIn('status',[3,4,5,6])->orderBy('id','ASC')->select('delivery_boy_id')->first();
                if(!empty($order_details)) {
                    $getNumber = $this->deliverypartners->where('id',$order_details->delivery_boy_id)->select('phone')->first();
                    if(!empty($getNumber)) {
                        $search = '+91';
                        $number = str_replace($search, '', $getNumber->phone);
                    }
                }
            }
        }else {
            $check_number = $request->caller_id_number;
            $search = '-';
            $finalValue = str_replace($search, '', $check_number);
            $getUser = $this->deliverypartners->where('phone',$finalValue)->select('id')->first();
            if(!empty($getUser)) {
                $order_details = $this->foodrequest->where('delivery_boy_id',$getUser->id)->whereIn('status',[3,4,5,6])->orderBy('id','ASC')->select('user_id')->first();
                if(!empty($order_details)) {
                    $getNumber = $this->users->where('id',$order_details->user_id)->select('phone')->first();
                    if(!empty($getNumber)) {
                        $search = '91';
                        $number = str_replace($search, '', $getNumber->phone);
                    }
                }
            }
        }
        if($number != 0) {
            $array = [new stdClass()];
            $array[0]->transfer = new stdClass();
            $array[0]->transfer->type = "number";
            $array[0]->transfer->data = [$number];
            log::info('Response data number:' . $number);
            return $array;
        }else {
            log::info('Response :' . 0);
            return 0;
        }
    }

    /**
	 * view all coupon code
	 * @param object $request
	 * @return json $response
	 */

    public function view_all_coupon_code(Request $request) {
        try {
            $restaurantID = $request->restaurant_id;
            $restaurantCouponCode = DB::table('restaurant_coupon_code')->where('restaurant_id', $restaurantID)
                                    ->where('status', 1)
                                    ->where('is_approve',1)
                                    ->whereDate('available_from', '<=', Carbon::now()->toDateString())
                                    ->whereDate('valid_till', '>=', Carbon::now()->toDateString())
                                    ->get();

            $subadmincouponcode = DB::table('sub_admin_coupon_code')
            ->join('admin', 'admin.id', '=', 'sub_admin_coupon_code.admin_id')
            ->whereRaw('json_contains(admin.restaurant_id, \'["' .$restaurantID.'"]\')')
            ->where('sub_admin_coupon_code.status', 1)
            ->where(function($q) use($restaurantID){
                $q->whereRaw('json_contains(sub_admin_coupon_code.restaurant_id, \'["' .$restaurantID.'"]\')')
                ->orWhereRaw('json_contains(sub_admin_coupon_code.restaurant_id, \'["0"]\')');
            })
            ->whereDate('sub_admin_coupon_code.available_from', '<=', Carbon::now()->toDateString())
            ->whereDate('sub_admin_coupon_code.valid_till', '>=', Carbon::now()->toDateString())->get();  

            $getPromocode = $this->promocode->where('status', 1)
                ->where(function($q) use($restaurantID){
                    $q->whereRaw('json_contains(restaurant_id, \'["' .$restaurantID.'"]\')')
                    ->orWhereRaw('json_contains(restaurant_id, \'["0"]\')');
                })
                ->whereDate('available_from', '<=', Carbon::now()->toDateString())
                ->whereDate('valid_till', '>=', Carbon::now()->toDateString())->get();
            $promoCodeList = $restaurantCouponCode->merge($subadmincouponcode);
            $promoCodeList = $promoCodeList->merge($getPromocode);
            $response_array = array('status' => true, 'promoCodeList' => $promoCodeList);

            return response()->json($response_array, 200);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }
}