<?php

namespace App\Http\Controllers\api;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController;
use App\Base\Helpers\ExceptionHandlerModel;
use Log;

class NationController extends BaseController
{
    /**
     * Get country list.
     *
     * @return value to blade file
     */
    public function Getcountrylist()
    {
        try {
            $country = $this->country->get();
            $country1=array();
            foreach($country as $data){
                $country1[]=array(
                    'country_id'=>$data->id,
                    'country_name'=>$data->country,
            );
            }
            return response()->json(['status' => true,'data'=>$country1]);
        }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
        }
    }

  
  
    /**
     * Get State list.
     *
     * @return json response
     */

      public function Getstatelist($id) 
      {   
          try {
            $state = $this->state->where('country_id',$id)->get();
            $state1=array();
            foreach($state as $data){
                $state1[]=array(
                    'state_id'=>$data->id,
                    'state_name'=>$data->state);
            }
            return response()->json(['data' => $state1]);      
          }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
          }
      }


      /**
       * get city based on state
       * 
       * @param int int $state_id
       * 
       * @return array $data
       */
      public function Getcitylist($state_id)
      {
        try {
          $city = $this->state->where('new_state_id',$state_id)->select('id','state as city')->without('City','NewState')->get();
          return response()->json(['data' => $city]);
        }catch(\Exception $e){
          Log::error($e);
          return ExceptionHandlerModel::sendEmail($e);
        }
      }

      public function search_city(Request $request)
      {
          try {
            $city = $this->addcity::where('city','like', $request->key_word . '%')->get();
            return response()->json(['data' => $city]);
          }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
          }
      }

      /**
       * get area based on city
       * 
       * @param int $city_id
       * 
       * @return array $data
       */
      public function Getarealist($city_id)
      {
          try {
            $area = $this->addarea->where('add_city_id',$city_id)->get();
            return response()->json(['data' => $area]);  
          }catch(\Exception $e){
            Log::error($e);
            return ExceptionHandlerModel::sendEmail($e);
          }
      }
}
