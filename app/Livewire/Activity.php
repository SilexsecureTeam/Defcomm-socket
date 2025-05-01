<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;

class Activity extends Component
{
    public $notification;

    public function render()
    {
        $this->notification = Notification::where('status', 'active')->where('company_id', auth()->user()->company_id)->get();
        return view('livewire.activity');
    }
}
