<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use View;
use Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Validator\ValidationExtend;

class AppServiceProvider extends ServiceProvider
{
  
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  
    public function boot()
    {
            $setting_value = DB::table('settings')->pluck('value','key_word')->toArray();
            $country = DB::table('country')->where('is_default',1)->first();


            if(isset($setting_value)){

                define('STRIPE_SK_KEY', (isset($setting_value['stripe_sk_key']) ?  $setting_value['stripe_sk_key'] : 'sk_test_BlD4SrbP60Qa94PrQ1pTHYtB'));

                define('STRIPE_PK_KEY', (isset($setting_value['stripe_pk_key']) ?  $setting_value['stripe_pk_key'] : 'pk_test_uzFnOtl3tNwStqKIi5Vflq61'));

                define('APP_NAME', (isset($setting_value['app_name']) ?  $setting_value['app_name'] : 'TRUELY'));

                define('USER_NOTIFICATION_KEY', (isset($setting_value['user_notification_key']) ?  $setting_value['user_notification_key'] : 'AIzaSyD5tCcm18TB_StcOlo5rNOcponUHxzX9Gg'));

                define('PARTNER_NOTIFICATION_KEY', (isset($setting_value['partner_notification_key']) ?  $setting_value['partner_notification_key'] : 'AIzaSyD5tCcm18TB_StcOlo5rNOcponUHxzX9Gg'));

                define('DEFAULT_RADIUS', (isset($setting_value['default_radius']) ?  $setting_value['default_radius'] : 5));

                define('HIGHLIGHT_COLOR', (isset($setting_value['highlight_color']) ?  $setting_value['highlight_color'] : '#23262f'));

                define('MENU_COLOR', (isset($setting_value['menu_color']) ?  $setting_value['menu_color'] : '#2C303B'));

                define('SITE_CONTACT', (isset($setting_value['site_contact']) ?  $setting_value['site_contact'] : ''));

                define('SITE_EMAIL', (isset($setting_value['site_email']) ?  $setting_value['site_email'] : ''));

                define('SITE_FAVICON', (isset($setting_value['site_favicon']) ?  $setting_value['site_favicon'] : 'facicon.ico'));

                define('SITE_LOGO', (isset($setting_value['site_logo']) ?  $setting_value['site_logo'] : 'eatzilla1.jpg'));

                define('DEFAULT_UNIT', (isset($setting_value['default_unit']) ?  $setting_value['default_unit'] : 'KM'));

                define('EMAIL_ENABLE', (isset($setting_value['email_enable']) ?  $setting_value['email_enable'] : 0));

                define('SMS_ENABLE', (isset($setting_value['sms_enable']) ?  $setting_value['sms_enable'] : 0));

                define('EMAIL_USER_NAME', (isset($setting_value['email_user_name']) ?  $setting_value['email_user_name'] : 'mail.gonotlob.com'));

                define('EMAIL_PASSWORD', (isset($setting_value['email_password']) ?  $setting_value['email_password'] : ''));

                define('TIME_ZONE', (isset($setting_value['time_zone']) ?  $setting_value['time_zone'] : 'Asia/Kolkata'));

                define('FIREBASE_URL', (isset($setting_value['firebase_url']) ?  $setting_value['firebase_url'] : 'https://joldiasho-10cd9.firebaseio.com'));

                define('GOOGLE_API_KEY', (isset($setting_value['google_api_key']) ?  $setting_value['google_api_key'] : ''));

                define('ANDROID_API_KEY', (isset($setting_value['android_api_key']) ?  $setting_value['android_api_key'] : 'AIzaSyAVrnjmTHuHu4PMz6Ygsz9L1wL3jlzBEi4'));

                define('IOS_API_KEY', (isset($setting_value['ios_api_key']) ?  $setting_value['ios_api_key'] : 'AIzaSyBdEfPJwQWOrfLG9BC6cEwUeTcQUGgy7h0'));



                View::share('GOOGLE_API_KEY', GOOGLE_API_KEY);

                define('ORDER_ID_PREFIX', (isset($setting_value['order_prefix']) ?  $setting_value['order_prefix'] : 'TRUELY'));
                define('NOTIFICATION_TIME', (isset($setting_value['provider_timeout']) ?  $setting_value['provider_timeout'] : 60));
                define('IDEAL_TIME', (isset($setting_value['idel_time']) ?  $setting_value['idel_time'] : 5));
                define('USER_ANDROID_VERSION', (isset($setting_value['user_android_version']) ?  $setting_value['user_android_version'] : 5));

                define('LOYALTY_POINT', (isset($setting_value['loyalty_point']) ?  $setting_value['loyalty_point'] : 1));
                define('MAXIMUM_LOYALTY_POINTS', (isset($setting_value['Maximum_loyalty_points']) ?  $setting_value['Maximum_loyalty_points'] : 0));
                define('LOYALTY_AMOUNT', (isset($setting_value['loyalty_amount']) ?  $setting_value['loyalty_amount'] : 0));
                define('USER_IOS_DELETE_KEY', (isset($setting_value['user_ios_delete_key']) ?  $setting_value['user_ios_delete_key'] : 0));
                define('RIDER_IOS_DELETE_KEY', (isset($setting_value['rider_ios_delete_key']) ?  $setting_value['rider_ios_delete_key'] : 0));
                define('NEW_RESTAURANT_COMMISSION', (isset($setting_value['restaurant_commission']) ?  $setting_value['restaurant_commission'] : 5));
                define('NEW_ADMIN_COMMISSION', (isset($setting_value['admin_commission']) ?  $setting_value['admin_commission'] : 5));
                define('NEW_ADMIN_GST', (isset($setting_value['admin_gst']) ?  $setting_value['admin_gst'] : 18));
                define('USER_TOLL_NUMBER', (isset($setting_value['user_toll_number']) ?  $setting_value['user_toll_number'] : 186));
                define('RIDER_TOLL_NUMBER', (isset($setting_value['rider_toll_number']) ?  $setting_value['rider_toll_number'] : 187));

                Config::set('app.timezone', TIME_ZONE);
                date_default_timezone_set(TIME_ZONE);

            }

            if(!isset($setting_value)){

                define('ADMIN_COMMISSION', '0');
                define('DELIVERY_BOY_COMMISSION', '0');
                define('RESTAURANT_COMMISSION', '0');
                define('NEW_RESTAURANT_COMMISSION','5');
                define('STRIPE_SK_KEY', 'sk_test_BlD4SrbP60Qa94PrQ1pTHYtB');
                define('STRIPE_PK_KEY', 'pk_test_uzFnOtl3tNwStqKIi5Vflq61');
                define('APP_NAME', 'TRUELY');
                define('USER_NOTIFICATION_KEY', 'AIZASYD5TCCM18TB_STCOLO5RNOCPONUHXZX9GG');
                define('PARTNER_NOTIFICATION_KEY', 'AIzaSyD5tCcm18TB_StcOlo5rNOcponUHxzX9Gg');
                define('DEFAULT_RADIUS', 5);
                define('HIGHLIGHT_COLOR', '#23262f');
                define('MENU_COLOR', '#0819c4');
                define('SITE_CONTACT', '');
                define('SITE_EMAIL', '');
                define('SITE_FAVICON', 'facicon.ico');
                define('SITE_LOGO', 'eatzilla1.jpg');
                define('DEFAULT_UNIT', 'KM');
                define('EMAIL_ENABLE', 0);
                define('SMS_ENABLE', 0);
                define('EMAIL_USER_NAME', '');
                define('EMAIL_PASSWORD', '');
                define('TIME_ZONE', 'Asia/Kolkata');
                define('FIREBASE_URL', 'https://joldiasho-10cd9.firebaseio.com');
                define('GOOGLE_API_KEY', 'AIzaSyBWIomenTvO9o1V8ZfCbQBXV_UG9iDcNsg');
                define('ORDER_ID_PREFIX', 'TRUELY');
                define('NOTIFICATION_TIME', 60);
                define('IDEAL_TIME', 5);
                define('USER_ANDROID_VERSION', 5);
                define('LOYALTY_POINT', 0);
                define('MAXIMUM_LOYALTY_POINTS', 0);
                define('LOYALTY_AMOUNT', 0);
            }

            if(isset($country)){

                define('DEFAULT_COUNTRY', (isset($country->country) ?  $country->country : 'India'));

                define('DEFAULT_COUNTRY_CODE', (isset($country->country_code) ?  $country->country_code : '+91'));

                define('DEFAULT_CURRENCY', (isset($country->currency_code) ?  $country->currency_code : 'INR'));

                define('DEFAULT_CURRENCY_SYMBOL',(isset($country->currency_symbol) ?  $country->currency_symbol : '₹'));

                //Config::set('DEFAULT_CURRENCY_SYMBOL',(isset($country->country_symbol) ?  $country->country_symbol : '₹'));
            }else{

                define('DEFAULT_COUNTRY', 'India');

                define('DEFAULT_COUNTRY_CODE', '+91');

                define('DEFAULT_CURRENCY', 'INR');

                define('DEFAULT_CURRENCY_SYMBOL', '₹');
            }
            if(App::environment('local')) {
                URL::forceScheme('http');
            }elseif(App::environment('dev')) {
                URL::forceScheme('https');
            }
            define('NOTIFICATION_URL',Config::get('app.notification_url'));
            define('SOCKET_URL',Config::get('app.socket_url'));
            ValidationExtend::extend();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
