# Catatan Praktikum Minggu 4 Pemrograman Web

---

**Mata Kuliah:** SI2514024 — Pemrograman Web (Semester Ganjil 2026/2027)  
**Program Studi:** Sistem Informasi — Institut Teknologi Kalimantan (ITK)  
**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05  
**Peran:** Frontend Developer & UI Designer  
**Dosen Pengampu:** Pak Aidil Saputra Kirsan  
**Asisten Dosen:** Kak Achmad Zaki Zaidan  

---

# I. READ: Analisis Siklus Request Form, Validasi, dan Pengelolaan State

Pada Minggu ke-4 ini, pembelajaran difokuskan pada pengelolaan **State** dalam arsitektur web yang bersifat *stateless* (HTTP), implementasi validasi sisi server menggunakan **Form Request**, penerapan pola **PRG (Post-Redirect-Get)**, pengamanan formulir via **CSRF Token**, serta persistensi filter pencarian dan **Pagination dengan Query String**.

---

### 1. Masalah Mendasar: Mengapa HTTP Bersifat *Stateless*?

HTTP adalah protokol tanpa memori (*stateless*). Server memperlakukan setiap request yang masuk sebagai entitas baru yang sepenuhnya terisolasi tanpa mengingat siapa pengirim request sebelumnya. Untuk membangun aplikasi interaktif seperti LMS, server membutuhkan mekanisme penyimpanan **State**.

Pemilihan lokasi penyimpanan state di Laravel:

| Lokasi State | Durasi / Siklus Hidup | Kapan Digunakan | Konsekuensi & Risiko |
|---|---|---|---|
| **Query String** (`?q=web&page=2`) | Selama URL tersebut diakses | Filter data, pencarian (*search*), paginasi halaman | Terlihat di URL browser pengguna, dapat dimanipulasi manual. |
| **Session Standar** | Selama sesi browser aktif | Identitas login pengguna, status otentikasi | Boros memori server jika diisi dataset besar. |
| **Flash Session** (`session('success')`) | Tepat 1 siklus request berikutnya | Notifikasi/flash message setelah redirect | Otomatis terhapus setelah dibaca sekali oleh browser. |
| **Cookie** | Bergantung durasi kedaluwarsa (*expiry*) | Preferensi tema antarmuka (dark/light) | Disimpan di klien, rawan jika menyimpan data sensitif. |
| **Database** | Permanen | Data transaksi, entitas bisnis utama | Membutuhkan I/O disk basis data. |

> **Aturan Emas:** Filter dan pencarian data **wajib menggunakan Query String**, bukan Session. Menyimpan kata kunci pencarian di session akan merusak fungsi tombol *back* browser, membuat URL tidak dapat dibagikan (*shareable*), dan menyebabkan tab baru merusak filter di tab lama.

---

### 2. Analisis Siklus Form Gagal (Pertanyaan READ Modul)

Ketika pengguna mengirimkan formulir dengan data tidak valid (contoh: SKS = 99 pada form mata kuliah):

1. **Method & Controller Penerima:**  
   Request diterima oleh `CourseController@store` atau `CourseController@update` yang di-typehint dengan Form Request (`StoreCourseRequest`).
2. **Titik Eksekusi Validasi:**  
   Validasi dieksekusi **sebelum baris pertama di dalam controller dijalankan**. Laravel secara otomatis mengeksekusi method `authorize()` dan `rules()` pada *Form Request lifecycle* sebelum controller dipanggil. Jika gagal, exception `ValidationException` dilempar langsung.
3. **Arah Redirect:**  
   Laravel secara otomatis mengalihkan (*redirect*) pengguna kembali ke halaman form sebelumnya (`url()->previous()`). Header HTTP yang dihasilkan adalah `302 Found`.
4. **Sumber Pesan Error (`@error`):**  
   Laravel menaruh pesan kesalahan di *View Error Bag* (`$errors`) dalam flash session. Direktif `@error('sks')` memeriksa apakah terdapat error pada key `'sks'` dan menyediakan variabel `$message` yang berisi teks error spesifik.
