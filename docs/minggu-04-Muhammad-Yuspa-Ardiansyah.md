# Catatan Minggu 4 PROWEB

Nama: Muhammad Yuspa Ardiansyah
NIM: 10241052

---

# READ

1. Request form tambah mata kuliah diterima oleh method `store()`pada `CourseController`. `Route POST /courses` dibuat oleh `Route::resource('courses', CourseController::class)`.
   
2. Validasi dilakukan **sebelum baris pertama pada controller dieksekusi**. Laravel secara otomatis menjalankan method `authorize()` dan `rules()` dalam *Form Request lifecycle* terlebih dahulu sebelum controller dipanggil. Apabila validasi gagal, Laravel akan langsung melempar exception `ValidationException`.

3.  Laravel secara otomatis melakukan *redirect* kembali ke halaman form sebelumnya melalui `url()->previous()`. Response HTTP yang diberikan menggunakan status `302 Found`.

4. Laravel menyimpan informasi kesalahan ke dalam *View Error Bag* (`$errors`) melalui flash session. Direktif `@error('sks')` digunakan untuk mengecek apakah terdapat error pada field `'sks'`, sekaligus menyediakan variabel `$message` yang berisi pesan kesalahan tersebut.

5. Helper `old('sks')` mengambil nilai input sebelumnya yang secara otomatis disimpan Laravel dalam Flash Session dengan key `_old_input`. Nilai tersebut hanya tersedia **untuk satu request berikutnya** atau selama masa flash.

6. Nama cookie session dapat diperiksa melalui browser pada menu: `DevTools → Application → Cookies`. Nama cookie yang umum digunakan Laravel adalah `kampuslms_session` atau `laravel_session` (dalam kondisi terenkripsinyaa), bersama cookie `XSRF-TOKEN`.

---

# BREAK

| # | Skenario yang Dirusak | Prediksi Sebelum Mencoba | Hasil & Konsekuensi Sebenarnya |
|:--:|:---|:---|:---|
| **1** | Menghapus direktif `@csrf` dari form tambah mata kuliah. | Request POST diperkirakan akan ditolak server karena tidak menyertakan token keamanan yang sah. | Terjadi error **`419 Page Expired`**. Middleware `ValidateCsrfToken` menghentikan request sebelum mencapai controller dan memberikan perlindungan terhadap serangan Cross-Site Request Forgery. |
| **2** | Mengubah `$request->validated()` menjadi `$request->all()` pada controller, kemudian mengirim field tambahan menggunakan cURL. | Semua data yang dikirim dari client akan diteruskan langsung ke model tanpa penyaringan hasil validasi. | **Kerentanan Mass Assignment terbuka kembali**. Parameter yang tidak tercantum pada form dapat ikut diproses apabila model tidak memiliki konfigurasi `$fillable` yang ketat. |
| **3** | Menghapus aturan `exists:users,id` pada field `lecturer_id`, kemudian mengirim `lecturer_id = 99999`. | Sistem berpotensi mencoba menyimpan ID dosen yang sebenarnya tidak terdapat di database. | Jika database menggunakan Foreign Key, MySQL dapat menghasilkan error `SQLSTATE[23000]: Integrity constraint violation`. Jika tidak ada FK, data dapat menjadi **data yatim (*orphaned data*)**. |
| **4** | Menghapus aturan `in:draft,active,archived` pada field `status`, lalu mengirim `status = superadmin`. | Nilai status apa pun akan dapat melewati penyaringan validasi Laravel. | Validasi PHP akan menerima nilai tersebut, tetapi MySQL akan menolaknya apabila kolom memakai `ENUM` (`Data truncated for column 'status'`). Jika kolom berupa `VARCHAR`, nilai yang tidak sesuai dapat masuk ke database. |
| **5** | Menghilangkan `->withQueryString()` dari pemanggilan pagination pada controller. | Saat pengguna menuju halaman berikutnya, parameter pencarian pada URL diperkirakan tidak ikut terbawa. | **Filter pencarian hilang ketika berpindah halaman**. Pengguna yang mencari "Web" pada halaman pertama dapat melihat data tanpa filter lagi ketika membuka halaman kedua. |
| **6** | Mengganti `return redirect()` dengan `return view()` pada method `store()`, lalu melakukan refresh dengan F5 setelah submit form. | Halaman tetap dapat ditampilkan, tetapi browser akan meminta konfirmasi pengiriman ulang data saat direfresh. | Muncul dialog browser **"Confirm Form Resubmission"**. Jika pengguna memilih OK, data mata kuliah dapat tersimpan kembali dan menyebabkan **duplikasi data**. Hal ini menunjukkan pentingnya penerapan pola PRG. |
| **7** | Menghapus helper `old(...)` dari atribut `value` pada input HTML. | Ketika validasi gagal, data yang sebelumnya sudah dimasukkan pengguna akan hilang dari form. | Form akan kembali kosong dan pengguna harus mengulang seluruh pengisian hanya karena terdapat kesalahan pada satu field. Hal ini membuat pengalaman pengguna atau **User Experience (UX)** menjadi buruk. |

