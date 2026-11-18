# Rancangan Master Pemeriksaan

Dokumen ini menjelaskan struktur fitur master pemeriksaan dan bagian yang telah tersedia pada aplikasi.

## Tujuan

- Menampilkan master pemeriksaan sebagai tree dengan urutan yang dapat diatur.
- Membedakan Header layanan, Header paket, Sub Header, dan pemeriksaan.
- Memungkinkan paket menampilkan satu atau beberapa header layanan beserta seluruh turunannya tanpa menggandakan master.
- Menyimpan harga langsung pada pemeriksaan yang dapat dipesan.
- Mendukung hasil angka, teks, dan pilihan beserta nilai rujukannya.
- Menentukan status normal atau abnormal dari hasil pasien menggunakan aturan rujukan yang sesuai.

## Contoh tree

```text
Daftar layanan:
Darah Lengkap                         [header layanan]
├── Hematologi                        [Sub Header]
│   ├── Hemoglobin                    [pemeriksaan, Rp25.000]
│   └── Leukosit                      [pemeriksaan, Rp20.000]
└── Golongan Darah                    [pemeriksaan, Rp30.000]

Kimia Klinik                          [header layanan]
├── Glukosa                           [pemeriksaan, Rp30.000]
└── Asam Urat                         [pemeriksaan, Rp35.000]

Daftar paket:
Paket MCU                             [header paket]
└── Kimia Klinik                      [referensi ke header layanan di atas]
    ├── Glukosa                       [pemeriksaan yang sama, Rp30.000]
    └── Asam Urat                     [pemeriksaan yang sama, Rp35.000]
```

Di bawah `Paket MCU`, `Kimia Klinik` adalah header layanan yang sama dengan yang ada di daftar layanan. Baris tersebut bukan salinan master. Paket menyimpan hubungan ke layanan, lalu tampilan menurunkan isi layanan tersebut. Total ilustratif paket MCU adalah Rp65.000.

Pemeriksaan juga boleh mempunyai anak berupa parameter hasil. Jika pemeriksaan induk sudah mempunyai harga, parameter anak tidak ditagihkan lagi.

## Jenis node dan perilakunya

| Jenis | Peran | Harga | Punya hasil dan nilai rujukan |
| --- | --- | --- | --- |
| Header layanan | Akar layanan yang dapat dipilih sendiri atau dimasukkan ke paket | Total dari pemeriksaan di bawah layanan | Tidak |
| Sub Header | Membuat cabang di bawah Header layanan atau Sub Header lain | Tidak | Tidak |
| Header paket | Menggabungkan satu atau beberapa header layanan | Total dari layanan anggotanya | Tidak |
| Pemeriksaan | Item yang mempunyai hasil; boleh mempunyai parameter anak | Harga pada pemeriksaan yang dapat dipesan | Ya, jika menghasilkan nilai |

Parameter anak tetap memakai `node_type = examination`, tetapi `is_orderable = false`. Jadi jenis node tidak perlu bertambah hanya karena sebuah pemeriksaan menjadi anak dari pemeriksaan lain.

### Aturan harga

1. Kolom `price` diisi pada node `examination` yang dapat dipesan (`is_orderable = true`). Harga nol boleh dipakai jika memang gratis.
2. Semua header tidak menyimpan harga. Total layanan dihitung dari pemeriksaan yang dapat dipesan di bawahnya; total paket dihitung dari pemeriksaan yang dapat dipesan di seluruh layanan anggotanya.
3. Pemeriksaan yang sama hanya dihitung sekali dalam satu paket. Anak dari pemeriksaan yang sudah berharga dipakai sebagai parameter hasil dan tidak ditagihkan lagi.
4. Saat pesanan dibuat, salin harga saat itu ke detail pesanan agar perubahan harga master tidak mengubah riwayat tagihan.

## Struktur data yang diusulkan

### 1. `examinations` — node tree dan harga

| Kolom | Keterangan |
| --- | --- |
| `id` | Kunci utama. |
| `parent_id` | Induk dalam tree layanan; `NULL` untuk root. Tipe dan foreign key harus sama dengan `id`. Keanggotaan paket tidak memakai kolom ini. |
| `name` | Nama node; wajib diisi. |
| `code` | Kode unik pemeriksaan, terutama untuk integrasi dan pencarian. |
| `node_type` | `header`, `sub_header`, atau `examination`; wajib diisi. |
| `header_type` | `service` atau `package`; hanya diisi pada Header. Sub Header tidak memakai kolom ini. |
| `is_orderable` | `true` untuk pemeriksaan yang dapat dipesan dan ditagihkan. |
| `result_type` | `number`, `text`, atau `choice`; hanya untuk node yang menghasilkan nilai. |
| `price` | Harga pemeriksaan yang dapat dipesan; `NULL` untuk node tanpa harga. Gunakan `DECIMAL(14,2)` seperti kolom `price` yang sudah direncanakan. |
| `unit` | Satuan hasil angka, bila ada. |
| `method` | Metode pemeriksaan, bila diperlukan. |
| `input_mode` | Saat ini `manual`; berbeda dari `result_type`. |
| `sort_order` | Urutan di antara anak pada induk yang sama. |
| `is_active` | Status aktif master. |
| `created_at`, `updated_at` | Waktu pembuatan dan perubahan. |

Kolom lain dari contoh struktur awal, seperti `service_type_id`, `poli_id`, `barcode_name`, `default_description`, `default_impression`, `default_result`, dan konfigurasi perhitungan, belum dipakai pada fitur ini. Kolom tersebut dapat ditambahkan ketika alur terkait sudah ditentukan. `category` tidak dipakai karena cabang tampilan sudah diwakili oleh tree Header dan Sub Header.

