
# Catatan Praktikum Minggu 3

**Mata Kuliah:** Pemrograman Web

**Nama:** Nova Reskianti

**NIM:** 10241058

---

## READ: Database dan CRUD

### 1. Redraw ERD



---

### 2. Menentukan `onDelete`

`onDelete` digunakan untuk menentukan apa yang terjadi pada data yang memiliki foreign key ketika data induknya dihapus.

- `cascade` digunakan ketika data anak boleh ikut terhapus bersama data induk.
- `restrict` digunakan untuk mencegah data induk dihapus jika masih digunakan oleh data lain.
- `null` digunakan jika foreign key boleh menjadi `NULL` ketika data induk dihapus.

Pada `courses.lecturer_id`, digunakan `restrictOnDelete()` karena mata kuliah masih memiliki hubungan dengan dosen.

---

### 3. Jika Dosen Dihapus

Jika dosen yang masih digunakan oleh sebuah mata kuliah dihapus, maka seharusnya mata kuliah tersebut tidak ikut terhapus.

Oleh karena itu, `courses.lecturer_id` menggunakan `restrictOnDelete()`. Dengan aturan ini, dosen tidak dapat dihapus selama masih digunakan oleh data `courses`.

---

### 4. Alasan `grades.submission_id` Harus Unik

`submission_id` pada tabel `grades` harus unik karena satu submission hanya boleh memiliki satu nilai.

Jika tidak menggunakan `unique`, satu submission dapat memiliki lebih dari satu data nilai sehingga menyebabkan data nilai menjadi tidak konsisten.

---

## BREAK: Rusak dengan sengaja

| # | Yang dirusak | Prediksi Anda sebelum mencoba | Hasil/Error sebenarnya |
|---|---|---|---|
| 1 | Menghapus unique composite pada `course_user` | Mahasiswa dapat mendaftar mata kuliah yang sama lebih dari satu kali. | Database tidak lagi mencegah kombinasi `course_id` dan `user_id` yang sama sehingga data pendaftaran ganda dapat dibuat. |
| 2 | Menambahkan `role` ke `$fillable` pada model `User` | Field `role` dapat diisi melalui mass assignment. | Jika request mengirim `role=admin`, nilai tersebut dapat ikut diproses dan berisiko membuat pengguna biasa menjadi admin. |
| 3 | Mengganti `$fillable` dengan `$guarded = []` | Semua atribut model dapat diisi melalui mass assignment. | Field yang seharusnya dilindungi, seperti `role`, dapat ikut diisi dari request. |
| 4 | Mengosongkan method `down()` | Migration tidak dapat melakukan rollback dengan benar. | `php artisan migrate:refresh` dapat gagal atau struktur database tidak dikembalikan seperti kondisi sebelumnya. |
| 5 | Mengubah `lecturer_id` dari `restrict` menjadi `cascade` | Penghapusan dosen dapat menghapus mata kuliah yang terkait. | Saat dosen dihapus, data `courses` yang menggunakan dosen tersebut juga dapat ikut terhapus. |

### Contoh Pengujian Mass Assignment

Perintah yang dapat digunakan:

```bash
php artisan tinker
````

Kemudian:

```php
App\Models\User::create([
    'name' => 'Penyusup',
    'email' => 'x@x.test',
    'password' => 'rahasia123',
    'role' => 'admin',
]);

App\Models\User::where('email', 'x@x.test')->value('role');
```

Jika `role` masuk ke `$fillable`, nilai `admin` dapat ikut disimpan melalui mass assignment.

---


## Checkpoint Minggu 3

### 1. Tunjukkan migration yang telah dibuat dan jelaskan constraint-nya.

Migration dibuat untuk membentuk struktur database sesuai dengan ERD.

Constraint yang digunakan meliputi:

* Primary key untuk identitas setiap data.
* Foreign key untuk menghubungkan tabel.
* Unique untuk mencegah data duplikat.
* Unique composite untuk mencegah kombinasi data tertentu muncul lebih dari satu kali.
* `onDelete` untuk menentukan perilaku ketika data induk dihapus.

---

### 2. Mengapa `course_user` menggunakan unique composite?

`course_user` menggunakan unique composite:

```php
$table->unique(['course_id', 'user_id']);
```

Karena kombinasi satu mahasiswa dan satu mata kuliah hanya boleh muncul satu kali.

Jika constraint tersebut dihapus, mahasiswa dapat memiliki data pendaftaran yang sama lebih dari satu kali.

---

### 3. Apa itu mass assignment dan bagaimana cara melindunginya?

Mass assignment adalah proses mengisi beberapa atribut model sekaligus menggunakan array.

Contohnya:

```php
User::create($request->all());
```

Cara tersebut berisiko karena semua data dari request dapat ikut diproses.

Perlindungannya dilakukan dengan `$fillable` dan hanya memasukkan field yang memang boleh diisi.

---

### 4. Mengapa `role` tidak boleh dimasukkan ke `$fillable`?

`role` menentukan hak akses pengguna sehingga tidak boleh dikendalikan langsung melalui input pengguna.

Jika `role` dimasukkan ke `$fillable`, pengguna dapat mencoba mengirim:

```text
role=admin
```

Hal tersebut dapat menyebabkan privilege escalation.

Karena itu, role ditentukan secara eksplisit oleh aplikasi atau controller.

---

### 5. Apa perbedaan `restrict` dan `cascade`?

`restrict` mencegah data induk dihapus jika masih memiliki data yang menggunakannya.

`cascade` membuat data anak ikut terhapus ketika data induknya dihapus.

Contohnya, `courses.lecturer_id` menggunakan:

```php
->restrictOnDelete()
```

agar dosen yang masih digunakan oleh mata kuliah tidak dapat dihapus secara sembarangan.

---

### 6. Jalankan `migrate:refresh`

Perintah yang digunakan:

```bash
php artisan migrate:refresh
```

Perintah tersebut melakukan rollback migration kemudian menjalankan kembali migration dari awal.

Hasil yang diharapkan adalah seluruh migration dapat dijalankan kembali tanpa error dan struktur database tetap sesuai dengan rancangan.

---

### 7. Tunjukkan penggunaan AI dan jelaskan perubahan yang dilakukan.

AI digunakan sebagai alat bantu dalam memahami dan membuat beberapa bagian kode Laravel, seperti migration, model Eloquent, relasi, dan seeder.

Kode yang dihasilkan AI tidak langsung digunakan begitu saja, tetapi diperiksa kembali dan disesuaikan dengan spesifikasi project.

Perubahan dilakukan apabila terdapat bagian yang tidak sesuai dengan struktur database, aturan `$fillable`, relasi, atau kebutuhan aplikasi.




