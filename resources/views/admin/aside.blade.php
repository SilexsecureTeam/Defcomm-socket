<!--begin::Aside menu-->
<div class="aside-menu flex-column-fluid" id="kt_aside_menu">
    <!--begin::Aside Menu-->
    <div class="hover-scroll-y my-2 my-lg-5 scroll-ms" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="5px">
        <!--begin::Menu-->
        <div class="menu menu-column menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500 fw-semibold" id="#kt_aside_menu" data-kt-menu="true">
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item here show py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/dashboard')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-home-2 fs-2x"></i>
                    </span>
                    <span class="menu-title">Home</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/account')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Account</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/notification')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Notification</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/group')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Group</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/form')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Form</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/file')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Files</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/admin/profile')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Profile</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            <!--begin::Wrapper-->
            <div class="d-flex align-items-center">
                <!--begin::Avatar-->
                <div class="symbol symbol-circle symbol-40px">
                    <!--<img src="assets/media/avatars/300-1.jpg" alt="photo" />-->
                </div>
                <!--end::Avatar-->
                <!--begin::User info-->
                <div class="ms-2 mt-5">
                    <!--begin::Name-->
                    <a href="{{ url('logout')}}" class=" btn btn-danger text-gray-800 text-hover-primary fs-6 fw-bold lh-1">Logout</a>
                    <!--end::Name-->
                </div>
                <!--end::User info-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Menu-->
    </div>
    <!--end::Aside Menu-->
</div>
<!--end::Aside menu-->