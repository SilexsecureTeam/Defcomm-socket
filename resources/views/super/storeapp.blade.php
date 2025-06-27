@extends('layouts.super')

@section('content')
<!-- Nav tabs -->
<ul class="nav nav-tabs mb-5" id="statusTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approved" type="button">Approved</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reject" type="button">Rejected</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#disable" type="button">Disabled</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#active" type="button">Active</button>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane fade show active" id="pending">
        @include('super.storeappTable', ['data' => $data->where('status', 'pending')])
    </div>
    <div class="tab-pane fade" id="approved">
        @include('super.storeappTable', ['data' => $data->where('status', 'approved')])
    </div>
    <div class="tab-pane fade" id="reject">
        @include('super.storeappTable', ['data' => $data->where('status', 'reject')])
    </div>
    <div class="tab-pane fade" id="disable">
        @include('super.storeappTable', ['data' => $data->where('status', 'disable')])
    </div>
    <div class="tab-pane fade" id="active">
        @include('super.storeappTable', ['data' => $data->where('status', 'active')])
    </div>
</div>
@endsection