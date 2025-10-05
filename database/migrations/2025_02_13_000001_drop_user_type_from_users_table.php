<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('users', 'user_type')) {
            $this->synchronizeRolesFromUserType();

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['Customer', 'Fixer', 'Admin', 'Support'])->default('Customer')->after('contact_number');
            }
        });
    }

    private function synchronizeRolesFromUserType(): void
    {
        $users = DB::table('users')->select('id', 'user_type')->get();
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $record) {
            $user = User::find($record->id);
            if (! $user) {
                continue;
            }

            $type = strtolower((string) $record->user_type);
            $roles = ['Customer'];

            if ($type === 'fixer') {
                $roles[] = 'Fixer';
            } elseif ($type === 'admin') {
                $roles[] = 'Admin';
            } elseif ($type === 'support') {
                $roles[] = 'Support';
            }

            foreach (array_unique($roles) as $role) {
                if (! $user->hasRole($role)) {
                    $user->assignRole($role);
                }
            }
        }
    }
};
