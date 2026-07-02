<?php
/**
 * FILE: includes/footer.php
 * DESKRIPSI: Footer halaman — menutup layout, memuat semua script JS,
 *            dan menampilkan SweetAlert flash messages dari session.
 */
if (!isset($base_path)) $base_path = '';

// Ambil flash messages
$flash_success = get_flash('success');
$flash_error   = get_flash('error');
$flash_warning = get_flash('warning');
$flash_info    = get_flash('info');
?>

                </div><!-- .content-wrapper -->
            </main><!-- #main-wrapper -->
        </div><!-- .d-flex -->

    <!-- ================================================
         FOOTER BAR
    ================================================ -->
    <footer class="border-top py-3 text-center no-print"
            style="background:var(--bg-card);color:var(--text-muted);font-size:12px;margin-left:var(--sidebar-width);transition:margin-left .25s;">
        <span>Copyright &copy; <?= date('Y'); ?>
            <strong style="color:var(--primary);">Aplikasi Pendataan Pelanggan</strong>.
            Tugas Besar Pemrograman Web.
        </span>
    </footer>

    <!-- ================================================
         JAVASCRIPT LIBRARIES
    ================================================ -->
    <!-- Bootstrap 5 Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom App JS -->
    <script src="<?= $base_path; ?>assets/js/script.js"></script>

    <!-- ================================================
         FLASH ALERT TRIGGER (dari Session PHP → SweetAlert)
    ================================================ -->
    <?php if ($flash_success): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast(<?= json_encode($flash_success); ?>, 'success');
    });
    </script>
    <?php endif; ?>

    <?php if ($flash_error): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast(<?= json_encode($flash_error); ?>, 'error');
    });
    </script>
    <?php endif; ?>

    <?php if ($flash_warning): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast(<?= json_encode($flash_warning); ?>, 'warning');
    });
    </script>
    <?php endif; ?>

    <?php if ($flash_info): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast(<?= json_encode($flash_info); ?>, 'info');
    });
    </script>
    <?php endif; ?>

    <!-- Slot untuk script halaman-spesifik -->
    <?php if (isset($page_scripts)) echo $page_scripts; ?>

</body>
</html>