### 2. `package_services` — anggota paket

| Kolom | Keterangan |
| --- | --- |
| `id` | Kunci utama. |
| `package_id` | Mengacu pada header dengan `header_type = package`. |
| `service_id` | Mengacu pada header dengan `header_type = service`. |
| `sort_order` | Urutan layanan ketika ditampilkan di dalam paket. |

Pasangan `package_id` dan `service_id` harus unik. Satu layanan dapat masuk ke beberapa paket. Mengubah pemeriksaan atau harga dalam layanan akan memengaruhi total paket yang memakainya untuk pesanan baru; harga pesanan lama tetap memakai salinan harga saat pesanan dibuat.

**Penting:** `Paket MCU → Kimia Klinik` dibentuk dari relasi `package_services`. `parent_id` milik `Kimia Klinik` tetap `NULL`, sehingga layanan yang sama tetap dapat muncul sebagai root pada daftar layanan.

### 3. `examination_reference_ranges` — rujukan hasil angka

| Kolom | Keterangan |
| --- | --- |
| `id`, `examination_id` | Identitas aturan dan pemeriksaan pemiliknya. |
| `sex`, `age_min_days`, `age_max_days` | Disiapkan untuk rujukan berdasarkan jenis kelamin atau usia; belum tersedia pada form. |
| `min_value`, `max_value` | Batas normal; masing-masing boleh `NULL` untuk batas satu sisi. |

Form master saat ini mengelola satu rentang umum per pemeriksaan angka. Rentang boleh kosong sampai nilainya disetujui. Kolom usia dan jenis kelamin tersedia pada tabel untuk pengembangan berikutnya, tetapi belum tersedia pada form atau logika penentuan hasil pasien.

### 4. `examination_result_options` — pilihan hasil

| Kolom | Keterangan |
| --- | --- |
| `id`, `examination_id` | Identitas opsi dan pemeriksaan pemiliknya. |
| `value`, `label` | Nilai tersimpan dan teks yang ditampilkan. |
| `is_normal` | Apakah pilihan tersebut dianggap normal. |
| `sort_order` | Urutan pilihan. |

Contoh ilustratif: `Negatif` dapat ditandai normal dan `Positif` abnormal pada pemeriksaan yang memang menggunakan interpretasi tersebut. Penetapan klinis untuk setiap pemeriksaan tetap harus divalidasi oleh penanggung jawab laboratorium.

## Rancangan aturan hasil pasien

- Status `normal`/`abnormal` adalah **hasil evaluasi per pasien**, bukan atribut tetap pada master pemeriksaan.
- Untuk `result_type = number`, evaluasi memakai rentang rujukan yang cocok. Untuk `choice`, evaluasi memakai `is_normal` pada opsi yang dipilih.
- Untuk `text`, status tidak dihitung otomatis kecuali ada aturan interpretasi tambahan yang didefinisikan secara khusus.
- Simpan hasil, status, dan rujukan yang digunakan pada detail hasil pasien agar riwayat tetap dapat dibaca walaupun master berubah.

Penyimpanan hasil pasien dan perhitungan status normal/abnormal belum menjadi bagian dari fitur master ini.

## Validasi tree

- `parent_id` harus menunjuk node yang ada pada tree layanan.
- Node tidak boleh menjadi induk dirinya sendiri atau membentuk siklus.
- `header_type` hanya boleh diisi untuk `node_type = header`. Nilainya hanya `service` atau `package`.
- Header layanan dan paket berada pada root tree master. Sub Header berada di bawah Header layanan atau Sub Header lain.
- Header paket memiliki anggota melalui `package_services`, bukan melalui `parent_id`; anggota tersebut harus berupa header layanan.
- Header paket harus mempunyai setidaknya satu layanan dengan pemeriksaan yang dapat dipesan sebelum diaktifkan untuk pemesanan.
- Pemeriksaan dengan `result_type = number` menggunakan rentang angka; `choice` menggunakan daftar opsi.
- Node tidak boleh dipindahkan dengan cara yang membuat harga satu pemeriksaan terhitung dua kali dalam satu paket.

## Alur halaman master

1. Pengguna memilih tab **Layanan** atau **Paket** di atas tabel lebar penuh. Tab menampilkan semua root sesuai jenisnya beserta seluruh turunan dengan indentasi sesuai tingkat tree.
2. Baris yang mempunyai turunan dapat dibuka atau ditutup. Pencarian tabel menampilkan baris yang cocok beserta induknya.
3. Saat menambah Header, pengguna memilih **Layanan** atau **Paket**. Sub Header dipilih sebagai jenis node tersendiri.
4. Pengguna dapat menambah pemeriksaan di bawah Header layanan atau Sub Header, lalu mengisi tipe hasil, apakah dapat dipesan, dan harga jika dapat dipesan.
5. Setelah pemeriksaan tersimpan, pengguna mengisi rentang rujukan atau opsi hasil sesuai tipe hasilnya.
6. Pada form paket, pengguna memilih satu atau beberapa header layanan sebagai anggotanya.
7. Tabel paket menampilkan layanan terpilih beserta turunannya dan menghitung total dari pemeriksaan yang dapat dipesan.

## Batasan struktur awal

Tree layanan dengan satu `parent_id` berarti satu pemeriksaan berada pada satu cabang layanan. Satu **layanan yang sama** dapat tampil di beberapa paket melalui `package_services` tanpa menyalin header atau pemeriksaannya. Jika kelak satu pemeriksaan perlu menjadi anggota langsung di beberapa layanan yang berbeda, kebutuhan itu memerlukan relasi tambahan di luar `parent_id`.
