<?php // Menandai bahwa file ini menggunakan PHP.

namespace App\Http\Requests; // Menentukan namespace class request.

use Illuminate\Foundation\Http\FormRequest; // Mengimpor FormRequest Laravel.
use Illuminate\Validation\Rule; // Mengimpor Rule agar kita bisa menggunakan Rule::unique().

class UpdateCourseRequest extends FormRequest // Membuat request khusus untuk proses UPDATE mata kuliah.
{
    public function authorize(): bool // Menentukan apakah request update diperbolehkan.
    {
        // TODO: Ganti dengan Policy pada minggu 7. // Hak akses nantinya akan diperiksa menggunakan Policy.
        return true; // Untuk Week 4 sementara request diperbolehkan.
    }

    public function rules(): array // Menentukan aturan validasi untuk proses update.
    {
        return [ // Mengembalikan aturan validasi.
            'code' => [ // Membuka aturan untuk field code.
                'required', // Kode wajib diisi.
                'string', // Kode harus berupa teks.
                'max:20', // Kode maksimal 20 karakter.
                Rule::unique('courses', 'code')->ignore($this->course), // Kode harus unik tetapi data course yang sedang diedit tidak dihitung.
            ],
            'name' => ['required', 'string', 'max:150'], // Nama wajib, berupa teks, maksimal 150 karakter.
            'description' => ['nullable', 'string'], // Deskripsi boleh kosong tetapi jika diisi harus teks.
            'sks' => ['required', 'integer', 'between:1,6'], // SKS wajib, bilangan bulat, dan 1 sampai 6.
            'lecturer_id' => ['required', 'exists:users,id'], // Dosen wajib ada dan ID-nya harus ditemukan pada users.
            'status' => ['required', 'in:draft,active,archived'], // Status wajib dan hanya boleh tiga nilai yang ditentukan.
        ];
    }

    public function messages(): array // Menentukan pesan error yang mudah dipahami pengguna.
    {
        return [ // Mengembalikan pesan validasi.
            'code.required' => 'Kode mata kuliah wajib diisi.', // Pesan jika kode kosong.
            'code.string' => 'Kode mata kuliah harus berupa teks.', // Pesan jika kode bukan teks.
            'code.max' => 'Kode mata kuliah maksimal 20 karakter.', // Pesan jika kode terlalu panjang.
            'code.unique' => 'Kode mata kuliah ini sudah digunakan oleh mata kuliah lain.', // Pesan jika kode bentrok dengan course lain.

            'name.required' => 'Nama mata kuliah wajib diisi.', // Pesan jika nama kosong.
            'name.string' => 'Nama mata kuliah harus berupa teks.', // Pesan jika nama bukan teks.
            'name.max' => 'Nama mata kuliah maksimal 150 karakter.', // Pesan jika nama terlalu panjang.

            'description.string' => 'Deskripsi mata kuliah harus berupa teks.', // Pesan jika deskripsi bertipe salah.

            'sks.required' => 'SKS wajib diisi.', // Pesan jika SKS kosong.
            'sks.integer' => 'SKS harus berupa angka bulat.', // Pesan jika SKS bukan bilangan bulat.
            'sks.between' => 'SKS harus antara 1 sampai 6.', // Pesan jika SKS di luar batas.

            'lecturer_id.required' => 'Dosen wajib dipilih.', // Pesan jika dosen tidak dipilih.
            'lecturer_id.exists' => 'Dosen yang dipilih tidak ditemukan.', // Pesan jika ID dosen tidak ada.

            'status.required' => 'Status mata kuliah wajib dipilih.', // Pesan jika status kosong.
            'status.in' => 'Status hanya boleh draft, active, atau archived.', // Pesan jika status tidak sesuai pilihan.
        ];
    }
}
