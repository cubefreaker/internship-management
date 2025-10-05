<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dudi;
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
        $guruCounter = 1;
        User::factory()->count(10)
            ->state(function () use ($userFaker, &$guruCounter) {
                $name = $userFaker->name();
                $email = "guru{$guruCounter}@mail.com";
                $guruCounter++;
                return [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'guru',
                    'alamat' => $userFaker->address(),
                    'telepon' => $userFaker->numerify('08##########'),
                    'nip' => $userFaker->numerify('##########'),
                    'email_verified_at' => now(),
                ];
            })
            ->create();

        // Create 289 siswa users (id_ID locale) dan record siswa
        $siswaCounter = 1;
        User::factory()->count(289)
            ->state(function () use ($userFaker, &$siswaCounter) {
                $name = $userFaker->name();
                $email = "siswa{$siswaCounter}@mail.com";
                $siswaCounter++;
                return [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'siswa',
                    'alamat' => $userFaker->address(),
                    'telepon' => $userFaker->numerify('08##########'),
                    'nis' => $userFaker->numerify('##########'),
                    'kelas' => $userFaker->randomElement(['X', 'XI', 'XII']),
                    'jurusan' => $userFaker->randomElement(['RPL', 'TKJ']),
                    'email_verified_at' => now(),
                ];
            })
            ->create();

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
