<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\UserDevice;
use Carbon\Carbon;

class LogoutAndDeleteDevices extends Command
{
    protected $signature = 'logout:devices';

    protected $description = 'Logs out users and deletes user devices every 24 hours';

    public function handle()
    {
        $inactiveDuration = Carbon::now()->subDay(); // Define the inactive duration (24 hours ago)

        $inactiveDevices = UserDevice::where('created_at', '<', $inactiveDuration)->get();

        foreach ($inactiveDevices as $device) {
            // Log out the user
            // auth()->logoutOtherDevices($device->user->password);

            // Delete the device
            $device->delete();
        }

        $this->info('Device activity checked successfully.');
    }
}
