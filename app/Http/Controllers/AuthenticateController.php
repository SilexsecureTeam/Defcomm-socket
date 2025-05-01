<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Models\CompanyUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Livewire\Attributes\Validate;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticateController extends Controller
{
    public $email, $password;

    public function index()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        // $this->validate();

        $this->email = $request->email;
        $this->password = $request->password;

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($request->only(['email', 'password']))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);

            return redirect()->back()->with('error', "Wrong Detail Entered");
        }

        if(auth()->user()->status != 'active'){
            Auth::logout();
            return redirect('/login')->with('error', "Your Account is not Active Please contact admin");
        }

        if (auth()->user()->enable_2fa == 1) {
            $user = User::find(auth()->user()->id);
            $otp = rand(10000, 99999);
            $user->update(['otp' => $otp]);

            Mail::to($request->email)->send(new OtpMail($user->name, $otp));

            Auth::logout();

            return redirect('/loginOtp')->with('success', "OTP Sent. Check your mail")->with('email', $request->email)->with('password', $request->password);
        }

        RateLimiter::clear($this->throttleKey());

        Session::regenerate();

        if(auth()->user()->role == 'super' ){
            return redirect('/super/dashboard')->with('success', "Login successfully");
        }if(auth()->user()->role == 'admin' ){
            return redirect('/admin/dashboard')->with('success', "Login successfully");
        }else{
            return redirect('/user/dashboard')->with('success', "Login successfully");
        }
    }

    public function loginOtp()
    {
        return view('auth.loginOtp');
    }

    public function loginOtpStore(Request $request)
    {
        $this->email = $request->email;
        $this->password = $request->password;

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($request->only(['email', 'password']))) {
            RateLimiter::hit($this->throttleKey());

            // throw ValidationException::withMessages([
            //     'form.email' => trans('auth.failed'),
            // ]);

            return redirect('/login')->with('error', "Wrong Detail Entered");
        }

        // if (auth()->user()->status != 'active') {
        //     Auth::logout();
        //     return redirect('/login')->with('error', "Your Account is not Active Please contact admin");
        // }

        $user = User::where('email', '=', $request->email)->where('otp', $request->otp);
        if ($user->get()->isNotEmpty()) {
            $cur = Carbon::now()->subMinute(2)->format('Y-m-d H:i:s');
            if (strtotime($user->first()->updated_at->format('Y-m-d H:i:s')) >= strtotime($cur)) {
                $user->update(['status' => 'active' ]);
                RateLimiter::clear($this->throttleKey());
                Session::regenerate();
                if (auth()->user()->role == 'super') {
                    return redirect('/super/dashboard')->with('success', "Login successfully");
                }
                if (auth()->user()->role == 'admin') {
                    return redirect('/admin/dashboard')->with('success', "Login successfully");
                } else {
                    return redirect('/user/dashboard')->with('success', "Login successfully");
                }
            } else {
                Auth::logout();
                return redirect('/')->with('error', "The OTP has expired. Request again");
            }
        }

        Auth::logout();
        return redirect()->back()->with('error', "Invalid input. Try again")->with('email', $request->email)->with('password', $request->password);
        
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    public function resetAccount($encrypt)
    {
        $user = User::where('email', decrypt($encrypt))->first();

        if($user){
            return view('auth.resetAccount', [
                'email' => $encrypt,
                'lable' => "Defcomm Invitation",
            ]);
        }else{
            return redirect('/login')->with('error', "Wrong Detail Entered");
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function resetAccountStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = User::where('email', decrypt($request->email))->first();

        if($user){
            $user->update(['password' => Hash::make($request->password), 'status' => 'active']);

            return redirect('/login')->with('success', "Account password successfully reset");
        }

        return redirect('/login')->back()->with('error', "Wrong Detail Entered");
    }

    public function register()
    {
        return view('auth.register');
    }

    public function registerStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
        ]);

        if ($validator->fails()) {
            // return dd(($validator->messages()->toArray())['email'][0]);
            // return redirect()->back()->with('error', $validator->messages()->toArray());
            return redirect()->back()->withErrors($validator);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => "admin",
            'password' => Hash::make($request->password),
        ]);

       $comp = CompanyUser::create([
            'name' => $request->name,
            'user_id' => $user->id,
        ]);

        $user->update(['company_id' => $comp->id]);

        // Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt));

        return redirect('/login')->with('success', "Account successfully create");
    }
}
