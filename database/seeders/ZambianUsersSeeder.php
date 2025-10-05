<?php

namespace Database\Seeders;

use App\Models\LocationOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ZambianUsersSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'John','Peter','Brian','Moses','Mike','Kennedy','Patrick','Emmanuel','Felix','Elijah',
            'Mary','Grace','Ruth','Agness','Martha','Brenda','Lindiwe','Thandiwe','Memory','Miriam',
            'Chanda','Mwila','Mutinta','Kapambwe','Mulenga','Chisomo','Mumba','Mubanga','Bwalya','Lombe',
        ];

        $lastNames = [
            'Phiri','Zimba','Mumba','Mulenga','Mwale','Nyirenda','Daka','Tembo','Banda','Nkhoma',
            'Zulu','Moyo','Sakala','Lungu','Chileshe','Kunda','Chirwa','Kalunga','Mwansa','Siwale',
        ];

        $towns = LocationOption::query()->pluck('name')->all();
        if (empty($towns)) {
            $towns = ['Lusaka','Ndola','Kitwe','Livingstone','Kabwe','Chipata','Chingola','Mansa'];
        }

        for ($i = 0; $i < 30; $i++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $username = Str::of($fn.'-'.$ln.'-'.Str::random(3))->lower()->slug('_');
            $email = Str::of($fn.'.'.$ln.$i.'@example.com')->lower();
            $msisdnPrefixes = ['095','096','097'];
            $contact = $msisdnPrefixes[array_rand($msisdnPrefixes)] . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);

            $user = User::updateOrCreate(
                ['email' => (string) $email],
                [
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'username' => (string) $username,
                    'contact_number' => $contact,
                    'status' => 'Active',
                    'address' => $towns[array_rand($towns)],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('Customer');
        }

        $regionalAdmin = User::updateOrCreate([
            'email' => 'admin.zm@example.com',
        ], [
            'first_name' => 'Chanda',
            'last_name' => 'Mulenga',
            'username' => 'admin_zm',
            'contact_number' => '097' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'status' => 'Active',
            'address' => $towns[array_rand($towns)],
            'password' => Hash::make('password'),
        ]);
        $regionalAdmin->assignRole('Admin');

        $regionalSupport = User::updateOrCreate([
            'email' => 'support.zm@example.com',
        ], [
            'first_name' => 'Thandiwe',
            'last_name' => 'Phiri',
            'username' => 'support_zm',
            'contact_number' => '096' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'status' => 'Active',
            'address' => $towns[array_rand($towns)],
            'password' => Hash::make('password'),
        ]);
        $regionalSupport->assignRole('Support');
    }
}
