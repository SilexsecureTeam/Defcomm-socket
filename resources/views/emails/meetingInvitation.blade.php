<div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:13px;  max-width:600px;margin:auto; box-sizing:border-box;">
    <div style="background-color:white; padding: 20px 40px 20px 40px; border: 1px solid rgba(0, 0, 0, 0.209);">
        <div style="display: flex; align-items: center;  background-color:#36460A;justify-content: center; padding: 10px; text-align: center;">
            <div style="max-width:150px; margin: 0 auto;">
                <img src="https://backend.defcomm.ng/img/Defcomm-Logo.png" alt="Defcomm Logo" style="max-width: 100%; height: auto;">
            </div>
        </div>
        <div style=" display:flex; align-items:center; margin-top:10px; justify-content:center; ">
            <p style="padding: 10px; display: inline-block; font-size: 20px; text-align: center; font-weight: bold; margin: 0 auto;">
                New meeting invation from {{$meet->userCreate->name}}
            </p>
        </div>


        <p style="font-weight:bold;">Hi {{ $name ?? '' }},</p>
        <div style="margin-bottom:30px;">
            <p>You are invited to join a meeting. The meeting detial below</p>

            <p>
                @if($meet->title)
                Title: {{$meet->title}}<br />
                @endif
                @if($meet->agenda)
                Agenda: {{$meet->agenda}}<br />
                @endif
                @if($meet->subject)
                Subject: {{$meet->subject}}<br />
                @endif
                @if($meet->meeting_link)
                Meeting Link: {{$meet->meeting_link}}/{{encrypt($meet->id)}}<br />
                @endif
                @if($meet->meeting_id)
                Meeting ID: {{$meet->meeting_id}}<br />
                @endif
                @if($meet->startdatetime)
                Meeting Date: {{$meet->startdatetime}}<br />
                @endif
            </p>

            <p>Kindly click the button to proceed</p>
            <div style="text-align: center;">
                <a href="{{$meet->meeting_link}}/{{encrypt($meet->id)}}" style="background-color: #36460A; padding: 10px; border-radius:5px; color: #ffffff;">Join</a>
            </div>
            <div>
                <p>Please use this link if the join button is not working</p>
                <a href="{{$meet->meeting_link}}/{{encrypt($meet->id)}}">{{$meet->meeting_link}}/{{encrypt($meet->id)}}</a>
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