---

# CHECKPOINT MINGGU 4
---

# Checkpoint Minggu 4 — KampusLMS

## 1. Kenapa validasi di JavaScript / frontend tidak dianggap keamanan?
Validasi di frontend hanya membantu pengguna, tetapi bukan pengaman utama karena pengguna bisa mengubah atau melewatinya. Validasi yang menjaga data harus dilakukan di server menggunakan Form Request Laravel.

### Analoginya gini  ;
Frontend seperti **pagar rumah**. Pagar bisa dibuka atau dilewati.

Validasi server seperti **satpam di pintu**. Data tetap diperiksa sebelum diproses.

`resources/views/courses/create.blade.php`

Contoh validasi di input:

```html
<input
    type="number"
    min="1"
    max="6"
>
```

Nilai `min="1"` dan `max="6"` hanya berada di sisi form.

Validasi server berada di:

`app/Http/Requests/StoreCourseRequest.php`

```php
'sks' => ['required', 'integer', 'between:1,6'],
```

Artinya nilai SKS wajib diisi, harus berupa bilangan bulat, dan hanya boleh 1 sampai 6.

---

## 2. Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?

`$request->validated()` hanya mengambil data yang **lolos aturan validasi Form Request**.

Sedangkan `$request->all()` mengambil **semua input yang dikirim pengguna**.

### Analogi

`validated()` seperti **petugas sortir**.

Barang yang sudah lolos pemeriksaan saja yang masuk.

`app/Http/Controllers/CourseController.php`

```php
public function store(StoreCourseRequest $request)
{
    $data = $request->validated();

    Course::create($data);
}
```

`$request->validated()` mengambil data yang sudah dinyatakan valid oleh `StoreCourseRequest`.

Form Request:

`app/Http/Requests/StoreCourseRequest.php`

```php
public function rules(): array
{
    return [
        'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
        'name' => ['required', 'string', 'max:150'],
        'description' => ['nullable', 'string'],
        'sks' => ['required', 'integer', 'between:1,6'],
        'lecturer_id' => ['required', 'exists:users,id'],
        'status' => ['required', 'in:draft,active,archived'],
    ];
}
```

---

## 3. Jelaskan pola PRG. Apa yang terjadi kalau `store` mengembalikan view?

PRG adalah:

**POST → Redirect → GET**

Setelah data berhasil disimpan, server melakukan redirect ke halaman berikutnya.

Tujuannya supaya saat pengguna menekan refresh, browser tidak mengirim ulang request POST yang sama.

### Analogi

Seperti **setelah selesai membayar, kasir memberi nomor antrean baru**.

Saat nomor antrean dibuka lagi, kita tidak membayar untuk kedua kalinya.

`app/Http/Controllers/CourseController.php`

