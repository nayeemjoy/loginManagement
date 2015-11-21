<?php

class AuthController extends \BaseController {

	public function login(){
		return View::make('auth.login')
					->with('title', 'Login');
	}

	public function doLogin()
	{
		$rules = array
		(
					'email'    => 'required',
					'password' => 'required'
		);
		$allInput = Input::all();
		$validation = Validator::make($allInput, $rules);

		//dd($allInput);


		if ($validation->fails())
		{

			return Redirect::route('login')
						->withInput()
						->withErrors($validation);
		} else
		{

			$credentials = array
			(
						'email'    => Input::get('email'),
						'password' => Input::get('password')
			);

			if (Auth::attempt($credentials))
			{
				return Redirect::intended('dashboard');
			} else
			{
				return Redirect::route('login')
							->withInput()
							->withErrors('Error in Email Address or Password.');
			}
		}
	}

	public function logout(){
		Auth::logout();
		return Redirect::route('login')
					->with('success',"You are successfully logged out.");
	}

	public function dashboard(){
		return View::make('dashboard')
					->with('title','Dashboard');
	}

	public function changePassword(){
		return View::make('auth.changePassword')
					->with('title',"Change Password");
	}

	public function doChangePassword(){
		$rules =[
			'password'              => 'required|confirmed',
			'password_confirmation' => 'required'
		];
		$data = Input::all();

		$validation = Validator::make($data,$rules);

		if($validation->fails()){
			return Redirect::back()->withErrors($validation)->withInput();
		}else{
			$user = Auth::user();
			$user->password = Hash::make($data['password']);

			if($user->save()){
				Auth::logout();
				return Redirect::route('login')
							->with('success','Your password changed successfully.');
			}else{
				return Redirect::route('dashboard')
							->with('error',"Something went wrong.Please Try again.");
			}
		}
	}
	public function issueAccessToken(){


		$type = Input::get('grant_type');
		if($type == 'password'){
			$rules = array
			(
						'username'    => 'required',
						'password' => 'required'
			);
			$data = Input::all();
			$validation = Validator::make($data, $rules);
			if($validation->fails())
			{
				return $this->getMessage($validation->messages(),400);
			} 
			$credentials = array
			(
				'username'    => $data['username'],
				'password' => $data['password']
			);
			try {
				// dd(Auth::attempt($credentials));
				if(Auth::attempt($credentials))
				{
					$user = User::find(Auth::user()->id);
					$accessToken = new AccessToken;
					$accessToken->user_id = $user->id;
					$a_token = $data['password'].$data['username'].Carbon::now().str_random(10);
					$r_token = $data['username'].$data['password'].Carbon::now().str_random(10);
					$accessToken->access_token = md5($a_token);
					$accessToken->refresh_token = md5($r_token);
					$accessToken->expire_time = 120;
					$accessToken->save();
					$message = [
						'access_token'  => $accessToken->access_token,
						'refresh_token' => $accessToken->refresh_token,
						'expires' => $accessToken->expire_time
					];
					return $this->getMessage($message,200);
				}
				else{
					return $this->getMessage('Invalid Username or Password',400);
				}
				// return 'not ok';
			} catch (Exception $e) {
				
				return $this->getMessage('Error',400);
			
			}
		}
		elseif($type == 'refresh_token'){
			$r_token = Input::get('refresh_token');
			if(isset($r_token)){
				$accessToken = AccessToken::whereRefreshToken($r_token)->first();
				if(!$accessToken){
					return Response::json([
						'error' => 'Unauthorized',
						'status' => 401
					]);		
				}
				$user = User::find($accessToken->user_id);
				// return 	$user->password;
				$a_token = $user->username.$user->password.Carbon::now().str_random(10);
				$r_token = $user->username.$user->password.Carbon::now().str_random(10);
				// return md5($a_token);
				$accessToken->access_token = md5($a_token);
				$accessToken->refresh_token = md5($r_token);
				$accessToken->expire_time = 120;
				$accessToken->save();
					$message = [
						'access_token'  => $accessToken->access_token,
						'refresh_token' => $accessToken->refresh_token,
						'expires' => $accessToken->expire_time
					];
					return $this->getMessage($message,200);
			}
			else{
				return Response::json([
					'error' => 'Refresh Token Required',
					'status' => 400
				]);
			}
		}
		return Response::json([
					'error' => 'grant_type parameter missing',
					'status' => 400
				]);
		
		
		
	}
	private function getMessage($message, $status){
		return Response::json([
						'message' => $message,
						'status'  => $status
					]);
	}
	public static function getOwner(){
		$token = Input::get('access_token');
		$access_token = AccessToken::whereAccessToken($token)->first();

		return $access_token->user_id;
	}
}