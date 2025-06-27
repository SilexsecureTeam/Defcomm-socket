@extends('layouts.super')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between">
        <div>
            <h3 class="mb-4">App Store Submissions</h3>
        </div>
        <div class="mb-4">
            <a data-bs-toggle="modal" data-bs-target="#kt_modal_add_user" class=" btn btn-warning">Approval</a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{ $app->name }} <span class="badge bg-primary text-capitalize">{{ $app->status }}</span></h5>
            <small>Submitted by: User #{{ $app->userId->name }}</small>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    @if($app->app_icon)
                    <img src="{{ asset('storage/'.$app->app_icon) }}" alt="App Icon" class="img-thumbnail mb-3" style="width: 100px;">
                    @endif

                    @if($app->feature_image)
                    <img src="{{ asset('storage/'.$app->feature_image) }}" alt="Feature Image" class="img-fluid">
                    @endif
                </div>
                <div class="col-md-9">
                    <p><strong>Description:</strong> {{ $app->description }}</p>
                    <p><strong>Category:</strong> {{ $app->category }}</p>
                    <p><strong>OS:</strong> {{ $app->os }}</p>
                    <p><strong>Version:</strong> {{ $app->version }} ({{ $app->name_release }})</p>
                    <p><strong>App Bundle:</strong> {{ $app->app_bundle }}</p>
                    <p><strong>Policy:</strong> {{ $app->policy }}</p>

                    <p><strong>Contact Info:</strong></p>
                    <ul>
                        <li>Name: {{ $app->contact_name }}</li>
                        <li>Email: {{ $app->contact_email }}</li>
                        <li>Phone: {{ $app->contact_phone }}{{ $app->phone_opt ? ', ' . $app->phone_opt : '' }}</li>
                        <li>Address: {{ $app->contact_address }}</li>
                        <li>Other: {{ $app->contact_other }}</li>
                    </ul>

                    <p><strong>Location Access:</strong> Precise: {{ $app->location_precise }}, Coarse: {{ $app->location_coarse }}</p>
                    <p><strong>Sensitive Info:</strong> {{ $app->sensitive_info }}</p>

                    <p><strong>Release Type:</strong> {{ $app->release }}</p>
                    <p><strong>Data Collection:</strong> {{ $app->collect_data }}</p>

                    <p><strong>App ID:</strong> {{ $app->app_id_prefix }}{{ $app->app_id_name }}{{ $app->app_id_surfix }}</p>

                    <p><strong>Company Info:</strong></p>
                    <ul>
                        <li>RC Number: {{ $app->rc_number }}</li>
                        <li>TIN Number: {{ $app->tin_number }}</li>
                    </ul>

                    @if($app->comment)
                    <p><strong>Reviewer Comment:</strong> {{ $app->comment }}</p>
                    @endif

                    <p class="mb-0">
                        <strong>Status Dates:</strong>
                        <small>
                            @if($app->active_date) Active: {{ $app->active_date }} | @endif
                            @if($app->disable_date) Disabled: {{ $app->disable_date }} | @endif
                            @if($app->reject_date) Rejected: {{ $app->reject_date }} | @endif
                            @if($app->resubmit_date) Resubmitted: {{ $app->resubmit_date }} @endif
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!--begin::Modal - Add task-->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">Send Comment</h2>
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
                <form id="kt_modal_add_user_form" method="post" class="form" action="{{route('super.store.app.detailSub')}}">
                    @csrf
                    <input type="hidden" value="{{encrypt($app->id)}}" name="id" />
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Message</label>
                            <!--end::Label-->
                            <!--begin::Input--><textarea id="editor" name="description">{{$app->comment}}</textarea>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Status</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="status" class="form-control form-control-solid mb-3 mb-lg-0">
                                <option value="{{$app->status}}">{{$app->status}}</option>
                                <option value="approved">Approved</option>
                                <option value="reject">Reject</option>
                            </select>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-kt-users-modal-action="cancel">Discard</button>
                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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


<!--end::Modal - Add task-->
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
@endsection