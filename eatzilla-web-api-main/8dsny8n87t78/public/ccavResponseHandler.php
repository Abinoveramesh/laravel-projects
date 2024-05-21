<?php include('Crypto.php')?>
<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "truely";
// Create connection
$conn = new mysqli($servername, $username, $password, $database);

	require_once '../vendor/autoload.php';
	error_reporting(0);
	$workingKey= '405A29B3A027EAA6F28895FAF2B2D4F1';		//Working Key should be provided here.
	$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
	$rcvdString=decrypt($encResponse,$workingKey);		//Crypto Decryption used as per the specified working key.
	$order_status="";
	$decryptValues=explode('&', $rcvdString);
	$dataSize=sizeof($decryptValues);
	echo "<center>";
	$apiurl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$ccavenu = "SELECT * FROM cc_avenue_transactions ORDER BY id DESC LIMIT 1";
	$databaseconnect =mysqli_query($conn,$ccavenu);
	$Ccavenudetails = mysqli_fetch_assoc($databaseconnect);
	$id = $Ccavenudetails['id'];
	$order_id = $Ccavenudetails['order_id'];

	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
		if($i==3)	$order_status=$information[1];
	}

	if($order_status==="Success")
	{

		$url = $apiurl.'/api/cc_avenue_success/'.$id.'/'.$order_id;
		$curl = curl_init();
		 curl_setopt($curl, CURLOPT_URL, $url);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		 curl_setopt($curl, CURLOPT_HEADER, false);
 
		 // execute and return string (this should be an empty string '')
		 $file = curl_exec($curl);
 
		 curl_close($curl);
		echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
		
	}
	else if($order_status==="Aborted")
	{

		$url = $apiurl.'/api/cc_avenue_failed/'.$id.'/'.$order_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);

		// execute and return string (this should be an empty string '')
		$file = curl_exec($curl);

		curl_close($curl);

		echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
	
	}
	else if($order_status==="Failure")
	{
		$url = $apiurl.'/api/cc_avenue_failed/'.$id.'/'.$order_id;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);

		// execute and return string (this should be an empty string '')
		$file = curl_exec($curl);

		curl_close($curl);

		echo "<br>Thank you for shopping with us.However,the transaction has been declined.";
	}
	else
	{
		echo "<br>Security Error. Illegal access detected";
	
	}

	echo "<br><br>";

	echo "<table cellspacing=4 cellpadding=4>";
	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
	    	echo '<tr><td>'.$information[0].'</td><td>'.$information[1].'</td></tr>';
	}

	echo "</table><br>";
	echo "</center>";
	
?>
