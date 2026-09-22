## Minggu 3 - Read → Break → Fix → Build<br>
---
**Nama: Muhammad Zaldy Syah Firaz**  
**NIM: 10241054**<br>

### READ
1. Untuk setiap foreign key, tentukan perilaku `onDelete`-nya dan tuliskan alasannya.
   **Jawab:**  
   - `courses.lecturer_id → restrictOnDelete()`:
     Melindungi integritas data akademik. Dosen tidak boleh dihapus dari sistem jika masih terikat sebagai pengampu mata kuliah yang aktif, agar relasi data pengajaran tidak terputus.
     <br>
   - `course_user.course_id` & `user_id → cascadeOnDelete()`: 
    Bersifat relasi pivot. Jika mata kuliah atau mahasiswanya dihapus, data pendaftaran relasi tersebut otomatis dibersihkan karena sudah tidak relevan.
    <br>
   - `materials.course_id → cascadeOnDelete()`: 
    Materi pembelajaran melekat mutlak pada mata kuliahnya; jika mata kuliah dihapus, materinya ikut terhapus.
    <br>
   - `materials.uploaded_by → restrictOnDelete()`: 
    Melindungi jejak kepemilikan dokumen. Pengguna/dosen pengunggah tidak boleh dihapus selama masih tercatat mengunggah materi tertentu.
    <br>
   - `assignments.course_id → cascadeOnDelete()`: 
    Tugas terikat langsung pada mata kuliah, ikut terhapus jika mata kuliah induk dihapus.
    <br>
   - `assignments.created_by → restrictOnDelete()`: 
    Mencegah penghapusan pembuat tugas selama tugas tersebut masih aktif di sistem.
    <br>
   - `submissions.assignment_id → cascadeOnDelete()`: 
    Riwayat pengumpulan tugas akan ikut terhapus otomatis jika tugas aslinya dihapus.
    <br>
   - `submissions.user_id → restrictOnDelete()`: 
    Melindungi data historis mahasiswa. Mahasiswa tidak bisa dihapus jika memiliki riwayat pengumpulan tugas.
    <br>
   - `grades.submission_id → cascadeOnDelete()`: 
    Nilai eksklusif terikat pada submission tertentu; jika submission dihapus, nilai di dalamnya ikut hilang.
    <br>
   - `grades.graded_by → restrictOnDelete()`: 
    Dosen penilai dilindungi dari penghapusan agar rekam jejak penilaian tetap valid.
    <br>
2. Kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?<br>
   **Jawab:**
   Sistem database akan menolak proses penghapusan tersebut dan memunculkan error (karena batasan restrictOnDelete()). Hal ini dirancang agar mata kuliah tidak kehilangan dosen pengampunya secara tiba-tiba, menjaga konsistensi jadwal kuliah, serta memastikan rekam jejak akademik tetap utuh. Dosen harus dipindahkan atau dilepaskan dari mata kuliah tersebut terlebih dahulu sebelum bisa dihapus dari database.