5. **Sumber Nilai Lama (`old()`):**  
   Helper `old('sks')` mengambil nilai masukan sebelumnya yang disimpan otomatis oleh Laravel di dalam Flash Session (`_old_input`). Nilai ini hanya bertahan **tepat satu kali request berikutnya** (flash lifetime).
6. **Nama Cookie Session Laravel:**  
   Dapat ditemukan di browser: `DevTools → Application → Cookies`. Standar nama cookie Laravel adalah `kampuslms_session` atau `laravel_session` (terenkripsi) bersama dengan cookie `XSRF-TOKEN`.

---

# II. BREAK: Tujuh Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Bagian ini memetakan 7 eksperimen kegagalan terencana untuk memahami mekanisme pertahanan Laravel:

| # | Skenario yang Dirusak | Prediksi Sebelum Mencoba | Hasil & Konsekuensi Sebenarnya |
|:--:|:---|:---|:---|
| **1** | Menghapus direktif `@csrf` dari formulir penambahan mata kuliah. | Request POST akan ditolak server karena tidak membawa token keamanan yang valid. | Terjadi error **`419 Page Expired`**. Middleware `ValidateCsrfToken` memblokir request sebelum sampai ke controller, melindungi aplikasi dari eksploitasi Cross-Site Request Forgery. |
| **2** | Mengganti `$request->validated()` menjadi `$request->all()` pada controller, lalu mengirimkan field tambahan via cURL. | Seluruh data yang dikirim klien akan langsung masuk ke model Eloquent tanpa melewati filter. | **Celah Mass Assignment kembali terbuka**. Parameter yang tidak terdaftar di formulir dapat lolos jika model tidak memiliki `$fillable` ketat. |
| **3** | Menghapus aturan `exists:users,id` pada field `lecturer_id`, lalu mengirim `lecturer_id = 99999`. | Aplikasi mungkin mencoba menyimpan data dengan ID dosen yang tidak pernah ada di database. | Jika database memiliki Foreign Key, MySQL melempar error `SQLSTATE[23000]: Integrity constraint violation`. Tanpa FK, akan tercipta **data yatim (*orphaned data*)**. |
| **4** | Menghapus validasi `in:draft,active,archived` pada kolom `status`, lalu mengirim `status = superadmin`. | Input status sembarangan akan lolos dari filter validasi Laravel. | Validasi PHP mengizinkan nilai tersebut, tetapi MySQL menolak jika kolom bertipe `ENUM` (`Data truncated for column 'status'`). Jika bertipe `VARCHAR`, data kotor masuk ke database. |
| **5** | Menghapus method `->withQueryString()` pada pemanggilan pagination di controller. | Ketika berpindah ke halaman 2, parameter pencarian yang ada di URL akan hilang. | **Filter pencarian hilang saat ganti halaman**. Pengguna yang mencari "Web" di halaman 1 akan melihat seluruh data campur aduk ketika mengklik tombol Halaman 2. |
| **6** | Mengganti `return redirect()` menjadi `return view()` pada method `store()`, lalu menekan F5 setelah submit form. | Halaman berhasil tampil, namun browser akan meminta konfirmasi pengiriman ulang data jika di-refresh. | Muncul dialog browser **"Confirm Form Resubmission"**. Jika user menekan OK, data mata kuliah akan ter-insert dua kali (duplikasi data). Ini membuktikan mengapa pola PRG mutlak diperlukan. |
| **7** | Menghapus helper `old(...)` dari atribut `value` input HTML form. | Jika form gagal validasi, seluruh isian teks yang sudah diketik pengguna akan hilang bersih. | Form kembali kosong. Pengguna dipaksa mengetik ulang seluruh isian form dari awal hanya karena salah mengisi satu field. User Experience (UX) menjadi sangat buruk. |

---

# III. FIX: Analisis dan Penanganan Kerusakan

Berdasarkan skenario *BREAK*, berikut adalah ringkasan solusi perbaikan standar pada Laravel 12:

