@extends('layouts.super')

@section('content')
<div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">{{$type}}
        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="{{route('super.bountyUser')}}" class="menu-link px-3">User</a>
            <a href="{{route('super.bountyUser', ['type'=>'group'])}}" class="menu-link px-3">Group</a>
            <a href="{{route('super.bountyUser', ['type'=>'company'])}}" class="menu-link px-3">Company</a>
        </div>
    </div>
</div>

<!-- Nav tabs -->
<ul class="nav nav-tabs mb-5" id="statusTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending</button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#Active" type="button">Active</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#disable" type="button">Disabled</button>
    </li>
</ul>


<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane fade" id="pending">
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
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="users" id="output1">
                                <button type="submit" class="btn btn-primary ml-3">
                                    Activate Users</button>
                            </form>
                        </div>
                    </div>
                </div>
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
                            <th class="min-w-125px">User</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Phone</th>
                            <th class="min-w-125px">Group</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($userPen as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input checkbox1" type="checkbox" value="{{$dt->id}}" />
                                </div>
                            </td>
                            <td>
                                @if($dt->group->group_company == "" || $dt->group->group_company == null)
                                <span class="badge badge-light-success">Individual</span>
                                @else
                                <span class="badge badge-light-primary">{{$dt->group->group_company}}</span>
                                @endif<br/>
                                {{$dt->firstName}} {{$dt->lastName}}
                            </td>
                            <td>{{$dt->email}}</td>
                            <td>{{$dt->phone}}</td>
                            <td>{{$dt->rel_group ? $dt->group->username : "System"}}</td>
                            <td>
                                @if($dt->emailVerify == "true")
                                <button type="button" class="btn btn-primary">
                                    Verified</button>
                                @else
                                <button type="button" class="btn btn-danger">
                                    Unverified</button>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('super.bountyUserId', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View App</a>
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
    <div class="tab-pane fade show active" id="Active">
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
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.block')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="users" id="output2">
                                <button type="submit" class="btn btn-primary ml-3">
                                    Block Users</button>
                            </form>
                        </div>
                    </div>
                </div>
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
                            <th class="min-w-125px">User</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Phone</th>
                            <th class="min-w-125px">Group</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($userApp as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input checkbox2" type="checkbox" value="{{$dt->id}}" />
                                </div>
                            </td>
                            <td>
                                @if($dt->group->group_company == "" || $dt->group->group_company == null)
                                <span class="badge badge-light-success">Individual</span>
                                @else
                                <span class="badge badge-light-primary">{{$dt->group->group_company}}</span>
                                @endif<br/>
                                {{$dt->firstName}} {{$dt->lastName}}
                            </td>
                            <td>{{$dt->email}}</td>
                            <td>{{$dt->phone}}</td>
                            <td>{{$dt->rel_group ? $dt->group->username : "System"}}</td>
                            <td>
                                @if($dt->emailVerify == "true")
                                <button type="button" class="btn btn-primary">
                                    Verified</button>
                                @else
                                <button type="button" class="btn btn-danger">
                                    Unverified</button>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('super.bountyUserId', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View App</a>
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
    <div class="tab-pane fade" id="disable">
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
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <div class="d-flex justify-content-between">
                            <form action="{{ route('super.bountyUser.active')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="users" id="output3">
                                <button type="submit" class="btn btn-primary ml-3">
                                    Activate Users</button>
                            </form>
                        </div>
                    </div>
                </div>
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
                            <th class="min-w-125px">User</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Phone</th>
                            <th class="min-w-125px">Group</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($userBlk as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input checkbox3" type="checkbox" value="{{$dt->id}}" />
                                </div>
                            </td>
                            <td>
                                @if($dt->group->group_company == "" || $dt->group->group_company == null)
                                <span class="badge badge-light-success">Individual</span>
                                @else
                                <span class="badge badge-light-primary">{{$dt->group->group_company}}</span>
                                @endif<br/>
                                {{$dt->firstName}} {{$dt->lastName}}
                            </td>
                            <td>{{$dt->email}}</td>
                            <td>{{$dt->phone}}</td>
                            <td>{{$dt->rel_group ? $dt->group->username : "System"}}</td>
                            <td>
                                @if($dt->emailVerify == "true")
                                <button type="button" class="btn btn-primary">
                                    Verified</button>
                                @else
                                <button type="button" class="btn btn-danger">
                                    Unverified</button>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('super.bountyUserId', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View App</a>
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
    {{--<div class="tab-pane fade" id="active">
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
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_users .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-125px">User</th>
                            <th class="min-w-125px">Email</th>
                            <th class="min-w-125px">Phone</th>
                            <th class="min-w-125px">Group</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($data as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </div>
                            </td>
                            <td>{{$dt->firstName}} {{$dt->lastName}}</td>
    <td>{{$dt->email}}</td>
    <td>{{$dt->phone}}</td>
    <td>{{$dt->rel_group ? $dt->group->username : "System"}}</td>
    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
        <i class="ki-outline ki-down fs-5 ms-1"></i></a>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="{{route('super.bountyUserId', ['id'=>encrypt($dt->id)])}}" class="menu-link px-3">View App</a>
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
</div>--}}
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll1 = document.getElementById('checkAll1');
        const checkboxes1 = document.querySelectorAll('.checkbox1');

        function updateOutput1() {
            let checkedValues1 = [];

            checkboxes1.forEach(function(checkbox1) {
                if (checkbox1.checked) {
                    checkedValues1.push(parseInt(checkbox1.value));
                }
            });

            document.getElementById('output1').value = JSON.stringify(checkedValues1);
        }

        checkboxes1.forEach(function(checkbox1) {
            checkbox1.addEventListener('change', function() {
                updateOutput1();
                if (!checkbox1.checked) {
                    checkAll1.checked = false;
                }
            });
        });

        checkAll1.addEventListener('change', function() {
            const isChecked1 = checkAll1.checked;
            checkboxes1.forEach(function(checkbox1) {
                checkbox1.checked = isChecked1;
            });
            updateOutput1();
        });

        updateOutput1();
    });
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll2 = document.getElementById('checkAll2');
        const checkboxes2 = document.querySelectorAll('.checkbox2');

        function updateOutput2() {
            let checkedValues2 = [];

            checkboxes2.forEach(function(checkbox2) {
                if (checkbox2.checked) {
                    checkedValues2.push(parseInt(checkbox2.value));
                }
            });

            document.getElementById('output2').value = JSON.stringify(checkedValues2);
        }

        checkboxes2.forEach(function(checkbox2) {
            checkbox2.addEventListener('change', function() {
                updateOutput2();
                if (!checkbox2.checked) {
                    checkAll2.checked = false;
                }
            });
        });

        checkAll2.addEventListener('change', function() {
            const isChecked2 = checkAll2.checked;
            checkboxes2.forEach(function(checkbox2) {
                checkbox2.checked = isChecked2;
            });
            updateOutput2();
        });

        updateOutput2();
    });
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll3 = document.getElementById('checkAll3');
        const checkboxes3 = document.querySelectorAll('.checkbox3');

        function updateOutput3() {
            let checkedValues3 = [];

            checkboxes3.forEach(function(checkbox3) {
                if (checkbox3.checked) {
                    checkedValues3.push(parseInt(checkbox3.value));
                }
            });

            document.getElementById('output3').value = JSON.stringify(checkedValues3);
        }

        checkboxes3.forEach(function(checkbox3) {
            checkbox3.addEventListener('change', function() {
                updateOutput3();
                if (!checkbox3.checked) {
                    checkAll3.checked = false;
                }
            });
        });

        checkAll3.addEventListener('change', function() {
            const isChecked3 = checkAll3.checked;
            checkboxes3.forEach(function(checkbox3) {
                checkbox3.checked = isChecked3;
            });
            updateOutput3();
        });

        updateOutput3();
    });
</script>

@endsection