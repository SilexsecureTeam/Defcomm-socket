@extends('layouts.admin')

@section('content')
<!--begin::Card-->
<div class="card table-wrapper">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h3 class="card-label">Certificates for {{ $form->name }}</h3>
        </div>
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_certificate">
                    <i class="ki-outline ki-plus fs-2"></i>Add Certificate</button>
            </div>
            
            <!--begin::Modal - Add Certificate-->
            <div class="modal fade" id="kt_modal_add_certificate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Add Certificate</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body px-5 my-7">
                            <form class="form" action="{{route('admin.form.certificate.create')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{$id}}">
                                <div class="d-flex flex-column scroll-y px-5 px-lg-10">
                                    <div class="fv-row mb-7">
                                        <label class="required fw-semibold fs-6 mb-2">Certificate Name</label>
                                        <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="e.g. Participation Certificate" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fw-semibold fs-6 mb-2">Template (Image)</label>
                                        <input type="file" name="template" class="form-control form-control-solid mb-3 mb-lg-0" accept="image/*" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="fw-semibold fs-6 mb-2">Status</label>
                                        <select name="status" class="form-select form-select-solid">
                                            <option value="active">Active</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-center pt-10">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Submit</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_certificates">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Name</th>
                    <th class="min-w-125px">Template</th>
                    <th class="min-w-125px">Status</th>
                    <th class="min-w-125px">Created</th>
                    <th class="text-end min-w-100px">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($data as $dt)
                <tr>
                    <td>{{$dt->name}}</td>
                    <td>
                        <a href="{{ asset('certificates/' . $dt->template) }}" target="_blank" class="symbol symbol-50px">
                            <img src="{{ asset('certificates/' . $dt->template) }}" alt="Template" />
                        </a>
                    </td>
                    <td>
                        <div class="badge badge-light-{{$dt->status == 'active' ? 'success' : 'danger'}}">{{ucfirst($dt->status)}}</div>
                    </td>
                    <td>{{$dt->created_at->format('Y-m-d H:i')}}</td>
                    <td class="text-end">
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                            <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_certificate-{{$dt->id}}">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="{{ route('admin.form.certificate.applicants', encrypt($dt->id)) }}" class="menu-link px-3">Applicants</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="{{ route('admin.form.certificate.delete', encrypt($dt->id)) }}" class="menu-link px-3 text-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@foreach($data as $dt)
<div class="modal fade" id="kt_modal_edit_certificate-{{$dt->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Edit Certificate</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form class="form" action="{{route('admin.form.certificate.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{encrypt($dt->id)}}">
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10">
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Certificate Name</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" value="{{$dt->name}}" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Template (Leave blank to keep current)</label>
                            <input type="file" name="template" class="form-control form-control-solid mb-3 mb-lg-0" accept="image/*" />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Status</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="active" {{$dt->status == 'active' ? 'selected' : ''}}>Active</option>
                                <option value="disabled" {{$dt->status == 'disabled' ? 'selected' : ''}}>Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="indicator-label btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
