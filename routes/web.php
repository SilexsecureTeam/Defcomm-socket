<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AuthenticateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'test'])->name('tesr');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// require __DIR__.'/auth.php';

Route::get('/pdf-proxy/{filename}', function ($filename) {
    $path = public_path("/secure/{$filename}");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});


Route::get('/', [AuthenticateController::class, 'index'])->name('home');
Route::get('/login', [AuthenticateController::class, 'index'])->name('login');
Route::get('/loginOtp', [AuthenticateController::class, 'loginOtp'])->name('loginOtp');
Route::post('/loginOtp/store', [AuthenticateController::class, 'loginOtpStore'])->name('loginOtp.store');
Route::get('/logout', [AuthenticateController::class, 'logout'])->name('logout');
Route::get('/register', [AuthenticateController::class, 'register'])->name('register');
Route::post('/login/store', [AuthenticateController::class, 'login'])->name('login.store');
Route::post('/register/store', [AuthenticateController::class, 'registerStore'])->name('register.store');
Route::get('/reset/account/{encrypt}', [AuthenticateController::class, 'resetAccount'])->name('reset.account');
Route::post('/reset/account/login/store', [AuthenticateController::class, 'resetAccountStore'])->name('reset.account.store');

Route::get('/user/file/view/{id}/{user}', [CommonController::class, 'fileView'])->name('user.com.file.view');

Route::middleware(['auth'])->group(function () {
    Route::post('/common/submitChatSetting', [CommonController::class, 'submitChatSetting'])->name('common.submitChatSetting');
    Route::post('/common/submitChatFile', [CommonController::class, 'submitChatFile'])->name('common.submitChatFile');
});

Route::middleware(['auth', 'user-role:super'])->group(function () {
    Route::get('/super/dashboard', [SuperAdminController::class, 'dashboard'])->name('super.dashboard');
    Route::get('/super/account', [SuperAdminController::class, 'account'])->name('super.account');
    Route::post('/super/accountCreate', [SuperAdminController::class, 'accountCreate'])->name('super.accountCreate');
    Route::post('/super/accountEdit', [SuperAdminController::class, 'accountEdit'])->name('super.accountEdit');
    Route::get('/super/accountView/{id}', [SuperAdminController::class, 'accountView'])->name('super.accountView');
    Route::get('/super/accessLog/{id}', [SuperAdminController::class, 'accessLog'])->name('super.accessLog');
    Route::get('/super/accountDelete/{id}', [SuperAdminController::class, 'accountDelete'])->name('super.accountDelete');
    Route::get('/super/accountToken', [SuperAdminController::class, 'accountToken'])->name('super.accountToken');

    Route::get('/super/systemMail', [SuperAdminController::class, 'systemMail'])->name('super.systemMail');
    Route::post('/super/systemMailUpdate', [SuperAdminController::class, 'systemMailUpdate'])->name('super.systemMailUpdate');

    Route::get('/super/language', [SuperAdminController::class, 'language'])->name('super.language');
    Route::post('/super/languageCreate', [SuperAdminController::class, 'languageCreate'])->name('super.languageCreate');
    Route::post('/super/languageEdit', [SuperAdminController::class, 'languageEdit'])->name('super.languageEdit');

    Route::get('/super/agreements', [SuperAdminController::class, 'agreements'])->name('super.agreements');
    Route::post('/super/agreementsCreate', [SuperAdminController::class, 'agreementsCreate'])->name('super.agreementsCreate');
    Route::post('/super/agreementsEdit', [SuperAdminController::class, 'agreementsEdit'])->name('super.agreementsEdit');

    Route::get('/super/store/user', [SuperAdminController::class, 'storeUser'])->name('super.store.user');
    Route::get('/super/store/user/{id}', [SuperAdminController::class, 'storeUserDetail'])->name('super.store.user.detail');
    Route::post('/super/store/user/detailSub', [SuperAdminController::class, 'storeuserdetailSub'])->name('super.store.user.detailSub');
    Route::get('/super/store/app/{id?}', [SuperAdminController::class, 'storeApp'])->name('super.store.app');
    Route::get('/super/store/app/detail/{id}', [SuperAdminController::class, 'storeappdetail'])->name('super.store.appt.detail');
    Route::post('/super/store/app/detailSub', [SuperAdminController::class, 'storeappdetailSub'])->name('super.store.app.detailSub');
    Route::get('/super/web/contact', [SuperAdminController::class, 'webContact'])->name('super.web.contact');
    Route::get('/super/web/booking', [SuperAdminController::class, 'webBooking'])->name('super.web.booking');
    Route::get('/super/plan', [SuperAdminController::class, 'plan'])->name('super.plan');
    Route::post('/super/planAdd', [SuperAdminController::class, 'planAdd'])->name('super.planAdd');
    Route::post('/super/planEdit', [SuperAdminController::class, 'planEdit'])->name('super.planEdit');

    Route::get('/super/bounty/user', [SuperAdminController::class, 'bountyUser'])->name('super.bountyUser');
    Route::get('/super/bounty/user/{id}', [SuperAdminController::class, 'bountyUserId'])->name('super.bountyUserId');
});

