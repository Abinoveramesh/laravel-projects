<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::GET('/help', function () {
                                return view('help');   
                            });

Route::get('/cms/{name}','admin\SettingController@viewcms_page');

Route::get('/payment/true/{user_id}','admin\UserController@payment_true');
Route::get('/payment/false/{user_id}','admin\UserController@payment_true');

Route::group(array('domain' => 'restaurants.gonotlob.com'), function() {
	Route::get('/', function () {
		if (Session::has('userid')) {
        return redirect()->to('https://restaurantsdemo.gonotlob.com');
    	}
	return redirect()->to('https://restaurantsdemo.gonotlob.com');
	});
});

Route::group(array('domain' => 'admin.gonotlob.com'), function() {
	Route::get('/', function () {
		if (Session::has('userid')) {
        return redirect()->to('https://admin.gonotlob.com/admin/dashboard');
    	}
	return redirect()->to('https://admin.gonotlob.com/admin');
	});
});

Route::group(array('domain' => 'restaurantsdemo.gonotlob.com'), function() {
	Route::get('/', function () {
		if (Session::has('userid')) {
        return redirect()->to('https://restaurantsdemo.gonotlob.com/admin/dashboard');
    	}
	return redirect()->to('https://restaurantsdemo.gonotlob.com/admin');
	});
});
Route::get('/.well-known/assetlinks.json', function () {
    $json = file_get_contents(base_path('.well-known/assetlinks.json'));
    return response($json, 200)
        ->header('Content-Type', 'application/json');
});

Route::GET('/success','admin\DashboardController@getSuccess');
Route::GET('/failure','admin\DashboardController@getFailure');

Route::get('/apple-app-site-association', function () {
    $json = file_get_contents(base_path('.well-known/apple-app-site-association'));
    return response($json, 200)
        ->header('Content-Type', 'application/json');
});

Route::get('/get-invoice',function(){
	$getInvoicePdf = view('order.get-invoice')->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($getInvoicePdf)->setOptions(['dpi' => 200, 'defaultFont' => 'sans-serif', 'isRemoteEnabled' => true])->setPaper('A4');
        return $pdf->stream();
});