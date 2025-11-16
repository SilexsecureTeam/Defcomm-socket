<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Program;
use App\Mail\Invitation;
use App\Models\AppStore;
use App\Models\Language;
use App\Models\UserPlan;
use App\Models\BountyUser;
use App\Models\SystemMail;
use App\Models\CompanyUser;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use App\Models\ContactBooking;
use App\Models\UserLoginDevice;
use App\Models\BountyUserReport;
use App\Models\BountyUserProgram;
use App\Models\ContactSubmission;
use App\Models\ProgramAttendance;
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

    public function account($user)
    {
        $usr = User::where('role', $user)->get();
        $plan = UserPlan::where('status', 'active')->get();
        return view('super.account', [
            'page' => $user == "super" ? "Super Account" : "Company Account",
            'opt' => $user,
            'usr' => $usr,
            'plan' => $plan
        ]);
    }

    public function accountView($id)
    {
        $comp = CompanyUser::find(decrypt($id));
        if ($comp) {
            $usr = User::where('role', 'user')->where('company_id', $comp->id)->get();
            $plan = UserPlan::where('status', 'active')->get();
            return view('super.account', [
                'page' => $comp->name . " User's Account",
                'opt' => 'user',
                'usr' => $usr,
                'plan' => $plan
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

    public function accountDelete($id)
    {
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

        $otp = rand(1000, 9999);

        $usr = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'plan_id' => $request->plan_id,
            'role' => 'admin',
            'company_id' => 0,
            'password' => Hash::make($request->password),
            'access_token' => uniqid(),
            'otp' => $otp,
            'enable_2fa' => 1,
            'access' => $request->access
        ]);

        if($request->nameorg){
            $comp = CompanyUser::create([
                'name' => $request->nameorg,
                'user_id' => $usr->id
            ]);

            $usr->update(['company_id' => $comp->id]);
        }else{
            $usr->update(['status' => "active", 'role' => 'super',]);
        }

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

        if ($request->password) {
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
        foreach ($user as $usr) {
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
        ], [
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
        ], [
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

    public function storeApp($id = null)
    {
        if ($id) {
            $data = AppStore::where('user', decrypt($id))->get();
        } else {
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

    public function bountyUser($type = 'user')
    {
        $data = BountyUser::where('user_type', $type)->get();
        $userPen = BountyUser::where('user_type', $type)->where('status', 'pending')->get();
        $userApp = BountyUser::where('user_type', $type)->where('status', 'active')->get();
        $userBlk = BountyUser::where('user_type', $type)->where('status', 'block')->get();

        return view('super.bountyuser', [
            'page' => "Bounty User",
            'opt' => 'admin',
            'data' => $data,
            'type' => $type ?? "user",
            'userPen' => $userPen,
            'userApp' => $userApp,
            'userBlk' => $userBlk
        ]);
    }

    public function bountyUserId($id)
    {
        $data = BountyUser::find(decrypt($id));

        return view('super.bountyuserdetail', [
            'page' => "Bounty User",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function bountyProgram()
    {
        $data = BountyUserProgram::get();

        return view('super.bountyProgram', [
            'page' => "Bounty User",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function bountyProgramAdd(Request $request)
    {
        BountyUserProgram::create([
            'title' => $request->title,
            'detail' => $request->detail,
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', "Program successfully created");
    }

    public function bountyProgramUpdate(Request $request)
    {
        BountyUserProgram::find(decrypt($request->id))->update([
            'title' => $request->title,
            'detail' => $request->detail,
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', "Program successfully updated");
    }

    public function bountyReport($severity = null)
    {
        if($severity){
            $data = BountyUserReport::get();
            $dataNew = BountyUserReport::where('status', 'new')->where('severity', $severity)->get();
            $dataReview = BountyUserReport::where('status', 'review')->where('severity', $severity)->get();
            $dataAccept = BountyUserReport::where('status', 'accept')->where('severity', $severity)->get();
            $dataReject = BountyUserReport::where('status', 'reject')->where('severity', $severity)->get();
            $dataFix = BountyUserReport::where('status', 'fix')->where('severity', $severity)->get();
            $dataClose = BountyUserReport::where('status', 'close')->where('severity', $severity)->get();
        }else{
            $data = BountyUserReport::get();
            $dataNew = BountyUserReport::where('status', 'new')->get();
            $dataReview = BountyUserReport::where('status', 'review')->get();
            $dataAccept = BountyUserReport::where('status', 'accept')->get();
            $dataReject = BountyUserReport::where('status', 'reject')->get();
            $dataFix = BountyUserReport::where('status', 'fix')->get();
            $dataClose = BountyUserReport::where('status', 'close')->get();
        }

        return view('super.bountyReport', [
            'page' => "Bounty User Report",
            'opt' => 'admin',
            'data' => $data,
            'dataNew' => $dataNew,
            'dataReview' => $dataReview,
            'dataAccept' => $dataAccept,
            'dataReject' => $dataReject,
            'dataFix' => $dataFix,
            'dataClose' => $dataClose,
            'severity' => $severity ?? "all"
        ]);
    }

    public function program()
    {
        $data = Program::orderBy('started_at', 'DESC')->get();        
        return view('super.program', [
            'page' => "Program",
            'opt' => 'admin',
            'data' => $data
        ]);

    }

    public function programAdd(Request $request)
    {
        Program::create([
            'user_id' => auth()->user()->id,
            'label' => $request->label,
            'description' => $request->description,
            'started_at' => $request->started_at,
            'type' => $request->type,
            'status' => $request->status
        ]);
        return redirect()->back()->with('success', "Program successfully created");
    }

    public function programUpdate(Request $request)
    {
        $data = Program::find(decrypt($request->id));
        $data->update([
            'label' => $request->label,
            'description' => $request->description,
            'started_at' => $request->started_at ?? $data->started_at,
            'type' => $request->type,
            'status' => $request->status
        ]);
        return redirect()->back()->with('success', "Program successfully updated");
    }

    public function attendance($id)
    {
        $data = ProgramAttendance::where('program_id', decrypt($id))->orderBy('created_at', 'ASC')->get();
        return view('super.programAttendance', [
            'page' => "Program Attendance",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function attendanceUser($id, $userId, $userType)
    {
        $data = ProgramAttendance::updateOrCreate([
            'program_id' => decrypt($id),
            'user_id' => decrypt($userId),
            'user_type' => decrypt($userType)
        ], [
            'comment' => "Checked by ". auth()->user()->name,
        ]);

        return redirect()->back()->with('tab', '');
    }
}
