@extends('layouts.super')

@section('content')
<!-- Nav tabs -->
<ul class="nav nav-tabs mb-5" id="statusTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending" type="button">Device</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approved" type="button">Login Log</button>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane fade show active" id="pending">
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
                            <th class="min-w-125px">IP</th>
                            <th class="min-w-125px">Device</th>
                            <th class="min-w-125px">Location</th>
                            <th class="min-w-125px">Cordinate</th>
                            <th class="text-end min-w-100px">More</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($device as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </div>
                            </td>
                            <td>
                                {{$dt->ip_address}}
                            </td>
                            <td>
                                Device: {{$dt->device}}<br>
                                Browser: {{$dt->browser}}<br>
                                OS: {{$dt->os}}<br>
                                Agent: {{$dt->user_agent}}<br>
                            </td>
                            <td>
                                Country: {{$dt->country}}<br>
                                Region: {{$dt->region}}<br>
                                City: {{$dt->city}}<br>
                            </td>
                            <td>
                                lat: {{$dt->lat}}<br>
                                lon: {{$dt->lon}}<br>
                            </td>
                            <td></td>
                            <td>
                                Status: {{$dt->status}}<br>
                                Last login: {{$dt->last_used_at}}<br>
                                Register: {{$dt->created_at }}<br>
                            </td>
                            {{--<td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('super.store.appt.detail',['id' => encrypt($dt->id)])}}" class="menu-link px-3">View</a>
            </div>
        </div>
        <!--end::Menu-->
        </td>--}}
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
<div class="tab-pane fade" id="approved">
    <div class="tab-pane fade show active" id="pending">
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
                            <th class="min-w-125px">IP</th>
                            <th class="min-w-125px">Device</th>
                            <th class="min-w-125px">Location</th>
                            <th class="min-w-125px">Cordinate</th>
                            <th class="text-end min-w-100px">More</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($log as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </div>
                            </td>
                            <td>
                                {{$dt->ip_address}}
                            </td>
                            <td>
                                Device: {{$dt->device}}<br>
                                Browser: {{$dt->browser}}<br>
                                OS: {{$dt->os}}<br>
                                Agent: {{$dt->user_agent}}<br>
                            </td>
                            <td>
                                Country: {{$dt->country}}<br>
                                Region: {{$dt->region}}<br>
                                City: {{$dt->city}}<br>
                            </td>
                            <td>
                                lat: {{$dt->lat}}<br>
                                lon: {{$dt->lon}}<br>
                            </td>
                            <td></td>
                            <td>
                                Status: {{$dt->status}}<br>
                                Last login: {{$dt->last_used_at}}<br>
                                Register: {{$dt->created_at }}<br>
                            </td>
                            {{--<td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('super.store.appt.detail',['id' => encrypt($dt->id)])}}" class="menu-link px-3">View</a>
            </div>
        </div>
        <!--end::Menu-->
        </td>--}}
        </tr>
        @endforeach
        </tbody>
        </table>
        <!--end::Table-->
    </div>
</div>
</div>
@endsection