<?php

namespace App\Http\Controllers\admin;
                                    
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use DB;

class UserController extends BaseController
{
	public function user_list(Request $request)
	{
		if(!empty($request->city_id) && $request->city_id != 'all') {
			$user_detail = $this->users->where('state_id',$request->city_id)->get();
		}else {
			$user_detail = $this->users->get();
		}
		$city = $this->state->get();
		return view('user_list',['user_detail'=>$user_detail , 'city'=>$city ,'city_id'=>$request->city_id]);
	}
	
	public function Adduser(Request $request)
	{
		return view('add_user');
	}

	public function payment_true($user_id)
	{
		$user_detail = $this->users::find($user_id);
		$user_detail->is_paid = 1;
		$user_detail->save();

		echo "Success";
	}

	public function payment_false($user_id)
	{
		$user_detail = $this->users::find($user_id);
		$user_detail->is_paid = 2;
		$user_detail->save();

		echo "Failed";
	}

    public function delete_user(Request $request)
    {
        $user_id = $request->id;
        $this->users->where('id',$user_id)->delete();

        return redirect('/admin/user_list')->with('success','User Deleted Successfully');
    }
}
