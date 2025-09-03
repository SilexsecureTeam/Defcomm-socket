<div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:13px;  max-width:600px;margin:auto; box-sizing:border-box;">
    <div style="background-color:white; padding: 20px 40px 20px 40px; border: 1px solid rgba(0, 0, 0, 0.209);">
        <div style="display: flex; align-items: center;  background-color:#36460A;justify-content: center; padding: 10px; text-align: center;">
            <div style="max-width:150px; margin: 0 auto;">
                <img src="https://backend.defcomm.ng/img/Defcomm-Logo.png" alt="Defcomm Logo" style="max-width: 100%; height: auto;">
            </div>
        </div>
        <div style=" display:flex; align-items:center; margin-top:10px; justify-content:center; ">
            <p style="padding: 10px; display: inline-block; font-size: 20px; text-align: center; font-weight: bold; margin: 0 auto;">
                Defcomm Contact
            </p>
        </div>


        <p style="font-weight:bold;">{{ $data->first_name ?? '' }} {{ $data->last_name ?? '' }} Contact Details</p>
        <div style="margin-bottom:30px;">

            <p>
                @if($data->first_name)
                Name: {{$data->first_name}} {{$data->last_name}}<br />
                @endif
                @if($data->email)
                Email: {{$data->email}}<br />
                @endif
                @if($data->phone)
                Phone: {{$data->phone}}<br />
                @endif
                @if($data->company)
                Company: {{$data->company}}<br />
                @endif
                @if($data->detail)
                Detail: {{$data->detail}}<br />
                @endif
                @if($data->req)
                UID: {{$data->req}}<br />
                @endif
            </p>

            <div>
                <p>Visit us</p>
                <a href="https://defcomm.cloud">defcomm.cloud</a>
            </div>
        </div>

        <div style="line-height: 6px;">
            <p>Regards,</p>
            <p>Defcomm team</p>
        </div>

    </div>
    <div style="font-family: sans-serif; color:white; font-size:smaller; font-weight:100;  box-sizing:border-box; background-color:#36460A; padding: 20px 40px 20px 40px;">

        <p>Defcomm</p>
        <hr>
        <p>If you have any questions or concerns we're here to help contact us via our Help Center</p>

        <div style="display: flex; align-items: center; justify-content: start;">
            <p>
                &copy; Defcomm
            </p>
        </div>
    </div>
</div>