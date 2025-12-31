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
use App\Models\BountyCategory;
use App\Models\ContactBooking;
use App\Models\UserLoginDevice;
use App\Models\BountyUserReport;
use App\Models\BountyCategorySub;
use App\Models\BountyUserProgram;
use App\Models\ContactSubmission;
use App\Models\ProgramAttendance;
use App\Models\StatementAgreement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;

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
    
    public function notification()
    {
        $notify = Notification::where('source', 'super')->get();
        return view('super.notification', [
            'notify' => $notify,
        ]);
    }

    public function notificationCreate(Request $request)
    {
        $file_name = null;
        if($request->hasFile('icon')) {
            $file = $request->file('icon');
            $file_name = time() . "icon." . $file->getClientOriginalExtension();
            $file->move(public_path('icon'), $file_name);
        }

        Notification::create([
            'label' => $request->label,
            'short_message' => $request->short_message,
            'body_message' => $request->body_message,
            'expire' => $request->expire,
            'status' => $request->status,
            'icon' => $file_name,
            'company_id' => auth()->user()->id,
            'source' => 'super',
        ]);

        // Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt));

        return redirect()->back()->with('success', "Notification successfully added");
    }

    public function notificationDelete($id)
    {
        $idUser = decrypt($id);
        Notification::find($idUser)->delete();
        return redirect()->back()->with('success', "Notification successfully removed");
    }

    public function notificationEdit(Request $request)
    {
        $idUser = decrypt($request->id);
        $file_name = null;
        if($request->hasFile('icon')) {
            $file = $request->file('icon');
            $file_name = time() . "icon." . $file->getClientOriginalExtension();
            $file->move(public_path('icon'), $file_name);
            $old_file = Notification::find($idUser)->icon;
            if ($old_file) {
                unlink(public_path('icon') . '/' . $old_file);
            }
        }
        Notification::find($idUser)->update([
            'label' => $request->label,
            'short_message' => $request->short_message,
            'body_message' => $request->body_message,
            'expire' => $request->expire,
            'status' => $request->status,
            'icon' => $file_name,
        ]);
        return redirect()->back()->with('success', "Notification successfully updated");
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

    public function bountyCategory()
    {
        $data = BountyCategory::get();

        return view('super.bountyCategory', [
            'page' => "Bounty Category",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function bountyCategoryAdd(Request $request)
    {
        BountyCategory::create([
            'label' => $request->label,
            'description' => $request->description,
        ]);
        return redirect()->back()->with('success', "Category successfully created");
    }

    public function bountyCategoryUpdate(Request $request)
    {
        BountyCategory::find(decrypt($request->id))->update([
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', "Category successfully updated");
    }

    public function bountySubCategory($id)
    {
        $data = BountyCategorySub::where('category_id', decrypt($id))->get();

        return view('super.bountyCategorySub', [
            'page' => "Bounty Sub-Category",
            'opt' => 'admin',
            'category_id' => $id,
            'data' => $data
        ]);
    }

    public function bountySubCategoryAdd(Request $request)
    {
        BountyCategorySub::create([
            'category_id' => decrypt($request->category_id),
            'label' => $request->label,
            'award_point' => $request->award_point,
            'award_amount' => $request->award_amount,
            'description' => $request->description,
        ]);
        return redirect()->back()->with('success', "Category successfully created");
    }

    public function bountySubCategoryUpdate(Request $request)
    {
        BountyCategorySub::find(decrypt($request->id))->update([
            'label' => $request->label,
            'award_point' => $request->award_point,
            'award_amount' => $request->award_amount,
            'description' => $request->description,
            'status' => $request->status,
        ]);
        return redirect()->back()->with('success', "Category successfully updated");
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

    public function bountyReport($severity = null, $category = null, $sub = null)
    {
        // Base query with optional filters
        $baseQuery = BountyUserReport::query();

        if ($severity && $severity != 'all') {
            $baseQuery->where('severity', $severity);
        }

        if ($category) {
            $baseQuery->where('category', $category);
        }

        if ($sub) {
            $baseQuery->where('category_sub', $sub);
        }

        // Get all reports
        $data = $baseQuery->get();

        // Define all statuses
        $statuses = ['new', 'review', 'accept', 'reject', 'fix', 'close'];

        // Build status-wise collections dynamically
        $statusData = [];

        foreach ($statuses as $status) {
            $statusData["data" . ucfirst($status)] = (clone $baseQuery)
                ->where('status', $status)->orderBy('updated_at', "DESC")
                ->get();
        }

        $cat = BountyCategory::get();
        $catsub = null;
        if($category){
            $catsub = BountyCategorySub::where('category_id', $category)->get();
        }

        return view('super.bountyReport', [
            'page' => "Bounty User Report",
            'opt' => 'admin',
            'data' => $data,
            'cat' => $cat,
            'catsub' => $catsub,
            'catLab' => $category ? BountyCategory::find($category)->label : "All",
            'catsubLab' => $sub ? BountyCategory::find($sub)->label : "All",
            'dataNew' => $statusData['dataNew'],
            'dataReview' => $statusData['dataReview'],
            'dataAccept' => $statusData['dataAccept'],
            'dataReject' => $statusData['dataReject'],
            'dataFix' => $statusData['dataFix'],
            'dataClose' => $statusData['dataClose'],
            'severity' => $severity ?? "all",
            'category' => $category,
            'sub' => $sub
        ]);
    }


    public function bountyReportView($id)
    {
        $data = BountyUserReport::find(decrypt($id));
        if($data->status == 'new'){
            $data->update(["status" => "review"]);
        }

        return view('super.bountyReportDetail', [
            'page' => "Bounty Report",
            'opt' => 'admin',
            'data' => $data
        ]);
    }

    public function reportApproval(Request $request)
    {
        $data = BountyUserReport::find(decrypt($request->id));
        if($request->status == "accept"){
            $data->update([
                "admin_comment" => $request->admin_comment,
                "amount" => $request->amount,
                "point" => $request->point,
                "status" => $request->status
            ]);
        }else{
            $data->update([
                "admin_comment" => $request->admin_comment,
                "status" => $request->status
            ]);
        }

        return redirect()->back()->with('success', "Report status successfully updated");
    }

    public function reportMarkFix($id)
    {
        $data = BountyUserReport::find(decrypt($id));
        $data->update([ "status" => "fix"]);

        return redirect()->back()->with('success', "Report status successfully updated");
    }

    public function bountyUserActive(Request $request)
    {
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                BountyUser::find($dt)->update([
                    'status' => 'active'
                ]);
            }

            return redirect()->back()->with('success', "User account activated successfully");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function bountyUserBlock(Request $request)
    {
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                BountyUser::find($dt)->update([
                    'status' => 'block'
                ]);
            }

            return redirect()->back()->with('success', "User account deactivated successfully");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
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
