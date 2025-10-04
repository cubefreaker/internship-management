<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dudi;
use App\Models\Siswa;
use App\Models\Guru;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables safely (respecting foreign keys)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('logbook')->truncate();
        DB::table('magang')->truncate();
        DB::table('dudi')->truncate();
        DB::table('siswa')->truncate();
        DB::table('guru')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create 1 admin user
        $admin = User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@mail.com',
            'password' => 'admin123',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // $this->call([
        //     DudiSeeder::class,
        // ]);

        // Use Indonesian locale for user data
        $userFaker = \Faker\Factory::create('id_ID');

        // Create 10 guru users (id_ID locale) dan record guru
        $emailCounter = 1;
        $gurus = User::factory()->count(10)
            ->state(function () use ($userFaker, &$emailCounter) {
                $name = $userFaker->name();
                // Bangun email dari nama dan pastikan unik dengan fallback counter
                $base = strtolower(preg_replace('/\s+/', '.', $name));
                $localPart = preg_replace('/[^a-z0-9\.]/', '', $base);
                $email = "$localPart@mail.com";
                if (\App\Models\User::where('email', $email)->exists()) {
                    $email = "$localPart.$emailCounter@mail.com";
                    $emailCounter++;
                }
                return [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'guru',
                    'email_verified_at' => now(),
                ];
            })
            ->create();

        foreach ($gurus as $guruUser) {
            Guru::create([
                'user_id' => $guruUser->id,
                'nip' => (string) rand(1000000000, 9999999999),
                'nama' => $guruUser->name,
                'alamat' => $userFaker->address(),
                'telepon' => $userFaker->numerify('08##########'),
            ]);
        }

        // Create 289 siswa users (id_ID locale) dan record siswa
        $emailCounterSiswa = 1;
        $siswas = User::factory()->count(289)
            ->state(function () use ($userFaker, &$emailCounterSiswa) {
                $name = $userFaker->name();
                $base = strtolower(preg_replace('/\s+/', '.', $name));
                $localPart = preg_replace('/[^a-z0-9\.]/', '', $base);
                $email = "$localPart@mail.com";
                if (\App\Models\User::where('email', $email)->exists()) {
                    $email = "$localPart.$emailCounterSiswa@mail.com";
                    $emailCounterSiswa++;
                }
                return [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'siswa',
                    'email_verified_at' => now(),
                ];
            })
            ->create();

        foreach ($siswas as $siswaUser) {
            Siswa::create([
                'user_id' => $siswaUser->id,
                'nama' => $siswaUser->name,
                'nis' => (string) rand(10000000, 99999999),
                'kelas' => $userFaker->randomElement(['X', 'XI', 'XII']) . ' ' . $userFaker->randomElement(['A', 'B', 'C']),
                'jurusan' => $userFaker->randomElement(['TKJ', 'RPL', 'MM', 'AKL']),
                'alamat' => $userFaker->address(),
                'telepon' => $userFaker->numerify('08##########'),
            ]);
        }

        // Create 20 DUDI records, owned by admin user
        $faker = \Faker\Factory::create('id_ID');
        for ($i = 1; $i <= 20; $i++) {
            Dudi::create([
                'user_id' => $admin->id,
                'nama_perusahaan' => 'PT ' . $faker->company(),
                'alamat' => $faker->address(),
                'telepon' => $faker->numerify('08##########'),
                'email' => $faker->unique()->companyEmail(),
                'penanggung_jawab' => $faker->name(),
                'status' => $faker->randomElement(['aktif', 'tidak_aktif']),
            ]);
        }
    }
}
