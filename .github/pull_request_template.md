## Apa yang diubah

## Kenapa

## Cara menguji
1.
2.

## Daftar periksa
- [ ] Validasi tersedia di server-side bila perubahan menerima input.
- [ ] Otorisasi diperiksa; panggil `Gate::authorize()` di controller bila relevan.
- [ ] Potensi N+1 diperiksa dengan Debugbar atau query counter bila perubahan mengakses relasi/data berulang.
- [ ] Ada feature test untuk jalur sukses dan jalur ditolak bila perubahan memengaruhi alur yang memerlukan pengujian tersebut.
- [ ] Tidak ada `dd()`, `dump()`, atau `console.log` tertinggal.
- [ ] `.env` tidak tersentuh dan tidak ada kredensial/secret yang ditambahkan.
- [ ] Aset frontend dapat dibangun dengan `npm run build` bila perubahan menyentuh frontend.
- [ ] Pengujian otomatis yang relevan lolos dengan `php artisan test`.

## Bagian yang dibantu AI
Sebutkan file atau bagian yang dibantu AI dan jelaskan perubahan manusia terhadap hasil AI agar sesuai dengan konteks KampusLMS. Jika tidak menggunakan AI, tulis “Tidak ada”.
