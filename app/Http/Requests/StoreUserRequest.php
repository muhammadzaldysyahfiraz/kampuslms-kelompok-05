<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Ganti dengan Policy pada minggu 7.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'role' => [
                'required',
                Rule::in(['admin', 'dosen', 'mahasiswa']),
            ],
            'nim_nip' => [
                'nullable',
                'string',
                'max:255',
                'unique:users,nim_nip',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',

            'role.required' => 'Peran (role) pengguna wajib dipilih.',
            'role.in' => 'Peran pengguna harus salah satu dari: admin, dosen, atau mahasiswa.',

            'nim_nip.unique' => 'NIM atau NIP ini sudah digunakan oleh pengguna lain.',
            'nim_nip.max' => 'NIM atau NIP maksimal 255 karakter.',
        ];
    }
}