1. **Memastikan Token CSRF Terpasang:** Setiap form HTML dengan method selain `GET` wajib menyertakan `@csrf`.
2. **Menggunakan Form Request & `$request->validated()`:** Tidak pernah menggunakan `$request->all()` untuk operasi `create` atau `update`.
3. **Pencegahan Error Update pada Aturan Unique:** Pada operasi update, kode mata kuliah harus mengabaikan ID miliknya sendiri menggunakan `Rule::unique('courses', 'code')->ignore($this->course)`.
4. **Pola PRG (Post-Redirect-Get):** Semua aksi `POST`, `PUT`, `DELETE` wajib mengembalikan `redirect()->route(...)->with('success', ...)`.
5. **Pagination Mempertahankan Parameter URL:** Rantai query pagination selalu diakhiri dengan `->withQueryString()`.
6. **Repopulasi Input:** Seluruh tag `<input>` dilengkapi dengan `value="{{ old('field', $model->field ?? '') }}"`.

---

# IV. BUILD: Implementasi Fitur Validasi, Form Request & State

Pada tahap *Build*, kita mengimplementasikan komponen-komponen berikut pada modul **Mata Kuliah** dan **Pengguna**:

### 1. Pembuatan Form Request Khusus (`StoreCourseRequest`)
Memisahkan logika validasi dari `CourseController` ke kelas dedicated:
```bash
php artisan make:request StoreCourseRequest
php artisan make:request UpdateCourseRequest
```

Contoh implementasi `StoreCourseRequest`:
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Dikelola oleh Gate/Policy di Minggu 7
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status'      => ['required', 'in:draft,active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'       => 'Kode mata kuliah wajib diisi.',
            'code.unique'         => 'Kode mata kuliah sudah terdaftar di sistem.',
            'name.required'       => 'Nama mata kuliah tidak boleh kosong.',
            'sks.between'         => 'Bobot SKS harus berada di antara 1 sampai 6 SKS.',
            'lecturer_id.exists'  => 'Dosen pengampu yang dipilih tidak valid.',
            'status.in'           => 'Status mata kuliah harus salah satu dari: draft, active, archived.',
        ];
    }
}
```

### 2. Penanganan Khusus `UpdateCourseRequest` (Mengabaikan Diri Sendiri)
```php
use Illuminate\Validation\Rule;

