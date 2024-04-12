<?php

namespace App\Http\Controllers;

use Session;
use App\User;
use App\Pages;
use App\LiveTV;
use App\Movies;
use App\Series;
use App\Slider;
use App\Sports;
use App\UserDevice;
use App\HomeSections;
use App\Http\Requests;
use App\RecentlyWatched;

use App\SubscriptionPlan;

use Illuminate\Http\Request;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg;
use FFMpeg\Coordinate\Dimension;


use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;
use Intervention\Image\Facades\Image; 
use FFMpeg\Filters\Video\WatermarkFilter;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Filters\AdvancedMedia\ComplexFilters;
use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider;


class IndexController extends Controller
{   
 
	  
    public function index()
    {   

        if(!$this->alreadyInstalled())
        {
            return redirect('public/install');
        }

    	$slider= Slider::where('status',1)->whereRaw("find_in_set('Home',slider_display_on)")->orderby('id','DESC')->get();
        
        if(Auth::check())
        {   
            $current_user_id=Auth::User()->id;
            
            if(getcong('menu_movies')==0 AND getcong('menu_shows')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','Movies')->where('video_type','!=','Episodes')->orderby('id','DESC')->get();
            }
            else if(getcong('menu_sports')==0 AND getcong('menu_livetv')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','Sports')->where('video_type','!=','LiveTV')->orderby('id','DESC')->get();
            }
            else if(getcong('menu_livetv')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','LiveTV')->orderby('id','DESC')->get();
            }   
            else if(getcong('menu_sports')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','Sports')->orderby('id','DESC')->get();
            }
            else if(getcong('menu_movies')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','Movies')->orderby('id','DESC')->get();
            }   
            else if(getcong('menu_shows')==0)
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->where('video_type','!=','Episodes')->orderby('id','DESC')->get();
            }
            else
            {
                $recently_watched = RecentlyWatched::where('user_id',$current_user_id)->orderby('id','DESC')->get();
            }   
            
        }
        else
        {
            $recently_watched = array();
        }

        $upcoming_movies = Movies::where('upcoming',1)->orderby('id','DESC')->get();
        $upcoming_series = Series::where('upcoming',1)->orderby('id','DESC')->get();

        //dd($upcoming_movies);exit;
        
        $home_sections = HomeSections::where('status',1)->orderby('id')->get();    
 
