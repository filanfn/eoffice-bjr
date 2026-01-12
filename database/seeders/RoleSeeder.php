<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\LetterType;
use App\Models\LetterRequest;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $admin->update(['role' => 'admin']); // Ensure role is set if user existed

        // 2. Create Regular User
        $user = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
        $user->update(['role' => 'user']); // Ensure role is set

        // 3. Create Sample Letter Types if none
        if (LetterType::count() === 0) {
            $sk = LetterType::create([
                'name' => 'Surat Keterangan',
                'code' => 'SK',
                'form_schema' => [
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true]
                ]
            ]);

            $sp = LetterType::create([
                'name' => 'Surat Perintah',
                'code' => 'SP',
                'form_schema' => [
                    ['name' => 'tugas', 'label' => 'Tugas', 'type' => 'textarea', 'required' => true],
                    ['name' => 'deadline', 'label' => 'Deadline', 'type' => 'date', 'required' => true]
                ]
            ]);
        } else {
            $sk = LetterType::first();
        }

        // 4. Create Sample Requests for Staff
        if (LetterRequest::where('user_id', $user->id)->count() === 0) {
            // Pending
            LetterRequest::create([
                'user_id' => $user->id,
                'letter_type_id' => $sk->id,
                'status' => 'pending',
                'payload_data' => ['keperluan' => 'Pengajuan kredit bank'],
            ]);

            // Completed
            LetterRequest::create([
                'user_id' => $user->id,
                'letter_type_id' => $sk->id,
                'status' => 'completed',
                'letter_number' => '001/01/BJR/2026/SK',
                'payload_data' => ['keperluan' => 'Visa application'],
            ]);
        }
    }
}
