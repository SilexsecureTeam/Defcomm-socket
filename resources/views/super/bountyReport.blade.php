@extends('layouts.super')

@section('content')
<div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
    <div>
        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Severity: {{$severity}}
            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
        <!--begin::Menu-->
        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
            <!--begin::Menu item-->
            <div class="menu-item px-3">
                <a href="{{route('super.bountyReport')}}" class="menu-link px-3">All</a>
                <a href="{{route('super.bountyReport', ['severity'=>'low', 'category'=>$category, 'sub'=>$sub])}}" class="menu-link px-3">Low</a>
                <a href="{{route('super.bountyReport', ['severity'=>'medium', 'category'=>$category, 'sub'=>$sub])}}" class="menu-link px-3">Medium</a>
                <a href="{{route('super.bountyReport', ['severity'=>'high', 'category'=>$category, 'sub'=>$sub])}}" class="menu-link px-3">High</a>
                <a href="{{route('super.bountyReport', ['severity'=>'critical', 'category'=>$category, 'sub'=>$sub])}}" class="menu-link px-3">Critical</a>
            </div>
        </div>
    </div>
    <div>
        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Category: {{$catLab}}
            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
        <!--begin::Menu-->
        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
            <!--begin::Menu item-->
            <div class="menu-item px-3">
                <a href="{{route('super.bountyReport')}}" class="menu-link px-3">All</a>
                @foreach($cat as $ct)
                <a href="{{route('super.bountyReport', ['severity'=>$severity, 'category'=>$ct->id, 'sub'=>$sub])}}" class="menu-link px-3">{{$ct->label}}</a>
                @endforeach
            </div>
        </div>
    </div>
    @if($category && $catsub)
    <div>
        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Sub Category: {{$catsubLab}}
            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
        <!--begin::Menu-->
        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
            <!--begin::Menu item-->
            <div class="menu-item px-3">
                <a href="{{route('super.bountyReport')}}" class="menu-link px-3">All</a>
                @foreach($catsub as $cst)
                <a href="{{route('super.bountyReport', ['severity'=>$severity, 'category'=>$category, 'sub'=>$cst->id])}}" class="menu-link px-3">{{$cst->label}}</a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Nav tabs -->
<ul class="nav nav-tabs mb-5" id="statusTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#review" type="button">Review</button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#new" type="button">New</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#accept" type="button">Accept</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reject" type="button">Reject</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fix" type="button">Fix</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#close" type="button">Close</button>
    </li>
</ul>


<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane fade" id="review">
        <!--begin::Card-->
        <div class="card table-wrapper">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->
                {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="reports" id="output1">
                <button type="submit" class="btn btn-primary ml-3">
                    Activate Users</button>
                </form>
            </div>
        </div>
    </div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll1" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataReview as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox1" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br /> 
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
<div class="tab-pane fade show active" id="new">
    <!--begin::Card-->
    <div class="card table-wrapper">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reports" id="output2">
            <button type="submit" class="btn btn-primary ml-3">
                Activate Users</button>
            </form>
        </div>
    </div>
</div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll2" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataNew as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox2" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br />
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
<div class="tab-pane fade" id="accept">
    <!--begin::Card-->
    <div class="card table-wrapper">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reports" id="output3">
            <button type="submit" class="btn btn-primary ml-3">
                Activate Users</button>
            </form>
        </div>
    </div>
</div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll3" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataAccept as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox3" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br />
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
<div class="tab-pane fade" id="reject">
    <!--begin::Card-->
    <div class="card table-wrapper">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reports" id="output4">
            <button type="submit" class="btn btn-primary ml-3">
                Activate Users</button>
            </form>
        </div>
    </div>
</div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll4" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataReject as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox4" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br />
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
<div class="tab-pane fade" id="fix">
    <!--begin::Card-->
    <div class="card table-wrapper">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reports" id="output5">
            <button type="submit" class="btn btn-primary ml-3">
                Activate Users</button>
            </form>
        </div>
    </div>
</div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll5" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataFix as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox5" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br />
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
<div class="tab-pane fade" id="close">
    <!--begin::Card-->
    <div class="card table-wrapper">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search user" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            {{--<div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reports" id="output6">
            <button type="submit" class="btn btn-primary ml-3">
                Activate Users</button>
            </form>
        </div>
    </div>
</div>--}}
</div>
<!--end::Card header-->
<!--begin::Card body-->
<div class="card-body py-4">
    <!--begin::Table-->
    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="w-10px pe-2">
                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                        <input class="form-check-input" type="checkbox" id="checkAll6" />
                    </div>
                </th>
                <th class="min-w-125px">Ref</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">Subject</th>
                <th class="min-w-125px">Program</th>
                <th class="min-w-125px">Category</th>
                <th class="min-w-125px">Severity</th>
                <th class="min-w-125px">Award</th>
                <th class="text-end min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @foreach($dataClose as $dt)
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input checkbox6" type="checkbox" value="{{$dt->id}}" />
                    </div>
                </td>
                <td>{{$dt->ref}}</td>
                <td>
                    @if($dt->user->group->group_company == "" || $dt->user->group->group_company == null)
                    <span class="badge badge-light-success">Individual</span>
                    @else
                    <span class="badge badge-light-primary">{{$dt->user->group->group_company}}</span>
                    @endif<br/>
                    {{$dt->user->firstName}} {{$dt->user->lastName}} <br />
                    {{$dt->email}} <br />
                    {{$dt->phone}}
                </td>
                <td>{{$dt->title}}</td>
                <td>{{$dt->program->title}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td>{{$dt->severity}}</td>
                <td>
                    {{$dt->categori->label}}<br />
                    {{$dt->categorySub->label}}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="{{route('super.bountyReportView', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View</a>
                        </div>
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
</div>
</div>

@endsection