<?php

namespace App\Http\Controllers\admin;
          
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController;
use DB;
use App\Service\CCaveEncrypt;
use App\Model\Settings;

class TransactionController extends BaseController
{
    /**
     * Get Driver (or) Restaurant Payouts.
     *
     * @param $type Restaurant (or) driver
     *
     * @return response to blade file
     */
    public function Getpayout($type,Request $request)
    {
        if($type=='restaurant'){

            $data = $this->restaurants->with('Foodrequest')
                         ->withcount('Foodrequest')
                        //  ->whereHas('Foodrequest', function ($query1) {
                        //       $query1->where('status',7)
                        //             ->where('restaurant_settlement_status',0);
                        //  })
                        ->where('pending_payout','!=','0')
                        ->get();
          
        }elseif($type=='driver'){
            $data = $this->deliverypartners->with('Foodrequest')
                         ->withcount('Foodrequest')
                        //  ->whereHas('Foodrequest', function ($query) {
                        //       $query->where('status',7)
                        //             ->where('driver_settlement_status',0);
                        //  })
                        ->where('pending_payout','!=','0')
                        ->get();
        }
        return view('payout',compact('type','data'));
    }

     /**
     * Get Driver (or) Restaurant Payout history.
     *
     * @param $type Restaurant (or) driver
     *
     * @return response to blade file
     */
    public function Getpayout_history($type,Request $request)
    {

        if($type=='restaurant'){            
            $data = $this->restaurant_payout_history->with('Restaurants')->orderBy('id','desc')->get();
        }elseif($type=='driver'){
            $data = $this->driver_payout_history->with('Deliverypartners')->orderBy('id','desc')->get(); 
          
        }
        return view('payout-history',compact('type','data'));
    }
    public function payoutStatus(Request $request)
    {
        $payout_id = $request->payout_id;
        $merchant_ref_no = $request->merchant_ref_no;
        $payout_trans_id = $request->payout_trans_id;
        $data = $this->restaurant_payout_history->with('Restaurants')->where('id',$payout_id)->first();
        $response = $data; 
        return response()->json($response, 200);

    }

     /**
     * Display add-payout Blade file.
     *
     * @param $type, $id
     *
     * @return response view blade file
     */
    public function Getaddpayment($type,$amount,$id,Request $request)
    {
        return view('add-payout',compact('type','amount','id'));
    }

     /**
     * Post payout to driver or restaurant based on type.
     *
     * @param $type, $id
     *
     * @return response view blade file
     */
    public function addpayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required',

        ]);

        $transaction_id = 'Trans-'.$this->generate_random_string();

        if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('error', $error_messages);

        }
        $type = $request->type;
        $randomNumber = mt_rand(100000000, 9999999999);  
        $amount = (float)$request->amount;
        $data = $this->restaurants->with('RestaurantBankDetails')->where('id',$request->id)->first();
        $data = Settings::pluck('value','key_word')->toArray();
                if($type=='restaurant'){ 
                // $requestdetails = $this->foodrequest->where('restaurant_id',$request->id)->update(['restaurant_settlement_status'=>1]);
                    $this->restaurants->find($request->id)->decrement('pending_payout', $request->amount );
                    $insert = $this->restaurant_payout_history;
                    $insert->restaurant_id = $request->id;
                }elseif($type=='driver'){
                    //$requestdetails = $this->foodrequest->where('delivery_boy_id',$request->id)->update(['driver_settlement_status'=>1]);
                    $this->deliverypartners->find($request->id)->decrement('pending_payout', $request->amount );
                    $insert = $this->driver_payout_history;
                    $insert->delivery_boy_id = $request->id;
                }
                $date = date('Y:m:d h:i:s');            
                $insert->transaction_id = $transaction_id;
                $insert->payout_amount = $request->amount;
                $insert->description = $request->description;
                $insert->status = 'Success';
                $insert->merchant_ref_no = NULL;
                $insert->trans_status = 'SUCCESS';
                $insert->payout_trans_id = NULL;
                $insert->trans_datetime = $date;
                $insert->failure_remark = NULL;
                $insert->save(); 
                $success = 'Payment Successfully Completed';
        return redirect('admin/payout/'.$type)->with('success', $success);

    }
}
