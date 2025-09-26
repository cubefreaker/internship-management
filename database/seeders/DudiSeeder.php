<?php

namespace Database\Seeders;

use App\Models\Dudi;
use App\Models\User;
use Illuminate\Database\Seeder;

class DudiSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'admin@smk1sby.sch.id'],
            [
                'name' => 'Admin Sistem',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Sample DUDI data
        $dudiData = [
            [
                'nama_perusahaan' => 'PT Kreatif Teknologi',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta',
                'telepon' => '021-12345678',
                'email' => 'info@kreatiftek.com',
                'penanggung_jawab' => 'Andi Wijaya',
                'status' => 'aktif',
            ],
            [
                'nama_perusahaan' => 'CV Digital Solusi',
                'alamat' => 'Jl. Sudirman No. 45, Surabaya',
                'telepon' => '031-87654321',
                'email' => 'contact@digitalsolusi.com',
                'penanggung_jawab' => 'Sari Dewi',
                'status' => 'aktif',
            ],
            [
                'nama_perusahaan' => 'PT Inovasi Mandiri',
                'alamat' => 'Jl. Diponegoro No. 78, Surabaya',
                'telepon' => '031-5553456',
                'email' => 'hr@inovasimandiri.co.id',
                'penanggung_jawab' => 'Budi Santoso',
                'status' => 'tidak_aktif',
            ],
            [
                'nama_perusahaan' => 'PT Teknologi Maju',
                'alamat' => 'Jl. HR Rasuna Said No. 12, Jakarta',
                'telepon' => '021-33445566',
                'email' => 'info@tekmaju.com',
                'penanggung_jawab' => 'Lisa Permata',
                'status' => 'aktif',
            ],
            [
                'nama_perusahaan' => 'CV Solusi Digital Prima',
                'alamat' => 'Jl. Gatot Subroto No. 88, Bandung',
                'telepon' => '022-7788990',
                'email' => 'contact@sdprima.com',
                'penanggung_jawab' => 'Rahmat Hidayat',
                'status' => 'aktif',
            ],
            [
                'nama_perusahaan' => 'PT Global Tech Indonesia',
                'alamat' => 'Jl. Thamrin No. 200, Jakarta',
                'telepon' => '021-9988776',
                'email' => 'info@globaltech.id',
                'penanggung_jawab' => 'Dewi Kartika',
                'status' => 'tidak_aktif',
            ],
        ];

        foreach ($dudiData as $data) {
            Dudi::create([
                'user_id' => $user->id,
                'nama_perusahaan' => $data['nama_perusahaan'],
                'alamat' => $data['alamat'],
                'telepon' => $data['telepon'],
                'email' => $data['email'],
                'penanggung_jawab' => $data['penanggung_jawab'],
                'status' => $data['status'],
            ]);
        }
    }
}
