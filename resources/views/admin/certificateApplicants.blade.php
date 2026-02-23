@extends('layouts.admin')

@section('content')
<!--begin::Card-->
<div class="card table-wrapper">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-label">Applicants for {{ $cert->name }}</h3>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_mail_certificate">
                    <i class="ki-outline ki-send fs-2"></i>Send Mail to Selected</button>
            </div>
            
            <div class="modal fade" id="kt_modal_mail_certificate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bold">Send Certificate via Mail</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body px-5 my-7">
                            <form action="{{route('admin.form.certificate.mail')}}" method="post">
                                @csrf
                                <input type="hidden" name="cert_id" value="{{$id}}">
                                <input type="hidden" name="registrations" id="selected_regs">
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Subject</label>
                                    <input type="text" name="subject" class="form-control form-control-solid" value="Your Certificate for {{ $cert->form->name }}" required />
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="required fw-semibold fs-6 mb-2">Message</label>
                                    <textarea name="message" class="form-control form-control-solid" rows="4" required>Hello, please find your certificate attached.</textarea>
                                </div>
                                <div class="text-center pt-10">
                                    <button type="submit" class="btn btn-primary">Send Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_applicants">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" id="checkAll" />
                        </div>
                    </th>
                    <th class="min-w-125px">Applicant Name</th>
                    <th class="min-w-125px">Email</th>
                    <th class="min-w-125px">Mail Sent</th>
                    <th class="min-w-125px">Physical Collection</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($data as $dt)
                @php
                    $regStatus = $cert->registrations()->where('event_registration_id', $dt->id)->first();
                    $isCollected = $regStatus ? $regStatus->pivot->is_collected : 0;
                    $isSent = $regStatus ? $regStatus->pivot->is_sent : 0;
                @endphp
                <tr>
                    <td>
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input checkbox" type="checkbox" value="{{encrypt($dt->id)}}" />
                        </div>
                    </td>
                    <td>{{$dt->user->name}}</td>
                    <td>{{$dt->user->email}}</td>
                    <td>
                        <div class="badge badge-light-{{$isSent ? 'success' : 'warning'}}">
                            {{$isSent ? 'Sent' : 'Not Sent'}}
                        </div>
                    </td>
                    <td>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input h-20px w-30px collect-toggle" type="checkbox" 
                                data-reg-id="{{encrypt($dt->id)}}" 
                                data-cert-id="{{$id}}"
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
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.checkbox');
    const selectedRegsInput = document.getElementById('selected_regs');

    function updateSelected() {
        let vals = [];
        checkboxes.forEach(c => { if(c.checked) vals.push(c.value); });
        selectedRegsInput.value = JSON.stringify(vals);
    }

    checkAll.addEventListener('change', function() {
        checkboxes.forEach(c => c.checked = checkAll.checked);
        updateSelected();
    });

    checkboxes.forEach(c => c.addEventListener('change', updateSelected));

    // AJAX for collection toggle
    document.querySelectorAll('.collect-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const regId = this.dataset.regId;
            const certId = this.dataset.certId;
            const status = this.checked ? 1 : 0;
            const label = this.nextElementSibling;

            fetch('{{route("admin.form.certificate.collect")}}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                body: JSON.stringify({ reg_id: regId, cert_id: certId, status: status })
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
