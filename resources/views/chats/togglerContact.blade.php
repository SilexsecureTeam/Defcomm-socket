<div class="tab-pane fade" id="kt_sidebar_tab_2" role="tabpanel" wire:ignore.self>
    <!--begin::Best Sellers Widget-->
    <div class="card card-flush card-p-0 card-reset mb-5">
        <!--begin::Search-->
        <div id="kt_header_search" class="header-search d-flex align-items-center w-lg-250px" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-search-responsive="lg" data-kt-menu-trigger="auto" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
            <!--begin::Tablet and mobile search toggle-->
            <div data-kt-search-element="toggle" class="search-toggle-mobile d-flex d-lg-none align-items-center">
                <div class="d-flex btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px">
                    <i class="ki-duotone ki-magnifier fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <!--end::Tablet and mobile search toggle-->
            <!--begin::Form(use d-none d-lg-block classes for responsive search)-->
            <div class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0">
                <!--begin::Hidden input(Added to disable form autocomplete)-->
                <input type="hidden" />
                <!--end::Hidden input-->
                <!--begin::Icon-->
                <i class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <!--end::Icon-->
                <!--begin::Input-->
                <input type="search" wire:model.live="search" class="form-control bg-transparent ps-13 fs-7 h-40px" placeholder="Quick Search" data-kt-search-element="input" />
                <!--end::Input-->
                <!--begin::Spinner-->
                <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
                    <span class="spinner-border h-15px w-15px align-middle text-gray-500"></span>
                </span>
                <!--end::Spinner-->
                <!--begin::Reset-->
                <span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
                    <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>
                <!--end::Reset-->
            </div>
            <!--end::Form-->
        </div>
        <!--end::Search-->
        <!--begin::Header-->
        <div class="card-header align-items-center border-0">
            <!--begin::Title-->
            <h3 class="card-title fw-bold text-white fs-3">My Contact</h3>
            <!--end::Title-->
            <!--begin::Toolbar-->
            <div class="card-toolbar">
                <button type="button" class="btn btn-icon btn-icon-white btn-active-color-primary me-n4" data-kt-menu-trigger="click" data-kt-menu-overflow="true" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-category fs-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                </button>
                <!--begin::Menu 3-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                    <!--begin::Heading-->
                    <div class="menu-item px-3">
                        <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Options</div>
                    </div>
                    <!--end::Heading-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3">Add Contact</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3">Block Contact</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3 my-1">
                        <a href="#" class="menu-link px-3">Settings</a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::Menu 3-->
            </div>
            <!--end::Title-->
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body py-0" id="kt_drawer_chat2_toggle">
            @if(auth()->user()->role == 'admin')
            @forelse($users as $dt)
            <!--begin::Item-->
            <div wire:click="chat({{$dt->id}},'user')" onclick="forceScrollDownLcl()" class="d-flex flex-nowrap align-items-center mb-7" id="kt_drawer_chat_toggle">
                <!--begin::Image-->
                <a href="#" class="symbol symbol-40px symbol-2by3 me-4">
                    <img src="{{ $dt->avatar ? asset('/'.$dt->avatar) : asset('/img/user.png')}}" alt="" class="mw-100 object-fit-cover" />
                </a>
                <!--end::Image-->
                <!--begin::Title-->
                <div class="d-flex flex-column flex-grow-1 my-lg-0 my-2 pe-3">
                    <a href="#" class="text-white fw-semibold text-hover-primary fs-6">{{$dt->name}}</a>
                    <span class="sidebar-text-muted fw-semibold fs-7 my-1">{{$dt->phone}} || {{$dt->email}}</span>
                </div>
                <!--end::Title-->
            </div>
            <!--end::Item-->
            @empty
            <p class="text-danger">No result found</p>
            @endforelse
            @else
            @forelse($users as $dt)
            <!--begin::Item-->
            <div wire:click="chat({{$dt->userLink->id}},'user')" onclick="forceScrollDownLcl()" class="d-flex flex-nowrap align-items-center mb-7" id="kt_drawer_chat_toggle">
                <!--begin::Image-->
                <a href="#" class="symbol symbol-40px symbol-2by3 me-4">
                    <img src="{{ $dt->userLink->avatar ? asset('/'.$dt->userLink->avatar) : asset('/img/user.png')}}" alt="" class="mw-100 object-fit-cover" />
                </a>
                <!--end::Image-->
                <!--begin::Title-->
                <div class="d-flex flex-column flex-grow-1 my-lg-0 my-2 pe-3">
                    <a href="#" class="text-white fw-semibold text-hover-primary fs-6">{{$dt->userLink->name}}</a>
                    <span class="sidebar-text-muted fw-semibold fs-7 my-1">{{$dt->userLink->phone}} || {{$dt->userLink->email}}</span>
                </div>
                <!--end::Title-->
            </div>
            <!--end::Item-->
            @empty
            <p class="text-danger">No result found</p>
            @endforelse
            @endif
        </div>
        <!--end: Card Body-->
    </div>
    <!--end::Best Sellers Widget-->
</div>