@extends('layouts.user')

@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">
        <!--begin::Row-->
        <div class="row gy-5 g-xl-10">
            <!--begin::Col-->
            <div class="col-xl-4">
                <!--begin::Mixed Widget 13-->
                <div class="card card-xl-stretch mb-xl-10 theme-dark-bg-body" style="background-color: #F7D9E3">
                    <!--begin::Body-->
                    <div class="card-body d-flex flex-column">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column flex-grow-1">
                            <!--begin::Title-->
                            <a href="#" class="text-gray-900 text-hover-primary fw-bold fs-3">Files</a>
                            <!--end::Title-->
                            <!--begin::Chart-->
                            <div class="mixed-widget-13-chart" style="height: 100px"></div>
                            <!--end::Chart-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Stats-->
                        <div class="pt-5">
                            <!--begin::Symbol-->
                            <!--<span class="text-gray-900 fw-bold fs-2x lh-0">$</span>-->
                            <!--end::Symbol-->
                            <!--begin::Number-->
                            <span class="text-gray-900 fw-bold fs-3x me-2 lh-0">{{$fileCount}}</span>
                            <!--end::Number-->
                            <!--begin::Text-->
                            <!--<span class="text-gray-900 fw-bold fs-6 lh-0">+ 28% this week</span>-->
                            <!--end::Text-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Mixed Widget 13-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-4">
                <!--begin::Mixed Widget 14-->
                <div class="card card-xxl-stretch mb-xl-10 theme-dark-bg-body" style="background-color: #CBF0F4">
                    <!--begin::Body-->
                    <div class="card-body d-flex flex-column">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column flex-grow-1">
                            <!--begin::Title-->
                            <a href="#" class="text-gray-900 text-hover-primary fw-bold fs-3">Groups</a>
                            <!--end::Title-->
                            <!--begin::Chart-->
                            <div class="mixed-widget-14-chart" style="height: 100px"></div>
                            <!--end::Chart-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Stats-->
                        <div class="pt-5">
                            <!--begin::Number-->
                            <span class="text-gray-900 fw-bold fs-3x me-2 lh-0">{{$groupCount}}</span>
                            <!--end::Number-->
                            <!--begin::Text-->
                            <!--<span class="text-gray-900 fw-bold fs-6 lh-0">- 12% this week</span>-->
                            <!--end::Text-->
                        </div>
                        <!--end::Stats-->
                    </div>
                </div>
                <!--end::Mixed Widget 14-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-4">
                <!--begin::Mixed Widget 14-->
                <div class="card card-xxl-stretch mb-5 mb-xl-10 theme-dark-bg-body" style="background-color: #CBD4F4">
                    <!--begin::Body-->
                    <div class="card-body d-flex flex-column">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column mb-7">
                            <!--begin::Title-->
                            <a href="#" class="text-gray-900 text-hover-primary fw-bold fs-3">Summary</a>
                            <!--end::Title-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Row-->
                        <div class="row g-0">
                            <!--begin::Col-->
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-9 me-2">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-light">
                                            <i class="ki-duotone ki-abstract-42 fs-1 text-gray-900">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Title-->
                                    <div>
                                        <div class="fs-5 text-gray-900 fw-bold lh-1">{{$groupsPendingCount}}</div>
                                        <div class="fs-7 text-gray-600 fw-bold">
                                            <a href="#groupPending">Group: Pending</a>
                                        </div>
                                    </div>
                                    <!--end::Title-->
                                </div>
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-12">
                                <div class="d-flex align-items-center me-2">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-light">
                                            <i class="ki-duotone ki-abstract-21 fs-1 text-gray-900">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Title-->
                                    <div>
                                        <div class="fs-5 text-gray-900 fw-bold lh-1">{{$filePendingCount}}</div>
                                        <div class="fs-7 text-gray-600 fw-bold">
                                            <a href="#filePending">File: Pending</a>
                                        </div>
                                    </div>
                                    <!--end::Title-->
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                    </div>
                </div>
                <!--end::Mixed Widget 14-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        @include('user.fileTable')
        @include('user.groupTable')
    </div>
    <!--end::Container-->
</div>
<!--end::Content-->
@endsection