<?php

namespace Database\Seeders;

use App\Models\SystemMail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SystemMailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'title' => 'A call from Defcomm',
                'label' => 'call',
                'message' => ''
            ],
            [
                'title' => 'Welcome to Defcomm',
                'label' => 'fileshare',
                'message' => ''
            ],
            [
                'title' => 'Welcome to Defcomm',
                'label' => 'invitation_account',
                'message' => 'You are invited to join Defcomm. Use the button to join'
            ],
            [
                'title' => 'Miss call from Defcomm',
                'label' => 'miss_call',
                'message' => ''
            ],
            [
                'title' => 'Password Reset',
                'label' => 'passreset',
                'message' => 'Your password was just reset on defcomm'
            ],
            [
                'title' => 'Welcome back to Defcomm',
                'label' => 'otp',
                'message' => ''
            ],
            [
                'title' => 'Defcomm Contact',
                'label' => 'contact',
                'message' => 'Thanks for contacting us. We will get in touch with you soon.'
            ],
            [
                'title' => 'Bookings Defcomm',
                'label' => 'booking',
                'message' => 'Thanks for sendinging your details. We will get in touch with you soon.'
            ],
        ];

        foreach ($records as $dt) {
            SystemMail::updateOrCreate([
                'label' => $dt['label'],
            ], [
                'title' => $dt['title'],
                'message' => $dt['message'],
            ]);
        }
    }
}
