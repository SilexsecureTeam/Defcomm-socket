<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WailkieTalkieChannel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Models\WailkieTalkieRecorder;
use App\Models\WailkieTalkieSubscriber;
use App\Events\PrivateWalkieMessageSent;
use App\Http\Services\FileUploadService;
use App\Http\Services\FileEncryptorService;
use App\Mail\WailkieTalkieChannelInvitation;

class WalkieTalkieController extends Controller
{
    public function channelcreate(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if ($user->plan_id === null) {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You do not have an active plan. Please subscribe',
                    'data' => null
                ],
                401
            );
        }

        if ($user->plan->enable_walkie == "no") {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You do not have access to this feature. Please subscribe',
                    'data' => null
                ],
                401
            );
        }

        $data = WailkieTalkieChannel::where(function ($query) use ($request) {
            $query->where('name', $request->name)
                ->orWhere('frequency', $request->frequency);
        })->where('user_id', auth()->user()->id)->first();
        if(is_null($data)){
            $data = WailkieTalkieChannel::create([
                'user_id' => auth()->user()->id,
                'name' => $request->name,
                'frequency' => $request->frequency ?? uniqid(),
                'description' => $request->description
            ]);

            WailkieTalkieSubscriber::updateOrCreate([
                'channel_id' => $data->id,
                'user_id' => auth()->user()->id,
                'user_type' => 'creator',
                'status' => 'active'
            ]);

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Record listed',
                    'data' => [
                        'id' => encryptHelper($data->id),
                        'name' => $data->name,
                        'frequency' => $data->frequency,
                        'description' => $data->description
                    ]
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => "Input already in use",
                'error' => [
                    'name' => $data->name == $request->name ? "name already used" : '',
                    'frequency' => $data->frequency == $request->frequency ? "frequency already used" : ''
                ],
                'data' => null
            ],
            401
        );
    }

    public function channelupdate(Request $request)
    {
        $data = WailkieTalkieChannel::find(decryptHelper($request->id));

        if($request->name){
            $data->update(['name' => $request->name]);
        }
        if($request->frequency){
            $data->update(['frequency' => $request->frequency]);
        }
        if($request->description){
            $data->update(['description' => $request->description]);
        }
    
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'id' => encryptHelper($data->id),
                    'name' => $data->name,
                    'frequency' => $data->frequency,
                    'description' => $data->description
                ]
            ],
            201
        );
    }

    public function channedelete($id)
    {
        $data = WailkieTalkieChannel::find(decryptHelper($id))->delete();
    
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record deleted',
                'data' => null
            ],
            201
        );
    }

    public function channecreatellist()
    {
        $chan = WailkieTalkieSubscriber::where('user_id', auth()->user()->id)->where('user_type', 'creator')->get();

        $data = [];
        foreach ($chan as $ch) {
            $data[] = [
                'sub_id' => encryptHelper($ch->id),
                'channel_id' => encryptHelper($ch->channel->id),
                'channel_id_un' => $ch->channel->id,
                'name' => $ch->channel->name,
                'frequency' => $ch->channel->frequency,
                'description' => $ch->channel->description,
                'status' => $ch->status
            ];
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

    public function channelinvite(Request $request){
        $channel = WailkieTalkieChannel::find(decryptHelper($request->channel_id));
        $json = str_replace("'", '"', $request->users);
        $array = json_decode($json, true);
        foreach ($array as $value) {
            WailkieTalkieSubscriber::updateOrCreate([
                'channel_id' => $channel->id,
                'user_id' => decryptHelper($value),
            ], [
                'status' => 'pending'
            ]);

            $usr = User::find(decryptHelper($value));
            if ($usr) {
                Mail::to($usr->email)->send(new WailkieTalkieChannelInvitation($usr->name, $usr->email, $channel));
            }
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Invitation sent',
                'data' => null
            ],
            201
        );
    }

    public function channellistinvited($status) 
    {
        $chan = WailkieTalkieSubscriber::where('user_id', auth()->user()->id)->where('user_type','user')->where('status', $status)->get();

        $data = [];
        foreach($chan as $ch){
            $data[] = [
                'sub_id' => encryptHelper($ch->id),
                'channel_id' => encryptHelper($ch->channel->id),
                'channel_id_un' => $ch->channel->id,
                'name' => $ch->channel->name,
                'frequency' => $ch->channel->frequency,
                'description' => $ch->channel->description,
                'status' => $ch->status
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record list',
                'data' => $data
            ],
            201
        );
    }

    public function channelinvitedstatus(Request $request)
    {
        WailkieTalkieSubscriber::find(decryptHelper($request->sub_id))->update(['status' => $request->status]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record status update',
                'data' => null
            ],
            201
        );
    }

    public function channelbroadcast(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $chan = WailkieTalkieSubscriber::where('user_id', auth()->user()->id)->where('channel_id', decryptHelper($request->channel))->first();

        if($request->hasFile('record')) {
            $file = $request->file('record');
            $file_size = $fileSize = $file->getSize();
            $file_ext = $file->getClientOriginalExtension();
            $file_time = time();
            $file_name = $file_time . $file->getClientOriginalName() . '.enc';
            $fileName = $file_time . $file->getClientOriginalName();
            $originalPath = $file->storeAs('secure/uploads', $fileName);
            $encryptHelperedPath = $file->storeAs('secure/encryptWailkieTalkie',  $file_name);
            $decryptedHelperedPath = public_path('WailkieTalkie/decrypted/decrypted_'.  $fileName);
            File::put($decryptedHelperedPath, "");

            $encryptor = new FileEncryptorService();
            $encryptor->encryptAudio(
                public_path('storage/' . $originalPath),
                public_path('storage/' . $encryptHelperedPath)
            );

            if ($file_size >= 1073741824) {
                $file_size = number_format($file_size / 1073741824, 2) . ' GB';
            } elseif ($file_size >= 1048576) {
                $file_size = number_format($file_size / 1048576, 2) . ' MB';
            } elseif ($file_size >= 1024) {
                $file_size = number_format($file_size / 1024, 2) . ' KB';
            } else {
                $file_size = $file_size . ' bytes';
            }

            // $puburl = (new FileUploadService)->makeAudioTemporarilyPublic('translate/' . basename($outputFile));

            $rec = WailkieTalkieRecorder::create([
                'channel_id' => $chan->channel->id,
                'subscriber_id' => $chan->id,
                'user_id' => auth()->user()->id,
                'record' => encrypt("storage/secure/encryptWailkieTalkie/" . $file_name),
                'record_text' => encrypt(googleAiTransSTENHelper(
                    public_path('storage/secure/encryptWailkieTalkie/' . $file_name),
                    'storage/secure/decrypted/decrypted_'.  $fileName,
                    $user->chatSettings->chat_language
                )),
                'path' => encrypt('WailkieTalkie/decrypted/decrypted_' .  $fileName),
                'source_language' => $user->chatSettings->chat_language,
                'file_size' => $file_size,
                'file_ext' => $file_ext,
                'fileSize_num' => $fileSize,
            ]);

            $sendData = [
                'state' => 'walkie',
                'sender' => [
                    'id' => $chan->user_id,
                    'id_en' => encryptHelper($chan->user_id),
                    'name' => $chan->user->name,
                    'phone' => $chan->user->phone,
                    'email' => $chan->user->email,
                ],
                'receiver' => [
                    'id' => $chan->channel_id,
                    'id_en' => encryptHelper($chan->channel_id),
                    'name' => $chan->channel->name,
                    'frequency' => $chan->channel->frequency
                ],
                'mss_chat' => [
                    'recording_id' => encryptHelper($rec->id),
                    'channel_id' => encryptHelper($rec->channel_id),
                    'channel_name' => $rec->channel->name,
                    'user_id' => encryptHelper($rec->user_id),
                    'user_name' => $rec->user->name,
                    'record' => decrypt($rec->path),
                    'record_text' => $rec->record_text ? decrypt($rec->record_text) : null,
                    'created_at' => $rec->created_at,
                ]
            ];

            broadcast(new PrivateWalkieMessageSent(encryptHelper(auth()->user()->id), encryptHelper($chan->channel->id), $sendData))->toOthers();

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Record create',
                    'data' => $sendData
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Error in communication',
                'data' => null
            ],
            201
        );
    }

    public function channelisbroadcasting(Request $request)
    {

        broadcast(new PrivateWalkieMessageSent(encryptHelper(auth()->user()->id), $request->channel_id, [
            'state' => $request->recording,
            'sender_id' => auth()->user()->id,
            'sender_iden' => encryptHelper(auth()->user()->id),
            'receiver_id' => $request->current_chat_user,
            'receiver_iden' => encryptHelper($request->current_chat_user),
            'message' => '',
            'name' => '',
            'data' => ''
        ]))->toOthers();

        return true;
    }

    public function channelbroadcastlist($id)
    {
        $record = WailkieTalkieRecorder::where('channel_id', decryptHelper($id))->get();

        $data = [];
        foreach($record as $rec){
            $data[] = [
                'recording_id' => encryptHelper($rec->id),
                'channel_id' => encryptHelper($rec->channel_id),
                'channel_name' => $rec->channel->name,
                'user_id' => encryptHelper($rec->user_id),
                'user_name' => $rec->user->name,
                'source_language' => $rec->source_language,
                'record' => $rec->path ? decrypt($rec->path) : null,
                'record_text' => $rec->record_text ? decrypt($rec->record_text) : null,
                'created_at' => $rec->created_at,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Recording',
                'data' => $data
            ],
            201
        );
    }

    public function channelbroadcastdel($id)
    {
        $wal = WailkieTalkieRecorder::find(decryptHelper($id));

        try {
            unlink(public_path($wal->record));
        } catch (\Exception $e) {
            // Optionally log the error
            // Log::error("Failed to delete old feature image: " . $e->getMessage());
        }

        $wal->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Recording deleted',
                'data' => null
            ],
            201
        );
    }
}
