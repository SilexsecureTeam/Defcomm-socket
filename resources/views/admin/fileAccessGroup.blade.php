@extends('layouts.admin')

@section('content')
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
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <!--begin::Add user-->
                @if($option == "fileAccess")
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                    <i class="ki-outline ki-plus fs-2"></i>Remove Access</button>
                @endif
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
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
                            <input class="form-check-input" type="checkbox" id="checkAll" />
                        </div>
                    </th>
                    <th class="min-w-125px">Name</th>
                    <th class="min-w-125px">Last Access</th>
                    <th class="text-end min-w-100px">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($users as $dt)
                <tr>
                    <td>
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input checkbox" type="checkbox" value="{{$dt->id}}" />
                        </div>
                    </td>
                    <td class="d-flex align-items-center">
                        <!--begin:: Avatar -->
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <a href="#">
                                <div class="symbol-label">
                                    <img src="#" alt="{{$dt->user->name}}" class="w-100" />
                                </div>
                            </a>
                        </div>
                        <!--end::Avatar-->
                        <!--begin::User details-->
                        <div class="d-flex flex-column">
                            <a href="#" class=" text-gray-800 text-hover-primary mb-1">{{$dt->companyGroup->name}}</a>
                        </div>
                        <!--begin::User details-->
                    </td>
                    </td>
                    <td>{{$dt->companyGroup->created_at}}</td>
                    <td class="text-end">
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <!--begin::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item">
                                <a href="{{ route('admin.file.access.revoke', ['id' => encrypt($dt->id)])}}" class="menu-link">Revoke Access</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->
                    </td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.checkbox');

        // Function to update the output input field with selected checkbox values
        function updateOutput() {
            // Initialize an array to hold the checked values
            let checkedValues = [];
            // Loop through checkboxes and add checked values to the array
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    checkedValues.push(checkbox.value);
                }
            });
            // Update the output input field with the array as a string
            document.getElementById('output').value = JSON.stringify(checkedValues);
        }

        // Add event listener to all checkboxes
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateOutput();
                // Uncheck "Check All" if not all checkboxes are checked
                if (!checkbox.checked) {
                    checkAll.checked = false;
                }
            });
        });

        // Add event listener to "Check All" checkbox
        checkAll.addEventListener('change', function() {
            const isChecked = checkAll.checked;
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            updateOutput();
        });

        // Initial call to set the output field on page load
        updateOutput();
    });
</script>
@endsection