<?php
namespace App\Service;
use App\Model\Addcity;
use App\Model\RequestDriverCommission;
use App\Model\Foodrequest;
use App\Model\Restaurants;

class MultiOrderAssign{
    /**
     * @param $request_id
     * @param $distance
     * @return bool
     */
    static function request_driver_commission($request_id,$distance,$driver_commission=0){
        $check_already_exist = RequestDriverCommission::where('request_id',$request_id)->count();
        if($check_already_exist==0){
            $request_driver = new RequestDriverCommission();
            $request_driver->request_id = $request_id;
            $request_driver->driver_id = 0;
            $request_driver->distance = $distance;
            $request_driver->driver_commission = $driver_commission;
            $request_driver->save();
        }
        return true;
    }

    /**
     * @param $driver_id
     * @param $request_id
     * @param $distance
     * @param int $status
     * @return bool
     * @throws \Exception
     */
    static function request_driver_commission_comparison($driver_id,$request_id,$distance,$status=0){
        $driver_commission_request_data = RequestDriverCommission::where('request_id',$request_id)->first();
        if(isset($driver_commission_request_data) && !empty($driver_commission_request_data)){
            if($driver_commission_request_data->driver_id==0 && $status==0){
                RequestDriverCommission::where('request_id',$request_id)->update(['driver_id'=>$driver_id]);
            }
            $check_already_exist = RequestDriverCommission::where('driver_id',$driver_id)->where('distance','>',$distance)->count();
            if($check_already_exist!=0){
                $request_data = Foodrequest::with(['Restaurants'])->find($request_id);
                $source = "";
                $destination = "";
                $distance = 0;
                $d_lat = "";
                $d_lng = "";
                if(!empty($request_data->Restaurants)){
                    $destination = $request_data->Restaurants->lat . ',' . $request_data->Restaurants->lng;
                }
                if(!empty($request_data)){
                    $source = $request_data->d_lat . ',' . $request_data->d_lng;
                    $d_lat = $request_data->d_lat;
                    $d_lng = $request_data->d_lng;
                    $distance = $request_data->distance;
                }
                if (!empty($request_data->Restaurants->driver_base_price) && $request_data->Restaurants->driver_base_price!=0) {
                    $driver_commission_data = $request_data->Restaurants;
                } else {
                    $driver_commission_data = Addcity::find($request_data->city_id);
                }
                $base_price = isset($driver_commission_data->driver_base_price)?$driver_commission_data->driver_base_price:0;
                $old_admin_commission =  $request_data->admin_commision;
                $old_driver_commission =  $request_data->delivery_boy_commision;
                $admin_commission = $old_admin_commission + $old_driver_commission - $base_price;
                $driver_commission = $base_price;
                Foodrequest::where('id',$request_id)->update(['admin_commision'=>$admin_commission,'delivery_boy_commision'=>$driver_commission]);
                RequestDriverCommission::where('request_id',$request_id)->update(['driver_commission'=>$driver_commission]);
            }else{
                $request_driver_data = RequestDriverCommission::where('driver_id',$driver_id)->where('request_id','!=',$request_id)->get();
                if(!empty($request_driver_data)){
                    foreach($request_driver_data as $r){
                        $request_data = "";
                        $request_data = Foodrequest::with(['Restaurants'])->find($r->request_id);
                        $source = "";
                        $destination = "";
                        $distance = 0;
                        $d_lat = "";
                        $d_lng = "";
                        if(!empty($request_data->Restaurants)){
                            $destination = $request_data->Restaurants->lat . ',' . $request_data->Restaurants->lng;
                        }
                        if(!empty($request_data)){
                            $source = $request_data->d_lat . ',' . $request_data->d_lng;
                            $d_lat = $request_data->d_lat;
                            $d_lng = $request_data->d_lng;
                            $distance = $request_data->distance;
                        }
                        $data = "";
                        $base_price = 0;
                        $old_admin_commission = 0;
                        $admin_commission = 0;
                        $driver_commission = 0;
                        $driver_commission_data = "";
                        if (!empty($request_data->Restaurants->driver_base_price) && $request_data->Restaurants->driver_base_price!=0) {
                            $driver_commission_data = $request_data->Restaurants;
                        } elseif (!empty($request_data->city_id)) {
                            $driver_commission_data = Addcity::find($request_data->city_id);
                        }
                        $base_price = isset($driver_commission_data->driver_base_price) ? $driver_commission_data->driver_base_price : 0;
                        $old_admin_commission =  isset($request_data->admin_commision) ? $request_data->admin_commision : 0;
                        $old_driver_commission =  isset($request_data->delivery_boy_commision) ? $request_data->delivery_boy_commision : 0;
                        $admin_commission = $old_admin_commission + $old_driver_commission - $base_price;
                        $driver_commission = $base_price;
                        Foodrequest::where('id',$r->request_id)->update(['admin_commision' => $admin_commission,'delivery_boy_commision'=>$driver_commission]);
                        RequestDriverCommission::where('request_id',$r->request_id)->update(['driver_commission'=>$driver_commission]);
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $request_id
     * @return bool
     * @throws \Exception
     */
    static function request_driver_commission_delete($request_id){
        $data = RequestDriverCommission::where('request_id',$request_id)->delete();
        return true;
    }
}
