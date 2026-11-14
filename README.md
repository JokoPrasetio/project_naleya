# Naleya SIMRS — UI Prototype

Prototype frontend statis untuk dashboard pelayanan dan pendaftaran pasien. Dibangun dengan HTML, CSS, JavaScript, Bootstrap 5, dan Bootstrap Icons.

## Menjalankan project

Dari folder project, jalankan server lokal:

```bash
python3 -m http.server 8000
```

Buka `http://localhost:8000/`. Halaman utama akan mengarahkan ke dashboard.

## Struktur folder

```text
.
├── assets/
│   ├── img/                     # Gambar aplikasi
│   └── vendor/                  # Library pihak ketiga yang disimpan lokal
│       ├── bootstrap/
│       └── fullcalendar/
├── components/
│   └── layout/                  # Sumber markup sidebar dan topbar
├── pages/
│   ├── operations/              # Halaman internal petugas
│   │   ├── dashboard.html
│   │   ├── registrations.html
│   │   ├── patients.html
│   │   └── registration-detail.html
│   └── public/                  # Halaman publik dan autentikasi
│       ├── online-registration.html
│       └── login.html
├── scripts/
│   ├── app.js                   # Interaksi umum dan tabel pendaftaran
│   └── pages/                   # Interaksi khusus halaman
├── styles/
│   ├── app.css                  # Token dan komponen visual bersama
│   └── pages/                   # Style khusus halaman
├── components/
└── index.html                   # Entry point dashboard
```

## Halaman

| Halaman            | File                                        |
| ------------------ | ------------------------------------------- |
| Dashboard          | `pages/operations/dashboard.html`           |
| Daftar pendaftaran | `pages/operations/registrations.html`       |
| Data pasien        | `pages/operations/patients.html`            |
| Detail pendaftaran | `pages/operations/registration-detail.html` |
| Daftar online      | `pages/public/online-registration.html`     |
| Login              | `pages/public/login.html`                   |

## Panduan kode

- Gunakan Inter untuk tipografi dan `styles/app.css` untuk token, komponen layout, feedback, form, dan tabel bersama.
- Gunakan nama file berdasarkan fungsinya. Interaksi khusus halaman berada di `scripts/pages/`, styling khusus halaman di `styles/pages/`.
- `components/layout/` berisi sumber markup sidebar dan topbar. Karena project statis, markup layout saat ini masih disalin di HTML. Saat memakai template engine, pindahkan menjadi partial/include.
- Bootstrap disimpan lokal di `assets/vendor/`. Bootstrap Icons dan Inter dimuat melalui CDN.
- Data pada halaman dan aksi simpan, hapus, check-in, ubah jadwal, serta logout masih berupa demo frontend; sambungkan ke API/backend sebelum penggunaan nyata.
