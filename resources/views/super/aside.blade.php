<!--begin::Aside menu-->
<div class="aside-menu flex-column-fluid" id="kt_aside_menu">
    <!--begin::Aside Menu-->
    <div class="hover-scroll-y my-2 my-lg-5 scroll-ms" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="5px">
        <!--begin::Menu-->
        <div class="menu menu-column menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500 fw-semibold" id="#kt_aside_menu" data-kt-menu="true">
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item here show py-2">
                <!--begin:Menu link-->
                <a href="{{ url('/super/dashboard')}}" class="menu-link menu-center">
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
                <a href="{{ url('/super/account')}}" class="menu-link menu-center">
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
                <span class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-briefcase fs-2x"></i>
                    </span>
                    <span class="menu-title">Settings</span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto" style="">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-section fs-5 fw-bolder ps-1 py-1">Settings</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ url('/super/language')}}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Language</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ url('/super/agreements')}}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Agreement Statement</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ url('/super/systemMail')}}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">System Mail</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <span class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-abstract-26 fs-2x"></i>
                    </span>
                    <span class="menu-title">Store</span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto" style="">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-section fs-5 fw-bolder ps-1 py-1">App Store</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ url('/super/store/user')}}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Users</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ url('/super/store/app')}}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">App</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="menu-item py-2">
                <!--begin:Menu link-->
                <a href="{{ url('logout')}}" class="menu-link menu-center">
                    <span class="menu-icon me-0">
                        <i class="ki-outline ki-notification-status fs-2x"></i>
                    </span>
                    <span class="menu-title">Logout</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
        </div>
        <!--end::Menu-->
    </div>
    <!--end::Aside Menu-->
</div>
<!--end::Aside menu-->