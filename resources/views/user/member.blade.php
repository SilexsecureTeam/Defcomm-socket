@extends('layouts.user')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <!--begin::Add user-->
                <h2>Group Member</h2>
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search" />
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
    </div>
    <a href="{{route('user.group.member.state', ['id' => encrypt($user->id), 'status' => $user->hide])}}" class="row p-6">
        <!--begin::Label-->
        <label class="col-lg-2 col-form-label fw-semibold fs-6">{{ $user->hide =="yes" ? 'Unhide my detail' : 'Hide my detail'}}</label>
        <!--begin::Label-->
        <!--begin::Label-->
        <div class="col-lg-8 d-flex align-items-center">
            <div class="form-check form-check-solid form-switch form-check-custom fv-row">
                <input class="form-check-input w-45px h-30px" type="checkbox" value="1" id="allowmarketing" {{ $user->hide =="yes" ? 'checked="checked"' : ''}} onClick="window.location.assign('{{route('user.group.member.state', ['id' => encrypt($user->id), 'status' => $user->hide])}}')" />
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
        <!--begin::Label-->
    </a>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($member as $dt)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$dt->user->name}}</td>
                    <td>{{$dt->hide == 'yes' ? "********": $dt->user->email}}</td>
                    <td>{{$dt->hide == 'yes' ? "********": $dt->user->phone}}</td>
                    <td class="text-end">
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <!--begin::Menu item-->
                            @if($dt->hide == 'no')
                            <div class="menu-item px-3">
                                <a href="{{route('user.contact.add', ['id' => encrypt($dt->user_id)])}}" class="menu-link px-3" data-kt-users-table-filter="delete_row">Add</a>
                            </div>
                            @endif
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
@endsection