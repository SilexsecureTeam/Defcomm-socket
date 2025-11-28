@extends('layouts.user')

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
                <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search" />
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            @if($option == "contact")
            <a href="{{ url('/user/group')}}" class="btn btn-primary"><i class="ki-outline ki-plus fs-2"></i>Add Contact</a>
            @endif
            @if($option == "fileShare")
            <form action="{{ route('user.file.share.user.add')}}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$id}}">
                <input type="hidden" name="users" id="output">
                <label>Enter date to expire, or leave blank</label><br>
                <input type="date" name="expire_date" class="btn form-control-solid" placeholder="Enter Expiration Date">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>Share</button>
            </form>
            @endif
            @if($option == "fileShareRequest")
            <form action="{{ route('user.file.share.user.add.request')}}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$id}}">
                <input type="hidden" name="users" id="output">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>Share</button>
            </form>
            @endif
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
                    @if($option == "fileShare" || $option == "fileShareRequest")
                    <th>
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" id="checkAll" />
                        </div>
                    </th>
                    @endif
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    @if($option == "contact")
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($contact as $dt)
                <tr>
                    @if($option == "fileShare" || $option == "fileShareRequest")
                    <td>
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input checkbox" type="checkbox" value="{{$dt->user_link}}" />
                        </div>
                    </td>
                    @endif
                    <td>{{$loop->iteration}}</td>
                    <td>{{$dt->userLink->name}}</td>
                    <td>{{$dt->userLink->email}}</td>
                    <td>{{$dt->userLink->phone}}</td>
                    @if($option == "contact")
                    <td>
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="{{route('user.contact.remove', ['id' => encrypt($dt->id)])}}" class="menu-link px-3 text-danger" data-kt-users-table-filter="delete_row">Remove</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->
                    </td>
                    @endif
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