        return view('pages.index',compact('slider','recently_watched','upcoming_movies','upcoming_series','home_sections'));
         
    } 

    public function home_collections($slug, $id)
    {
        $home_section = HomeSections::where('id',$id)->where('status',1)->first();

        //echo $home_section->post_type;exit;

        if($home_section->post_type=="Movie")
        {
            return view('pages.home.movies',compact('home_section'));
        }
        else if($home_section->post_type=="Shows")
        {             
            return view('pages.home.shows',compact('home_section'));
        }
        else if($home_section->post_type=="LiveTV")
        {             
            return view('pages.home.livetv',compact('home_section'));
        }
        else if($home_section->post_type=="Sports")
        {             
            return view('pages.home.sports',compact('home_section'));
        }
        else
        {
            return view('pages.home_section',compact('home_section'));
        }
        
    }


    public function alreadyInstalled()
    {   
         
        return file_exists(base_path('/public/.lic'));
    }

    public function search_elastic()
    {
        $keyword = $_GET['s'];  
        
        if(getcong('menu_movies'))
        {
            $s_movies_list = Movies::where('status',1)->where("video_title", "LIKE","%$keyword%")->orderBy('video_title')->get();

        }
        else
        {
            $s_movies_list =array();
        }

        if(getcong('menu_shows'))
        {
            $s_series_list = Series::where('status',1)->where("series_name", "LIKE","%$keyword%")->orderBy('series_name')->get();
        }
        else
        {
            $s_series_list=array();
        }
        
        if(getcong('menu_sports'))
        {
            $s_sports_list = Sports::where('status',1)->where("video_title", "LIKE","%$keyword%")->orderBy('video_title')->get();
        }
        else
        {
            $s_sports_list=array();
        }

        if(getcong('menu_livetv'))
        {
            $live_tv_list = LiveTV::where('status',1)->where("channel_name", "LIKE","%$keyword%")->orderBy('channel_name')->get();
        }
        else
        {
            $live_tv_list=array();
        }
        

        return view('_particles.search_elastic',compact('s_movies_list','s_series_list','s_sports_list','live_tv_list'));
        
    }

    public function search()
    {
        $keyword = $_GET['s'];  
        
        $movies_list = Movies::where('status',1)->where('upcoming',0)->where("video_title", "LIKE","%$keyword%")->orderBy('video_title')->get();

        $series_list = Series::where('status',1)->where('upcoming',0)->where("series_name", "LIKE","%$keyword%")->orderBy('series_name')->get();

        $sports_video_list = Sports::where('status',1)->where("video_title", "LIKE","%$keyword%")->orderBy('video_title')->get();

        $live_tv_list = LiveTV::where('status',1)->where("channel_name", "LIKE","%$keyword%")->orderBy('channel_name')->get();
    
        return view('pages.search',compact('movies_list','series_list','sports_video_list','live_tv_list'));
    }

    public function sitemap()
    {    
        return response()->view('pages.sitemap')->header('Content-Type', 'text/xml');
    }

    public function sitemap_misc()
    {   
        $pages_list = Pages::where('status',1)->orderBy('id')->get();

        return response()->view('pages.sitemap_misc',compact('pages_list'))->header('Content-Type', 'text/xml');
    }
 

    public function sitemap_movies()
    {   
        $movies_list = Movies::where('status',1)->orderBy('id','DESC')->get();

        return response()->view('pages.sitemap_movies',compact('movies_list'))->header('Content-Type', 'text/xml');
    }

    public function sitemap_show()
    {   
        $series_list = Series::where('status',1)->orderBy('id','DESC')->get();

        return response()->view('pages.sitemap_show',compact('series_list'))->header('Content-Type', 'text/xml');
    }

    public function sitemap_sports()
    {   
        $sports_video_list = Sports::where('status',1)->orderBy('id','DESC')->get();

        return response()->view('pages.sitemap_sports',compact('sports_video_list'))->header('Content-Type', 'text/xml');
    }

    public function sitemap_livetv()
    {   
        $live_list = LiveTV::where('status',1)->orderBy('id','DESC')->get();

        return response()->view('pages.sitemap_livetv',compact('live_list'))->header('Content-Type', 'text/xml');
    }

    public function login()
    {
        
        if (Auth::check()) {
            
            // return redirect('dashboard'); 
        }

        return view('pages.user.login');
    }

    public function postLogin(Request $request)
    {
        
   
        $data =  \Request::except(array('_token'));     
                
        $rule=array(
                'email' => 'required|email',
                'password' => 'required'                
                 );
        
        // $userAgent= $request->userAgent();
        // dd($userAgent);
        
         $validator = \Validator::make($data,$rule);
 
        if ($validator->fails())
        {
                Session::flash('login_flash_error', 'required');
                return redirect()->back()->withInput()->withErrors($validator->messages());
         }

        $credentials = $request->only('email', 'password');

        $remember_me = $request->has('remember') ? true : false;  
        
         if (Auth::attempt($credentials, $remember_me)) {

            if(Auth::user()->status=='0'){
                \Auth::logout();
                //return array("errors" => 'You account has been banned!');
                return redirect('/login')->withErrors(trans('words.account_banned'));
            }
            // Check if the user has a plan
            $has_plan = Auth::user()->plan_id ? true : false;

            // Check if the user has a plan and retrieve plan device limit
            $plan_device_limit = 0;
            if ($has_plan) {
                $plan_details = SubscriptionPlan::find(Auth::user()->plan_id);
                $plan_device_limit = $plan_details->plan_device;
            }

            // Check user logged devices
            $user_devices = UserDevice::where('user_id', Auth::user()->id)->get();

            $mydevice = $request->userAgent();
            $device_exists = false;
            $device_count = 0;

            // Loop through user devices to check if device exists
            foreach ($user_devices as $device) {
                if ($device->device_name == $mydevice) {
                    $device_exists = true;
                    break; // Exit loop if device is found
                }
                $device_count++;
            }

            // If the user has a plan and has exceeded the device limit
            if ($has_plan && !$device_exists && ($device_count + 1) > $plan_device_limit) {
                return redirect('/login')->with('error_flash_message', 'You have reached the limit of devices for this plan');
            }

            // If device does not exist, add new device
            if (!$device_exists) {
                // Add new device
                $new_device = new UserDevice;
                $new_device->user_id = Auth::user()->id;
                $new_device->device_name = $mydevice;
                $new_device->save();
            }

            return $this->handleUserWasAuthenticated($request);



        }

       // return array("errors" => 'The email or the password is invalid. Please try again.');
        //return redirect('/admin');
       Session::flash('login_flash_error', 'required'); 
       return redirect('/login')->withInput()->withErrors(trans('words.email_password_invalid'));
        
    }
    
     /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $throttles
     * @return \Illuminate\Http\Response
     */
    protected function handleUserWasAuthenticated(Request $request)
    {

        if (method_exists($this, 'authenticated')) {
            return $this->authenticated($request, Auth::user());
        }

        /*$previous_session = Auth::User()->session_id;
        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
        }

        Auth::user()->session_id = Session::getId();
        Auth::user()->save();
        */

        if(Auth::user()->usertype=='Admin' OR Auth::user()->usertype=='Sub_Admin')
        {
            return redirect('admin/dashboard'); 
        }
        else
        {
            $email=Auth::user()->email;
            $url = url('')."?email=$email";
            return redirect($url); 
        }
        
    }
    

    public function signup()
    {  
        return view('pages.user.signup');
    }

    public function postSignup(Request $request)
    { 
         

        $data =  \Request::except(array('_token'));
        
        $inputs = $request->all();
        
        $rule=array(
                'name' => 'required',                
                'email' => 'required|email|max:200|unique:users',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required'                
                 );
        
        
        
         $validator = \Validator::make($data,$rule);
 
        if ($validator->fails())
        {
                Session::flash('signup_flash_error', 'required');
                return redirect()->back()->withInput()->withErrors($validator->messages());
        } 
       
        $user = new User;

        //$confirmation_code = str_random(30);

        
        $user->usertype = 'User';
        $user->name = $inputs['name']; 
        $user->email = $inputs['email'];   
             
        $user->password= bcrypt($inputs['password']);     
        
        // get plan with whose price = 0 add to user on registration
        $plan = SubscriptionPlan::where('plan_price', '=', 0)->first();
        $user->plan_id = $plan->id;
        $user->start_date = strtotime(date('m/d/Y'));             
        $user->exp_date = strtotime(date('m/d/Y', strtotime("+$plan->plan_days days")));    

        $user->save();

        //Welcome Email

        try{
            $user_name=$inputs['name'];
            $user_email=$inputs['email'];

            $data_email = array(
                'name' => $user_name,
                'email' => $user_email
                );    

            Mail::send('emails.welcome', $data_email, function($message) use ($user_name,$user_email){
                $message->to($user_email, $user_name)
                ->from(getcong('site_email'), getcong('site_name'))
                ->subject('Welcome to '.getcong('site_name'));
            });    
        }catch (\Throwable $e) {
                 
            Log::info($e->getMessage());    
        }        

        
       return redirect('login')->with('message', "Account Created Successfully, Please login");

         
    }

    
    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // delete logged in device
        $userdevice = UserDevice::where('device_name',$request->userAgent())->where('user_id',Auth::user()->id)->delete();
        
        Auth::logout();

        return response()->json(['message' => 'All devices logged out successfully']);
    }
 
     
}
