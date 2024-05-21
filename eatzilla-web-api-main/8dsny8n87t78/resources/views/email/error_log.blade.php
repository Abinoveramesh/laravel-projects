<!DOCTYPE html>
<head>
	
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Maven+Pro" rel="stylesheet">
	<link href="ClanPro-Bold.woff" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<html>
<body>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr bgcolor="#24336a">
			<td align="center" style="padding: 25px 0px" colspan="3">
				<img src="{{URL::asset('public/favicon-new.png')}}" alt="Logo" style="width: 150px;margin-bottom: 10px;
				margin: 0 auto; " />
			</td>
		</tr>
		<tr>
			<td align="center">
				</br>
				<h2 style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 26px;color: #24336a;padding-bottom: 0px;margin: 0;font-weight:bold;letter-spacing: .5px; "> Greetings from TRUELY</h2>
				</br>
				<h2 style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 26px;color: #24336a;padding-bottom: 0px;margin: 0;font-weight:bold;letter-spacing: .5px; "> Error Logs from TRUELY</h2>
				</br>
				<h2 style="font-family: 'Rubik', Arial, Helvetica, sans-serif;font-size: 26px;color: #24336a;padding-bottom: 0px;margin: 0;font-weight:bold;letter-spacing: .5px; "> Error API - {{$apiPath}}</h2>
				<p style="font-family: SourceSansPro;font-size: 16px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.6px;text-align: left;color: #797373;padding: 50px 50px 50px 50px">{!! $data !!}</p>	
			</td>
		</tr>
	</table>
</body>
</html>