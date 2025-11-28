<!--begin::Card-->
<div class="card" id="filePending">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <!--begin::Add user-->
                <h2>Pending File Invitation</h2>
                <!--end::Add user-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" data-kt-user-table-filter="search" class="table-search form-control form-control-solid w-250px ps-13" placeholder="Search File" />
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th></th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Invitation Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($file as $dt)
                <tr>
                    <td>
                        {{$loop->iteration}}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-picture fs-2x text-primary me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <a href="#" class="text-gray-800 text-hover-primary">{{$dt->file->name}}</a>
                        </div>
                    </td>
                    <td>{{$dt->file->file_size}}</td>
                    <td>{{$dt->created_at}}</td>
                    <td>
                        <a href="{{route('user.file.accept', ['id' => encrypt($dt->id)])}}" class="btn btn-primary">Accept</a>
                        <a href="{{route('user.file.decline', ['id' => encrypt($dt->id)])}}" class="btn btn-danger">Decline</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->