<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WailkieTalkieChannel;
use Illuminate\Support\Facades\Mail;
use App\Models\WailkieTalkieRecorder;
use App\Models\WailkieTalkieSubscriber;
use App\Mail\WailkieTalkieChannelInvitation;

class WalkieTalkieController extends Controller
{
    public function channelcreate(Request $request)
    {
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
                        'id' => encrypt($data->id),
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
        $data = WailkieTalkieChannel::find(decrypt($request->id));

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
                    'id' => encrypt($data->id),
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
        $data = WailkieTalkieChannel::find(decrypt($id))->delete();
    
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
        $res = WailkieTalkieChannel::where('user_id', auth()->user()->id)->get();
        $data = [];
        foreach($res as $rs){
            $data[] = [
                'id' => encrypt($rs->id),
                'name' => $rs->name,
                'frequency' => $rs->frequency,
                'description' => $rs->description
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
        $channel = WailkieTalkieChannel::find(decrypt($request->channel_id));
        $json = str_replace("'", '"', $request->users);
        $array = json_decode($json, true);
        foreach ($array as $value) {
            WailkieTalkieSubscriber::updateOrCreate([
                'channel_id' => $channel->id,
                'user_id' => decrypt($value),
            ], [
                'status' => 'pending'
            ]);

            $usr = User::find(decrypt($value));
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
                'sub_id' => encrypt($ch->id),
                'channel_id' => encrypt($ch->channel->id),
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
        WailkieTalkieSubscriber::find(decrypt($request->sub_id))->update(['status' => $request->status]);
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
        $chan = WailkieTalkieSubscriber::where('user_id', auth()->user()->id)->where('channel_id', decrypt($request->channel))->first();

        if($request->hasFile('record')) {
            $filerecord = $request->file('record');
            $filerecord_name = time() . '_record.' . $filerecord->getClientOriginalExtension();
            $filerecord->move(public_path('WailkieTalkie'), $filerecord_name);

            WailkieTalkieRecorder::create([
                'channel_id' => $chan->channel->id,
                'subscriber_id' => $chan->id,
                'user_id' => auth()->user()->id,
                'record' => "WailkieTalkie/". $filerecord_name
            ]);

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Record create',
                    'data' => null
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

    public function channelbroadcastlist($id)
    {
        $record = WailkieTalkieRecorder::where('channel_id', decrypt($id))->get();

        $data = [];
        foreach($record as $rec){
            $data[] = [
                'recording_id' => encrypt($rec->id),
                'channel_id' => encrypt($rec->channel_id),
                'channel_name' => $rec->channel->name,
                'user_id' => encrypt($rec->user_id),
                'user_name' => $rec->user->name,
                'record' => $rec->record,
                'record_text' => $rec->record_text,
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
        $wal = WailkieTalkieRecorder::find(decrypt($id));

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
