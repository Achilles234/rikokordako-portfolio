<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateFamilySuperAdmin extends Command
{
    protected $signature = 'family:create-super-admin';

    protected $description = 'Create the first Family Portal Super Admin';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Command hanya boleh dijalankan di environment local.');
            return self::FAILURE;
        }

        if (DB::connection()->getDatabaseName() !== 'rikokordako_family') {
            $this->error('Database tidak sesuai. Operasi dibatalkan.');
            return self::FAILURE;
        }

        $email = 'superadmin@rikokordako.my.id';

        if (User::where('email', $email)->exists()) {
            $this->error('Email Super Admin sudah terdaftar.');
            return self::FAILURE;
        }

        $role = DB::table('roles')
            ->where('slug', 'super_admin')
            ->where('scope', 'global')
            ->where('is_system', true)
            ->first();

        if (! $role) {
            $this->error('Role Super Admin tidak ditemukan.');
            return self::FAILURE;
        }

        $setting = DB::table('family_portal_settings')
            ->where('id', 1)
            ->first();

        if ($setting && $setting->super_admin_user_id !== null) {
            $this->error('Super Admin utama sudah ditetapkan.');
            return self::FAILURE;
        }

        $this->info('Pembuatan akun Super Admin Family Portal');
        $this->line('Email: ' . $email);

        $password = $this->secret('Masukkan password (minimal 12 karakter)');

        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Password minimal 12 karakter.');
            return self::FAILURE;
        }

        $confirmation = $this->secret('Ulangi password');

        if ($password !== $confirmation) {
            $this->error('Password tidak cocok.');
            return self::FAILURE;
        }

        if (! $this->confirm('Buat akun Super Admin pada database rikokordako_family?', false)) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($email, $password, $role, $setting) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'role_scope' => 'global',
                'assigned_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($setting) {
                DB::table('family_portal_settings')
                    ->where('id', 1)
                    ->update([
                        'super_admin_user_id' => $user->id,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('family_portal_settings')->insert([
                    'id' => 1,
                    'super_admin_user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->info('Super Admin berhasil dibuat!');
        $this->line('Email: ' . $email);

        return self::SUCCESS;
    }
}
