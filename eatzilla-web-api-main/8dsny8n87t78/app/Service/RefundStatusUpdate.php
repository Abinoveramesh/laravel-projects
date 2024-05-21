<?php

namespace App\Service;
use App\Base\Helpers\HelperTools;
use App\Model\CCAvenueTransaction;
use App\Model\Settings;
use App\Model\Refund;

class RefundStatusUpdate{
 
 function refundStatusview($request_id){
    info('connect cron ccAvenueRefundstatus');
    $refund =  Refund::where('request_id',$request_id)->first();
    if(!empty($refund['request_id'])){
      $data = Settings::pluck('value','key_word')->toArray();
        if($data['ccavenue_refund'] == 1){
            $key = Config('app.test_working_key');
            $access_code = Config('app.test_access_code');
            $refund_url = Config('app.test_ccavenue_refund_url');
            info('Test-ccavenue-refund');
        }elseif($data['ccavenue_refund'] == 2){
            $key = Config('app.working_key');
            $access_code = Config('app.access_code');
            $refund_url = Config('app.ccavenue_refund_url');
            info('Live-ccavenue-refund');
        }
    $ccavenuedetails = CCAvenueTransaction::where('request_id', $request_id)->first();
    $encrypt['access_code'] = $access_code;
    $encrypt_details['reference_no']  = $ccavenuedetails->transaction_id;
    $plainText = json_encode($encrypt_details);
    info($plainText);
    $ccaveEncrypt = new CCaveEncrypt;
    $encrypt['enc_request'] = $ccaveEncrypt->encrypt($plainText,$key);
    $request_params = array(
        'command' => 'getRefundDetails',
        'access_code' => $encrypt['access_code'],
        'request_type' => 'JSON',
        'enc_request' => $encrypt['enc_request'],
        'response_type' =>'JSON',
        'version' =>'1.1'
    );
    $ch = curl_init($refund_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
    $response = curl_exec($ch);
    curl_close($ch);
    info($response);
    $arrayResponse = explode('&', $response);
    $status = explode('=', $arrayResponse[0]);
    $encrypted = explode('=', $arrayResponse[1]);
    info($encrypted[1]);
    $encryptedText = trim(preg_replace('/\s\s+/', ' ', $encrypted[1]));
        if($status[1] == 0){
            $return_response = json_decode($ccaveEncrypt->decrypt($encryptedText,$key));
            if($return_response->error_code == ''){
                $refundvalue = $return_response->refund_list['0']->refund_status;
                $refund_reference_no = $return_response->reference_no;
                $refund_initiate_date = $return_response->refund_list['0']->refund_issue_date;
                $refund_processing_date = $return_response->refund_list['0']->refund_processed_on;
                $refund_completed_date = $return_response->refund_list['0']->refund_completion_date;
                $refund_status = HelperTools::refundStatus($refundvalue);
                $refund_details = Refund::where('request_id',$request_id)->update(['reference_no' => $refund_reference_no,'refund_status' => $refund_status,'refund_initiate_date' => $refund_initiate_date,'refund_processing_date' => $refund_processing_date,'refund_completed_date' => $refund_completed_date]);
                info($refund_details);
            }
        }
    }
  }
}
?>