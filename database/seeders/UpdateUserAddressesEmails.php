<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAddress;
use Illuminate\Support\Str;

class UpdateUserAddressesEmails extends Seeder
{
    public function run(): void
    {
        UserAddress::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNull('email')
                    ->orWhere('email', '')
                    ->orWhere('email', '-');
            })
            ->with('user')
            ->chunkById(200, function ($addresses) {
                foreach ($addresses as $address) {
                    $user = $address->user;
                    if (!$user || empty($user->email)) {
                        continue;
                    }

                    $address->email = $user->email;
                    $address->save();
                }
            });

        UserAddress::whereNull('user_id')
            ->where(function ($query) {
                $query->whereNull('email')
                    ->orWhere('email', '')
                    ->orWhere('email', '-');
            })
            ->chunkById(200, function ($addresses) {
                foreach ($addresses as $address) {
                    $guestIdentifier = $address->guest_id ?? 'guest_' . Str::uuid()->toString();

                    if (!$address->guest_id) {
                        $address->guest_id = $guestIdentifier;
                    }

                    $address->email = $guestIdentifier . '@guest.local';
                    $address->save();
                }
            });
    }
}
