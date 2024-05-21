<?php
namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use DB;
use App\Model\Users;
use App\Http\Controllers\api\BaseController;
use Illuminate\Support\Facades\Log;
use App\Model\Cards;
use App\Model\Settings;
use App\Model\Transactions;
use App\Library\Payment;
use Validator;

class StripeController extends BaseController 
{
	//

    public function get_balance(Request $request)
    {
        $user = Users::find($request->header('authId'));
         $response_array = array('status' => true,'wallet_balance'=>$user->wallet_balance);
        $response = response()->json($response_array,200);
        return $response;
    }

    public function user_add_card_stripe(Request $request)
    {
        
        $user = Users::find($request->header('authId'));
        $payment = Cards::where('user_id',$request->header('authId'))->where('is_default',1)->first();

        $validator = Validator::make(
                    $request->all(),
                    array(
                        'last_four' => 'required',
                        'card_token' => 'required',
                        'customer_id' => 'required',
                    )
                );

        if ($validator->fails())
        {
           $error_messages = implode(',', $validator->messages()->all());
           $response_array = array('status' => false , 'error_code' => 101 , 'message' => $error_messages);

        } else {


            $customer_id = $request->customer_id;

            $cards = new Cards;
            $cards->user_id = $user->id;
            $cards->customer_id = $request->customer_id;
            $cards->last_four = $request->last_four;
            $cards->card_token = $request->card_token;

            // Check is any default is available
            $check_card = Cards::where('user_id',$request->id)->first();

            if($check_card ) {
                $cards->is_default = 0;
            } else {
                $cards->is_default = 1;
            }

            $cards->save();

            if($user) {
                // $user->payment_mode = CARD;
                $user->default_card = $check_card ? $user->default_card : $cards->id;
                $user->save();
            }

            $response_array = array('status' => true,'message'=>"Thanks for adding your Card");
        }

        $response = response()->json($response_array,200);
        return $response;
    }


    public function get_stripe_token(Request $request)
    {
    	$data=DB::table('settings')->where('key_word', '=', 'stripe_pk_key')->select('value')->first();
    	//$data=DB::table('settings')->where('key_word', '=', 'stripe_sk_key')->select('value')->first();
    	$token = $data->value;
    	return response()->json(['status'=>true,'token'=>$token]);
    }

      public function getCards(Request $request)
    {
    	$user_id = $request->header('authId');
    	$data = Cards::where('user_id',$user_id)->get();	
    	return response()->json(['status'=>true,'data'=>$data]);
    }

    public function add_money_to_wallet(Request $request)
    {

          $validator = Validator::make(
                    $request->all(),
                    array(
                        'amount' => 'required',
                    )
                );

        if ($validator->fails())
        {
           $error_messages = implode(',', $validator->messages()->all());
           $response_array = array('status' => false , 'error_code' => 101 , 'message' => $error_messages);

        } else 
        {

                $user = Users::find($request->header('authId'));
                $check_card_exists = Users::where('users.id' , $user->id)
                                ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                ->where('cards.id' , $user->default_card)
                                ->where('cards.is_default' , 1);

                    if($check_card_exists->count() != 0) {

                        $user_card = $check_card_exists->first();
                          //Stripe Payment
                          $stripe_charge = Payment::stripeCreateCharge($request->amount, $user_card);
                      
                          //if($transaction == '0'){
                          if(!$stripe_charge->success){
                            $response_array = array('success' => false, 'error_code' => 158);
                            return response()->json($response_array , 200);
                          }
                          else {
                                $user->wallet_balance = $user->wallet_balance + $request->amount;
                                $user->save();
                             $response_array = array('status'=>true, 'message' => 'Money added successfully', 'wallet_balance'=>$user->wallet_balance);
                          }
                    
                    }else
                    {
                        $response_array = array('status'=>false, 'message' => 'No Cards Found');
                    }

        }
        return response()->json($response_array , 200);

    }

   


public function default_card(Request $request) {

   $user_id = $request->header('authId');

   $validator = Validator::make(
    $request->all(),
    array(
        'card_id' => 'required|integer|exists:cards,id,user_id,'.$user_id,
    )
);

   if($validator->fails()) {

    $error_messages = implode(',', $validator->messages()->all());
    $response_array = array('status'=>false, 'message'=>$error_messages);

} else {

    $user = Users::find($request->id);

    $old_default = Cards::where('user_id' , $user_id)->where('is_default', 1)->update(array('is_default' => 0));

    $card = Cards::where('id' , $request->card_id)->update(array('is_default' => 1));

    if($card) {
        if($user) {
            $user->default_card = $request->card_id;
            $user->save();
        }
        $response_array = array('status'=>true, 'messages'=>'Default card changed successfully');
    } else {
        $response_array = array('status'=>false, 'error' => 'Something went wrong');
    }
}
return response()->json($response_array , 200);

}

    public function selectCard(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$request->id,
            )
        );

        if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error_message' =>$error_messages , 'error_code' => 101, 'message'=>$error_messages);

        } else {

            if($request->payment_mode == CARD){

                $user = User::find($request->id);

                $old_default = Cards::where('user_id' , $request->id)->where('is_default', 1)->update(array('is_default' => 0));

                $card = Cards::where('id' , $request->card_id)->update(array('is_default' => 1));

                if($card) {
                    if($user) {
                        // $user->payment_mode = CARD;
                        $user->default_card = $request->card_id;
                        $user->save();
                    }
                    $response_array = Helper::null_safe(array('success' => true));
                } else {
                    $response_array = array('status' => false , 'message' => 'Something went wrong');
                }

                Log::info("default card changed");
            }else{
                Log::info("payment_mode is different".print_r($request->payment_mode,true));

                $response_array = array(
                'success' => true
                );
            }

        }
        $response = response()->json($response_array, 200);
        return $response;

    }


 public function delete_card(Request $request) {

       $user_id = $request->header('authId');

        $card_id = $request->card_id;

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$user_id,
            ),
            array(
                'exists' => 'The :attribute doesn\'t belong to user:'.$user_id
            )
        );

        if ($validator->fails()) {
           $error_messages = implode(',', $validator->messages()->all());
           $response_array = array('status'=>false, 'messages' => $error_messages);
        } else {

            Cards::where('id',$card_id)->delete();

            $user = Users::find($user_id);

            if($user) {

                    if($check_card = Cards::where('user_id' , $user_id)->first()) {

                    $old_default = Cards::where('user_id' , $user_id)->where('is_default', 1)->update(array('is_default' => 0));

                    $card = Cards::where('id' , $check_card->id)->update(array('is_default' => 1));
                   
                }

                $user->save();
            }

            $response_array = array('status'=>true, 'messages'=>'Card deleted successfully');
        }

        return response()->json($response_array , 200);
    }


}
