<div>
    <!--begin::Chat drawer-->
    <div id="kt_drawer_chat" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="chat" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_drawer_chat_toggle" data-kt-drawer-close="#kt_drawer_chat_close" wire:ignore.self>
        <!--begin::Messenger-->
        <div class="card w-100 border-0 rounded-0" id="kt_drawer_chat_messenger">
            <!--begin::Card header-->
            <div class="card-header pe-5" id="kt_drawer_chat_messenger_header">
                <!--begin::Title-->
                <div class="card-title">
                    <!--begin::User-->
                    @if($current_chat_user)
                    <div class="d-flex justify-content-center flex-column me-3">
                        @if($current_chat_user_type == "user")
                        <a href="#" class="fs-4 fw-bold text-gray-900 text-hover-primary me-1 mb-2 lh-1">
                            {{$current_chat_user->name}}
                        </a>
                        <!--begin::Info-->
                        <div class="mb-0 lh-1">
                            @if($current_chat_user->is_online == 'yes')
                            <span class="badge badge-success badge-circle w-10px h-10px me-1"></span>
                            <span class="fs-7 fw-semibold text-muted">Active</span>
                            @else
                            <span class="badge badge-danger badge-circle w-10px h-10px me-1"></span>
                            <span class="fs-7 fw-semibold text-muted">Offline</span>
                            @endif
                        </div>
                        <!--end::Info-->
                        @else
                        <div class="card-title">
                            <!--begin::Users-->
                            <div class="symbol-group symbol-hover">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-5.jpg">
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg">
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <span class="symbol-label bg-light-warning text-warning 40px">C</span>
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-9.jpg">
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <span class="symbol-label bg-light-danger text-danger 40px">O</span>
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <span class="symbol-label bg-light-primary text-primary 40px">N</span>
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-23.jpg">
                                </div>
                                <!--end::Avatar-->
                                <!--begin::All users-->
                                <a href="#" class="symbol symbol-35px symbol-circle" data-bs-toggle="modal" data-bs-target="#kt_modal_view_users">
                                    <span class="symbol-label fs-8 fw-bold" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-original-title="View more users" data-kt-initialized="1">+42</span>
                                </a>
                                <!--end::All users-->
                            </div>
                            <!--end::Users-->
                        </div>
                        @endif
                    </div>
                    @endif
                    <!--end::User-->
                </div>
                <!--end::Title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat_close">
                        <div id="kt_drawer_chat2_toggle">
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
            @if($current_chat_user)
            <!--begin::Card body-->
            <div class="card-body" id="kt_drawer_chat_messenger_body">
                <!--begin::Messages-->
                <!--begin::Messages-->
                <div class="scroll-y me-n5 pe-5" data-kt-element="messages" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_drawer_chat_messenger_header, #kt_drawer_chat_messenger_footer" data-kt-scroll-wrappers="#kt_drawer_chat_messenger_body" data-kt-scroll-offset="0px" wire:poll="updateChat" style="height:60vh;" id="kt_drawer_chat_messenger_body_div">
                    @foreach($chat_message as $dt)
                    @if($dt->user_id == auth()->user()->id)
                    <!--begin::Message(out)-->
                    <div class="d-flex justify-content-end mb-10">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-end">
                            <!--begin::User-->
                            <div class="d-flex align-items-center mb-2">
                                @if(auth()->user()->chatSettings->hide_message == 1)
                                <div class="form-check form-check-solid form-switch form-check-custom fv-row ms-2">
                                    <input wire:click="toggleMessageHide({{$dt->id}})" class="form-check-input w-45px h-20px" type="checkbox" value="1" {{ auth()->user()->chatSettings->hide_message == 1 && !in_array($dt->id, $toggleMessage) ? 'checked="checked"' : ''}} name="hide_message" id="allowmarketing" />
                                    <label class="form-check-label" for="allowmarketing"></label>
                                </div>
                                @endif
                                @if($dt->is_important == 'no')
                                <a href="#" class="form-check form-check-solid form-switch form-check-custom fv-row ms-2" wire:click="markMessageImportant({{$dt->id}}, 'yes')">
                                    <i class="fa-regular fa-star"></i>
                                </a>
                                @else
                                <a href="#" class="form-check form-check-solid form-switch form-check-custom fv-row ms-2" wire:click="markMessageImportant({{$dt->id}}, 'no')">
                                    <i class="fa-solid fa-star text-danger"></i>
                                </a>
                                @endif
                                <div class="btn-group dropstart ms-2" wire:ignore>
                                    <a href="#" class="" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Forward</a></li>
                                    </ul>
                                </div>
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-end" data-kt-element="message-text">
                                @if(auth()->user()->chatSettings->hide_message == 1 && !in_array($dt->id, $toggleMessage))
                                ********************************
                                @else
                                @if($dt->is_file == "yes")
                                <a href="{{route('admin.file.view', ['id' => encrypt($dt->message)])}}" class="d-flex align-items-center">
                                    <i class="ki-duotone ki-picture fs-2x text-primary me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                @else
                                {!! decrypt($dt->message) !!}
                                @endif
                                @endif
                            </div>
                            <!--end::Text-->
                            <div class="d-flex align-items-center mb-2">
                                <!--begin::Details-->
                                <div class="me-3">
                                    <span class="text-muted fs-7 mb-1">{{$dt->created_at->diffForHumans()}}</span>
                                    <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary ms-1">You</a>
                                </div>
                                <!--end::Details-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="{{ $dt->user->avatar ? asset('/'.$dt->user->avatar) : asset('/img/user.png')}}" class="object-fit-cover" />
                                </div>
                                <!--end::Avatar-->
                            </div>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(out)-->
                    @else
                    <!--begin::Message(in)-->
                    <div class="d-flex justify-content-start mb-10">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="d-flex align-items-center mb-2">
                                <div class="btn-group dropstart" wire:ignore>
                                    <a href="#" class="" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Forward</a></li>
                                    </ul>
                                </div>
                                @if($dt->is_important == 'no')
                                <a href="#" class="form-check form-check-solid form-switch form-check-custom fv-row ms-2" wire:click="markMessageImportant({{$dt->id}}, 'yes')">
                                    <i class="fa-regular fa-star"></i>
                                </a>
                                @else
                                <a href="#" class="form-check form-check-solid form-switch form-check-custom fv-row ms-2" wire:click="markMessageImportant({{$dt->id}}, 'no')">
                                    <i class="fa-solid fa-star text-danger"></i>
                                </a>
                                @endif
                                @if(auth()->user()->chatSettings->hide_message == 1)
                                <div class="form-check form-check-solid form-switch form-check-custom fv-row ms-2">
                                    <input wire:click="toggleMessageHide({{$dt->id}})" class="form-check-input w-45px h-20px" type="checkbox" value="1" {{ auth()->user()->chatSettings->hide_message == 1 && !in_array($dt->id, $toggleMessage) ? 'checked="checked"' : ''}} name="hide_message" id="allowmarketing" />
                                    <label class="form-check-label" for="allowmarketing"></label>
                                </div>
                                @endif
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 rounded bg-light-info text-gray-900 fw-semibold mw-lg-400px text-start" data-kt-element="message-text">
                                @if(auth()->user()->chatSettings->hide_message == 1 && !in_array($dt->id, $toggleMessage))
                                ********************************
                                @else
                                @if($dt->is_file == "yes")
                                <a href="{{route('admin.file.view', ['id' => encrypt($dt->message)])}}" class="d-flex align-items-center">
                                    <i class="ki-duotone ki-picture fs-2x text-primary me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                @else
                                {!! decrypt($dt->message) !!}
                                @endif
                                @endif
                            </div>
                            <!--end::Text-->
                            <div class="d-flex align-items-center mt-2">
                                <!--begin::Avatar-->
                                <div class=" symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="{{ $dt->user->avatar ? asset('/'.$dt->user->avatar) : asset('/img/user.png')}}" class="object-fit-cover" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">
                                        {{$dt->user->name}}
                                    </a>
                                    <span class="text-muted fs-7 mb-1">{{$dt->updated_at->diffForHumans()}}</span>
                                </div>
                                <!--end::Details-->
                            </div>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(in)-->
                    @endif
                    @endforeach
                </div>
                <!--end::Messages-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer pt-4" id="kt_drawer_chat_messenger_footer">
                <!--begin::Input-->
                <textarea wire:model="message" class="form-control form-control-flush mb-3" rows="1" data-kt-element="input" placeholder="Type a message"></textarea>
                <!--end::Input-->
                <!--begin:Toolbar-->
                <div class="d-flex flex-stack">
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center me-2">
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button" data-bs-toggle="modal" data-bs-target="#kt_modal_add_file_chat" wire:ignore>
                            <i class="ki-duotone ki-paper-clip fs-3"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button" data-bs-toggle="tooltip" title="Coming soon">
                            <i class="ki-duotone ki-cloud-add fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </button>
                    </div>
                    <!--end::Actions-->
                    <!--begin::Send-->
                    <button wire:click="send" onclick="forceScrollDownLcl()" class="btn btn-primary" type="button" data-kt-element="send">Send</button>
                    <!--end::Send-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card footer-->
            @else
            <div class="text-center mt-5" id="kt_drawer_chat_close">
                <a href="#" id="kt_drawer_chat2_toggle" class="btn w-50 btn-success"><i class="bi bi-chat-square-text-fill fs-4 me-2"></i> Start new chat</a>
            </div>
            @endif
        </div>
        <!--end::Messenger-->
    </div>
    <!--end::Chat drawer-->
</div>
@include('includes.fileUpload')
<script>
    function forceScrollDownLcl() {
        setTimeout(function() {
            var div = document.getElementById("kt_drawer_chat_messenger_body_div")
            div.scrollTop = div.scrollHeight;
            // window.scroll(0, height);
        }, 2000);
    }
</script>