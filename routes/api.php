<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('requestOtpSms', [AuthController::class, 'requestOtpSms']);
Route::post('loginWithPhone', [AuthController::class, 'loginWithPhone']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/file', [UserController::class, 'file']);
    Route::get('/user/file/pending', [UserController::class, 'fileOtherPending']);
    Route::get('/user/file/other', [UserController::class, 'fileOther']);
    Route::get('/user/file/request', [UserController::class, 'fileRequest']);
    Route::post('/user/file/upload', [UserController::class, 'fileUpload']);
    Route::post('/user/file/share', [UserController::class, 'fileShare']);
    Route::get('/user/file/{id}/view', [UserController::class, 'fileView']);
    Route::get('/user/file/{id}/url', [UserController::class, 'fileViewUrl']);
    Route::get('/user/file/{id}/accept', [UserController::class, 'fileAccept']);
    Route::get('/user/file/{id}/decline', [UserController::class, 'fileDecline']);
    Route::get('/user/group', [UserController::class, 'group']);
    Route::get('/user/group/pending', [UserController::class, 'groupPendig']);
    Route::get('/user/group/{id}/accept', [UserController::class, 'groupAccept']);
    Route::get('/user/group/{id}/decline', [UserController::class, 'groupDecline']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/profile/upload', [UserController::class, 'profileUpload']);

    Route::get('/user/group/member/{id}', [UserController::class, 'groupMember']);
    Route::get('/user/contact', [UserController::class, 'contact']);
    Route::get('/user/contact/add/{id}', [UserController::class, 'contactAdd']);
    Route::get('/user/contact/remove/{id}', [UserController::class, 'contactRemove']);

    Route::get('/user/chat/history', [UserController::class, 'chatHistory']);
    Route::get('/user/chat/messages/{chat_user_id}/{user_group}', [UserController::class, 'chatMessages']);
    Route::post('/user/chat/messages/send', [UserController::class, 'sendMessage']);
    Route::post('/user/chat/messages/call', [UserController::class, 'sendMessageCall']);

    Route::post('/user/meeting/create', [UserController::class, 'meetingCreate']);
    Route::post('/user/meeting/update', [UserController::class, 'meetingUpdate']);
    Route::get('/user/getmeeting', [UserController::class, 'getmeeting']);
    Route::get('/user/getmeetingid/{id}/{type}', [UserController::class, 'getmeetingid']);
    Route::post('/user/meetingInvitation', [UserController::class, 'meetingInvitation']);
    Route::post('/user/meetingInvitationGroup', [UserController::class, 'meetingInvitationGroup']);
    Route::get('/user/meetingInvitationJoin/{id}', [UserController::class, 'meetingInvitationJoin']);

    Route::post('/user/folder/create', [UserController::class, 'folderCreate']);
    Route::post('/user/folderUpdate', [UserController::class, 'folderUpdate']);
    Route::get('/user/folder/{id}', [UserController::class, 'folderget']);
    Route::get('/user/folderDel/{id}', [UserController::class, 'folderdelete']);
    Route::post('/user/folderFile', [UserController::class, 'folderFile']);

    Route::post('/user/setting', [UserController::class, 'setting']);

    Route::get('/user/notification', [UserController::class, 'notification']);
});
