<?php

namespace App\Http\Controllers\admin;
                                    
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use DB;
use App\Jobs\SendPushNotificationJob;
use Log;

class NoteboardController extends BaseController
{

    /**
     * function to get notification list 
     * @param no param
     * @return array o blade file
     */

	public function noticeboard_list()
	{
		return view('noticeboard_list');
	}

    /**
     * function to View add_noticeboard blade file 
     * @param no param
     * @return view add_noticeboard
     */

	public function add_noticeboard()
	{
		return view('add_noticeboard');
	}

    /**
     * function to View custum push blade file 
     * @param no param
     * @return view custumpush
     */

	public function custumpush()
	{
		return view('custumpush');
	}

    /**
     * function to send push notification based send to user , provider or all 
     * @param Request param
     * @return back with success response
     */
    public function send_custumpush(Request $request)
    {
        Log::info($request);
        dispatch(new SendPushNotificationJob($request->title, $request->message, $request->image, $request->send_to,$request->user_id,$request->driver_id,$request->status,$request->customstartdate,$request->customenddate,$request->city));
        return response()->json(array('status' => true), 200);
    }


	public function base_image_upload_product($request,$key)    
    {        
        $imageName = $request->file($key)->getClientOriginalName();       
         $ext = $request->file($key)->getClientOriginalExtension();
         $imageName = $this->generate_random_string().'.'.$ext;        
         $request->file($key)->move('public/promo_images/',$imageName);       
         return $imageName;
    }
}