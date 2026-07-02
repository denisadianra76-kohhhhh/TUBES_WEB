/**
 * FILE: assets/js/script.js
 * DESKRIPSI: JavaScript global untuk Aplikasi Pendataan Pelanggan
 *   1. Loading screen management
 *   2. Jam digital & tanggal
 *   3. Dark Mode toggle (persist localStorage)
 *   4. Sidebar toggle (desktop collapse + mobile drawer)
 *   5. Validasi form client-side
 *   6. Live search tabel
 *   7. Konfirmasi hapus SweetAlert2
 *   8. Toast notification
 *   9. Preview modal pelanggan & transaksi
 *  10. Export Excel helper
 */

'use strict';

/* =============================================================
   1. LOADING SCREEN
============================================================= */
function hideLoading() {
    const screen = document.getElementById('loading-screen');
    if (screen) {
        screen.classList.add('hidden');
        setTimeout(() => screen.remove(), 450);
    }
}

/* =============================================================
   2. JAM DIGITAL & TANGGAL HARI INI
============================================================= */
function updateClock() {
    const clockEl = document.getElementById('digital-clock');
    const dateEl  = document.getElementById('date-today');
    if (!clockEl && !dateEl) return;

    const now = new Date();

    if (clockEl) {
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = `${hh}:${mm}:${ss}`;
    }

    if (dateEl) {
        const opts = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        dateEl.textContent = now.toLocaleDateString('id-ID', opts);
    }
}

/* =============================================================
   3. DARK MODE TOGGLE
============================================================= */
function initDarkMode() {
    const saved = localStorage.getItem('theme') || 'light';
    applyTheme(saved);

    const btn = document.getElementById('dark-mode-btn');
    if (btn) {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem('theme', next);
        });
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const btn  = document.getElementById('dark-mode-btn');
    const icon = document.getElementById('dark-mode-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    if (btn) {
        btn.title = theme === 'dark' ? 'Mode Terang' : 'Mode Gelap';
    }
}

/* =============================================================
   4. SIDEBAR TOGGLE
============================================================= */
function initSidebar() {
    const toggleBtn  = document.getElementById('sidebar-toggle');
    const backdrop   = document.getElementById('sidebar-backdrop');
    const body       = document.body;

    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', () => {
        if (window.innerWidth >= 992) {
            body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
        } else {
            body.classList.toggle('sidebar-open');
        }
    });

    // Restore desktop sidebar state
    if (window.innerWidth >= 992 && localStorage.getItem('sidebarCollapsed') === 'true') {
        body.classList.add('sidebar-collapsed');
    }

    // Close on backdrop click (mobile)
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            body.classList.remove('sidebar-open');
        });
    }

    // Close on resize if mobile sidebar open
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            body.classList.remove('sidebar-open');
        }
    });
}

/* =============================================================
   5. VALIDASI FORM CLIENT-SIDE
============================================================= */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Custom: Validasi password konfirmasi
            const pass    = form.querySelector('[name="password"]');
            const confirm = form.querySelector('[name="konfirmasi_password"]');
            if (pass && confirm && pass.value !== confirm.value) {
                confirm.setCustomValidity('Password tidak cocok');
                e.preventDefault();
                e.stopPropagation();
            } else if (confirm) {
                confirm.setCustomValidity('');
            }

            // Custom: Validasi telepon (hanya angka)
            const telp = form.querySelector('[name="telepon"]');
            if (telp && !/^\d{6,15}$/.test(telp.value)) {
                telp.setCustomValidity('Nomor telepon hanya boleh berisi angka (6-15 digit)');
                e.preventDefault();
                e.stopPropagation();
            } else if (telp) {
                telp.setCustomValidity('');
            }

            form.classList.add('was-validated');
        });
    });
}

/* =============================================================
   6. LIVE SEARCH TABEL
============================================================= */
function initLiveSearch() {
    const searchInput = document.getElementById('table-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const filter = this.value.toLowerCase().trim();
        const rows   = document.querySelectorAll('.searchable-table tbody tr');
        let   count  = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const show = text.includes(filter);
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });

        const countEl = document.getElementById('search-count');
        if (countEl) countEl.textContent = count + ' data ditemukan';
    });
}

