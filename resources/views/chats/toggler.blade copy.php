<!--begin::Sidebar-->
<div id="kt_sidebar" class="sidebar" data-kt-drawer="true" data-kt-drawer-name="sidebar" data-kt-drawer-activate="{default: true, xxl: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'lg': '400px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_sidebar_toggler" wire:ignore.self>
    <!--begin::Sidebar Content-->
    <div class="d-flex flex-column sidebar-body px-5 py-10" id="kt_sidebar_body" wire:ignore.self>
        <!--begin::Close-->
        <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_sidebar_toggler" wire:ignore>
            <i class="ki-duotone ki-cross-square fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Close-->
        <!--begin::Sidebar Nav-->
        <ul class="sidebar-nav nav nav-tabs mb-10" id="kt_sidebar_tabs" role="tablist" wire:ignore>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_1">
                    <i class="ki-duotone ki-abstract-36">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_2">
                    <i class="ki-duotone ki-abstract-41">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_3">
                    <i class="ki-duotone ki-abstract-35">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_4">
                    <i class="ki-duotone ki-setting-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_5">
                    <i class="ki-duotone ki-abstract-25">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </li>
        </ul>
        <!--end::Sidebar Nav-->
        <!--begin::Sidebar Content-->
        <div id="kt_sidebar_content">
            <div class="hover-scroll-y" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-offset="0px" data-kt-scroll-dependencies="#kt_sidebar_tabs" data-kt-scroll-wrappers="#kt_sidebar_content, #kt_sidebar_body" wire:ignore.self>
                <!--begin::Tab content-->
                <div class="tab-content px-5 px-xxl-10" wire:ignore.self>
                    <!--begin::Tab pane-->
                    @include('chats.togglerContact')
                    <!--end::Tab pane-->
                    <!--begin::Tab pane-->
                    @include('chats.togglerHistory')
                    <!--end::Tab pane-->
                </div>
                <!--end::Tab content-->
            </div>
        </div>
        <!--end::Sidebar Content-->
    </div>
    <!--end::Sidebar Content-->
</div>
<!--end::Sidebar-->