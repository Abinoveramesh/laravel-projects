<?php

namespace App\Http\Controllers\admin;
                                    
use App\Service\MultiOrderAssign;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use App\Model\AvailableProviders;
use Carbon\Carbon;
use DB;
use PDF;
use Log;
use DateTime;

class OrderController extends BaseController
{
	public function neworder_list(Request $request)
	{
		$restaurant_id = $request->session()->get('userid');

		$role = $request->session()->get('role');

		if($role==2)

		{

			$data = DB::table('requests')->where('requests.restaurant_id',$restaurant_id)
									 ->join('users','users.id','=','requests.user_id')
									 ->select('requests.id as request_id','requests.status as order_status','users.name as user_name','requests.*','users.*')
									 ->orderBy('request_id','desc')
									 ->get();
									 // print_r($data); exit;
			$data1 = DB::table('requests')->where('requests.restaurant_id',$restaurant_id)
									 ->join('request_detail','request_detail.request_id','=','requests.id')
									 ->join('food_list','food_list.id','=','request_detail.food_id')
									 ->select('food_list.name as food_name','request_detail.*','food_list.*','requests.id')
									 ->get();
		}else
		{
			$data = DB::table('requests')->join('users','users.id','=','requests.user_id')
									 ->select('requests.id as request_id','requests.status as order_status','users.name as user_name','requests.*','users.*')
									 ->orderBy('request_id','desc')
									 ->get();
									 // print_r($data); exit;
			$data1 = DB::table('requests')->join('request_detail','request_detail.request_id','=','requests.id')
									 ->join('food_list','food_list.id','=','request_detail.food_id')
									 ->select('food_list.name as food_name','request_detail.*','food_list.*','requests.id')
									 ->get();
		}

		return view('neworder_list',['data'=>$data,'data1'=>$data1]);
	}

	public function accept_request($request_id,Request $request)
	{
		$restaurant_id = $request->session()->get('userid');

		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;

		$foodrequest->where('id',$request_id)->update(['status'=>1]);

		$trackorderstatus->request_id = $request_id;
		$trackorderstatus->status = 1;
		$trackorderstatus->detail = "Order Accepted by Restaurant";
		$trackorderstatus->save();

		//  $status_entry[] = array(
        //         'request_id'=>$request_id,
        //         'status'=>1,
        //         'detail'=>"Order Accepted by Restaurant"
        //     );
		//   $trackorderstatus->insert($status_entry);
		  
		  $user_data = $this->foodrequest->where('id',$request_id)->first();
		  if($user_data->delivery_type==1){
				// to insert into firebase
				$postdata = array();
				$postdata['request_id'] = $request_id;
				$postdata['user_id'] = $user_data->user_id;
				$postdata['status'] = 1;
				$postdata = json_encode($postdata);
				$this->update_firebase($postdata, 'current_request', $request_id);

		  }

		return back();

	}

