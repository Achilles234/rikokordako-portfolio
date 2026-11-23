<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FamilyBootstrapOwner extends Command
{
    protected $signature = 'app:family-bootstrap-owner';

    protected $description = 'Prepare the existing Super Admin fixture for local testing';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Ditolak: command hanya boleh berjalan di environment local atau testing.');
            return self::FAILURE;
        }

        $connection = DB::connection();

        if ($connection->getDatabaseName() !== 'rikokordako_family_test') {
            $this->error('Ditolak: command hanya boleh berjalan di rikokordako_family_test.');
            return self::FAILURE;
        }

        $user = DB::table('users')->where('id', 3)->first();
        $role = DB::table('roles')
            ->where('slug', 'super_admin')
            ->where('scope', 'global')
            ->where('is_system', true)
            ->first();

        $setting = DB::table('family_portal_settings')
            ->where('id', 1)
            ->first();

        if (! $user || ! $role || ! $setting ||
            (int) $setting->super_admin_user_id !== 3) {
            $this->error('Fixture Super Admin tidak sesuai. Tidak ada perubahan.');
            return self::FAILURE;
        }

        if (DB::table('user_roles')
            ->where('user_id', 3)
            ->where('role_id', $role->id)
            ->exists()) {
            $this->warn('Role Super Admin sudah terpasang.');
        }

        $password = $this->secret('Masukkan password testing baru (minimal 12 karakter)');

        if (strlen($password) < 12) {
            $this->error('Password minimal 12 karakter.');
            return self::FAILURE;
        }

        $confirmation = $this->secret('Ulangi password');

        if ($password !== $confirmation) {
            $this->error('Password tidak cocok.');
            return self::FAILURE;
        }

        if ($this->confirm('Pastikan hanya database testing yang akan diubah. Lanjutkan?', false) !== true) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($role, $password) {
            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => 3,
                    'role_id' => $role->id,
                ],
                [
                    'role_scope' => 'global',
                    'assigned_by' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('users')
                ->where('id', 3)
                ->update([
                    'password' => Hash::make($password),
                    'updated_at' => now(),
                ]);
        });

        $this->info('Akun Super Admin testing berhasil disiapkan.');
        $this->line('Email: ' . $user->email);

        return self::SUCCESS;
    }
}
