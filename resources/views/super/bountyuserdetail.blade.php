@extends('layouts.super')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between">
        <div>
            <h3 class="mb-4">Bounty Submissions</h3>
        </div>
        <div class="mb-4">
            <a class=" btn btn-warning">
                {{$data->rel_group ? $data->group->username : "System"}} || {{$data->user_type}}
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{ $data->username }} <span class="badge bg-primary text-capitalize">{{ $data->status }}</span></h5>
            <small>Submitted by: User # {{$data->firstName}} {{$data->lastName}}</small>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h2>Name: {{$data->firstName}} {{$data->lastName}}</h2>
                    @if($data->photo)
                    <a href="{{url('/'.$data->photo)}}"><i class="fas fa-download"></i></a>
                    @endif
                </div>
                <div class="col-md-9">
                    <p><strong>Username:</strong> {{ $data->username }}</p>
                    <p><strong>Email:</strong> {{ $data->email }}</p>
                    <p><strong>Phone:</strong> {{ $data->phone }}</p>

                    <p><strong>Details:</strong></p>
                    <ul>
                        <li>Country: {{ $data->country }}</li>
                        <li>Zipcode: {{ $data->zipcode }}</li>
                        <li>Timezone: {{ $data->timezone }}</li>
                    </ul>
                </div>
                <p>{{$data->bio}}</p>
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
                <form id="kt_modal_add_user_form" method="post" class="form" action="{{route('super.store.user.detailSub')}}">
                    @csrf
                    <input type="hidden" value="{{encrypt($data->id)}}" name="id" />
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Message</label>
                            <!--end::Label-->
                            <!--begin::Input--><textarea id="editor" name="commentApp">{{$data->commentApp}}</textarea>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Status</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="statusApp" class="form-control form-control-solid mb-3 mb-lg-0">
                                <option value="{{$data->statusApp}}">{{$data->statusApp}}</option>
                                <option value="verified">Verified</option>
                                <option value="reject">Reject</option>
                                <option value="block">Block</option>
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