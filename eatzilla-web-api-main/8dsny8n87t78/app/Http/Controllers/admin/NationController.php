<?php

namespace App\Http\Controllers\admin;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController;

class NationController extends BaseController
{
    /**
     * Get country list.
     *
     * @return value to blade file
     */
    public function Getcountrylist()
    {
        $data = $this->country->get();
        return view('country-list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Addcountry()
    {
        return view('add-country');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function AddEditcountry($id)
    {
        $data = $this->country->find($id);
        return view('add-country',compact('data'));
    }

    /**
     * Update the country.
     * @param  Request $request param
     * @return success response 
     */
    public function Savecountry(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'currency_code' => 'required|max:5',
            'currency_symbol' => 'required|max:3',
            'country_code' => 'required|max:5',
            'country' => 'required',

        ]);
         if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('error', $error_messages)->withInput();

        }
        $id = $request->id;
        if($id !="" || $id !=null){
            $check = $this->country->where('id','!=',$id)->where('country',$request->country)->count();

            $path= 'admin/edit_country/'.$id;
            $insert = $this->country->find($id);
        }else{
            $check = $this->country->where('country',$request->country)->count();
            $path= 'admin/country_list';
            $insert = $this->country;

        }     
        if($check !=0){
            return back()->with('error', trans('constants.already_exist',['param'=>'Country is ']))->withInput();
        }

            $insert->country = $request->country;
            $insert->country_code = $request->country_code;
            $insert->currency_code = $request->currency_code;
            $insert->currency_symbol = $request->currency_symbol;
            $insert->save();

        return redirect($path)->with('success',trans('constants.save_success_msg'));

    }

    /**
     * Set country to default country.
     *
     * @return back to blade file
     */
    public function Defaultcountry($id)
    {
        $this->country->where('is_default',1)->update(['is_default'=>0]);
        $country_update = $this->country->find($id);
        $country_update->is_default = 1;
        $country_update->save();  

        return back()->with('success',trans('constants.update_success_msg_country'));
  
    }

    /**
     * Get New State list.
     *
     * @return value to blade file
     */
    public function GetNewStateList()
    {
        $data = $this->newState->get();
        return view('new-state-list',compact('data'));
    }

    /**
     * Create New State.
     *
     * @return value to blade file
     */
    public function AddNewState()
    {
        return view('add-new-state');
    }

    /**
     * Save New State.
     * @param  Request $request param
     * @return success response 
     */
    public function SaveNewState(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state' => 'required',
            'country' => 'required',
        ]);

        if($validator->fails()) {
            $error_messages = implode(',',$validator->messages()->all());
            return back()->with('error', $error_messages)->withInput();
        }

        if(!empty($request->id)) {
            $check = $this->newState->where('id','!=',$request->id)
                            ->where('country_id',5)
                            ->where('state',$request->state)
                            ->count();
            if($check !=0){
                return back()->with('error', trans('constants.already_exist',['param'=>'State is ']))->withInput();
            }
            $this->newState->where('id',$request->id)->update(['state' => $request->state]);
            return redirect('admin/new_state_list')->with('success',trans('constants.update_state'));
        }else {
            $check = $this->newState->where('country_id',5)
                            ->where('state',$request->state)
                            ->count();
            if($check !=0){
                return back()->with('error', trans('constants.already_exist',['param'=>'State is ']))->withInput();
            }
            $insert = $this->newState;
            $insert->country_id = 5;
            $insert->state = $request->state;
            $insert->save();

            return redirect('admin/new_state_list')->with('success',trans('constants.save_success_msg'));
        }
    }

     /**
     * Edit New State.
     * @param  Request $request param
     * @return success response 
     */
    public function EditNewState($id)
    {
        $data = $this->newState->where('id',$id)->first();
        return view('add-new-state',compact('data'));
    }

    /**
     * Delete the New State.
     * @param  Request $request param
     * @return success response 
     */
    public function DeleteNewState($id)
    {
        $this->newState->where('id',$id)->delete();
        return back()->with('success',trans('constants.delete_state'));
    }

    /**
     * Get State list.
     *
     * @return value to blade file
     */
    public function Getstatelist()
    {
        $data = $this->state->with('Country','NewState')->get();
        return view('state-list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Addstate()
    {
        $newState = $this->newState->get();
        return view('add-state',compact('newState'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return value to blade file
     */
    public function AddEditstate($id)
    {
        $country = $this->country->get();
        $data = $this->state->find($id);
        $newState = $this->newState->get();
        return view('add-state',compact('data','country','newState'));
    }


    /**
     * delete city.
     *
     * @param  int  $id
     * @return value to blade file
     */
    public function Deletestate($id)
    {
        $data = $this->state->where('id',$id)->delete();
        return redirect('admin/state_list')->with('success',trans('constants.delete_city'));
    }

    /**
     * Update the state.
     * @param  Request $request param
     * @return success response 
     */
    public function Savestate(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'state' => 'required',
            'new_state_id' => 'required',
            'city_code' => 'required',

        ]);
         if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('error', $error_messages)->withInput();

        }
        $id = $request->id;
        $country_id = 5;
        $new_state_id = $request->new_state_id;
        $state = $request->state;
        $cityCode = $request->city_code;


        if($id !="" || $id !=null){

            $check = $this->state->where('id','!=',$id)
                                  ->where('country_id',$country_id)
                                  ->where('state',$state)
                                  ->count();
          
            $insert = $this->state->find($id);
        }else{

            $check = $this->state->where('country_id',$country_id)
                                 ->where('state',$state)
                                 ->count();

            $insert = $this->state;

        }
          if($check !=0){
                return back()->with('error', trans('constants.already_exist',['param'=>'City is ']))->withInput();
            }

            $insert->state = $state;
            $insert->country_id = $country_id;
            $insert->new_state_id = $new_state_id;
            $insert->city_code = $cityCode;
            $insert->save();

        return redirect('admin/state_list')->with('success',trans('constants.save_success_msg'));

    }

    /**
     * Get State list.
     *
     * @return json response
     */

      public function get_state_ajax(Request $request,$id) {
         
         $state = $this->state->where('new_state_id',$id)->get();
         echo json_encode($state);

      }


      /**
       * get state or city based on country
       * 
       * @param int $provinceid, int $id
       * 
       * @return array $data
       */
      public function getprovience($provienceid, $id)
      {
        if($id==1)
            $data =  $this->country->find($provienceid);
        else
            $data = $this->state->find($provienceid);

        return  $data;
      }

}
