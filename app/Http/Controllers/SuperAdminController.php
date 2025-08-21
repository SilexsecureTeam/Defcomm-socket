<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Mail\Invitation;
use App\Models\AppStore;
use App\Models\Language;
use App\Models\UserPlan;
use App\Models\SystemMail;
use App\Models\CompanyUser;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use App\Models\ContactBooking;
use App\Models\UserLoginDevice;
use App\Models\ContactSubmission;
use App\Models\StatementAgreement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $usr = User::where('role', 'admin')->get();
        $plan = UserPlan::where('status', 'active')->get();
        return view('super.dashboard', [
            'page' => "Dashboard",
            'opt' => 'admin',
            'usr' => $usr,
            'plan' => $plan
        ]);
    }

    public function account()
    {
        $usr = User::where('role', 'admin')->get();
        $plan = UserPlan::where('status', 'active')->get();
        return view('super.account', [
            'page' => "Account",
            'opt' => 'admin',
            'usr' => $usr,
            'plan' => $plan
        ]);
    }

    public function accountView($id)
    {
        $comp = CompanyUser::find(decrypt($id));
        if($comp){
            $usr = User::where('role', 'user')->where('company_id', $comp->id)->get();
            return view('super.account', [
                'page' => $comp->name . " User's Account",
                'opt' => 'user',
                'usr' => $usr
            ]);
        }
        return redirect()->back()->with('error', 'No data found for this account');
    }

    public function accessLog($id)
    {
        $device = UserLoginDevice::where('user_id', decrypt($id))->get();
        $log = UserLoginLog::where('user_id', decrypt($id))->get();
        $user = User::find(decrypt($id));

        return view('common.deviceaccesslog', [
            'page' => $user->name . " User's Device Access",
            'opt' => 'user',
            'device' => $device,
            'log' => $log
        ]);
    }

    public function accountDelete($id){
        User::find(decrypt($id))->delete();
        return redirect()->back()->with('success', 'User successfully deleted');
    }

    public function accountCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
        ]);

        $error = "";
        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $mess) {
                $error .= "$mess <br>";
            }
            return redirect()->back()->with('error', $error);
        }

        $usr = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'plan_id' => $request->plan_id,
            'role' => 'admin',
            'password' => Hash::make($request->password),
            'access_token' => uniqid()
        ]);

        $comp = CompanyUser::create([
            'name' => $request->nameorg,
            'user_id' => $usr->id
        ]);

        $otp = rand(1000, 9999);

        $usr->update(['company_id' => $comp->id, 'otp' => $otp]);

        $encrypt = encrypt($request->email);

        Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt, $otp));

        $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join. If you have any questions or concerns we  are here to help contact us via our Help Center';

        $this->TermiiSms($request->phone, $bodysms);

        return redirect()->back()->with('success', "User successfully added");
    }

    public function accountEdit(Request $request)
    {
        $id = decrypt($request->id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
        ]);

        $error = "";
        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $mess) {
                $error .= "$mess <br>";
            }
            return redirect()->back()->with('error', $error);
        }

        $usr = User::find($id);
        $usr->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'plan_id' => $request->plan_id,
        ]);

        if($request->password){
            $usr->update(['password' => Hash::make($request->password)]);
        }

        $comp = CompanyUser::find($usr->company_id)->update([
            'name' => $request->nameorg,
        ]);

        return redirect()->back()->with('success', "User successfully updated");
    }

    public function accountToken()
    {
        $user = User::whereNull('access_token')->get();
        foreach($user as $usr){
            $usr->update(['access_token' => uniqid()]);
        }
    }

    public function language()
    {
        $data = Language::get();
        return view('super.language', [
            'page' => "Language",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function languageCreate(Request $request)
    {
        Language::updateOrCreate([
            'label' => $request->label
        ],[
            'description' => $request->description
        ]);
        return redirect()->back()->with('success', "Language successfully updated");
    }

    public function languageEdit(Request $request)
    {
        Language::find(decrypt($request->id))->update([
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status
        ]);
        return redirect()->back()->with('success', "Language successfully updated");
    }

    public function agreements()
    {
        $data = StatementAgreement::get();
        return view('super.agreements', [
            'page' => "agreements",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function agreementsCreate(Request $request)
    {
        StatementAgreement::updateOrCreate([
            'title' => $request->title,
            'label' => $request->label
        ],[
            'description' => $request->description
        ]);
        return redirect()->back()->with('success', "agreements successfully updated");
    }

    public function agreementsEdit(Request $request)
    {
        StatementAgreement::find(decrypt($request->id))->update([
            'title' => $request->title,
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status
        ]);
        return redirect()->back()->with('success', "agreements successfully updated");
    }

    public function systemMail()
    {
        $data = SystemMail::get();
        return view('super.systemmail', [
            'page' => "System Mail",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function systemMailUpdate(Request $request)
    {
        SystemMail::find(decrypt($request->id))->update([
            'title' => $request->title,
            'message' => $request->message,
            'status' => $request->status
        ]);
        return redirect()->back()->with('success', "Mail successfully updated");
    }

    public function storeUser()
    {
        $data = User::with('appStore')->whereHas('appStore')->get();
        $userPen = User::where('app_role', 'developer')->where('statusApp', 'pending')->get();
        $userApp = User::where('app_role', 'developer')->where('statusApp', 'verified')->get();
        $userRej = User::where('app_role', 'developer')->where('statusApp', 'reject')->get();
        $userBlk = User::where('app_role', 'developer')->where('statusApp', 'block')->get();
        return view('super.storeuser', [
            'page' => "Store User",
            'opt' => 'admin',
            'data' => $data,
            'userPen' => $userPen,
            'userApp' => $userApp,
            'userRej' => $userRej,
            'userBlk' => $userBlk
        ]);
    }

    public function storeUserDetail($id)
    {
        $data = User::find(decrypt($id));
        return view('super.storeuserdetail', [
            'page' => "Store User",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function storeuserdetailSub(Request $request)
    {
        User::find(decrypt($request->id))->update([
            'statusApp' => $request->statusApp,
            'commentApp' => $request->commentApp
        ]);
        return redirect()->back()->with('success', "App status successfully updated");
    }
    
    public function storeApp($id=null)
    {
        if($id){
            $data = AppStore::where('user', decrypt($id))->get();
        }else{
            $data = AppStore::get();
        }

        return view('super.storeapp', [
            'page' => "Store App",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function storeappdetail($id)
    {
        $data = AppStore::find(decrypt($id));
        
        return view('super.storeappdetail', [
            'page' => "Store App",
            'opt' => 'admin',
            'app' => $data
        ]);
    }

    public function storeappdetailSub(Request $request)
    {
        AppStore::find(decrypt($request->id))->update([
            'status' => $request->status,
            'comment' => $request->comment
        ]);
        return redirect()->back()->with('success', "App status successfully updated");
    }

    public function webContact()
    {
        $data = ContactSubmission::get();

        return view('super.webcontact', [
            'page' => "Web Contact",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function webBooking()
    {
        $data = ContactBooking::get();

        return view('super.webbooking', [
            'page' => "Web Booking",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function plan()
    {
        $data = UserPlan::get();

        return view('super.plan', [
            'page' => "Plan",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function planAdd(Request $request) 
    {
        UserPlan::create([
            'name' => $request->name,
            'file_size' => $request->file_size,
            'no_user' => $request->no_user,
            'no_group' => $request->no_group,
            'enable_chat' => $request->enable_chat,
            'enable_meeting' => $request->enable_meeting,
            'enable_walkie' => $request->enable_walkie,
            'enable_call' => $request->enable_call,
            'description' => $request->description,
        ]);
        return redirect()->back()->with('success', "Plan successfully added");      
    }

    public function planEdit(Request $request) 
    {
        UserPlan::find(decrypt($request->id))->update([
            'name' => $request->name,
            'file_size' => $request->file_size,
            'no_user' => $request->no_user,
            'no_group' => $request->no_group,
            'enable_chat' => $request->enable_chat,
            'enable_meeting' => $request->enable_meeting,
            'enable_walkie' => $request->enable_walkie,
            'enable_call' => $request->enable_call,
            'description' => $request->description,
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', "Plan successfully updated");      
    }
}
