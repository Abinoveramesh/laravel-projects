<!DOCTYPE html>
<head>
	
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Maven+Pro" rel="stylesheet">
	<link href="ClanPro-Bold.woff" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<html>
<body >
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
	    	<td width="33%" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:2px; color:#ffffff;">.</td>
	    	<td width="35%" align="center" valign="top">
				<section style="background-image: url('{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}welcome_header_bg.png');padding: 20px 0px 142px 30px;background-repeat: no-repeat;background-position: right;background-size: cover;">
					<div style="width: 50%;max-width: 50%;display: inline-block;">
						<img src="{{URL::asset(RESTAURANT_UPLOADS_PATH.SITE_LOGO)}}" alt="{{APP_NAME}} Logo" style="width: 170px;">
					</div>
					
					<h2>
						<span style="font-family: ClanPro;font-size: 37px;font-weight: 600;font-style: normal;font-stretch: normal;line-height: 1.14;letter-spacing: 1.4px;text-align: left;color: #353535;">Hi {{ ucfirst($data->name)}},<br>WELCOME TO</span><br><span style="font-size: 35px;font-weight: bold;letter-spacing: 0.7px;color: #4d9d45">{{APP_NAME}}
						</span>
					</h2>
		
		
				</section>
	
	<p style="font-family: SourceSansPro;font-size: 16px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.6px;text-align: left;color: #797373;padding: 50px 50px 50px 50px">
	Thanks for signing up. We look forward to serve you delicious meals at your door step, and you are just a few clicks away from your finger-licking first bite. 
	</p>
	<h2 style=" font-family: ClanPro;font-size: 24.9px;font-weight: bold;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 1.5px;text-align: center;color: #353535;">How {{APP_NAME}} works</h2>
	<div style="width: 97.4px;height: 3.3px;background-color: #4d9d45;margin-left: auto;margin-right: auto;"></div>

	<div>
		<table style="width: 100%;padding-top: 60px;padding-left: 30px;padding-bottom: 60px;">
			<tr>
				<td style="text-align: center;">
					<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}pick.png" alt="PICK THE BEST">	
				</td>
				<td style="text-align: center;">
					<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}choose_mobile.png" alt="CHOOSE YOUR FOOD">	
				</td>
				<td style="text-align: center;">
					<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}meal_away.png" alt="MEAL IS ON THE WAY">	
				</td>
			</tr>
			<tr>
				<td>
					<p style=" font-family: ClanPro;font-size: 14px;font-weight: 600;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.1px;text-align: center;color: #4d9d45;">PICK THE BEST</p>	
					<p style="font-family: SourceSansPro;font-size: 13px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.4px;text-align: center;color: #9f9f9f;">Pick the right place using <br> trusted ratings and reviews</p>			
				</td>
				<td>
					<p style=" font-family: ClanPro;font-size: 14px;font-weight: 600;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.1px;text-align: center;color: #4d9d45;">CHOOSE YOUR FOOD</p>	
					<p style="font-family: SourceSansPro;font-size: 13px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.4px;text-align: center;color: #9f9f9f;">Easily find the type of food <br> You're Carving</p>			
				</td>
				<td>
					<p style=" font-family: ClanPro;font-size: 14px;font-weight: 600;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.1px;text-align: center;color: #4d9d45;">MEAL IS ON THE WAY</p>	
					<p style="font-family: SourceSansPro;font-size: 13px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.4px;text-align: center;color: #9f9f9f;">Get ready and comfortable while <br> our Delivery Patner brings Food <br> to your door Step</p>			
				</td>
			</tr>
			
		</table>
	</div>
	
	<!-- footer -->
	<section style="background-color: #000000;padding-bottom: 30px;">
		<table style="width: 100%;padding-top: 30px;padding-left: 30px;">
			<tr>
				<td>
					<img src="{{URL::asset(RESTAURANT_UPLOADS_PATH.SITE_LOGO)}}" alt="{{APP_NAME}} Logo" style="width: 170px;filter: invert(100%);">
				</td>
				<td>
					<span style="background-color: white;border-radius: 50%;padding: 16px 3px 6px 6px;">
						<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}instagram.png" alt="Instagram" style="width: 28px;">
					</span>
					<span style=" background-color: white; border-radius: 50%; padding: 16px 3px 6px 6px; margin-left: 8px;">
						<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}linkedin.png" alt="Linkedin" style="width: 28px;">
					</span>
					<span style=" background-color: white; border-radius: 50%; padding: 19px 3px 6px 6px; margin-left: 8px;">
						<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}facebook.png" alt="Facebook" style="width: 28px;">
					</span>
					<span style=" background-color: white; border-radius: 50%; padding: 16px 6px 6px 6px; margin-left: 8px;">
						<img src="{{BASE_URL}}{{UPLOADS_EMAIL_PATH}}youtube.png" alt="Youtube" style="width: 28px;">
					</span>
				</td>
			</tr>
		</table>
		<div style="border: 1px solid #79636370;width: 690px;margin-left: 35px;margin-top: 20px;"></div>
		<table style="width: 100%;padding-top: 30px;padding-left: 30px;">
			<tr>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.77;letter-spacing: 0.6px;text-align: left;color: #ffffff;">FAQ</td>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.6px;text-align: left;color: #b2b2b2;">{{APP_NAME}}</td>
			</tr>
			<tr>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.77;letter-spacing: 0.6px;text-align: left;color: #ffffff;">Forgot Password</td>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.6px;text-align: left;color: #b2b2b2;">980 Post Oak Blvd,</td>
			</tr>
			<tr>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.77;letter-spacing: 0.6px;text-align: left;color: #ffffff;">Privacy</td>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.2;letter-spacing: 0.6px;text-align: left;color: #b2b2b2;">Harris Park, NSW, 2150.</td>
			</tr>
			<tr>
				<td style="font-family: 'Source Sans Pro', sans-serif;font-size: 14px;font-weight: normal;font-style: normal;font-stretch: normal;line-height: 1.77;letter-spacing: 0.6px;text-align: left;color: #ffffff;">Terms</td>
				<td></td>
			</tr>
		</table>
	</section>
	<!-- footer -->
	</td>
    		<td width="33%" align="center" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:2px; color:#ffffff;">.</td>
  		</tr>
	</table>
</body>
</html>