    /**
     * @param $request_id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign_request($request_id, Request $request)
    {
        $restaurant_id = $request->session()->get('userid');
        $old_provider = 0;
        $trackorderstatus = $this->trackorderstatus;
        $request_data = $this->foodrequest->where('id', $request_id)->first();
        $restuarant_detail = $this->restaurants->where('id', $restaurant_id)->first();
        $source_lat = $restuarant_detail->lat;
        $source_lng = $restuarant_detail->lng;
        $data = file_get_contents(FIREBASE_URL . "/available_providers/.json");
        $data = json_decode($data);
        $temp_driver = 0;
        $last_distance = 0;
        if ($data != NULL && $data != "") {
            foreach ($data as $key => $value) {
                $driver_id = $key;

                //check previous rejected drivers
                $current_request = file_get_contents(FIREBASE_URL . "/current_request/" . $request_id . ".json");
                $current_request = json_decode($current_request);
                if (isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) {
                    $reject_drivers = explode(',', $current_request->reject_drivers);
                    if (in_array($driver_id, $reject_drivers)) {
                        continue;
                    }
                }
                $check = $this->deliverypartners->where('id', $driver_id)->where('status', 1)->first();
                if ($check) {
                    if ($old_provider == 0) {
                        $old_provider = -1;
                    }
                    if ($driver_id != $old_provider) {
                        if ($value != NULL && $value != "") {
                            $driver_lat = $value->lat;
                            $driver_lng = $value->lng;
                            $updated_time = isset($value->updated_at) ? $value->updated_at : date("Y-m-d H:i:s");
                            $dt = new Carbon($updated_time);
                            $last_updated_time = $dt->addMinutes(IDEAL_TIME);
                            $current_time = date("Y-m-d H:i:s");
                            if (strtotime($last_updated_time) >= strtotime($current_time)) {
                                try {
                                    $q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false&key=" . GOOGLE_API_KEY;
                                    $json = file_get_contents($q);
                                    $details = json_decode($json, TRUE);
                                    if (isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS') {
                                        $current_distance_with_unit = $details['rows'][0]['elements'][0]['distance']['text'];
                                        $current_distance = (float)$details['rows'][0]['elements'][0]['distance']['value'];
                                        $unit = str_after($current_distance_with_unit, ' ');
                                        $current_distance = $current_distance / 1000;
                                        if ($current_distance <= DEFAULT_RADIUS) {
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
                                    }
                                } catch (Exception $e) {
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($temp_driver != 0) {
            $ins_data = array();
            $user_data = $this->foodrequest->find($request_id);
            $user_data->delivery_boy_id = $temp_driver;
            $user_data->status = 2;
            $user_data->save();
            $check_status = $trackorderstatus->where('request_id', $request_id)->where('status', 2)->count();
            if ($check_status == 0) {
                $trackorderstatus->request_id = $request_id;
                $trackorderstatus->status = 2;
                $trackorderstatus->detail = "Food is being prepared";
                $trackorderstatus->save();
            }

            // to insert into firebase
            $postdata = array();
            $postdata['request_id'] = $request_id;
            $postdata['provider_id'] = (string)$temp_driver;
            $postdata['user_id'] = $user_data->user_id;
            $postdata['status'] = 2;
            $postdata = json_encode($postdata);
            $this->update_firebase($postdata, 'current_request', $request_id);

            //send push notification to user
            $provider = $this->deliverypartners->find($temp_driver);
            if (isset($provider->device_token) && $provider->device_token != '') {
                $title = $message = trans('constants.new_order');
                $data = array(
                    'device_token' => $provider->device_token,
                    'device_type' => $provider->device_type,
                    'title' => $title,
                    'message' => $message,
                    'request_id' => $request_id,
                    'delivery_type' => $request_data->delivery_type
                );
                $this->user_send_push_notification($data);
            }

            // sending request to driver
            $postdata = array();
            $postdata['request_id'] = $request_id;
            $postdata['user_id'] = $user_data->user_id;
            $postdata['status'] = 1;
            $postdata = json_encode($postdata);
            $this->update_firebase($postdata, 'new_request', $temp_driver);

            return back()->with('success', 'Providers assigned successfully');
        } else {
            //update in firebase for restaurant notification
            $postdata = array();
            $postdata['status'] = 10;
            $postdata = json_encode($postdata);
            $this->update_firebase($postdata, 'restaurant_request/' . $restaurant_id, $request_id);
            $title = "No Providers available";
            return back()->with('error', $title);
        }
    }

	/** 
	* to get order list based on status
	*
	* @param object $request, string $type
	*
	* @return view page with details
	*/
	public function order_list(Request $request, $type)
	{
			$restaurant_id = $request->session()->get('userid');
			$role = $request->session()->get('role');
			if($role==2){
				if($type=='new') $status = [0];
			}else{
				if($type=='new') $status = [0];
			}
			// if($type=='new') $status = [0,1,2];
			if($type=='processing') $status = [1,2];	
			if($type=='pickup') $status = [3,4,5];
			if($type=='delivered') $status = [6,7];	
			if($type=='cancelled') $status = [9,10];
			if($role==2)
			{
				$data = $this->foodrequest->whereIn('status',$status)->where('delivery_type',1)
							->where('restaurant_id',$restaurant_id)->orderBy('id','desc')->get();
			}else
			{
				$data = $this->foodrequest->whereIn('status',$status)->where('delivery_type',1)
							->orderBy('id','desc')->get();
			}
			//dd($data);

			return view('orders_list',['data'=>$data,'title'=>$type]);
	}



	/**
	 * cancel the order request
	 * 
	 * @param int $request_id, object $request
	 * 
	 * @return return to blade page
	 */
	public function cancel_request($request_id,Request $request)
	{
		$role = $request->session()->get('role');

		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;
		if($role==1){
			$status = 9;
			$message = "Order Cancelled by Admin";
		}else{
			$status = 10;
			$message = "Order Cancelled by Restaurant";
		} 

		$foodrequest->where('id',$request_id)->update(['status'=>10,'is_canceled_by_user'=>2]);

		$trackorderstatus->request_id = $request_id;
		$trackorderstatus->status = $status;
		$trackorderstatus->detail = $message;
		$trackorderstatus->save();

		$data = $foodrequest->find($request_id);

		// to insert into firebase
		$postdata = array();
		$postdata['request_id'] = (String)$request_id;
		$postdata['provider_id'] = (String)$data->delivery_boy_id;
		$postdata['user_id'] = $data->user_id;
		$postdata['status'] = 10;
		$postdata = json_encode($postdata);
		$this->update_firebase($postdata, 'current_request', $request_id);  

		$header = array();
		$header[] = 'Content-Type: application/json';
		$ch = curl_init(FIREBASE_URL."/new_user_request/".$data->user_id."/".$request_id.".json");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		$result = curl_exec($ch); 
		curl_close($ch); 
		
		$provider = $this->users->find($data->user_id);
		if(isset($provider->device_token) && $provider->device_token!='')
		{
			$title = $message = trans('constants.order_cancel');
			$data = array(
				'device_token' => $provider->device_token,
				'device_type' => $provider->device_type,
				'title' => $title,
				'message' => $message,
				'request_id' => $request_id,
				'delivery_type' => $data->delivery_type
			);
			$this->user_send_push_notification($data);
		}
        MultiOrderAssign::request_driver_commission_delete($request_id);
		return redirect('/admin/orders/new');

	}


