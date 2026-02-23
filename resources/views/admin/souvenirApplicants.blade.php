@extends('layouts.admin')

@section('content')
<!--begin::Card-->
<div class="card table-wrapper">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-label">Applicants for Souvenir: {{ $souvenir->name }}</h3>
        </div>
    </div>
    
    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_applicants">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Applicant Name</th>
                    <th class="min-w-125px">Email</th>
                    <th class="min-w-125px">Collection Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($data as $dt)
                @php
                    $regStatus = $souvenir->registrations()->where('event_registration_id', $dt->id)->first();
                    $isCollected = $regStatus ? $regStatus->pivot->is_collected : 0;
                @endphp
                <tr>
                    <td>{{$dt->user->name}}</td>
                    <td>{{$dt->user->email}}</td>
                    <td>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input h-20px w-30px collect-toggle" type="checkbox" 
                                data-reg-id="{{encrypt($dt->id)}}" 
                                data-souvenir-id="{{$id}}"
                                {{$isCollected ? 'checked' : ''}} />
                            <label class="form-check-label">{{$isCollected ? 'Collected' : 'Mark as Collected'}}</label>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX for collection toggle
    document.querySelectorAll('.collect-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const regId = this.dataset.regId;
            const souvenirId = this.dataset.souvenirId;
            const status = this.checked ? 1 : 0;
            const label = this.nextElementSibling;

            fetch('{{route("admin.form.souvenir.collect")}}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                body: JSON.stringify({ reg_id: regId, souvenir_id: souvenirId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    label.innerText = status ? 'Collected' : 'Mark as Collected';
                }
            });
        });
    });
});
</script>
@endsection