public function rules(): array
{
    return [
        'code' => [
            'required',
            'string',
            'max:20',
            Rule::unique('courses', 'code')->ignore($this->route('course')),
        ],
        'name'        => ['required', 'string', 'max:150'],
        'description' => ['nullable', 'string'],
        'sks'         => ['required', 'integer', 'between:1,6'],
        'lecturer_id' => ['required', 'exists:users,id'],
        'status'      => ['required', 'in:draft,active,archived'],
    ];
}
```

### 3. Controller dengan Pola PRG & Flash Message
```php
public function store(StoreCourseRequest $request)
{
    $course = Course::create($request->validated());

    return redirect()
        ->route('courses.index')
        ->with('success', 'Mata kuliah "' . $course->name . '" berhasil ditambahkan.');
}
```

### 4. Search, Filter & Pagination Preserving State
```php
public function index(Request $request)
{
    $courses = Course::query()
        ->with('lecturer')
        ->when($request->filled('q'), function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('code', 'like', '%' . $request->q . '%');
            });
        })
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->latest()
        ->paginate(10)
        ->withQueryString(); // ⚠️ State filter bertahan saat klik pagination

    return view('courses.index', compact('courses'));
}
```

---

# V. CHECKPOINT MINGGU 4 — PERSIAPAN INTERVIEW

Berikut adalah jawaban teknis mendalam untuk seluruh pertanyaan evaluasi Checkpoint Minggu 4:

---

### ❓ 1. Kenapa validasi di JavaScript tidak dianggap keamanan? Peragakan cara melewatinya.

* **Penjelasan Konseptual:**  
  JavaScript berjalan sepenuhnya di lingkungan klien (*client-side browser*). Lingkungan klien berada di bawah kendali penuh pengguna. Validasi JavaScript dan atribut HTML5 (seperti `required`, `min="1"`, `max="6"`) hanya berfungsi sebagai **alat bantu kenyamanan pengguna (*User Experience*)** untuk memberikan feedback instan tanpa perlu menunggu loading request. Ini **bukan sistem keamanan**.
* **Cara Melewatinya (Demonstrasi):**
  1. **Lewat Browser DevTools:** Buka tab *Elements*, cari tag `<input required>`, lalu hapus kata `required` atau ubah tipe datanya. Form akan terkirim dengan field kosong.
  2. **Matikan JavaScript:** Nonaktifkan JavaScript di browser settings.
  3. **Lewat cURL / HTTP Client (Postman/Insomnia):** Penyerang mengirimkan HTTP Request langsung ke URL endpoint backend tanpa membuka browser sama sekali:
     ```bash
     curl -X POST http://127.0.0.1:8000/courses \
       -H "X-CSRF-TOKEN: <token>" \
       -d "name=" -d "sks=99"
     ```
* **Kesimpulan:** Keamanan sejati **hanya ada di Backend (Server-Side Validation)** via Form Request Laravel.

---

### ❓ 2. Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?

* **Perbedaan Fundamental:**
  * `$request->all()` mengembalikan **seluruh data mentah** yang dikirimkan oleh klien di dalam payload HTTP request, termasuk parameter-parameter liar yang disisipkan secara sengaja oleh penyerang.
  * `$request->validated()` **hanya mengembalikan data yang secara eksplisit didefinisikan dan lolos aturan pemeriksaan** pada method `rules()` di Form Request.
* **Mengapa Jauh Lebih Aman?**
  * Jika ada penyerang menyelipkan parameter `role=admin` atau `is_approved=1` pada request pembuatan data, `$request->validated()` secara otomatis membuang (*whitelist filtering*) parameter tersebut karena tidak terdaftar pada aturan validasi.
  * Ini merupakan pertahanan lini pertama yang mencegah celah keamanan **Mass Assignment Vulnerability**.

---

### ❓ 3. Jelaskan pola PRG (Post-Redirect-Get). Apa yang terjadi kalau `store` mengembalikan view?

* **Definisi Pola PRG:**  
  PRG adalah pola arsitektur standar pengembangan web:
  1. **POST:** Browser mengirim data form ke server via method `POST` (atau `PUT`/`DELETE`).
  2. **REDIRECT:** Setelah data berhasil diproses/disimpan, server **TIDAK** menampilkan view secara langsung, melainkan merespons dengan header HTTP redirect (`302 Redirect`) menuju route tujuan (misal halaman `index` atau `show`).
  3. **GET:** Browser secara otomatis menjalankan request `GET` ke URL tujuan baru tersebut untuk menampilkan halaman hasil.
* **Apa yang Terjadi Jika `store` Mengembalikan View Langsung (`return view(...)`)?**
  * Status request terakhir yang tercatat di browser tetap berupa request `POST`.
  * Jika pengguna menekan tombol **F5 / Refresh** atau tombol **Back**, browser akan mencoba mengirimkan ulang request POST tersebut (*Form Resubmission*).
  * Dampak nyata: Terjadi **duplikasi data di database** (mata kuliah tersimpan dua kali, atau transaksi belanja terproses ganda).

---

### ❓ 4. Kenapa filter pencarian sebaiknya di query string, bukan session? Beri satu skenario yang rusak kalau dipindah ke session.

* **Alasan Desain:**  
  Filter dan pencarian adalah representasi dari data yang sedang dilihat (*view state*), bukan identitas pengguna. Menyimpan filter di **Query String** (`?q=basis+data&status=active`) menjaga prinsip dasar arsitektur web RESTful dan *idempotency*.
* **Keuntungan Query String:**
  1. **Shareable URL:** Pengguna bisa menyalin tautan dan mengirimkannya ke orang lain, dan orang tersebut akan melihat hasil pencarian yang sama persis.
  2. **Fungsi Tombol Back/Forward Berjalan Normal:** Riwayat navigasi browser bekerja sesuai harapan.
  3. **Multi-Tab Friendly:** Membuka pencarian berbeda di dua tab tidak saling merusak.
* **Skenario Nyata yang Rusak Jika Filter Disimpan di Session:**
  * Dosen membuka Tab 1 untuk mencari mata kuliah *"Pemrograman Web"*. Filter tersimpan di Session: `session(['filter_q' => 'Pemrograman Web'])`.
  * Dosen kemudian membuka Tab 2 untuk mencari mata kuliah *"Kalkulus"*. Session tertimpa menjadi: `session(['filter_q' => 'Kalkulus'])`.
  * Saat dosen kembali ke Tab 1 dan mengklik tombol "Halaman 2", data yang muncul tiba-tiba berubah menjadi halaman 2 dari mata kuliah *Kalkulus*! Tab 1 rusak karena datanya bergantung pada session global.

---

### ❓ 5. Apa fungsi `@csrf`? Serangan apa yang dicegahnya, dan bagaimana serangan itu bekerja?

* **Fungsi `@csrf`:**  
  Menghasilkan elemen input tersembunyi (`<input type="hidden" name="_token" value="...">`) yang berisi token acak rahasia yang dihasilkan oleh server dan terikat pada sesi pengguna saat ini.
* **Serangan yang Dicegah:**  
  **Cross-Site Request Forgery (CSRF)** — serangan di mana situs web berbahaya mengeksploitasi sesi otentikasi aktif milik korban untuk mengeksekusi aksi yang tidak diinginkan pada aplikasi target.
* **Bagaimana Serangan CSRF Bekerja Tanpa `@csrf`?**
  1. Pengguna (Admin KampusLMS) sedang login di `kampuslms.test`. Cookie session admin tersimpan aktif di browser.
  2. Admin membuka tab baru dan mengunjungi situs web jebakan (misal: `situs-hadiah.test`).
  3. Di situs jebakan tersebut terdapat skrip tersembunyi yang otomatis men-submit form ke:
     `POST http://kampuslms.test/courses/10/destroy`
  4. Karena form dikirim dari browser admin, browser secara otomatis menyertakan cookie session admin yang sah.
  5. Jika KampusLMS tidak memverifikasi token CSRF, server akan mengira request tersebut sah atas kehendak admin, dan mata kuliah ID 10 akan terhapus secara otomatis!
