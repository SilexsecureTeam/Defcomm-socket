@extends('layouts.super')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between">
        <div>
            <h3 class="mb-4">Bounty Report Submissions</h3>
        </div>
        <div class="mb-4 d-flex">
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                <i class="ki-outline ki-plus fs-2"></i>Approval</button>
            @if($data->status == "accept")
            <a href="{{route('super.reportMarkFix', ['id'=>encrypt($data->id)])}}" class="btn btn-primary ms-2">Mark as Fixed</a>
            @endif
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{ $data->ref }} <span class="badge bg-primary text-capitalize">{{ $data->status }}</span></h5>
            <small>Submitted on: User # {{$data->created_at->diffForHumans()}}</small>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h2>Name: {{$data->user->firstName}} {{$data->user->lastName}}</h2>
                    @if($data->user->photo)
                    <a href="{{url('/'.$data->user->photo)}}"><i class="fas fa-download"></i></a>
                    @endif
                    <p><strong>Point:</strong> {{ $data->point }}</p>
                    <p><strong>Amount:</strong> {{ $data->amount }}</p>
                </div>
                <div class="col-md-9">
                    <p><strong>Program:</strong> {{ $data->program->title }}</p>
                    <p><strong>Category:</strong> {{ $data->categori->label }}</p>
                    <p><strong>Sub Category:</strong> {{ $data->categorySub->label }}</p>
                    <p><strong>Severity:</strong> {{ $data->severity }}</p>
                    <p><strong>Subject:</strong> {{ $data->title }}</p>

                    <p><strong>Details:</strong></p>
                    <div style="margin-left: 50px;">{!! $data->detail !!}</div>

                    <p><strong>Attachments:</strong></p>
                    @foreach(json_decode($data->attachment, true) as $item)
                    <img src="{{asset('/'.$item)}}" style="width: 100%; height: 300px; object-fit: contain; object-position: center;" alt="att" /><br />
                    @endforeach
                </div>
                <p>{{$data->admin_comment}}</p>
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
                <h2 class="fw-bold">Approval</h2>
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
                <form id="kt_modal_add_user_form" method="post" class="form" action="{{route('super.reportApproval')}}">
                    @csrf
                    <input type="hidden" value="{{encrypt($data->id)}}" name="id" />
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                        {{--<!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Message</label>
                            <!--end::Label-->
                            <!--begin::Input--><textarea id="editor" name="commentApp">{{$data->commentApp}}</textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->--}}
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">Comment</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" value="{{$data->admin_comment}}" name="admin_comment" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Label" />
                        <!--end::Input-->
                    </div>
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">Amount</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" value="{{$data->amount ?? $data->categorySub->award_amount}}" name="amount" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Label" />
                        <!--end::Input-->
                    </div>
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">Point</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" value="{{$data->point ?? $data->categorySub->award_point}}" name="point" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Label" />
                        <!--end::Input-->
                    </div>
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">Status</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="status" class="form-control form-control-solid mb-3 mb-lg-0">
                            <option value="{{$data->status}}">{{$data->status}}</option>
                            <option value="accept">Accept</option>
                            <option value="reject">Reject</option>
                            <option value="close">Close</option>
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