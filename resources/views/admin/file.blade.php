@extends('layouts.admin')

@section('content')

<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">
        <!--begin::Card-->
        <div class="card card-flush pb-0 bgi-position-y-center bgi-no-repeat mb-10" style="background-size: auto calc(100% + 10rem); background-position-x: 100%; background-image: url('assets/media/illustrations/dozzy-1/4.png')">
            <!--begin::Card header-->
            <div class="card-header pt-10">
                <div class="d-flex align-items-center">
                    <!--begin::Icon-->
                    <div class="symbol symbol-circle me-5">
                        <div class="symbol-label bg-transparent text-primary border border-secondary border-dashed">
                            <i class="ki-duotone ki-abstract-47 fs-2x text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <!--end::Icon-->
                    <!--begin::Title-->
                    <div class="d-flex flex-column">
                        <h2 class="mb-1">File Manager</h2>
                        <div class="text-muted fw-bold">
                            <a href="#">File Manager</a>
                            <span class="mx-3">|</span>2.6 GB
                            <span class="mx-3">|</span>758 items
                        </div>
                    </div>
                    <!--end::Title-->
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pb-0">
                <!--begin::Navs-->
                <div class="d-flex overflow-auto h-55px">
                    <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-semibold flex-nowrap">
                        <!--begin::Nav item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary me-6 {{ $option == 'admin' ? 'active' : ''}}" href="{{url('/admin/file')}}">My File</a>
                        </li>
                        <!--end::Nav item-->
                        <!--begin::Nav item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary me-6 {{ $option == 'user' ? 'active' : ''}}" href="{{url('/admin/file/user')}}">Users File</a>
                        </li>
                        <!--end::Nav item-->
                        <!--begin::Nav item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary me-6 {{ $option == 'request' ? 'active' : ''}}" href="{{url('/admin/file/request')}}">User's Request</a>
                        </li>
                        <!--end::Nav item-->
                    </ul>
                </div>
                <!--begin::Navs-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
        <!--begin::Card-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header pt-8">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" data-kt-filemanager-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search Files & Folders" />
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-filemanager-table-toolbar="base">
                        <!--begin::Back to folders
                        <a href="apps/file-manager/folders.html" class="btn btn-icon btn-light-primary me-3">
                            <i class="ki-duotone ki-exit-up fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>-->
                        <!--end::Back to folders-->
                        <!--begin::Export
                        <button type="button" class="btn btn-flex btn-light-primary me-3" id="kt_file_manager_new_folder">
                            <i class="ki-duotone ki-add-folder fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>New Folder</button>-->
                        <!--end::Export-->
                        <!--begin::Add customer-->
                        <button type="button" class="btn btn-flex btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                            <i class="ki-duotone ki-folder-up fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>Upload Files</button>
                        <!--end::Add customer-->
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body">
                @if($option == "request")
                <!--begin::Table Request-->
                <table id="kt_file_manager_list" data-kt-filemanager-table="files" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2"></th>
                            <th>File Name</th>
                            <th>File Size</th>
                            <th>Request By</th>
                            <th>Recieving User</th>
                            <th>Requested Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($file as $dt)
                        <tr>
                            <td>
                                {{$loop->iteration}}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-picture fs-2x text-primary me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <a href="#" class="text-gray-800 text-hover-primary">{{$dt->file->name}}</a>
                                </div>
                            </td>
                            <td>{{$dt->file->file_size}}</td>
                            <td>{{$dt->userFrom->name}}</td>
                            <td>{{$dt->user->name}}</td>
                            <td>{{$dt->created_at}}</td>
                            <td>
                                <a href="{{route('admin.file.accept', ['id' => encrypt($dt->id)])}}" class="btn btn-primary">Accept</a>
                                <a href="{{route('admin.file.decline', ['id' => encrypt($dt->id)])}}" class="btn btn-primary">Reject</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <!--end::Table-->
                @else
                <!--begin::Table Files-->
                <table id="kt_file_manager_list" data-kt-filemanager-table="files" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_file_manager_list .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-250px">Name</th>
                            <th class="min-w-10px">Size</th>
                            @if($option == 'user')
                            <th class="min-w-10px">Uploaded by</th>
                            @endif
                            <th class="min-w-125px">Uploaded Date</th>
                            <th class="w-125px">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($file as $dt)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-picture fs-2x text-primary me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <a href="#" class="text-gray-800 text-hover-primary">{{$dt->name}}</a>
                                </div>
                            </td>
                            <td>{{$dt->file_size}}</td>
                            @if($option == 'user')
                            <th class="min-w-10px">{{$dt->user->name}}</th>
                            @endif
                            <td>{{$dt->created_at}}</td>
                            <td class="text-end" data-kt-filemanager-table="action_dropdown">
                                <div class="d-flex justify-content-end">
                                    <!--begin::Share link-->
                                    <div class="ms-2" data-kt-filemanger-table="copy_link">
                                        <a href="{{route('admin.file.download', ['id' => encrypt($dt->id)])}}" class="btn btn-sm btn-icon btn-light">
                                            <i class="ki-duotone ki-fasten fs-5 m-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a>
                                    </div>
                                    <!--end::Share link-->
                                    <!--begin::More-->
                                    <div class="ms-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            <i class="ki-duotone ki-dots-square fs-5 m-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </button>
                                        <!--begin::Menu-->
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                            <!--begin::Menu item-->
                                            {{-- <div class="menu-item px-3">
                                                <a href="{{route('admin.file.share.group', ['id' => encrypt($dt->id)])}}" class="menu-link px-3">Share to groups</a>
                                        </div> --}}
                                        <!--end::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="{{route('admin.file.share.user', ['id' => encrypt($dt->id)])}}" class="menu-link px-3">Share to users</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        {{-- <div class="menu-item px-3">
                                                <a href="{{route('admin.file.access.group', ['id' => encrypt($dt->id)])}}" class="menu-link px-3" data-kt-filemanager-table="rename">Group Access</a>
                                    </div> --}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('admin.file.access.user', ['id' => encrypt($dt->id)])}}" class="menu-link px-3" data-kt-filemanager-table="rename">User Access</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="{{route('admin.file.access.log', ['id' => encrypt($dt->id)])}}" class="menu-link px-3" data-kt-filemanager-table="rename">Access Log</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link text-danger px-3" data-kt-filemanager-table-filter="delete_row">Delete</a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
            </div>
            <!--end::More-->
        </div>
        </td>
        </tr>
        @endforeach
        </tbody>
        </table>
        <!--end::Table-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
<!--begin::Modal - Add task-->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">Upload File</h2>
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
                <form id="kt_modal_add_user_form" class="form" action="{{route('admin.file.upload')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">File Label</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" required placeholder="File Label" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--end::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">File</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="file" name="file" class="form-control form-control-solid mb-3 mb-lg-0" accept=".pdf" required placeholder="Upload file" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">Description</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="description" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Type in description" />
                            <!--end::Input-->
                        </div>
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary">
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
</div>
<!--end::Modal - Add task-->
</div>
<!--end::Container-->
</div>
<!--end::Content-->
<script src="{{ asset('demo3/js/custom/apps/file-manager/list.js')}}"></script>
<!--end::Custom Javascript-->
<!--end::Javascript-->
@endsection