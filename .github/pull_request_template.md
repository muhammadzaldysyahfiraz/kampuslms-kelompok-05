## Apa yang diubah


## Kenapa


## Cara menguji
1. 
2. 

## Daftar periksa
- [ ] Validasi ada di server-side
- [ ] Otorisasi diperiksa (`Gate::authorize()` dipanggil di controller)
- [ ] Tidak ada N+1 (diperiksa dengan Debugbar / query counter)
- [ ] Ada feature test untuk jalur sukses dan jalur ditolak
- [ ] Tidak ada `dd()`, `dump()`, atau `console.log` tertinggal
- [ ] `.env` tidak tersentuh (tidak ada kredensial/secret bocor)
- [ ] Aset frontend terkompilasi bersih (`npm run build`)
- [ ] Seluruh pengujian otomatis lolos (`php artisan test`)

## Bagian yang dibantu AI
(Sebutkan bagian mana yang dibantu AI dan apa penyesuaian yang Anda ubah dari keluaran aslinya agar sesuai dengan konteks KampusLMS)
