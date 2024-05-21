<?php

namespace App\Http\Controllers\api;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\BaseController;

class FirebaseController extends BaseController
{
	public function delete_firebase_junk_nodes()
	{

		$header = array();
		$header[] = 'Content-Type: application/json';

		for($i=4001;$i<=4500;$i++)
		{
			$ch = curl_init(FIREBASE_URL."/current_request/$i.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			$result = curl_exec($ch); 
			curl_close($ch); 
		}

        return response()->json(array('status'=>true,'message'=>"Junk nodes deleted"), 200);

	}
}