	public function order_dashboard(Request $request)
	{
		 $restaurant_id = $request->session()->get('userid');
           $role = $request->session()->get('role');
         if($role == 1)
         {
         	$today_orders = DB::table('requests')
		                ->whereDate('created_at', Carbon::today())
		                ->count();

			$today_completed_orders = DB::table('requests')
		                          ->whereDate('created_at', Carbon::today())
		                          ->where('status',7)
		                          ->count();

			$today_cancel_orders = DB::table('requests')
		                       ->whereDate('created_at', Carbon::today())
		                       ->where('status',10)
		                       ->count();

			$today_processing_orders = DB::table('requests')
		                       ->whereDate('created_at', Carbon::today())
		                       ->whereIn('status',[2,3,4,5,6,8])
		                       ->count();
         }else
         {
         	$today_orders = DB::table('requests')
         				->where('restaurant_id',$restaurant_id)
		                ->whereDate('created_at', Carbon::today())
		                ->count();

			$today_completed_orders = DB::table('requests')
								  ->where('restaurant_id',$restaurant_id)
		                          ->whereDate('created_at', Carbon::today())
		                          ->where('status',7)
		                          ->count();

			$today_cancel_orders = DB::table('requests')
								->where('restaurant_id',$restaurant_id)
		                       ->whereDate('created_at', Carbon::today())
		                       ->where('status',10)
		                       ->count();

			$today_processing_orders = DB::table('requests')
								->where('restaurant_id',$restaurant_id)
		                       ->whereDate('created_at', Carbon::today())
		                       ->whereIn('status',[2,3,4,5,6,8])
		                       ->count();
         }
		
          
		   $query = $this->foodrequest
                    ->orderby('id','desc')
                    ->limit(5);
           $query = $query->when(($role!=1),function($q)use($restaurant_id){
                    return $q->where('restaurant_id',$restaurant_id);
            });
                    
    $recent_orders = $query->get();

		// $recent_orders = DB::table('requests')
		//                      ->join('users','users.id','=','requests.user_id')
		//                      ->select('requests.*','users.name as name')
		//                      ->orderby('id','desc')
		//                      ->limit(5)
		//                      ->get();

		$area_wise_earnings = $this->foodrequest
		                      ->join('restaurants','restaurants.id','=','requests.restaurant_id')
		                      ->join('add_area','add_area.id','=','restaurants.area')
		                      
		                      ->groupBy('restaurants.area','requests.id','add_area.area')
		                      ->select('restaurants.area','requests.id','add_area.area as res_area')
                              ->get();

		$column=array();
        foreach ($area_wise_earnings as $key => $value) {
           
            $col['res_area']=isset($value->Restaurants->Area)?$value->Restaurants->Area->area:"";
            $col['id']=$value->id;
           
            array_push($column, $col);
        }

        $area_wise_earnings = $column;


        //print_r($area_wise_earnings);exit();

		//dd($area_wise_earnings[0]); 

		//print_r($area_wise_earnings);exit();

		return view('order_dashboard',['today_orders'=>$today_orders,'today_completed_orders'=>$today_completed_orders,'today_cancel_orders'=>$today_cancel_orders,'today_processing_orders'=>$today_processing_orders,'recent_orders'=>$recent_orders,'area_wise_earnings'=>$area_wise_earnings,]);                
	}

	/**
	 * View the order request
	 * 
	 * @param int $request_id, object $request
	 * 
	 * @return return to blade page
	 */
	public function view_order($request_id,Request $request)
	{
		$data = $this->foodrequest->where('id',$request_id)
								  ->with('Restaurants.city_list')
								  // ->with('Restaurants.Area')
								  ->first();
		// print_r($data->Restaurants->city_list); exit;
		$delivery_address_detail = $this->deliveryaddress::find($data->delivery_address_id);
		if(!empty($delivery_address_detail))
		{
			$delivery_address_detail = 0;
			$address_count = 0;
		}else
		{
			$address_count = 1;
		}
		return view('view_order',compact('data'))->with('delivery_address_detail',$delivery_address_detail)->with('address_count',$address_count);
	}



	/** 
	* to get order list based on pickup
	*
	* @param object $request
	*
	* @return view page with details
	*/
	public function pickup_orders(Request $request)
	{
			$restaurant_id = $request->session()->get('userid');
			$role = $request->session()->get('role');
			if($role==2)
			{
				$data = $this->foodrequest->where('delivery_type',2)
								->where('restaurant_id',$restaurant_id)->orderBy('id','desc')->get();
			}else
			{
				$data = $this->foodrequest->where('delivery_type',2)->orderBy('id','desc')->get();
			}
			//dd($data);

			return view('pickup_orders',['data'=>$data,'title'=>__('constants.pickup')]);
	}