* **Dengan Adanya `@csrf`:** Situs jahat tidak bisa membaca token rahasia yang ada di sesi KampusLMS (karena terhalang aturan *Same-Origin Policy*), sehingga request penyerang ditolak langsung dengan status **`419 Page Expired`**.

---

### ❓ 6. Kenapa aturan `unique` pada update perlu `ignore()`?

* **Masalah Tanpa `ignore()`:**  
  Ketika mengedit data mata kuliah (misal mengubah nama dari *"Basis Data"* menjadi *"Basis Data Lanjut"* tanpa mengubah kodenya `SI101`):
  * Controller menjalankan validasi: `unique:courses,code`.
  * Validator Laravel melakukan query ke database: *"Apakah ada baris di tabel `courses` yang memiliki `code = 'SI101'`?"*
  * Database menjawab: *"Ada!"* (yaitu baris mata kuliah itu sendiri yang sedang diedit).
  * Akibatnya: Form ditolak dengan error *"Kode mata kuliah ini sudah dipakai"*, padahal pengguna sama sekali tidak berniat mengubah kode tersebut.
* **Solusi dengan `ignore()`:**  
  Dengan menambahkan `Rule::unique('courses', 'code')->ignore($course->id)`:
  Validator Laravel akan menambahkan klausul SQL:
  `SELECT count(*) FROM courses WHERE code = 'SI101' AND id != [id_saat_ini]`
  Sehingga sistem mengecualikan baris mata kuliah yang sedang diedit dan validasi berjalan mulus.

---

*Disusun untuk Dokumentasi & Persiapan Evaluasi Minggu 4 — KampusLMS Kelompok 05*
