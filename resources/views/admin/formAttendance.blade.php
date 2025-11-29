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
                {{--<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                    <i class="ki-outline ki-plus fs-2"></i>Add Form</button>--}}
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Modal - Add task-->
            {{--<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header" id="kt_modal_add_user_header">
                            <!--begin::Modal title-->
                            <h2 class="fw-bold">Add Form</h2>
                            <!--end::Modal title-->
                            <!--begin::Close-->
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                            <!--end::Close-->
                        </div>
                        <!--end::Modal header-->
                        <!--begin::Modal body-->
                        <div class="modal-body px-5 my-7">
                            <!--begin::Form-->
                            <form id="kt_modal_add_user_form" class="form" action="{{route('admin.form.create')}}" method="post">
            @csrf
            <!--begin::Scroll-->
            <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                <!--begin::Input group-->
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Name</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Enter group name" />
                    <!--end::Input-->
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Meeting</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="meeting_id" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Enter meeting_id" />
                    <!--end::Input-->
                </div>
                <!--end::Input group-->
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Group</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <select name="group_id" class="form-control form-control-solid mb-3 mb-lg-0">
                        <option value="">Select</option>
                        @foreach($groups as $grp)
                        <option value="{{encrypt($grp->id)}}">{{$grp->name}}</option>
                        @endforeach
                    </select>
                    <!--end::Input-->
                </div>
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Signup</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <select name="signup" class="form-control form-control-solid mb-3 mb-lg-0">
                        <option value="">Select</option>
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>
                    </select>
                    <!--end::Input-->
                </div>
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Status</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <select name="status" class="form-control form-control-solid mb-3 mb-lg-0">
                        <option value="">Select</option>
                        <option value="active">Active</option>
                        <option value="disable">Disable</option>
                    </select>
                    <!--end::Input-->
                </div>
                <!--begin::Input group-->
                <div class="fv-row mb-7">
                    <!--begin::Label-->
                    <label class="required fw-semibold fs-6 mb-2">Message</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <textarea id="editor" name="message"></textarea>
                    <!--end::Input-->
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Scroll-->
            <!--begin::Actions-->
            <div class="text-center pt-10">
                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
            <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Modal body-->
    </div>
    <!--end::Modal content-->
</div>
<!--end::Modal dialog-->
</div>--}}
<!--end::Modal - Add task-->
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
                <th class="min-w-125px">S/N</th>
                <th class="min-w-125px">User</th>
                <th class="min-w-125px">comment</th>
                <th class="min-w-125px">Time</th>
                <th class="text-end min-w-100px">Date</th>
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
                <td>{{$loop->iteration}}</td>
                <td>
                    {{$dt->user->name}}<br>
                    {{$dt->user->email}}<br>
                    {{$dt->user->phone}}<br>
                </td>
                <td>{{$dt->comment}}</td>
                <td>{{$dt->updated_at->format('H:i')}}</td>
                <td>{{$dt->updated_at->format('Y-m-d')}}</td>
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
<script src="https://cdn.tiny.cloud/1/4vvbz04oo7ekxbf7qy9wql3flmqdogtzmfhbr841milj2qbb/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'preview', 'anchor', 'searchreplace', 'visualblocks',
            'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>

@foreach($data as $dt)
<!--begin::Modal - Add task-->
<div class="modal fade" id="kt_modal_add_user-{{$dt->id}}" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{$dt->name}}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <div>
                    Name: {{$dt->name}}<br>
                    Email: {{$dt->email}}<br>
                    Phone: {{$dt->phone}}<br>
                    <pre>{{ json_encode(json_decode($dt->data, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Add task-->
<script>
    tinymce.init({
        selector: '#editor-<?= $dt->id ?>',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'preview', 'anchor', 'searchreplace', 'visualblocks',
            'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>
@endforeach
@endsection