	/** 
	* to get order list based on dining
	*
	* @param object $request
	*
	* @return view page with details
	*/
	public function dining_orders(Request $request)
	{
			$restaurant_id = $request->session()->get('userid');
			$role = $request->session()->get('role');
			if($role==2)
			{
				$data = $this->foodrequest->where('delivery_type',3)
								->where('restaurant_id',$restaurant_id)->orderBy('id','desc')->get();
			}else
			{
				$data = $this->foodrequest->where('delivery_type',3)->orderBy('id','desc')->get();
			}
			//dd($data);

			return view('pickup_orders',['data'=>$data,'title'=>__('constants.dining')]);
	}



	/** 
	* to complete the order based on dining/pickup
	*
	* @param int $request_id, object $request
	*
	* @return view page with details
	*/
	public function complete_request($request_id,Request $request)
	{
		$restaurant_id = $request->session()->get('userid');

		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;

		$foodrequest->where('id',$request_id)->update(['status'=>7,'is_paid'=>1]);

		$trackorderstatus->request_id = $request_id;
		$trackorderstatus->status = 7;
		$trackorderstatus->detail = "Order Completed by Restaurant";
		$trackorderstatus->save();

		//  $status_entry[] = array(
        //         'request_id'=>$request_id,
        //         'status'=>7,
        //         'detail'=>"Order Completed by Restaurant"
        //     );

		// 	$trackorderstatus->insert($status_entry);
			

		return back();

	}


	/** 
	* to complete the order based on dining/pickup
	*
	* @param int $id, object $request
	*
	* @return view page with details
	*/
	public function generate_pdf($id,Request $request)
	{
		$restaurant_id = $request->session()->get('userid');

		$data = $this->foodrequest->with('Restaurants.Area')->find($id);
		$delivery_address = DB::table('delivery_address')->where('id',$data->delivery_address_id)->first();
		$customPaper = array(0,0,567.00,200.80);
		$pdf = PDF::loadView('invoice.order_invoice', ['data'=>$data,'delivery_address'=>$delivery_address])->setPaper($customPaper, 'landscape');
		//$pdf->setPaper('A4','landscape');

		return $pdf->stream('Invoice-'.$data->order_id.'.pdf',array('Attachment'=>0));
		//return $pdf->download('customers.pdf');

	}


	/**
	* get driver and restaurant locations to map
	*
	*@param object $request
	*
	*@return view page
	*/
	public function availability_map(Request $request)
	{
		// $result = file_get_contents(FIREBASE_URL."/prov_location/.json");
		// $result = json_decode($result);
		// $result = array_filter((array)$result, function($value) { return $value !== null; });
		$result = AvailableProviders::all();
		$data = array();
		foreach($result as $value){
			$getdetails = $this->deliverypartners->select('name','phone','partner_id')->where('id',$value->provider_id)->first();
			$busyRiders = $this->foodrequest->where('delivery_boy_id',$value->provider_id)->whereIn('status', [3,4,5,6])->first();

			if(isset($busyRiders)) {
				$value->status = 2;
			}

			// $getstatus = file_get_contents(FIREBASE_URL."/providers_status/".$key.".json");
			// $getstatus = json_decode($getstatus);
			$data[] = (object)array(
				'id'=>$value->provider_id,
				'name'=>isset($getdetails->name)?$getdetails->name:"",
				'partner_id'=>isset($getdetails->partner_id)?$getdetails->partner_id:"",
				'phone'=>isset($getdetails->phone)?$getdetails->phone:"",
				'lat'=>$value->lat,
				'lng'=>$value->lng,
				'is_available'=>isset($value->status)?$value->status:0
			);
		}		
		$get_restaurant = $this->restaurants->where('status',1)->get();
		foreach($get_restaurant as $key=>$value){
			
			$data[] = (object)array(
				'id'=>$value->id,
				'name'=>$value->restaurant_name,
				'partner_id'=>"",
				'phone'=>$value->phone,
				'lat'=>$value->lat,
				'lng'=>$value->lng,
				'is_available'=>3
			);
		}
		return view('dashboard_map',['data'=>$data]);
	}





