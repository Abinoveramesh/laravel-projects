<?php include('Crypto.php')?>

<?php
$env = parse_ini_file('.env');

$apiurl = $env["API_URL"];
$servername = $env["SERVER_NAME"];
$username = $env["USER_NAME"];
$password = $env["PASSWORD"];
$database = $env["DATABASE"];
// Create connection
$conn = new mysqli($servername, $username, $password, $database);
	error_reporting(0);
	$settings = "SELECT * FROM settings WHERE key_word='ccavenue_payment'";
	$databaseconnect =mysqli_query($conn,$settings);
	$settingsdetails = mysqli_fetch_assoc($databaseconnect);
    if($settingsdetails['value'] == 1){
		$workingKey = $env["TEST_WORKING_KEY"];   	//Working Key should be provided here.
	}elseif($settingsdetails['value'] == 2){
		$workingKey = $env["WORKING_KEY"];   	//Working Key should be provided here.
	}
	$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
	$rcvdString=decrypt($encResponse,$workingKey);		//Crypto Decryption used as per the specified working key.

	$order_status="";
	$decryptValues=explode('&', $rcvdString);
	$dataSize=sizeof($decryptValues);
	echo "<center>";
	$ccavenu = "SELECT * FROM cc_avenue_transactions ORDER BY id DESC LIMIT 1";
	$databaseconnect =mysqli_query($conn,$ccavenu);
	$Ccavenudetails = mysqli_fetch_assoc($databaseconnect);
	$id = $Ccavenudetails['id'];
	$order_id = $Ccavenudetails['order_id'];
	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
		if($i==1)
		{
         $tracking_id=$information[1];
		}
		if($i==2)
		{
         $bank_ref_no=$information[1];
		}
		if($i==3)
		{
		$order_status=$information[1];
		}
		if($i==5)
		{
		$paymentmode=$information[1];
		}
	    if($i==6)
		{
	    $card_name=$information[1];		
		}
		if($i==40)
		{
			$date = DateTime::createFromFormat("d/m/Y H:i:s", $information[1]);
            $trans_date = $date->format("Y/m/d H:i:s");
		}	
	}

	if($order_status==="Success")
	{
		 $url = $apiurl.'/api/cc_avenue_success/'.$id.'/'.$order_id;
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 // execute and return string (this should be an empty string '')
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 $response = curl_exec($curl);
		 curl_close($curl);
		 if(empty($tracking_id))
		 {
			 $tracking_id = null;
		 }
         if(empty($bank_ref_no))
		 {
			$bank_ref_no = null;
		 }
		 $sql = "UPDATE cc_avenue_transactions SET transaction_id='$tracking_id', bank_ref_no='$bank_ref_no', payment_mode='$paymentmode' , order_status='$order_status', card_name='$card_name', trans_date='$trans_date' WHERE id='$id'";
		 $databaseconnect =mysqli_query($conn,$sql);
		 echo "<br>";
		 echo "<img src='https://image4.owler.com/logo/ccavenue_owler_20200307_180446_large.png' alt='CCAvenue' title='CCAvenue' class='img company-logo large ' itemprop='logo'>";
		 echo "<br>";
         echo "<img alt='File:Loading icon.gif' src='https://upload.wikimedia.org/wikipedia/commons/b/b1/Loading_icon.gif?20151024034921' decoding='async' width='341' height='191' data-file-width='341' data-file-height='191'>";
		 echo "<div style='line-height:5px'><p>Do not 'Close the Window' or press 'browser' </p><p>back/forward button'.</p></div>";

		 echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
		
	}
	else if($order_status==="Aborted")
	{

		$url = $apiurl.'/api/cc_avenue_failed/'.$id.'/'.$order_id;
		$curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 // execute and return string (this should be an empty string '')
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 $response = curl_exec($curl);
		 curl_close($curl);
         if(empty($tracking_id))
		 {
			 $tracking_id = null;
		 }
         if(empty($bank_ref_no))
		 {
			$bank_ref_no = null;
		 }
		 $sql = "UPDATE cc_avenue_transactions SET transaction_id='$tracking_id', bank_ref_no='$bank_ref_no', payment_mode='$paymentmode' , order_status='$order_status', card_name='$card_name', trans_date='$trans_date' WHERE id='$id'";
		 $databaseconnect =mysqli_query($conn,$sql);
		 echo "<br>";
		 echo "<img src='https://image4.owler.com/logo/ccavenue_owler_20200307_180446_large.png' alt='CCAvenue' title='CCAvenue' class='img company-logo large ' itemprop='logo'>";
		 echo "<br>";
         echo "<img alt='File:Loading icon.gif' src='https://upload.wikimedia.org/wikipedia/commons/b/b1/Loading_icon.gif?20151024034921' decoding='async' width='341' height='191' data-file-width='341' data-file-height='191'>";
		 echo "<div style='line-height:5px'><p>Do not 'Close the Window' or press 'browser' </p><p>back/forward button'.</p></div>";

		echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
	
	}
	else if($order_status==="Failure")
	{
		$url = $apiurl.'/api/cc_avenue_failed/'.$id.'/'.$order_id;
		$curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 // execute and return string (this should be an empty string '')
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
		 $response = curl_exec($curl);
		 curl_close($curl);
		 if(empty($tracking_id))
		 {
			 $tracking_id = null;
		 }
         if(empty($bank_ref_no))
		 {
			$bank_ref_no = null;
		 }
		 $sql = "UPDATE cc_avenue_transactions SET transaction_id='$tracking_id', bank_ref_no='$bank_ref_no', payment_mode='$paymentmode' , order_status='$order_status', card_name='$card_name', trans_date='$trans_date' WHERE id='$id'";
		 $databaseconnect =mysqli_query($conn,$sql);
		 echo "<br>";
		 echo "<img src='https://image4.owler.com/logo/ccavenue_owler_20200307_180446_large.png' alt='CCAvenue' title='CCAvenue' class='img company-logo large ' itemprop='logo'>";
		 echo "<br>";
         echo "<img alt='File:Loading icon.gif' src='https://upload.wikimedia.org/wikipedia/commons/b/b1/Loading_icon.gif?20151024034921' decoding='async' width='341' height='191' data-file-width='341' data-file-height='191'>";
		 echo "<div style='line-height:5px'><p>'Close the Window' or press 'browser' </p><p>back/forward button'.</p></div>";

		echo "<br>Thank you for shopping with us.However,the transaction has been declined.";
	}
	else
	{
		echo "<br>";
		echo "<img src='https://image4.owler.com/logo/ccavenue_owler_20200307_180446_large.png' alt='CCAvenue' title='CCAvenue' class='img company-logo large ' itemprop='logo'>";
		echo "<br>";
		echo "<br>Security Error. Illegal access detected";
	
	}

	// echo "<br><br>";

	// echo "<table cellspacing=4 cellpadding=4>";
	// for($i = 0; $i < $dataSize; $i++) 
	// {
	// 	$information=explode('=',$decryptValues[$i]);
	//     	echo '<tr><td>'.$information[0].'</td><td>'.$information[1].'</td></tr>';
	// }

	// echo "</table><br>";
	// echo "</center>";
	
?>