/* =============================================================
   7. KONFIRMASI HAPUS (SweetAlert2)
============================================================= */
function confirmDelete(url, name = 'data ini') {
    Swal.fire({
        title: 'Hapus Data?',
        html:  `Anda akan menghapus <strong>${name}</strong>.<br>Tindakan ini tidak dapat dibatalkan.`,
        icon:  'warning',
        showCancelButton:  true,
        confirmButtonText: '<i class="fas fa-trash me-1"></i>Ya, Hapus!',
        cancelButtonText:  '<i class="fas fa-times me-1"></i>Batal',
        confirmButtonColor: '#DC2626',
        cancelButtonColor:  '#64748B',
        reverseButtons: true,
        focusCancel: true
    }).then(result => {
        if (result.isConfirmed) {
            // Tampilkan loading singkat sebelum redirect
            Swal.fire({ title: 'Menghapus...', didOpen: () => Swal.showLoading() });
            window.location.href = url;
        }
    });
}

/* =============================================================
   8. TOAST NOTIFICATION
============================================================= */
const Toast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: t => {
        t.addEventListener('mouseenter', Swal.stopTimer);
        t.addEventListener('mouseleave', Swal.resumeTimer);
    }
}) : null;

function showToast(message, icon = 'success') {
    if (Toast) Toast.fire({ icon, title: message });
}

/* =============================================================
   9. PREVIEW MODAL PELANGGAN
============================================================= */
function previewPelanggan(data) {
    const modal = document.getElementById('modal-pelanggan');
    if (!modal) return;

    const fields = {
        'preview-kode':    data.kode    || '-',
        'preview-nama':    data.nama    || '-',
        'preview-jk':      data.jk === 'L' ? '♂ Laki-laki' : '♀ Perempuan',
        'preview-telp':    data.telp    || '-',
        'preview-email':   data.email   || '-',
        'preview-alamat':  data.alamat  || '-',
        'preview-tgl':     data.tgl     || '-',
        'preview-status':  data.status  || '-',
    };

    Object.entries(fields).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });

    const statusEl = document.getElementById('preview-status');
    if (statusEl) {
        statusEl.className = 'badge ' + (data.status === 'Aktif' ? 'badge-aktif' : 'badge-nonaktif');
    }

    new bootstrap.Modal(modal).show();
}

/* =============================================================
   9B. PREVIEW MODAL TRANSAKSI
============================================================= */
function previewTransaksi(data) {
    const modal = document.getElementById('modal-transaksi');
    if (!modal) return;

    const fields = {
        'trx-kode':     data.kode      || '-',
        'trx-pelanggan':data.pelanggan || '-',
        'trx-tanggal':  data.tanggal   || '-',
        'trx-jenis':    data.jenis     || '-',
        'trx-jumlah':   data.jumlah    || '-',
        'trx-ket':      data.ket       || '-',
        'trx-status':   data.status    || '-',
    };

    Object.entries(fields).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });

    new bootstrap.Modal(modal).show();
}

/* =============================================================
   10. EXPORT EXCEL (Client-side dari tabel HTML)
============================================================= */
function exportToExcel(tableId = 'main-table', filename = 'laporan') {
    const table = document.getElementById(tableId);
    if (!table) { showToast('Tabel tidak ditemukan', 'error'); return; }

    const html  = table.outerHTML.replace(/<i[^>]*><\/i>/g, '');
    const blob  = new Blob(['\uFEFF' + html], { type: 'application/vnd.ms-excel' });
    const url   = URL.createObjectURL(blob);
    const a     = document.createElement('a');
    a.href      = url;
    a.download  = `${filename}_${new Date().toISOString().slice(0,10)}.xls`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast('File Excel berhasil diunduh!');
}

/* =============================================================
   INISIALISASI — Dijalankan saat DOM siap
============================================================= */
document.addEventListener('DOMContentLoaded', () => {
    hideLoading();
    initDarkMode();
    initSidebar();
    initFormValidation();
    initLiveSearch();

    // Jam digital — update setiap detik
    updateClock();
    setInterval(updateClock, 1000);

    // Tandai link navigasi aktif berdasarkan URL (resolusi path penuh)
    const currentUrl = new URL(window.location.href);
    const currentPath = currentUrl.pathname.replace(/\/index\.php$/, '/');

    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;

        try {
            const resolved = new URL(href, currentUrl);
            const resolvedPath = resolved.pathname.replace(/\/index\.php$/, '/');
            if (resolvedPath === currentPath) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        } catch (err) {
            // jika href tidak valid, lewati
        }
    });

    // Nonaktifkan efek tampil animasi konten dari JS
    // (tidak menambahkan class fade-in)
});
