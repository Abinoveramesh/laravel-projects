<?php

namespace App\Http\Controllers\admin;
    
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController;
use DB;
use App\Model\DeliveryInstruction;
use App\Http\Requests\Admin\DeliveryInstructionRequest;

class SettingController extends BaseController
{
    /**
     * Get the settings data based on type.
     *
     * @return value to blade file
     */
    public function Getsettings($type)
    {
        $data = $this->settings->pluck('value','key_word')->toArray();
     //   dd($data);
        if($type=='site'){
            return view('site-settings',compact('data'));
        }elseif($type=='email'){
            return view('email-settings',compact('data'));
        }else{
            return view('google-settings',compact('data'));
        }
    }

    /**
     * Post the request data in setting table.
     *
     * @param Request $request
     *
     * @return value to blade file
     */
    public function Updatesetting(Request $request)
    {
        // dd($request->all());
        $type = $request->type;
        $settings = $this->settings;

        if($type == 'site'){

            $check = $this->settings->where('type',1)->get();

        }elseif($type == 'email'){

            $check = $this->settings->where('type',2)->get();

        }else{

            $check = $this->settings->where('type',3)->get();

        }

        $rules = array();

        foreach ($check as $key => $value) {

            $validate_field = '$request->'.$value->key_word;
       
            if ($value->key_word != 'site_favicon' && $value->key_word != 'site_logo' && $value->key_word !='email_user_name' && $value->key_word !='email_password') {
                $rules[$value->key_word] = 'required';
            }

            if ($value->key_word == 'site_favicon')
            {
                $rules[$value->key_word] = 'mimes:jpeg,ico,jpg,png,gif|max:2000';
            }

            if ($value->key_word == 'site_logo')
            {
                $rules[$value->key_word] = 'mimes:jpeg,ico,jpg,png,gif|max:2000';
            }

        }
        unset($rules['user_android_version']);
        //dd($rules);  
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('error', $error_messages);

        }

