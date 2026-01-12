<?php

namespace Database\Seeders;

use App\Models\LetterType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LetterTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $letterTypes = [
            [
                'name' => 'Surat Keterangan',
                'code' => 'SK',
                'form_schema' => [
                    ['name' => 'nama_karyawan', 'label' => 'Nama Karyawan', 'type' => 'text', 'required' => true],
                    ['name' => 'jabatan', 'label' => 'Jabatan', 'type' => 'text', 'required' => true],
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
                ],
            ],
            [
                'name' => 'Surat Perintah',
                'code' => 'SP',
                'form_schema' => [
                    ['name' => 'nama_karyawan', 'label' => 'Nama Karyawan', 'type' => 'text', 'required' => true],
                    ['name' => 'jabatan', 'label' => 'Jabatan', 'type' => 'text', 'required' => true],
                    ['name' => 'perihal', 'label' => 'Perihal', 'type' => 'textarea', 'required' => true],
                    ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
                    ['name' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => false],
                ],
            ],
            [
                'name' => 'Surat Permohonan Harga',
                'code' => 'SPH',
                'form_schema' => [
                    ['name' => 'nama_vendor', 'label' => 'Nama Vendor', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_vendor', 'label' => 'Alamat Vendor', 'type' => 'textarea', 'required' => true],
                    ['name' => 'deskripsi_barang', 'label' => 'Deskripsi Barang/Jasa', 'type' => 'textarea', 'required' => true],
                ],
            ],
            [
                'name' => 'Surat Gudang',
                'code' => 'SG',
                'form_schema' => [
                    ['name' => 'jenis_barang', 'label' => 'Jenis Barang', 'type' => 'text', 'required' => true],
                    ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'required' => true],
                    ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'required' => false],
                ],
            ],
        ];

        foreach ($letterTypes as $letterType) {
            LetterType::create($letterType);
        }
    }
}
