<?php

namespace App\Http\Controllers\admin;
                                    
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\BaseController;
use DB;
use Auth;
use Hash;

class LoginController extends BaseController
{

    /**
     * return to view login screen
     * 
     * @return view page
     */
    public function index()
    {
        return view('login');
    }

    
    /**
     * admin and restaurant login check
     * 
     * @param object $request
     * 
     * @return view page to home screen
     */
	public function login(Request $request){

        if(!$request->email || !$request->password){
            return back()->with('error','Wrong email or password try again');
        }

        $credentials = $request->only('email', 'password');
        //dd(Hash::make($request->password));
        if(Auth::attempt($credentials)){
            $request->session()->put('userid', auth()->user()->id);
            $request->session()->put('user_name', auth()->user()->name);
            $request->session()->put('role', auth()->user()->role);
            //dd(auth()->user()->AccessPrivilages);
            return redirect('admin/dashboard');
        }else
        {
            //$credentials['is_approved'] = 1;
            if(Auth::guard('restaurant')->attempt($credentials))
            { 
                if(auth()->guard('restaurant')->user()->is_approved!=1)
                {
                    Auth::logout();
                    return back()->with('error','Approval pending. Kindly contact admin.');
                }
                $request->session()->put('userid', auth()->guard('restaurant')->user()->id);
                $request->session()->put('user_name', auth()->guard('restaurant')->user()->restaurant_name);
                $request->session()->put('role',2);

                $url = 'admin/dashboard/';
                return redirect($url);
            }else{
                return back()->with('error','Wrong email or password try again');
            }
        }

    }

    public function logout(Request $request)
    {
       $request->session()->forget('userid');
       Auth::logout();
       return redirect('/admin')->with('success','Logout success');
    }


    /**
    * change password 
    *
    * @return blade page
    */

    public function change_password()
    {
        return view('change-password');
    }


    /**
    * Update password function
    *
    * @param $request object
    *
    * @return blade page
    */

    public function update_password(Request $request)
    {   
       $role = $request->session()->get('role');
       $user_id = $request->session()->get('userid');
       //dd($user_id);
       if($role==1){
        $admin = $this->admin->where('org_password',$request->old_pswd)->find($user_id);
        //dd($admin);
        //dd($request->password);
        if(isset($admin)){
            $admin->password = bcrypt($request->new_pswd);
            $admin->org_password = $request->new_pswd;
            $admin->save();
            return back()->with('success','Password update successfully');
        }else{
            return back()->with('error','Your old password is wrong');
        }
       
        
       }else{
            $res = $this->restaurants->where('org_password',$request->old_pswd)->find($user_id);
            //dd($admin);
            //dd($request->password);
            if(isset($res)){
                $res->password = bcrypt($request->new_pswd);
                $res->org_password = $request->new_pswd;
                $res->save();
                return back()->with('success','Password update successfully');
            }else{
                return back()->with('error','Your old password is wrong');
            }
       }
        
    }
}