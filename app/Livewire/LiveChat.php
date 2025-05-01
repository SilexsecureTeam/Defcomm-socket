<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\ChatLastLog;
use App\Models\ChatMessage;
use App\Models\ContactList;
use App\Models\ChatSettings;
use App\Models\CompanyGroup;
use App\Models\CompanyGroupUser;
use App\Http\Services\ChatService;
use App\Http\Services\FileUploadService;

class LiveChat extends Component
{
    public $users, $search, $group, $group_search, $current_chat_user, $current_chat_user_type, $userLastLog, $chat_message, $chat_history, $chat_history_search, $message, $toggleMessage=[];


    protected $queryString = ['search'];

    public function render()
    {
        // Contact List
        if(auth()->user()->role == 'admin'){
            $user_query = User::where('company_id', auth()->user()->CompanyUser->id)->where('status', 'active')->where('role', 'user')->orderBy('name', 'ASC');
            $this->users = $user_query->get();
            if ($this->search) {
                $this->users = $user_query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })->get();
            }
        } else {
            $user_query = ContactList::where('user_id', auth()->user()->id)->join('users', 'users.id', '=', 'contact_lists.user_link')->orderBy('users.name', 'ASC');
            $this->users = $user_query->get();
            if ($this->search) {
                $this->users = $user_query->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })->get();
            }
        }

        // Group List
        if(auth()->user()->role == 'admin'){
            $group_query = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->orderBy('name', 'ASC');
            $this->group = $group_query->get();
            if ($this->group_search) {
                $this->group = $group_query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->group_search . '%');
                })->get();
            }
        } else {
            $group_query = CompanyGroupUser::where('user_id', auth()->user()->id);
            // $group_query = CompanyGroupUser::where('user_id', auth()->user()->id)->join('companyGroup', 'companyGroup.id', '=', 'company_group_users.group_id')->where('status', 'joined')->orderBy('companyGroup.name', 'ASC')->get();
            $this->group = $group_query->get();
            if ($this->group_search) {
                $this->group = $user_query->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->group_search . '%');
                    })->get();
            }
        }

        $chat_history_query = ChatLastLog::where('user_id', auth()->user()->id)->leftjoin('users', 'users.id', '=', 'chat_last_logs.user_to')->leftjoin('company_groups', 'company_groups.id', '=', 'chat_last_logs.group_to')->orderBy('users.name', 'ASC');
        $this->chat_history = $chat_history_query->get();
        if ($this->chat_history_search) {
            $this->chat_history = $chat_history_query->where(function ($query) {
                $query->where('name', 'like', '%' . $this->chat_history_search . '%')
                    ->orWhere('phone', 'like', '%' . $this->chat_history_search . '%')
                    ->orWhere('email', 'like', '%' . $this->chat_history_search . '%');
            })->get();
        }

        return view('livewire.live-chat');
    }

    public function chat($user_id, $user_group)
    {
        $this->chat_message = "";
        $this->current_chat_user_type = $user_group;
        $thisuserLastLog = ChatLastLog::where('user_id', auth()->user()->id);
        $thisuserLastLogUser = $user_group == 'user' ? $thisuserLastLog->where('user_to', $user_id)->first() : $thisuserLastLog->where('group_to', $user_id)->first();
        $this->userLastLog = $thisuserLastLogUser ? $thisuserLastLogUser->group_to : null;
        $this->current_chat_user = $user_group == 'user' ? User::find($user_id) : CompanyGroup::find($user_id);
        $this->chat_message = ChatMessage::where('group_to', $this->userLastLog)->where(function ($query) {
            $query->where('user_id', $this->current_chat_user->id)
                ->orWhere('user_to', $this->current_chat_user->id)
                ->orWhere('group_to', $this->current_chat_user->id);
        })->orderBy('created_at', 'ASC')->get();
    }

    public function send()
    {
        if($this->message){

            (new ChatService())->submitChat(
                $this->current_chat_user_type,
                $this->current_chat_user->id,
                $this->userLastLog,
                $this->message,
                'no'
            );

            $this->message = "";
        }
    }

    public function updateChat()
    {
        $thisuserLastLog = $this->current_chat_user_type == 'user' ? ChatLastLog::where('user_id', auth()->user()->id)->where('user_to', $this->current_chat_user->id)->first() : ChatLastLog::where('group_to', $this->current_chat_user->id)->first();

        $this->userLastLog = $thisuserLastLog ? $thisuserLastLog->group_to : null;
        
        $this->chat_message = ChatMessage::where('group_to', $this->userLastLog)->where(function ($query) {
            $query->where('user_id', $this->current_chat_user->id)
                ->orWhere('group_to', $this->current_chat_user->id)
                ->orWhere('user_to', $this->current_chat_user->id);
        })->orderBy('created_at', 'ASC')->get();
    }

    public function historyChat() 
    {
        $chat_history_query = ChatLastLog::where('user_id', auth()->user()->id)->leftjoin('users', 'users.id', '=', 'chat_last_logs.user_to')->leftjoin('company_groups', 'company_groups.id', '=', 'chat_last_logs.group_to')->orderBy('users.name', 'ASC');
        $this->chat_history = $chat_history_query->get();
    }

    public function toggleMessageHide($id)
    {
        if(in_array($id, $this->toggleMessage)){
            unset($this->toggleMessage[$id]);
        }else{
            $this->toggleMessage[$id] = $id;
            // return dd($this->toggleMessage);
        }
    }

    public function markMessageImportant($id, $status)
    {
        ChatMessage::find($id)->update(['is_important' => $status]);
    }
}
