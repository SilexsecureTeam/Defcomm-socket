<div style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:13px;  max-width:600px;margin:auto; box-sizing:border-box;">
    <div style="background-color:white; padding: 20px 40px 20px 40px; border: 1px solid rgba(0, 0, 0, 0.209);">
        <div style="display: flex; align-items: center;  background-color:#36460A;justify-content: center; padding: 10px; text-align: center;">
            <div style="max-width:150px; margin: 0 auto;">
                <img src="https://backend.defcomm.ng/img/Defcomm-Logo.png" alt="Defcomm Logo" style="max-width: 100%; height: auto;">
            </div>
        </div>
        <div style=" display:flex; align-items:center; margin-top:10px; justify-content:center; ">
            <p style="padding: 10px; display: inline-block; font-size: 20px; text-align: center; font-weight: bold; margin: 0 auto;">
                Bounty OTP Request
            </p>
        </div>


        <p style="font-weight:bold;">Hi {{ $data->username ?? '' }},</p>
        <div style="margin-bottom:30px;">

            {{--<p>Kindly click the button to proceed</p>
            <div style="text-align: center;">
                <a href="https://cloud.defcomm.ng/onboarding?auth=<?= $encrypt ?>" style="background-color: #36460A; padding: 10px; border-radius:5px; color: #ffffff;">Join</a>
            </div>--}}
            <div style="text-align: center; margin-top:50px;">
                <p style="margin-bottom: 10px;"> Kindly Use the code below to access your account</p>
                <span style="background-color: #36460A; color: #ffffff; border-radius: 5px; padding: 10px;">
                    {{$otp}}
                </span>
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