Route::middleware(['auth', 'user-role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/notification', [AdminController::class, 'notification'])->name('admin.notification');
    Route::post('/admin/notification/create', [AdminController::class, 'notificationCreate'])->name('admin.notification.create');
    Route::get('/admin/notification/delete/{id}', [AdminController::class, 'notificationDelete'])->name('admin.notification.delete');
    Route::get('/admin/account', [AdminController::class, 'account'])->name('admin.account');
    Route::get('/admin/account/{id}/{status}', [AdminController::class, 'accountStatus'])->name('admin.account.status');
    Route::post('/admin/account/create', [AdminController::class, 'accountCreate'])->name('admin.account.create');
    Route::post('/admin/account/block', [AdminController::class, 'accountBlock'])->name('admin.account.block');
    Route::get('/admin/group', [AdminController::class, 'group'])->name('admin.group');
    Route::post('/admin/group/create', [AdminController::class, 'groupCreate'])->name('admin.group.create');
    Route::get('/admin/group/member/{id}', [AdminController::class, 'member'])->name('admin.member');
    Route::get('/admin/group/member/{id}/add', [AdminController::class, 'memberAdd'])->name('admin.member.add');
    Route::get('/admin/group/member/{id}/remove', [AdminController::class, 'memberRemove'])->name('admin.member.remove');
    Route::post('/admin/group/member/add', [AdminController::class, 'memberGroupAdd'])->name('admin.member.group.add');

    Route::get('/admin/meeting', [AdminController::class, 'meeting'])->name('admin.meeting');
    Route::post('/admin/meeting/create', [AdminController::class, 'meetingCreate'])->name('admin.meeting.create');

    Route::get('/admin/form', [AdminController::class, 'form'])->name('admin.form');
    Route::get('/admin/form/application/{id}', [AdminController::class, 'formApplication'])->name('admin.form.application');
    Route::post('/admin/form/create', [AdminController::class, 'formCreate'])->name('admin.form.create');
    Route::post('/admin/form/update', [AdminController::class, 'formUpdate'])->name('admin.form.update');

    Route::get('/admin/file', [AdminController::class, 'file'])->name('admin.file');
    Route::get('/admin/file/user', [AdminController::class, 'fileUser'])->name('admin.file.user');
    Route::get('/admin/file/request', [AdminController::class, 'fileRequest'])->name('admin.file.request');
    Route::get('/admin/file/view/{id}', [AdminController::class, 'fileView'])->name('admin.file.view');
    Route::get('/admin/file/download/{id}', [AdminController::class, 'fileDownload'])->name('admin.file.download');
    Route::post('/admin/file/upload', [AdminController::class, 'fileUpload'])->name('admin.file.upload');
    Route::get('/admin/file/share/group/{id}', [AdminController::class, 'fileShareGroup'])->name('admin.file.share.group');
    Route::post('/admin/file/share/group/add', [AdminController::class, 'fileShareGroupAdd'])->name('admin.file.share.group.add');
    Route::get('/admin/file/share/user/{id}', [AdminController::class, 'fileShareUser'])->name('admin.file.share.user');
    Route::post('/admin/file/share/user/add', [AdminController::class, 'fileShareUserAdd'])->name('admin.file.share.user.add');
    Route::get('/admin/file/access/group/{id}', [AdminController::class, 'fileAccessGroup'])->name('admin.file.access.group');
    Route::get('/admin/file/access/user/{id}', [AdminController::class, 'fileAccessUser'])->name('admin.file.access.user');
    Route::get('/admin/file/access/{id}/revoke', [AdminController::class, 'fileAccessRevoke'])->name('admin.file.access.revoke');
    Route::get('/admin/file/access/{id}/log', [AdminController::class, 'fileAccessLog'])->name('admin.file.access.log');
    Route::get('/admin/file/{id}/accept', [AdminController::class, 'fileAccept'])->name('admin.file.accept');
    Route::get('/admin/file/{id}/decline', [AdminController::class, 'fileDecline'])->name('admin.file.decline');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/admin/profile/upload', [AdminController::class, 'profileUpload'])->name('admin.profile.upload');
});