	/**
	 * To get orders count based on status
	 * 
	 * @param object $request
	 * 
	 * @param array $response 
	 */
	public function get_orders_count(Request $request)
	{
		$restaurant_id = $request->session()->get('userid');
        $role = $request->session()->get('role');
        if($role==1)
        {
            $new_orders = $this->foodrequest->where('status',0)->where('delivery_type',1)->count();
            $processing_orders = $this->foodrequest->whereIn('status',[1,2])->where('delivery_type',1)->count();
            $pickup_orders = $this->foodrequest->whereIn('status',[3,4,5])->where('delivery_type',1)->count();
            $delivered_orders = $this->foodrequest->whereIn('status',[6,7])->where('delivery_type',1)->count();
            $cancelled_orders = $this->foodrequest->whereIn('status',[9,10])->where('delivery_type',1)->count();
            $pickuporder = $this->foodrequest->where('status','!=',7)->where('delivery_type',2)->count();
            $diningorder = $this->foodrequest->where('status','!=',7)->where('delivery_type',3)->count();
        }else
        {
            $new_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->where('status',0)->where('delivery_type',1)->count();
            $processing_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereIn('status',[1,2])->where('delivery_type',1)->count();
            $pickup_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereIn('status',[3,4,5])->where('delivery_type',1)->count();
            $delivered_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereIn('status',[6,7])->where('delivery_type',1)->count();
            $cancelled_orders = $this->foodrequest->where('restaurant_id',$restaurant_id)->whereIn('status',[9,10])->where('delivery_type',1)->count();
            $pickuporder = $this->foodrequest->where('restaurant_id',$restaurant_id)->where('status','!=',7)->where('delivery_type',2)->count();
            $diningorder = $this->foodrequest->where('restaurant_id',$restaurant_id)->where('status','!=',7)->where('delivery_type',3)->count();
        }

		$data = array(
					'new_orders'=> $new_orders,
					'processing_orders'=> $processing_orders,
					'pickup_orders'=> $pickup_orders,
					'delivered_orders'=> $delivered_orders,
					'pickuporder'=> $pickuporder,
					'cancelled_orders'=> $cancelled_orders,
					'diningorder'=> $diningorder
				);
        return $data;
	}

