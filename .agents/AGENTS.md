
# KOMPAS (Aturan Dasar & 11 Pre-Flight Checklist)

Setiap eksekusi kode atau pembuatan rencana wajib mematuhi 9 Pilar Utama dan menjawab 11 Pertanyaan Wajib dalam `implementation_plan.md` untuk disetujui.

**9 Pilar Utama (Wajib Dipatuhi):**
1. **Pahami Struktur:** Riset dan baca struktur folder, database, dan alur kode sebelum menulis kode. Tidak boleh ada asumsi buta.
2. **Best Practice Bisnis & Segregasi Divisi:** Pastikan SOP logis, cegah penyatuan wewenang yang merusak integritas.
3. **Aman:** Bebas celah, cegah double submit dan kebocoran wewenang/data.
4. **Lebih Sederhana:** Hindari birokrasi sistem yang rumit (langsung pada intinya).
5. **User-Friendly:** Antarmuka intuitif tanpa perlu buku panduan.
6. **Ringan di Server:** Maksimalkan arsitektur SPA/AJAX untuk efisiensi resource.
7. **Cepat:** Waktu respons instan, minimalkan jeda loading.
8. **Desain iOS 26.6:** Modern, premium, dinamis, hidup, rapi, dan presisi tinggi.
9. **Inovatif:** Jauh melampaui ERP tradisional yang kaku.

**11 Pertanyaan Wajib:**
1. Akun terdampak? 2. Fitur bertambah? 3. Otoritas baru CEO? 4. Otoritas baru selain CEO? 5. Risiko & Solusi? 6. Potensi Error 500 & Mitigasi? 7. Ide tambahan *best practice*? 8. Integrasi *End-to-End*? 9. Divisi terdampak & Rencana UI/UX-Backend? 10. Akurasi file/folder? 11. Apakah responsif/mobile-friendly?

# Pemahaman Arsitektur Khusus ERP Sikarya
1. **Arsitektur SPA Berjenjang:** File kerangka utama `resources/views/master-portal.blade.php`, sedangkan elemen spesifik ada di folder masing-masing (contoh: Gudang di `inventory-umkm/index.blade.php`). Jangan mencari form modul di file master.
2. **Keamanan UI/UX Global:** Semua pop-up hapus/submit terpusat di `resources/views/components/global-loading.blade.php`. Dilarang memakai `confirm()` browser, wajib gunakan `window.showCustomConfirm()`.
3. **Mitigasi Race Condition SPA:** Eksekusi `switchView` dari master bisa terjadi sebelum `DOMContentLoaded`. Wajib tambahkan pengecekan `localStorage.getItem('subaActiveView') === 'nama_modul'` di dalam blok `DOMContentLoaded` pada script modul agar *fetch* data berjalan aman.

# Design Guidelines
- **Baseline Color Palette**: Always align the UI/UX colors with #0C3527 (Dark Green/Primary) and #D9EFE9 (Soft Green/Accent). Any shades, gradients, or complementary colors must harmonize with these core brand colors to maintain consistency across the entire ERP website.
