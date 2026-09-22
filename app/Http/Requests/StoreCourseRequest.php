<?php // Menandai bahwa file ini menggunakan PHP.

namespace App\Http\Requests; // Menentukan namespace tempat class request ini berada.

use Illuminate\Foundation\Http\FormRequest; // Mengimpor class FormRequest Laravel untuk membuat validasi terpisah.

class StoreCourseRequest extends FormRequest // Membuat request khusus untuk proses STORE/tambah mata kuliah.
{
    public function authorize(): bool // Menentukan apakah request ini diizinkan masuk ke proses validasi.
    {
        // TODO: Ganti dengan Policy pada minggu 7. // Catatan bahwa pengecekan hak akses akan dipindahkan ke Policy pada Week 7.
        return true; // Untuk Week 4 sementara semua request diizinkan.
    }

    public function rules(): array // Menentukan aturan validasi untuk data yang dikirim.
    {
        return [ // Mengembalikan seluruh aturan validasi dalam bentuk array.
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'], // Kode wajib, teks, maksimal 20 karakter, dan harus unik.
            'name' => ['required', 'string', 'max:150'], // Nama wajib, berupa teks, dan maksimal 150 karakter.
            'description' => ['nullable', 'string'], // Deskripsi boleh kosong, tetapi jika diisi harus berupa teks.
            'sks' => ['required', 'integer', 'between:1,6'], // SKS wajib, harus bilangan bulat, dan nilainya 1 sampai 6.
            'lecturer_id' => ['required', 'exists:users,id'], // Dosen wajib dipilih dan ID-nya harus benar-benar ada di tabel users.
            'status' => ['required', 'in:draft,active,archived'], // Status wajib dan hanya boleh salah satu dari tiga nilai tersebut.
        ];
    }

    public function messages(): array // Menentukan pesan error sendiri dalam bahasa Indonesia.
    {
        return [ // Mengembalikan kumpulan pesan validasi.
            'code.required' => 'Kode mata kuliah wajib diisi.', // Pesan ketika kode tidak diisi.
            'code.string' => 'Kode mata kuliah harus berupa teks.', // Pesan ketika kode bukan teks.
            'code.max' => 'Kode mata kuliah maksimal 20 karakter.', // Pesan ketika kode terlalu panjang.
            'code.unique' => 'Kode mata kuliah ini sudah digunakan.', // Pesan ketika kode sudah dipakai mata kuliah lain.

            'name.required' => 'Nama mata kuliah wajib diisi.', // Pesan ketika nama tidak diisi.
            'name.string' => 'Nama mata kuliah harus berupa teks.', // Pesan ketika nama bukan teks.
            'name.max' => 'Nama mata kuliah maksimal 150 karakter.', // Pesan ketika nama terlalu panjang.

            'description.string' => 'Deskripsi mata kuliah harus berupa teks.', // Pesan ketika deskripsi diisi dengan tipe yang salah.

            'sks.required' => 'SKS wajib diisi.', // Pesan ketika SKS tidak diisi.
            'sks.integer' => 'SKS harus berupa angka bulat.', // Pesan ketika SKS bukan bilangan bulat.
            'sks.between' => 'SKS harus antara 1 sampai 6.', // Pesan ketika SKS di luar rentang 1–6.

            'lecturer_id.required' => 'Dosen wajib dipilih.', // Pesan ketika dosen tidak dipilih.
            'lecturer_id.exists' => 'Dosen yang dipilih tidak ditemukan.', // Pesan ketika ID dosen tidak ada di database.

            'status.required' => 'Status mata kuliah wajib dipilih.', // Pesan ketika status tidak dikirim.
            'status.in' => 'Status hanya boleh draft, active, atau archived.', // Pesan ketika status bukan nilai yang diperbolehkan.
        ];
    }
}