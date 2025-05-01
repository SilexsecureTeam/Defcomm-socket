<div class="tab-pane fade" id="kt_sidebar_tab_4" role="tabpanel" wire:ignore.self>
    <!--begin::Best Sellers Widget-->
    <div class="card card-flush card-p-0 card-reset mb-5">
        <!--begin::Body-->
        <form class="card-body" action="{{ route('common.submitChatSetting')}}" method="Post">
            @csrf
            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Hide Message Style</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <select type="text" name="hide_message_style" required class="form-control form-control-lg form-control-solid" placeholder="Enter Address">
                        <option value="null">Select Option</option>
                        <option value="open_once" {{auth()->user()->chatSettings->hide_message_style == 'open_once' ? 'selected' : ''}}>Open Once</option>
                        <option value="hold_open" {{auth()->user()->chatSettings->hide_message_style == 'hold_open' ? 'selected' : ''}}>Hold to Open</option>
                    </select>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->
            <!--begin::Input group-->
            <div class="row mb-0">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Hide All Message</label>
                <!--begin::Label-->
                <!--begin::Label-->
                <div class="col-lg-8 d-flex align-items-center">
                    <div class="form-check form-check-solid form-switch form-check-custom fv-row">
                        <input class="form-check-input w-45px h-30px" type="checkbox" value="1" name="hide_message" id="allowmarketing" {{ auth()->user()->chatSettings->hide_message == 1 ? 'checked="checked"' : ''}} />
                        <label class="form-check-label" for="allowmarketing"></label>
                    </div>
                </div>
                <!--begin::Label-->
            </div>
            <!--end::Input group-->
            <!--begin::Actions-->
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
            </div>
            <!--end::Actions-->
        </form>
        <!--end: Card Body-->
    </div>
    <!--end::Best Sellers Widget-->
</div>