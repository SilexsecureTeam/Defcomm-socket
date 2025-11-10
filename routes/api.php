<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\BountyController;
use App\Http\Controllers\API\WebController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\TranslateController;
use App\Http\Controllers\API\QrLoginController;
use App\Http\Controllers\GoogleAiTransController;
use App\Http\Controllers\API\WalkieTalkieController;

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
Route::post('emailVerify', [AuthController::class, 'emailVerify']);
Route::post('userVerify', [AuthController::class, 'userVerify']);
Route::post('login', [AuthController::class, 'login']);
Route::post('requestOtpSms', [AuthController::class, 'requestOtpSms']);
Route::post('loginWithPhone', [AuthController::class, 'loginWithPhone']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::post('app/authenticate', [AuthController::class, 'appAuthenticate']);
Route::get('app/language', [AuthController::class, 'appLanguage']);
Route::get('app/agreements/{term?}', [AuthController::class, 'appAgreements']);
Route::get('/app/list', [UserController::class, 'appList']);
Route::get('/app/listId/{id}', [UserController::class, 'appListId']);

// QR Login
Route::post('/qr/create', [QrLoginController::class, 'create']);           // anonymous
Route::get('/qr/{code}/status', [QrLoginController::class, 'status']);     // anonymous poll
Route::post('/qr/{code}/exchange', [QrLoginController::class, 'exchange']); // desktop exchanges

Route::prefix('web')->group(function () {
    Route::post('/contact', [WebController::class, 'contact']); // submit form
    Route::post('/booking', [WebController::class, 'booking']); // submit form
    Route::post('/eventform', [WebController::class, 'eventform']); // submit form


    // Route::get('/', [WebController::class, 'index']); // list all
    // Route::get('/{id}', [WebController::class, 'show']); // single
    // Route::delete('/{id}', [WebController::class, 'destroy']); // delete
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/qr/{code}/approve', [QrLoginController::class, 'approve']); // mobile approves

    Route::post('app/resetPassword', [AuthController::class, 'appresetPassword']);
    Route::post('app/configuration', [AuthController::class, 'appConfiguration']);
    Route::post('app/developermode', [AuthController::class, 'appDevelopermode']);

    Route::get('auth/userplan', [AuthController::class, 'userplan']);
    Route::get('auth/logindevicelog', [AuthController::class, 'logindevicelog']);
    Route::get('auth/logindevice/{status}', [AuthController::class, 'logindevice']);
    Route::get('auth/logindevicestatus/{id}/{status}', [AuthController::class, 'logindevicestatus']);
    Route::post('auth/loginblockip', [AuthController::class, 'loginblockip']);
    Route::get('auth/loginblockip/list', [AuthController::class, 'loginblockipList']);
    Route::post('auth/loginunblockip', [AuthController::class, 'loginunblockip']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('/user/file', [UserController::class, 'file']);
    Route::get('/user/file/pending', [UserController::class, 'fileOtherPending']);
    Route::get('/user/file/other', [UserController::class, 'fileOther']);
    Route::get('/user/file/request', [UserController::class, 'fileRequest']);
    Route::post('/user/file/upload', [UserController::class, 'fileUpload']);
    Route::post('/user/file/share', [UserController::class, 'fileShare']);
    Route::get('/user/file/{id}/download', [UserController::class, 'fileDownload']);
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

    Route::get('/user/chat/callLog', [UserController::class, 'chatCallLog']);
    Route::get('/user/chat/history', [UserController::class, 'chatHistory']);
    Route::get('/user/chat/lastMessage', [UserController::class, 'lastMessage']);
    Route::get('/user/chat/messages/{chat_user_id}/{user_group}', [UserController::class, 'chatMessages']);
    Route::post('/user/chat/messages/send', [UserController::class, 'sendMessage']);
    Route::post('/user/chat/messages/call', [UserController::class, 'sendMessageCall']);
    Route::post('/user/messages/{type}', [UserController::class, 'messagesTyping']);
    Route::get('/user/messages/important/{id}', [UserController::class, 'messagesImportant']);
    Route::get('/user/messages/isread/{id}', [UserController::class, 'messagesIsread']);

    Route::get('/user/meetingTokenGen', [UserController::class, 'meetingTokenGen']);
    Route::post('/user/meeting/create', [UserController::class, 'meetingCreate']);
    Route::post('/user/meeting/update', [UserController::class, 'meetingUpdate']);
    Route::get('/user/getmeeting', [UserController::class, 'getmeeting']);
    Route::get('/user/getmeeting/{id}', [UserController::class, 'getmeetingDetail']);
    Route::get('/user/getmeetingid/{id}/{type}', [UserController::class, 'getmeetingid']);
    Route::post('/user/meetingInvitation', [UserController::class, 'meetingInvitation']);
    Route::post('/user/meetingInvitationGroup', [UserController::class, 'meetingInvitationGroup']);
    Route::get('/user/meetingInvitationJoin/{id}', [UserController::class, 'meetingInvitationJoin']);
    Route::get('/user/meetingInvitationlist/{status?}', [UserController::class, 'meetingInvitationlist']);
    Route::get('/user/meetingParticipantlist/{id}/{status}', [UserController::class, 'meetingParticipantlist']);

    Route::post('/user/folder/create', [UserController::class, 'folderCreate']);
    Route::post('/user/folderUpdate', [UserController::class, 'folderUpdate']);
    Route::get('/user/folder/', [UserController::class, 'folderget']);
    Route::get('/user/folder/{id}', [UserController::class, 'foldergetId']);
    Route::get('/user/folderDel/{id}', [UserController::class, 'folderdelete']);
    Route::post('/user/folderFile', [UserController::class, 'folderFile']);

    Route::post('/user/setting', [UserController::class, 'setting']);
    Route::get('/user/getsetting', [UserController::class, 'getsetting']);
    Route::get('/user/languagecode', [UserController::class, 'languagecode']);

    Route::get('/user/notification', [UserController::class, 'notification']);

    Route::post('/app/create', [UserController::class, 'appcreate']);
    Route::post('/app/status', [UserController::class, 'appstatus']);
    Route::get('/app/ownlist', [UserController::class, 'appOwnList']);
    Route::post('/app/audio/player/delete', [UserController::class, 'deleteTemporaryAudio']);

    Route::post('/walkietalkie/channelcreate', [WalkieTalkieController::class, 'channelcreate']);
    Route::post('/walkietalkie/channelupdate', [WalkieTalkieController::class, 'channelupdate']);
    Route::get('/walkietalkie/channecreatellist', [WalkieTalkieController::class, 'channecreatellist']);
    Route::get('/walkietalkie/channedelete/{id}', [WalkieTalkieController::class, 'channedelete']);
    Route::post('/walkietalkie/channelinvite', [WalkieTalkieController::class, 'channelinvite']);
    Route::get('/walkietalkie/channellistinvited/{status}', [WalkieTalkieController::class, 'channellistinvited']);
    Route::post('/walkietalkie/channelinvitedstatus', [WalkieTalkieController::class, 'channelinvitedstatus']);
    Route::post('/walkietalkie/channelbroadcast', [WalkieTalkieController::class, 'channelbroadcast']);
    Route::post('/walkietalkie/channelisbroadcasting', [WalkieTalkieController::class, 'channelisbroadcasting']);
    Route::get('/walkietalkie/channelbroadcastlist/{id}', [WalkieTalkieController::class, 'channelbroadcastlist']);
    Route::get('/walkietalkie/channelbroadcastdel/{id}', [WalkieTalkieController::class, 'channelbroadcastdel']);
    Route::post('/walkietalkie/subscriberJoin', [WalkieTalkieController::class, 'subscriberJoin']);
    Route::post('/walkietalkie/subscriberLeave', [WalkieTalkieController::class, 'subscriberLeave']);
    Route::get('/walkietalkie/subscriberActive/{id}', [WalkieTalkieController::class, 'subscriberActive']);

    Route::post('/trans/speech-to-text', [GoogleAiTransController::class, 'speechToText']);
    Route::post('/trans/text-to-speech', [GoogleAiTransController::class, 'textToSpeech']);
    Route::post('/trans/translate-text', [GoogleAiTransController::class, 'translateText']);
    Route::post('/trans/speech-to-speech', [GoogleAiTransController::class, 'speechToSpeech']);
});

Route::post('/bounty/register', [BountyController::class, 'register']);
Route::post('/bounty/verify', [BountyController::class, 'verify']);
Route::post('/bounty/requestOtp', [BountyController::class, 'requestOtp']);
Route::post('/bounty/login', [BountyController::class, 'login']);
Route::post('/bounty/loginVerify', [BountyController::class, 'loginVerify']);
Route::post('/bounty/forgot-password', [BountyController::class, 'forgotPassword']);
Route::post('/bounty/reset-password', [BountyController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bounty/profile', [BountyController::class, 'profile']);
    Route::post('/bounty/logout', [BountyController::class, 'logout']);
});
