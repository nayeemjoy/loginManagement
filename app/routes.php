<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/',function(){
	return Redirect::route('dashboard');
});

Route::group(['before' => 'guest'], function(){
	Route::controller('password', 'RemindersController');
	Route::get('login', ['as'=>'login','uses' => 'AuthController@login']);
	Route::post('login', array('uses' => 'AuthController@doLogin'));
});

Route::group(array('before' => 'auth'), function()
{

	Route::get('logout', ['as' => 'logout', 'uses' => 'AuthController@logout']);
	Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'AuthController@dashboard'));
	Route::get('change-password', array('as' => 'password.change', 'uses' => 'AuthController@changePassword'));
	Route::post('change-password', array('as' => 'password.doChange', 'uses' => 'AuthController@doChangePassword'));


});


Route::group(array('before' => 'oauth','prefix' => 'api/v1'), function()
{
	Route::get('/',function(){
		$owner = AuthController::getOwner();
		return $owner;
	});
});

Route::post('oauth/access_token',['as' => 'logout', 'uses' => 'AuthController@issueAccessToken']);

Route::get('/test',function(){
	
});