```php
public function store(StoreCourseRequest $request)
{
    $data = $request->validated();

    Course::create($data);

    return redirect()
        ->route('courses.index')
        ->with('success', 'Mata kuliah berhasil ditambahkan.');
}
```

Bagian:

```php
return redirect()
    ->route('courses.index');
```

menunjukkan pola PRG.

Kalau `store()` langsung mengembalikan `view()`, kemudian browser di-refresh, browser dapat meminta pengiriman ulang POST sehingga data berpotensi tersimpan lagi.

---

## 4. Kenapa filter pencarian sebaiknya di query string, bukan session?

Search dan filter sebaiknya disimpan di **query string** karena state tersebut cocok mengikuti URL.

Contoh:

```text
/courses?q=sistem&status=active
```

Dengan begitu filter tetap terlihat di URL dan dapat ikut terbawa ketika berpindah halaman.

### Analogi

Query string seperti **catatan yang ditempel di alamat rumah**.

Siapa pun yang melihat alamat itu tahu pencarian dan filternya.

Session seperti **catatan pribadi di dalam satu tas**.

### Masalah kalau filter disimpan di session

Misalnya pengguna membuka:

**Tab 1**

```text
/courses
```

lalu memilih:

```text
status=active
```

Kemudian membuka **Tab 2** dan memilih:

```text
status=draft
```

Kalau filter disimpan di session, kedua tab memakai penyimpanan session yang sama. Perubahan dari satu tab dapat memengaruhi tab lainnya.

Karena itu search dan filter lebih cocok menggunakan query string.

`app/Http/Controllers/CourseController.php`

```php
$validated = $request->validate([
    'q' => ['nullable', 'string', 'max:100'],
    'status' => ['nullable', 'in:all,draft,active,archived'],
]);

$keyword = $validated['q'] ?? null;
$status = $validated['status'] ?? 'all';
```

Kemudian:

```php
$courses = $query->paginate(15)->withQueryString();
```

`withQueryString()` membuat parameter seperti `q` dan `status` tetap terbawa ke link pagination.

`resources/views/courses/index.blade.php`

```blade
<form action="{{ route('courses.index') }}" method="GET">
    <input type="text" name="q" value="{{ request('q') }}">
</form>
```

`method="GET"` membuat pencarian dikirim sebagai query string.

---

## 5. Apa fungsi `@csrf`? Serangan apa yang dicegahnya?

`@csrf` membuat token keamanan pada form.

Token tersebut digunakan Laravel untuk memeriksa bahwa request POST / perubahan data berasal dari form yang benar.

Fungsi utamanya adalah membantu mencegah **Cross-Site Request Forgery (CSRF)**.

### Analogi

`@csrf` seperti **tiket khusus**.

Orang yang tidak punya tiket yang benar tidak boleh masuk.

`resources/views/courses/create.blade.php`

```blade
<form action="{{ route('courses.store') }}" method="POST">
    @csrf
```

`@csrf` menghasilkan token CSRF di dalam form.

Saat request dikirim, Laravel memeriksa token tersebut.

Kalau token tidak sesuai atau tidak ada, request dapat ditolak dengan:

```text
419 Page Expired
```

---

## 6. Kenapa aturan `unique` pada update perlu `ignore()`?

Saat STORE, kode harus unik terhadap semua mata kuliah.

Saat UPDATE, kode milik mata kuliah yang sedang diedit harus boleh tetap dipakai.

### Analogi

Kita sedang mengganti isi **rumah nomor 5**.

Kalau rumah nomor 5 tetap memakai nomor 5, itu bukan duplikat.

Yang bermasalah adalah kalau ada **rumah lain** yang juga memakai nomor 5.

`app/Http/Requests/UpdateCourseRequest.php`

```php
use Illuminate\Validation\Rule;
```

Kemudian:

```php
'code' => [
    'required',
    'string',
    'max:20',
    Rule::unique('courses', 'code')->ignore($this->course),
],
```

`ignore($this->course)` membuat Laravel mengabaikan data course yang sedang diedit ketika memeriksa keunikan kode.

---