        if($type == 'site'){

            if($request->admin_commission !=""){
                $settings->where('key_word','admin_commission')
                         ->update(['value'=>$request->admin_commission]);
            }

            if($request->restaurant_commission !=""){
                $settings->where('key_word','restaurant_commission')
                         ->update(['value'=>$request->restaurant_commission]);
            }

            if($request->delivery_boy_commission !=""){
                $settings->where('key_word','delivery_boy_commission')
                         ->update(['value'=>$request->delivery_boy_commission]);
            }

            if($request->stripe_sk_key !=""){
                $settings->where('key_word','stripe_sk_key')
                         ->update(['value'=>$request->stripe_sk_key]);
            }
            
            if($request->app_name !=""){
                $settings->where('key_word','app_name')
                         ->update(['value'=>$request->app_name]);
            }
            
            if($request->partner_notification_key !=""){
                $settings->where('key_word','partner_notification_key')
                         ->update(['value'=>$request->partner_notification_key]);
            }

            if($request->provider_timeout !=""){
                $settings->where('key_word','provider_timeout')
                         ->update(['value'=>$request->provider_timeout]);
            }

            
            if($request->default_radius !=""){
                $settings->where('key_word','default_radius')
                         ->update(['value'=>$request->default_radius]);
            }
            
            if($request->highlight_color !=""){
                $settings->where('key_word','highlight_color')
                         ->update(['value'=>$request->highlight_color]);
            }
            
            if($request->menu_color !=""){
                $settings->where('key_word','menu_color')
                         ->update(['value'=>$request->menu_color]);
            }

            if($request->site_contact !=""){
                $settings->where('key_word','site_contact')
                         ->update(['value'=>$request->site_contact]);
            }

            if($request->site_email !=""){
                $settings->where('key_word','site_email')
                         ->update(['value'=>$request->site_email]);
            }

            if($request->site_favicon !=""){


                $site_favicon = $this->custom->restaurant_upload_image($request,'site_favicon');

                $settings->where('key_word','site_favicon')
                         ->update(['value'=>$site_favicon]);

            }

            if($request->site_logo !=""){
               
                $site_logo = $this->custom->restaurant_upload_image($request,'site_logo');
          
                $settings->where('key_word','site_logo')
                         ->update(['value'=>$site_logo]);
           
            }

            if($request->default_unit !=""){
                $settings->where('key_word','default_unit')
                         ->update(['value'=>$request->default_unit]);
            }

            if($request->email_enable !=""){
                $settings->where('key_word','email_enable')
                         ->update(['value'=>$request->email_enable]);
            }

            if($request->sms_enable !=""){
                $settings->where('key_word','sms_enable')
                         ->update(['value'=>$request->sms_enable]);
            }

            if($request->time_zone !=""){
                $settings->where('key_word','time_zone')
                         ->update(['value'=>$request->time_zone]);
            }

            if($request->country_currency !=""){
                $setting = $settings->where('key_word','country_currency')->first();
                if(empty($setting) || $setting == null)
                {
                  $settings->key_word = 'country_currency';
                  $settings->value = $request->country_currency;
                  $settings->save();              
                }else{
                  $settings->where('key_word','country_currency')
                           ->update(['value'=>$request->country_currency]);
                }
              }
            
            if($request->order_prefix !=""){
                $settings->where('key_word','order_prefix')
                         ->update(['value'=>$request->order_prefix]);
            }

            if($request->idel_time !=""){
                $settings->where('key_word','idel_time')
                         ->update(['value'=>$request->idel_time]);
            }

            if($request->loyalty_point !=""){
                $settings->where('key_word','loyalty_point')
                         ->update(['value'=>$request->loyalty_point]);
            }
            if($request->Maximum_loyalty_points !=""){
                $settings->where('key_word','Maximum_loyalty_points')
                         ->update(['value'=>$request->Maximum_loyalty_points]);
            }
            if($request->loyalty_amount !=""){
                $settings->where('key_word','loyalty_amount')
                         ->update(['value'=>$request->loyalty_amount]);
            }
            if($request->user_ios_delete_key !=""){
                $settings->where('key_word','user_ios_delete_key')
                         ->update(['value'=>$request->user_ios_delete_key]);
            }
            if($request->rider_ios_delete_key !=""){
                $settings->where('key_word','rider_ios_delete_key')
                         ->update(['value'=>$request->rider_ios_delete_key]);
            }
            if($request->admin_commission !=""){
                $settings->where('key_word','admin_commission')
                         ->update(['value'=>$request->admin_commission]);
            }
            if($request->admin_gst !=""){
                $settings->where('key_word','admin_gst')
                         ->update(['value'=>$request->admin_gst]);
            }
            if($request->restaurant_commission !=""){
                $settings->where('key_word','restaurant_commission')
                         ->update(['value'=>$request->restaurant_commission]);
            }
            if($request->user_toll_number !=""){
                $settings->where('key_word','user_toll_number')
                         ->update(['value'=>$request->user_toll_number]);
            }
            if($request->rider_toll_number !=""){
                $settings->where('key_word','rider_toll_number')
                         ->update(['value'=>$request->rider_toll_number]);
            }
            if($request->ccavenue_payment !=""){
                $settings->where('key_word','ccavenue_payment')
                         ->update(['value'=>$request->ccavenue_payment]);
            }
            if($request->ccavenue_refund !=""){
                $settings->where('key_word','ccavenue_refund')
                         ->update(['value'=>$request->ccavenue_refund]);
            }
            if($request->b2biz_payment !=""){
                $settings->where('key_word','b2biz_payment')
                         ->update(['value'=>$request->b2biz_payment]);
            }
            
        }elseif($type == 'email'){
            
            if($request->email_user_name !=""){
                $settings->where('key_word','email_user_name')
                         ->update(['value'=>$request->email_user_name]);
            }

            if($request->email_password !=""){
                $settings->where('key_word','email_password')
                         ->update(['value'=>$request->email_password]);
            }
        }else{
       
            if($request->google_api_key !=""){
                $settings->where('key_word','google_api_key')
                         ->update(['value'=>$request->google_api_key]);
            }

            if($request->android_api_key !=""){
                $settings->where('key_word','android_api_key')
                         ->update(['value'=>$request->android_api_key]);
            }

            if($request->ios_api_key !=""){
                $settings->where('key_word','ios_api_key')
                         ->update(['value'=>$request->ios_api_key]);
            }

            if($request->firebase_url !=""){
                $settings->where('key_word','firebase_url')
                         ->update(['value'=>$request->firebase_url]);
            }

            if($request->user_notification_key !=""){
                $settings->where('key_word','user_notification_key')
                         ->update(['value'=>$request->user_notification_key]);
            }

            if($request->partner_notification_key !=""){
                $settings->where('key_word','partner_notification_key')
                         ->update(['value'=>$request->partner_notification_key]);
            }

            if($request->stripe_sk_key !=""){
                $settings->where('key_word','stripe_sk_key')
                         ->update(['value'=>$request->stripe_sk_key]);
            }

            if($request->stripe_pk_key !=""){
                $settings->where('key_word','stripe_pk_key')
                         ->update(['value'=>$request->stripe_pk_key]);
            }

        }
        return back()->with('success',trans('constants.update_success_msg',['param'=>'Setting']));
    }

    /**
     * Get the blade file.
     *
     * @return value to blade file
     */
    public function Getaddemail()
    {
        return view('add_email');
    }

    /**
     * Get the email template list.
     *
     * @return value to blade file
     */
    public function Getemailtemplate()
    {
        return view('email-template-list');
    }


    /**
     * Get the cms page based on page name.
     * 
     * @param string $page
     *
     * @return value to blade file
     */
    public function getcms_page($page)
    {
        $data = $this->cms->where('page_name',$page)->first();
        return view('cms_page',compact('data'));
    }


    /**
     * Update the cms page based on page name.
     * 
     * @param string $page, Object $request
     *
     * @return value to blade file
     */
    public function updatecms_page($page, Request $request)
    {
        $data = $this->cms->find($request->id);
        $data->title = $request->title;
        $data->description = $request->description;
        $data->save();
        
        return back()->with('success',__('constants.update_success_msg',['param'=>'Content']));
    }


    /**
     * Get the cms content based on page name.
     * 
     * @param string $page, Object $request
     *
     * @return value to blade file
     */
    public function viewcms_page($page, Request $request)
    {
        $data = $this->cms->where('page_name',$page)->first();
        return view('pages')->with('data',$data);
    }

     /**
     * to show the cms detail page 
     * 
     * @return value to blade file
     */
    public function getcms_detail()
    {
        $data = $this->cms->get();
        //dd($data);
        return view('cms_list',compact('data'));
    }

    /**
     * to show instruction list
     * @return value to blade file
     */
    public function instruction_setting(){
        $data = DeliveryInstruction::get();
        return view('instruction_setting_list',compact('data'));
    }

    /**
     * @return value to blade file
     */
    public function add_delivery_instruction(){
        return view('add_delivery_instruction');
    }

    /**
     * To store the delivery instruction
     * @param object $request
     * @return value to blade file
     */
    public function create_delivery_instruction(DeliveryInstructionRequest $request){
        $data = new DeliveryInstruction();
        if(!empty($request->id)){
            $data = $data->find($request->id);
        }
        if($request->image !=""){
            $image = $this->custom->restaurant_upload_image($request,'image');
        }
        $data->instruction = !empty($request->instruction)?$request->instruction:"";
        if(!empty($image)){
            $data->image = $image;
        }        
        $data->save();
        return redirect('/admin/instruction_setting')->with('success',trans('constants.add_instruction_suc_msg'));
    }

    /**
     * To go to edit delivery instruction page
     * @param $id
     * @return to view page
     */
    public function edit_delivery_instruction($id){
        if(!empty($id)){
            $data = new DeliveryInstruction();
            $data = $data->find($id);
            return view('add_delivery_instruction',compact('data'));            
        }else{
            return back()->with('error',trans('constants.bad_request'));
        }        
    }

    /**
     * To delete the delivery instruction
     * @param object $request
     * @return value to blade file
     */
    public function delete_delivery_instruction(Request $request){
        if(!empty($request->id)){
            $data = new DeliveryInstruction();
            $data = $data->where('id',$request->id)->delete();
            return redirect('/admin/instruction_setting')->with('success',trans('constants.deleted_suc_msg'));
        }else{
            return back()->with('error',trans('constants.bad_request'));
        }   
    }
}
