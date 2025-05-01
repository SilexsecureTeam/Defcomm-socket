<div>
    <!--begin::Chat drawer-->
    <div id="kt_drawer_chat2" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="chat" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_drawer_chat2_toggle" data-kt-drawer-close="#kt_drawer_chat2_close" wire:ignore.self>
        <!--begin::Messenger-->
        <div class="card w-100 border-0 rounded-0" id="kt_drawer_chat2_messenger">
            <!--begin::Card header-->
            <div class="card-header pe-5" id="kt_drawer_chat2_messenger_header">
                <!--begin::Title-->
                <div class="card-title">
                    <!--begin::User-->
                    Quick Tool Box
                    <!--end::User-->
                </div>
                <!--end::Title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat2_close">
                        <div id="kt_sidebar_toggler">
                            <i class="ki-duotone ki-cross-square fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Sidebar Nav-->
            <ul class="sidebar-nav nav nav-tabs mb-10" id="kt_sidebar_tabs" role="tablist" wire:ignore>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_1">
                        <i class="ki-duotone ki-abstract-36 tabIconNavChat">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_2">
                        <i class="ki-duotone ki-abstract-41 tabIconNavChat">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_3">
                        <i class="ki-duotone ki-abstract-35 tabIconNavChat">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_4">
                        <i class="ki-duotone ki-setting-2 tabIconNavChat">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-kt-countup-tabs="true" href="#kt_sidebar_tab_5">
                        <i class="ki-duotone ki-abstract-25 tabIconNavChat">
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
                        @include('chats.togglerGroup')
                        <!--end::Tab pane-->
                        <!--begin::Tab pane-->
                        @include('chats.togglerContact')
                        <!--end::Tab pane-->
                        <!--begin::Tab pane-->
                        @include('chats.togglerSetting')
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
        <!--end::Messenger-->
    </div>
    <!--end::Chat drawer-->
</div>
<script>
    function forceScrollDown() {
        var height = document.getElementById("kt_drawer_chat2_messenger_body")
        window.scroll(0, height);
    }
    forceScrollDown()
</script>