Route::middleware(['auth', 'user-role:user'])->group(function () {
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/file', [UserController::class, 'file'])->name('user.file');
    Route::get('/user/file/other', [UserController::class, 'fileOther'])->name('user.file.other');
    Route::get('/user/file/request', [UserController::class, 'fileRequest'])->name('user.file.request');
    Route::post('/user/file/upload', [UserController::class, 'fileUpload'])->name('user.file.upload');
    Route::get('/user/file/{id}/view', [UserController::class, 'fileView'])->name('user.file.view');
    Route::get('/user/file/{id}/accept', [UserController::class, 'fileAccept'])->name('user.file.accept');
    Route::get('/user/file/{id}/decline', [UserController::class, 'fileDecline'])->name('user.file.decline');
    Route::get('/user/file/share/{id}/user', [UserController::class, 'fileShareUser'])->name('user.file.share.user');
    Route::post('/user/file/share/user/add', [UserController::class, 'fileShareUserAdd'])->name('user.file.share.user.add');
    Route::get('/user/file/share/{id}/user/request', [UserController::class, 'fileShareUserRequest'])->name('user.file.share.user.request');
    Route::post('/user/file/share/user/add/request', [UserController::class, 'fileShareUserAddRequest'])->name('user.file.share.user.add.request');
    Route::get('/user/file/access/user/{id}', [UserController::class, 'fileAccessUser'])->name('user.file.access.user');
    Route::get('/user/file/access/log/{id}', [UserController::class, 'fileAccessLog'])->name('user.file.access.log');
    Route::get('/user/file/access/{id}/revoke', [UserController::class, 'fileAccessRevoke'])->name('user.file.access.revoke');
    Route::get('/user/group', [UserController::class, 'group'])->name('user.group');
    Route::get('/user/group/member/{id}', [UserController::class, 'member'])->name('user.group.member');
    Route::get('/user/group/member/{id}/{status}/state', [UserController::class, 'memberChangeState'])->name('user.group.member.state');
    Route::get('/user/group/{id}/accept', [UserController::class, 'groupAccept'])->name('user.group.accept');
    Route::get('/user/group/{id}/decline', [UserController::class, 'groupDecline'])->name('user.group.decline');
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile/upload', [UserController::class, 'profileUpload'])->name('user.profile.upload');
    Route::get('/user/contact', [UserController::class, 'contact'])->name('user.contact');
    Route::get('/user/contact/add/{id}', [UserController::class, 'contactAdd'])->name('user.contact.add');
    Route::get('/user/contact/remove/{id}', [UserController::class, 'contactRemove'])->name('user.contact.remove');
});