    /**
     * @param $request_id
     * @param $role
     * @param Request $request
     * @return array
     */
	public function manual_assign_driver($request_id,$role,Request $request)
	{
		$old_provider=0;
		$trackorderstatus = $this->trackorderstatus;
		$request_data = $this->foodrequest->where('id',$request_id)->first();
		$restuarant_detail = $this->restaurants->where('id',$request_data->restaurant_id)->first();
		$source_lat = $restuarant_detail->lat;
		$source_lng = $restuarant_detail->lng;
		if($role=='restaurant'){
			$drivers = [];
			$data = $this->deliverypartners->where('restaurant_id',$request_data->restaurant_id)->where('status',1)->where('is_approved',1)->get();
			foreach ($data as $key => $value) {
				$driver_id = $value->id;
				$drivers[]= $this->deliverypartners->where('id',$driver_id)->first();
			}
			return $drivers;
		}else{
			$data = file_get_contents(FIREBASE_URL."/available_providers/.json");
			$data = json_decode($data);
			$temp_driver = [];
			$drivers = [];
			$last_distance = 0;
			if($data != NULL && $data !="")
			{
				foreach ($data as $key => $value) {
					$driver_id = $key;

					//check previous rejected drivers    
					$current_request = file_get_contents(FIREBASE_URL."/current_request/".$request_id.".json");
					$current_request = json_decode($current_request);
					if(isset($current_request->reject_drivers) && !empty($current_request->reject_drivers)) 
					{
						$reject_drivers = explode(',',$current_request->reject_drivers);
						if(in_array($driver_id, $reject_drivers))
						{
							continue;
						}
					}

					if($request->session()->get('role')==1 || $request->session()->get('role')==3){
						$check = $this->deliverypartners->where('id',$driver_id)->where('restaurant_id',0)->where('status',1)->first();
						$check_request = 0;
					}elseif($request->session()->get('role')==2){
						$restaurant_id = $request->session()->get('userid');
						$check = $this->deliverypartners->where('id',$driver_id)->where('restaurant_id',$restaurant_id)->where('status',1)->first();
						$check_request = 0;
					}
					
					if($check && $check_request==0)
					{
						if($old_provider==0){
							$old_provider = -1;
						}
						if($driver_id != $old_provider){
							if ($value != NULL && $value != ""){
								$driver_lat = $value->lat;
								$driver_lng = $value->lng;
								$updated_time = isset($value->updated_at)?$value->updated_at:date("Y-m-d H:i:s");
								$dt = new Carbon($updated_time);
								$last_updated_time = $dt->addMinutes(IDEAL_TIME);
								$current_time = date("Y-m-d H:i:s");
								if(strtotime($last_updated_time) >= strtotime($current_time))
								{
									try 
									{
										$q = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$source_lat,$source_lng&destinations=$driver_lat,$driver_lng&mode=driving&sensor=false&key=".GOOGLE_API_KEY;
										$json = file_get_contents($q); 
										$details = json_decode($json, TRUE);
										if(isset($details['rows'][0]['elements'][0]['status']) && $details['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS'){
											$current_distance_with_unit = $details['rows'][0]['elements'][0]['distance']['text'];
											$current_distance = (float)$details['rows'][0]['elements'][0]['distance']['value'];
											$unit =str_after($current_distance_with_unit, ' ');
											$current_distance = $current_distance/1000;
											$drivers[]= $this->deliverypartners->where('id',$driver_id)->first();
										}
									} catch (Exception $e) {
										
									}
								}
							}
						}
					}
				}
				return $drivers;
			}else
			{
				return $drivers;
			}
		}
	}



	/**
	*  Function for to assign the driver manually
	*  
	*  @param $temp_driver
	*
	*/
	public function assign_driver($temp_driver,$request_id)
	{
		$trackorderstatus = $this->trackorderstatus;
		$request_data = $this->foodrequest->where('id',$request_id)->first();
		$ins_data = array();
		$user_data = $this->foodrequest->find($request_id);
		$user_data->delivery_boy_id = $temp_driver;
		$user_data->status = 2;
		$user_data->save();
		$check_status = $trackorderstatus->where('request_id',$request_id)->where('status',2)->count();
		if($check_status==0)
		{
			 if(SMS_ENABLE==1)
            {
                $sms_message = "Your order ".$user_data->order_id." has been approved by the restaurant.";
                $sendSms = $this->send_otp_softsms($user_data->Users->phone,$sms_message);
            }
			$trackorderstatus->request_id = $request_id;
			$trackorderstatus->status = 2;
			$trackorderstatus->detail = "Food is being prepared";
			$trackorderstatus->save();
		}
	
	  	// to insert into firebase
		$postdata = array();
		$postdata['request_id'] = $request_id;
		$postdata['provider_id'] = (String)$temp_driver;
		$postdata['user_id'] = $user_data->user_id;
		$postdata['status'] = 2;
		$postdata = json_encode($postdata);
		$this->update_firebase($postdata, 'current_request', $request_id);  

		// sending request to driver
		$postdata = array();
		$postdata['request_id'] = $request_id;
		$postdata['user_id'] = $user_data->user_id;
		$postdata['status'] = 1;
        $postdata['timestamp'] = microtime(true) * 1000;
		$postdata = json_encode($postdata);
        $this->update_firebase($postdata, 'new_request', $temp_driver.'/'.$request_id);

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
				'delivery_type' => $request_data->delivery_type
			);
			$this->user_send_push_notification($data);
		}
		return back()->with('success','Providers assigned successfully');
	}

	/**
	*  Function for to list the driver to assign
	*  
	*  @param $id
	*
	*/

	public function assign_driver_list($id,Request $request){
		$restaurant_driver_list = $this->deliverypartners->where('restaurant_id',$id)->where('is_approved',1)->get();
		$admin_driver_list_data = $this->deliverypartners->where('restaurant_id',0)->get();
		$data = file_get_contents(FIREBASE_URL."/available_providers/.json");
		$data = json_decode($data);
		$admin_driver_list = [];
		if($data != NULL && $data !=""){
			foreach($data as $key => $value){
				$driver_id = $key;
				$driver_data = $this->deliverypartners->where('id',$driver_id)->where('restaurant_id',0)->first();
				if($driver_data != ""){
					$driver_status = $driver_data->status;
					if($driver_status == 1){
						$admin_driver_list[] = $driver_data;
					}
				}	
					
			}
		}
		$datas = [
		    'restaurant_driver_list' => $restaurant_driver_list,
		    'admin_driver_list' => $admin_driver_list
		];
		return $datas;
		// return json_encode(array("restaurant_driver_list" => $restaurant_driver_list, "admin_driver_list" => $admin_driver_list));
	}

	public function accept_assign_driver($temp_driver,$request_id,Request $request){
		$restaurant_id = $request->session()->get('userid');

		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;

		$foodrequest->where('id',$request_id)->update(['status'=>1]);

		$trackorderstatus->request_id = $request_id;
		$trackorderstatus->status = 1;
		$trackorderstatus->detail = "Order Accepted by Restaurant";
		$trackorderstatus->save();

		//  $status_entry[] = array(
        //         'request_id'=>$request_id,
        //         'status'=>1,
        //         'detail'=>"Order Accepted by Restaurant"
        //     );
		//   $trackorderstatus->insert($status_entry);
		  
		  $user_data = $this->foodrequest->where('id',$request_id)->first();
		  if($user_data->delivery_type==1){
				// to insert into firebase
				$postdata = array();
				$postdata['request_id'] = $request_id;
				$postdata['user_id'] = $user_data->user_id;
				$postdata['status'] = 1;
				$postdata = json_encode($postdata);
				$this->update_firebase($postdata, 'current_request', $request_id);

		  }

		// return back();
		# code...
		$request_data = DB::table('requests')->where('id',$request_id)->first();
		$user_data = $this->foodrequest->find($request_id);
		$user_data->delivery_boy_id = $temp_driver;
		$user_data->status = 2;
		$user_data->save();


		$check_status = $trackorderstatus->where('request_id',$request_id)->where('status',2)->count();
		if($check_status==0)
		{
			 if(SMS_ENABLE==1)
            {
            	
                $sms_message = "Your order ".$user_data->order_id." has been approved by the restaurant.";
                $sendSms = $this->send_otp_softsms($user_data->Users->phone,$sms_message);
            }
            
			$trackorderstatus->request_id = $request_id;
			$trackorderstatus->status = 2;
			$trackorderstatus->detail = "Food is being prepared";
			$trackorderstatus->save();
		}
	
	  	// to insert into firebase
		$postdata = array();
		$postdata['request_id'] = $request_id;
		$postdata['provider_id'] = (String)$temp_driver;
		$postdata['user_id'] = $user_data->user_id;
		$postdata['status'] = 2;
		$postdata = json_encode($postdata);
		$this->update_firebase($postdata, 'current_request', $request_id);  

		// sending request to driver
		$postdata = array();
		$postdata['request_id'] = $request_id;
		$postdata['user_id'] = $user_data->user_id;
		$postdata['status'] = 1;
		$postdata = json_encode($postdata);
		$this->update_firebase($postdata, 'new_request', $temp_driver); 

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
				'delivery_type' => $request_data->delivery_type
			);
			$this->user_send_push_notification($data);

			
		}
		return back()->with('success','Providers assigned successfully');
	}

	public function assign_notlob_drivers($request_id, Request $request)
	{

		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;

		$foodrequest->where('id',$request_id)->update(['status'=>1]);

		$trackorderstatus->request_id = $request_id;
		$trackorderstatus->status = 1;
		$trackorderstatus->detail = "Order Accepted by Restaurant";
		$trackorderstatus->save();

		$user_data = $foodrequest->where('id',$request_id)->first();
		  if($user_data->delivery_type==1){
				// to insert into firebase
				$postdata = array();
				$postdata['request_id'] = $request_id;
				$postdata['user_id'] = $user_data->user_id;
				$postdata['status'] = 1;
				$postdata = json_encode($postdata);
				$this->update_firebase($postdata, 'current_request', $request_id);

		  }

		 //  	$user_data = $foodrequest->find($request_id);
			// $user_data->delivery_boy_id = $temp_driver;
			$user_data->status = 2;
			$user_data->save();

		  $check_status = $trackorderstatus->where('request_id',$request_id)->where('status',2)->count();
		if($check_status==0)
		{
			 if(SMS_ENABLE==1)
            {
            	
                $sms_message = "Your order ".$user_data->order_id." has been approved by the restaurant.";
                if(isset($user_data->Users))
                {
                	$sendSms = $this->send_otp_softsms($user_data->Users->phone,$sms_message);
                }
                
            }

			$trackorderstatus->request_id = $request_id;
			$trackorderstatus->status = 2;
			$trackorderstatus->detail = "Food is being prepared";
			$trackorderstatus->save();
		}
	
	  	// to insert into firebase
	  	$temp_driver = 0;
		$postdata = array();
		$postdata['request_id'] = $request_id;
		$postdata['provider_id'] = (String)$temp_driver;
		$postdata['user_id'] = $user_data->user_id;
		$postdata['status'] = 2;
		$postdata = json_encode($postdata);
		$this->update_firebase($postdata, 'current_request', $request_id);  


		$admin_driver_list_data = $this->deliverypartners->where('restaurant_id',0)->get();
		$data = file_get_contents(FIREBASE_URL."/available_providers/.json");
		$data = json_decode($data);
	
		if($data != NULL && $data !=""){
			foreach($data as $key => $value){
				$driver_id = $key;
				$driver_data = $this->deliverypartners->where('id',$driver_id)->where('restaurant_id',0)->first();
				if($driver_data != ""){
					$driver_status = $driver_data->status;
					if($driver_status == 1){

						Log::info('Assining to Notlob Drivers :'.$driver_id);
						$temp_drivers_list[] = $driver_id;

								// sending request to driver
						$postdata = array();
						$postdata['request_id'] = $request_id;
						$postdata['user_id'] = $user_data->user_id;
						$postdata['status'] = 1;
						$postdata = json_encode($postdata);
						$this->update_firebase($postdata, 'new_request', $driver_id); 

						//send push notification to user
						$provider = $this->deliverypartners->find($driver_id);
						if(isset($provider->device_token) && $provider->device_token!='')
						{
							$title = $message = trans('constants.new_order');
							$data = array(
								'device_token' => $provider->device_token,
								'device_type' => $provider->device_type,
								'title' => $title,
								'message' => $message,
								'request_id' => $request_id,
								'delivery_type' => $user_data->delivery_type
							);
							$this->user_send_push_notification($data);

							
						}
						
					}
				}	
					
			}

			$temp_drivers_list = implode(",",$temp_drivers_list);

			$user_data->temp_drivers = $temp_drivers_list;
			$user_data->save();

			return back()->with('success','Request sent to '.APP_NAME.' drivers');
		}
		
		return back()->with('error','No Providers available');
	}

	public function notify_restaurant_for_new_orders($restaurant_id,Request $request)
	{
        $request_data = $this->foodrequest::where('restaurant_id',$restaurant_id)->where('status',0)->first();
		// $ordered_time = Carbon::createFromFormat($request_data->ordered_time);
		if($request_data)
		{
			$ordered_time =new DateTime($request_data->ordered_time);

	        $current_time = new DateTime();
	        $diff = date_diff($ordered_time, $current_time);
			$time_difference = $diff->format("%i");
			// echo $time_difference;
			// echo $diff->format("%a days %h Hours %i Minute %s Seconds ");
	        // echo $diff;
	        if($time_difference>=5)
	        {
	        	return json_encode(array("status" =>"true"));
	        }
	    }

	    return json_encode(array("status" =>"false"));
	}


	/**
	* assign without driver function
	*
	* @param $id,$status,object $request
	*
	* @return to the blade page
	*/

	public function assign_without_driver($id,$status,Request $request){
		$request_id = $id;
		$restaurant_id = $request->session()->get('userid');
		$foodrequest = $this->foodrequest;
		$trackorderstatus = $this->trackorderstatus;
		$request_detail = $this->foodrequest->find($request_id);
		$user_data = $this->foodrequest->where('id',$request_id)->first();
		if($status==1){
			$foodrequest->where('id',$request_id)->update(['status'=>1]);

			$check_same_status_exist = $trackorderstatus::where('request_id',$request_id)->where('status',1)->first();

			if(!$check_same_status_exist)
			{
				$message = "Order Accepted by Restaurant";
				$trackorderstatus->request_id = $request_id;
				$trackorderstatus->status = 1;
				$trackorderstatus->detail = "Order Accepted by Restaurant";
				$trackorderstatus->save();
			  	if($user_data->delivery_type==1){
					// to insert into firebase
					$postdata = array();
					$postdata['request_id'] = $request_id;
					$postdata['user_id'] = $user_data->user_id;
					$postdata['status'] = 1;
					$postdata = json_encode($postdata);
					$this->update_firebase($postdata, 'current_request', $request_id);

			  	}

			  	 if(SMS_ENABLE==1)
	            {
	            	
	                $sms_message = "Your order ".$user_data->order_id." has been approved by the restaurant.";
	                $sendSms = $this->send_otp_softsms($user_data->Users->phone,$sms_message);
	            }

			  	if(isset($request_detail->Users->device_token) && $request_detail->Users->device_token!='')
				{
					$title = trans('constants.order_status_update');
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
			}
			
		}
		if($status==3){
			$message = "Your food is prepared. Our Rider is on its way";
			$foodrequest->where('id',$request_id)->update(['status'=>3,'delivery_boy_id'=>99]);

			$check_same_status_exist = $trackorderstatus::where('request_id',$request_id)->where('status',2)->first();
			if(!$check_same_status_exist)
			{
				$trackorderstatus->request_id = $request_id;
				$trackorderstatus->status = 2;
				$trackorderstatus->detail = "Your food is prepared.";
				$trackorderstatus->save();
			}
			
			$check_same_status_exist1 = $trackorderstatus::where('request_id',$request_id)->where('status',3)->first();
			if(!$check_same_status_exist1)
			{
				$trackorderstatus->request_id = $request_id;
				$trackorderstatus->status = 3;
				$trackorderstatus->detail = "Our Rider is on its way";
				$trackorderstatus->save();
			}
			
		  // to insert into firebase
			$postdata = array();
			$postdata['request_id'] = $request_id;
			$postdata['provider_id'] = (string)99;
			$postdata['user_id'] = $user_data->user_id;
			$postdata['status'] = 3;
			$postdata = json_encode($postdata);
			$this->update_firebase($postdata, 'current_request', $request_id); 

			if(SMS_ENABLE==1)
            {
            	
            	$restaurant_data = $this->restaurants::find($request_detail->restaurant_id);
                $sms_message = "Your order from ".$restaurant_data->restaurant_name." is on the way!";
                $sendSms = $this->send_otp_softsms($request_detail->Users->phone,$sms_message);
            }

			if(isset($request_detail->Users->device_token) && $request_detail->Users->device_token!='')
			{
				$title = trans('constants.order_status_update');
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
		}

		if($status==7){
			$message = "Thank you for placing an order with us. Hope you enjoy your food. Bon Appetite.";
			$restaurant_id = $request->session()->get('userid');
			$foodrequest = $this->foodrequest;
			$trackorderstatus = $this->trackorderstatus;

			$foodrequest->where('id',$request_id)->update(['status'=>7,'is_paid'=>1]);

			$check_same_status_exist = $trackorderstatus::where('request_id',$request_id)->where('status',7)->first();

			if(!$check_same_status_exist)
			{
				$trackorderstatus->request_id = $request_id;
				$trackorderstatus->status = 7;
				$trackorderstatus->detail = "Thank you for placing an order with us. Hope you enjoy your food. Bon Appetite.";
				$trackorderstatus->save();
			}

			

			$postdata = array();
			$postdata['status'] = 7;
			$postdata = json_encode($postdata);
			$this->update_firebase($postdata, 'current_request', $request_id); 

			if(isset($request_detail->Users->device_token) && $request_detail->Users->device_token!='')
			{
				$title = trans('constants.order_status_update');
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
		}

		return back();
		
	}

}