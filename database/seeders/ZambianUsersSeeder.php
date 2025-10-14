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
