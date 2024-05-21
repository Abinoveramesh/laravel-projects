<?php



header('Access-Control-Allow-Origin: *');
// header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
//date_default_timezone_set("Asia/Kolkata");


use Illuminate\Http\Request;
use App\Http\Controllers\admin\NoteboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['namespace'=>'api'],function(){
	Route::GET('/get_restaurant_document','RestaurantController@get_restaurant_document');
	Route::GET('/get_country','NationController@Getcountrylist');
	Route::GET('/get_state/{id?}','NationController@Getstatelist');
	Route::GET('/get_city/{state_id?}','NationController@Getcitylist');
	Route::POST('/search_city','NationController@search_city');
	Route::GET('/get_area/{city_id?}','NationController@Getarealist');
	Route::POST('/add_to_restaurants','RestaurantController@add_to_restaurants');
	Route::POST('/restaurant_signup_request','RestaurantController@restaurant_signup_request');
	Route::POST('/add_driver','RestaurantController@add_driver');
	Route::POST('/version_code_check','UserController@check_version');
	Route::POST('/register','LoginController@register');
	Route::POST('/register_new','LoginController@register_new');
	Route::POST('/login','LoginController@user_login');
	Route::POST('/guest_login','LoginController@guest_user_login');
	Route::POST('/verify_guest_user','LoginController@verify_guest_user');
	Route::POST('/send_otp','LoginController@send_otp_login');
	Route::POST('/forgot_password','LoginController@forgot_password');
	Route::POST('/reset_password','LoginController@reset_password');
	Route::POST('/update_profile','LoginController@update_profile');
    Route::POST('/get_area_by_latlng','UserController@get_area_by_latlng');
	//get cms pages
	Route::GET('/get_cms_pages','LoginController@get_cms_pages');
	Route::POST('/encrypt','RestaurantController@encrypt');
	Route::GET('cc_avenue_success/{id}/{order_id}','RestaurantController@ccAvenuesuccess');
	Route::GET('cc_avenue_failed/{id}/{order_id}','RestaurantController@ccAvenuefailed');
	Route::POST('cc_avenue_status','RestaurantController@ccAvenuestatus');
	Route::GET('cc_avenue_refund/{request_id}/{refund_amount}','RestaurantController@ccAvenuerefund');
	Route::POST('cc_avenue_refund_status','RestaurantController@ccAvenueRefundstatus');
	Route::GET('cc_avenue_create_vendor/{id}','RestaurantController@ccAvenueCreatevendor');
	Route::GET('cc_avenue_edit_vendor/{id}','RestaurantController@ccAvenueEditvendor');
	Route::GET('cc_avenue_delete_vendor/{id}','RestaurantController@ccAvenueDeletevendor');
	Route::GET('cc_avenue_rider_create_vendor/{id}','RestaurantController@ccAvenueRiderCreatevendor');
	Route::GET('cc_avenue_rider_edit_vendor/{id}','RestaurantController@ccAvenueRiderEditvendor');
	Route::GET('cc_avenue_rider_delete_vendor/{id}','RestaurantController@ccAvenueRiderDeletevendor');
	Route::GET('cc_avenue_rider_payout/{id}/{amount}/{description}','RestaurantController@ccAvenueRiderpayout');
	Route::GET('/payout_status', 'RestaurantController@payoutStatus');


	// Payment Gateways
	Route::GET('/benifit_payment_status','UserController@benifit_payment_status');
	Route::POST('/pay_by_credit','UserController@pay_by_credit');
	Route::POST('/create_credimax_session','UserController@create_credimax_session');
	Route::POST('/create_credimax_session_mobile','UserController@create_credimax_session_mobile');
	Route::GET('/credimax_gateway_redirect_mobile','UserController@credimax_gateway_redirect_mobile');
	Route::GET('/update_credimax_payment_status/{transaction_id}','UserController@update_credimax_payment_status');
	Route::POST('/validate_credimax_transaction','UserController@validate_credimax_transaction');
	Route::POST('/validate_benifit_transaction','UserController@validate_benifit_transaction');

    Route::group(['middleware'=>'authCheck'],function(){
        Route::POST('/paynow','RestaurantController@paynow');

        Route::GET('/order_history','UserController@order_history');
        Route::GET('/get_order_status','UserController@get_order_status');
        Route::POST('/cancel_order','OrderController@cancel_order_by_user');
        Route::GET('/logout','LoginController@logout');
        Route::GET('get_default_address','UserController@get_default_address');
        Route::POST('set_default_address', 'UserController@set_default_address');
        Route::POST('set_delivery_address','UserController@set_delivery_address');
        Route::POST('track_order_detail','UserController@track_order_detail');
        Route::GET('/get_current_order_status','UserController@get_current_order_status');
        // Api to fetch area name during new delivery address selection in user app side

        Route::GET('/get_delivery_address','UserController@get_delivery_address');
        Route::POST('/add_delivery_address','UserController@add_delivery_address');
        Route::POST('/edit_delivery_address','UserController@edit_delivery_address');
        Route::GET('/get_profile','LoginController@get_profile');
        Route::get('/get_loyaltypoints','UserController@get_loyaltypoints');

		// current address store and get api
		Route::POST('/add_current_address','UserController@addCurrentAddress');
		Route::GET('/get_current_address','UserController@getCurrentAddress');

        //update ratings for order
        Route::POST('/order_ratings','OrderController@order_ratings');
        //validate promocode
        Route::POST('/check_promocode','OrderController@check_promocode');
        Route::POST('/checkout','RestaurantController@checkout');

		// view all coupon code 
        Route::POST('/view_all_coupon_code','OrderController@view_all_coupon_code');

		// ios delete user account api
		Route::get('/delete_account','LoginController@delete_account');

		//ccavenue payment
		Route::POST('/ccavenue-payment','UserController@ccavenuePayment');
    });

	Route::GET('/delete_delivery_address/{id}','UserController@delete_delivery_address');

		Route::GET('/get_filter_list/{filter_type}','UserController@get_filter_list');	// filter_type =1 - Cusines table else relevance table
		Route::POST('/get_relevance_restaurant','UserController@get_relevance_restaurant');
		Route::POST('/get_menu','UserController@get_menu');
		Route::POST('/get_nearby_restaurant','UserController@get_nearby_restaurant');
		Route::POST('/newly_added_restaurant','UserController@newly_added_restaurant');
		Route::GET('/get_banners','UserController@get_banners');
		Route::GET('/get_popular_brands','UserController@get_popular_brands');
		Route::GET('/get_cuisines','UserController@get_cuisines');
		Route::POST('/search_restaurants','RestaurantController@search_restaurants');
		// Route::GET('/get_restaurant_list','UserController@get_restaurant_list');
		Route::GET('/get_favourite_list','UserController@get_favourite_list');
		Route::POST('/update_favourite','UserController@update_favourite');
//		Route::POST('/filter_relevance_restaurant','UserController@filter_relevance_restaurant');
		Route::POST('/single_restaurant','RestaurantController@single_restaurant');
		Route::POST('/add_to_cart','RestaurantController@add_to_cart');
		Route::POST('/add_to_cart_log','RestaurantController@add_to_cart_log');
		Route::POST('/reduce_from_cart','RestaurantController@reduce_from_cart');
		Route::GET('/check_cart','RestaurantController@check_cart');
		Route::GET('/get_category/{restaurant_id}','RestaurantController@get_category');
		Route::POST('/get_category_wise_food_list','RestaurantController@get_category_wise_food_list');
		Route::POST('/get_food_list','RestaurantController@get_food_list');

		//check restuarant during checkout
		Route::POST('/check_restaurant_availability','UserController@check_restaurant_availability');
		Route::POST('/check_restaurant_availability_by_parent','UserController@check_restaurant_availability_by_parent');
		Route::POST('/get_location_suburb','UserController@get_location_suburb');

		//get offers list
		Route::GET('/get_promo_list','UserController@get_promo_list');

		//list dining restaurants
		Route::POST('/get_dining_restaurant','RestaurantController@get_dining_restaurant');

		//paynow api for dining type
		Route::POST('/paynow_dining','RestaurantController@paynow_dining');

		//get todays special food list
		Route::get('/todays_special','RestaurantController@todays_special');


		Route::GET('/get_password','LoginController@generate_password');

		//generate checksum for payumoney api
		Route::post('/generateChecksum','UserController@generateChecksum');
		//get loyalty points for user
		

		Route::post('/driver_tip','OrderController@driver_tip');

		//stripe payment
		Route::get('/get_cards','StripeController@getCards');
		Route::get('/get_stripe_token','StripeController@get_stripe_token');
		Route::post('/add_card','StripeController@user_add_card_stripe');
		Route::post('/default_card','StripeController@default_card');
		Route::post('/delete_card','StripeController@delete_card');
		
	
        Route::post('/add_cuisines','RestaurantController@add_cuisines');
        Route::GET('/delete_delivery_address/{id}','UserController@delete_delivery_address');


	Route::group(['prefix'=>'providerApi'],function(){

		
		Route::POST('/register','Provider_LoginController@register');
		Route::POST('/login','Provider_LoginController@provider_login');
		Route::POST('/send_otp','Provider_LoginController@send_otp_login');
		Route::POST('/forgot_password','Provider_LoginController@forgot_password');
		Route::POST('/reset_password','Provider_LoginController@reset_password');
		Route::GET('/get_provider_timeout','Provider_LoginController@get_provider_timeout');


		Route::group(['middleware'=>'authCheck'],function(){

			Route::GET('/get_profile','Provider_LoginController@get_profile');
			Route::POST('/update_profile','Provider_LoginController@update_profile');
			Route::POST('/get_address_detail','OrderController@get_address_detail');
			Route::POST('/update_request','OrderController@update_request');
			Route::POST('/cancel_request','OrderController@cancel_request');
			Route::GET('/get_order_status','OrderController@get_order_status');
			Route::GET('/order_history','OrderController@order_history');
			
			//earnings api
			Route::POST('/today_earnings','Provider_LoginController@today_earnings');
			Route::POST('/earnings_order_detail','Provider_LoginController@earnings_order_detail');
			Route::POST('/weekly_earnings','Provider_LoginController@weekly_earnings');
			Route::POST('/monthly_earnings','Provider_LoginController@monthly_earnings');

			//payout details api
			Route::GET('/payout_details','Provider_LoginController@payout_details');

			Route::POST('/available_status_update','Provider_LoginController@available_status_update');
			Route::GET('/logout','Provider_LoginController@logout');

            //Multi order assign
            Route::get('/get_order_list', 'Provider_LoginController@get_order_list');
			// ios delete rider account api
			Route::get('/delete_account','Provider_LoginController@delete_account');

		});

	});


	//vendor apis
	Route::group(['prefix'=>'vendorApi'],function(){

		Route::POST('login','VendorController@vendor_login');

		Route::group(['middleware'=>'authCheck'],function(){

			Route::GET('/get_profile','VendorController@get_profile');
//			Route::POST('/update_profile','VendorController@update_profile');
			Route::post('/order_list','VendorController@order_list');
			Route::GET('/status_update/{id}/{status}/{order_delivery_type}','VendorController@status_update');
			Route::GET('/logout','VendorController@logout');

            Route::GET('/available_status_update/{status}','VendorController@available_status_update');
            Route::GET('/get_dashboard_details','VendorController@get_dashboard_details');
            Route::post('/update_discount','VendorController@update_discount');
            Route::GET('/get_discount','VendorController@get_discount');
            Route::GET('/get_food_list','VendorController@get_food_list');
            Route::post('/add_foodproduct','VendorController@add_product');
            Route::get('/get_product_needs','VendorController@get_product_needs');
            Route::get('/delete_foodproduct/{id}','VendorController@delete_product');
            Route::GET('/food_available_status_update/{food_id}/{status}','VendorController@food_available_status_update');
            Route::get('/get_food_details/{id}','VendorController@get_food_details');
            Route::post('/get_payout_details','VendorController@get_payout_details');
            Route::get('/transaction_history','VendorController@transaction_history');
            //earnings api
            Route::POST('/today_earnings','VendorController@today_earnings');
            Route::POST('/weekly_earnings','VendorController@weekly_earnings');
            Route::POST('/monthly_earnings','VendorController@monthly_earnings');
            Route::get('/get_restaurant_status','VendorController@get_restaurant_status');
            Route::post('/get_payout_summary','VendorController@get_payout_summary');
            Route::post('/change_password','VendorController@change_password');

		});
		Route::GET('/get_cms_pages','VendorController@get_cms_pages');
	});

	//cron apis
	Route::GET('/check_ideal_drivers','Provider_LoginController@check_ideal_drivers');
	Route::GET('/check_ideal_orders','Provider_LoginController@check_ideal_orders');

	Route::GET('/update_choices_name','RestaurantController@update_choices_name');


	Route::GET('/delete_firebase_junk_nodes','FirebaseController@delete_firebase_junk_nodes');

	Route::GET('/get_availbale_providers/{restaurant_id}/{type}','RestaurantController@get_availbale_providers');
	Route::GET('/get_available_riders/{type}','RestaurantController@get_available_riders');

	Route::GET('/get_provider/{id}','RestaurantController@get_provider');
	Route::GET('/check_rider_status','RestaurantController@checkRiderStatus');
	Route::GET('/update_current_request/{request_id}/{provider_id}/{user_id}','OrderController@update_current_request');
	Route::GET('/admin_update_current_request/{request_id}/{status}','OrderController@admin_update_current_request');
	Route::GET('/admin_cancel_new_request/{request_id}/{type}','OrderController@admin_cancel_new_request');

	//pushover test
	Route::POST('/pushy_test','OrderController@pushy_test');
	Route::GET('/restaurant/{id}','RestaurantController@getRestaurant');
	Route::GET('/get_distance/{restaurant_location}/{delivery_location}','OrderController@get_distance');
	Route::get('/ios_delete_account_option','Provider_LoginController@iosDeleteAccountoption');

	Route::POST('/call_masking','OrderController@call_masking');
	Route::GET('/broadcast_delay_job/{id}/{prepared_type}','VendorController@broadcast_delay_job');
	Route::POST('/import_menu_item_data','VendorController@importMenuItemData');
});
Route::POST('/custompush','admin\NoteboardController@send_custumpush');


	

