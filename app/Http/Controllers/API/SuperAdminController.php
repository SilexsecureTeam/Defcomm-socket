<?php

namespace App\Http\Controllers\API;

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
use App\Models\Notification;
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
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware(function ($request, $next) {
        //     $user = Auth::user();

        //     if (!$user || $user->role !== 'super') {
        //         // Auth::logout();
        //         return response()->json(
        //             [
        //                 'status' => '400',
        //                 'message' => 'Unauthorized access',
        //                 'data' => null
        //             ],
        //             401
        //         );
        //     }

        //     // return $next($request);
        // });
    }

    public function dashboard()
    {
        $usr = User::where('role', 'admin')->get();
        $plan = UserPlan::where('status', 'active')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'usr' => $usr,
                    'plan' => $plan
                ]
            ],
            201
        );
    }

    public function account($user)
    {
        $usr = User::where('role', $user)->get();
        $plan = UserPlan::where('status', 'active')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'usr' => $usr,
                    'plan' => $plan
                ]
            ],
            201
        );
    }

    public function accountView($id)
    {
        $comp = CompanyUser::find(decrypt($id));
        if ($comp) {
            $usr = User::where('role', 'user')->where('company_id', $comp->id)->get();
            $plan = UserPlan::where('status', 'active')->get();
            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Record listed',
                    'data' => [
                        'usr' => $usr,
                        'plan' => $plan
                    ]
                ],
                201
            );
        }
        return response()->json(
            [
                'status' => '400',
                'message' => 'No data found for this account',
                'data' => []
            ],
            400
        );
    }

    public function accessLog($id)
    {
        $device = UserLoginDevice::where('user_id', decrypt($id))->get();
        $log = UserLoginLog::where('user_id', decrypt($id))->get();
        $user = User::find(decrypt($id));

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'device' => $device,
                    'log' => $log,
                    'user' => $user
                ]
            ],
            201
        );
    }

    public function accountDelete($id)
    {
        User::find(decrypt($id))->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'User successfully deleted',
                'data' => []
            ],
            201
        );
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
            return response()->json(
                [
                    'status' => '400',
                    'message' => $error,
                    'data' => []
                ],
                400
            );
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

        if ($request->nameorg) {
            $comp = CompanyUser::create([
                'name' => $request->nameorg,
                'user_id' => $usr->id
            ]);

            $usr->update(['company_id' => $comp->id]);
        } else {
            $usr->update(['status' => "active", 'role' => 'super',]);
        }

        $encrypt = encrypt($request->email);

        Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt, $otp));

        $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join. If you have any questions or concerns we  are here to help contact us via our Help Center';

        $this->TermiiSms($request->phone, $bodysms);

        return response()->json(
            [
                'status' => '200',
                'message' => 'User successfully added',
                'data' => []
            ],
            201
        );
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
            return response()->json(
                [
                    'status' => '400',
                    'message' => $error,
                    'data' => []
                ],
                400
            );
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

        return response()->json(
            [
                'status' => '200',
                'message' => 'User successfully updated',
                'data' => []
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function languageCreate(Request $request)
    {
        Language::updateOrCreate([
            'label' => $request->label
        ], [
            'description' => $request->description
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Language successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function languageEdit(Request $request)
    {
        Language::find(decrypt($request->id))->update([
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Language successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function agreements()
    {
        $data = StatementAgreement::get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function agreementsCreate(Request $request)
    {
        StatementAgreement::updateOrCreate([
            'title' => $request->title,
            'label' => $request->label
        ], [
            'description' => $request->description
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Agreements successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function agreementsEdit(Request $request)
    {
        StatementAgreement::find(decrypt($request->id))->update([
            'title' => $request->title,
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Agreements successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function notification()
    {
        $notify = Notification::where('source', 'super')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $notify
            ],
            201
        );
    }

    public function notificationCreate(Request $request)
    {
        $file_name = null;
        if ($request->hasFile('icon')) {
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

        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully added',
                'data' => []
            ],
            201
        );
    }

    public function notificationDelete($id)
    {
        $idUser = decrypt($id);
        Notification::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully removed',
                'data' => []
            ],
            201
        );
    }

    public function notificationEdit(Request $request)
    {
        $idUser = decrypt($request->id);
        $file_name = null;
        if ($request->hasFile('icon')) {
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function systemMail()
    {
        $data = SystemMail::get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function systemMailUpdate(Request $request)
    {
        SystemMail::find(decrypt($request->id))->update([
            'title' => $request->title,
            'message' => $request->message,
            'status' => $request->status
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Mail successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function storeUser()
    {
        $data = User::with('appStore')->whereHas('appStore')->get();
        $userPen = User::where('app_role', 'developer')->where('statusApp', 'pending')->get();
        $userApp = User::where('app_role', 'developer')->where('statusApp', 'verified')->get();
        $userRej = User::where('app_role', 'developer')->where('statusApp', 'reject')->get();
        $userBlk = User::where('app_role', 'developer')->where('statusApp', 'block')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'data' => $data,
                    'userPen' => $userPen,
                    'userApp' => $userApp,
                    'userRej' => $userRej,
                    'userBlk' => $userBlk
                ]
            ],
            201
        );
    }

    public function storeUserDetail($id)
    {
        $data = User::find(decrypt($id));
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function storeuserdetailSub(Request $request)
    {
        User::find(decrypt($request->id))->update([
            'statusApp' => $request->statusApp,
            'commentApp' => $request->commentApp
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'App status successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function storeApp($id = null)
    {
        if ($id) {
            $data = AppStore::where('user', decrypt($id))->get();
        } else {
            $data = AppStore::get();
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function storeappdetail($id)
    {
        $data = AppStore::find(decrypt($id));

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function storeappdetailSub(Request $request)
    {
        AppStore::find(decrypt($request->id))->update([
            'status' => $request->status,
            'comment' => $request->comment
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'App status successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function webContact()
    {
        $data = ContactSubmission::get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function webBooking()
    {
        $data = ContactBooking::get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function plan()
    {
        $data = UserPlan::get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Plan successfully added',
                'data' => []
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Plan successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function bountyUser($type = 'user')
    {
        $data = BountyUser::where('user_type', $type)->get();
        $userPen = BountyUser::where('user_type', $type)->where('status', 'pending')->get();
        $userApp = BountyUser::where('user_type', $type)->where('status', 'active')->get();
        $userBlk = BountyUser::where('user_type', $type)->where('status', 'block')->get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'data' => $data,
                    'userPen' => $userPen,
                    'userApp' => $userApp,
                    'userBlk' => $userBlk
                ]
            ],
            201
        );
    }

    public function bountyUserId($id)
    {
        $data = BountyUser::find(decrypt($id));

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function bountyCategory()
    {
        $data = BountyCategory::get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function bountyCategoryAdd(Request $request)
    {
        BountyCategory::create([
            'label' => $request->label,
            'description' => $request->description,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Category successfully created',
                'data' => []
            ],
            201
        );
    }

    public function bountyCategoryUpdate(Request $request)
    {
        BountyCategory::find(decrypt($request->id))->update([
            'label' => $request->label,
            'description' => $request->description,
            'status' => $request->status,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Category successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function bountySubCategory($id)
    {
        $data = BountyCategorySub::where('category_id', decrypt($id))->get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Category successfully created',
                'data' => []
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Category successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function bountyProgram()
    {
        $data = BountyUserProgram::get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function bountyProgramAdd(Request $request)
    {
        BountyUserProgram::create([
            'title' => $request->title,
            'detail' => $request->detail,
            'status' => $request->status,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Program successfully created',
                'data' => []
            ],
            201
        );
    }

    public function bountyProgramUpdate(Request $request)
    {
        BountyUserProgram::find(decrypt($request->id))->update([
            'title' => $request->title,
            'detail' => $request->detail,
            'status' => $request->status,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Program successfully updated',
                'data' => []
            ],
            201
        );
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
        if ($category) {
            $catsub = BountyCategorySub::where('category_id', $category)->get();
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'data' => $data,
                    'dataNew' => $statusData['dataNew'],
                    'dataReview' => $statusData['dataReview'],
                    'dataAccept' => $statusData['dataAccept'],
                    'dataReject' => $statusData['dataReject'],
                    'dataFix' => $statusData['dataFix'],
                    'dataClose' => $statusData['dataClose'],
                    'cat' => $cat,
                    'catsub' => $catsub
                ]
            ],
            201
        );
    }


    public function bountyReportView($id)
    {
        $data = BountyUserReport::find(decrypt($id));
        if ($data->status == 'new') {
            $data->update(["status" => "review"]);
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function reportApproval(Request $request)
    {
        $data = BountyUserReport::find(decrypt($request->id));
        if ($request->status == "accept") {
            $data->update([
                "admin_comment" => $request->admin_comment,
                "amount" => $request->amount,
                "point" => $request->point,
                "status" => $request->status
            ]);
        } else {
            $data->update([
                "admin_comment" => $request->admin_comment,
                "status" => $request->status
            ]);
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Report status successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function reportMarkFix($id)
    {
        $data = BountyUserReport::find(decrypt($id));
        $data->update(["status" => "fix"]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Report status successfully updated',
                'data' => []
            ],
            201
        );
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

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'User account activated successfully',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
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

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'User account deactivated successfully',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
    }

    public function program()
    {
        $data = Program::orderBy('started_at', 'DESC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Program successfully created',
                'data' => []
            ],
            201
        );
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Program successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function attendance($id)
    {
        $data = ProgramAttendance::where('program_id', decrypt($id))->orderBy('created_at', 'ASC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function attendanceUser($id, $userId, $userType)
    {
        $data = ProgramAttendance::updateOrCreate([
            'program_id' => decrypt($id),
            'user_id' => decrypt($userId),
            'user_type' => decrypt($userType)
        ], [
            'comment' => "Checked by " . auth()->user()->name,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Attendance marked successfully',
                'data' => []
            ],
            201
        );
    }
}
