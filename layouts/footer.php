<footer>
  <p>&copy; <?= date('Y') ?> Kost Pak Sapriadi.</p>
</footer>
<?php
$pageScripts = [
    'public/home' => ['assets/js/app.js', 'assets/js/pages/room-tour.js'],
    'public/units' => ['assets/js/app.js'],
    'public/unit-detail' => ['assets/js/pages/unit-detail.js'],
    'auth/login' => ['assets/js/auth/auth.js', 'assets/js/auth/login.js'],
    'admin/units' => ['assets/js/auth/auth.js', 'assets/js/pages/units.js'],
    'admin/dashboard' => ['assets/js/pages/dashboard.js'],
    'admin/tenants' => ['assets/js/pages/tenants.js'],
    'admin/payments' => ['assets/js/pages/payments.js'],
    'admin/facilities' => ['assets/js/pages/facilities.js'],
    'admin/complaints' => ['assets/js/auth/auth.js', 'assets/js/pages/complaints.js'],
    'tenant/dashboard' => ['assets/js/pages/dashboard.js'],
    'tenant/my-room' => ['assets/js/auth/auth.js', 'assets/js/pages/tenant-room.js'],
    'tenant/payments' => ['assets/js/pages/payments.js'],
    'tenant/complaints' => ['assets/js/auth/auth.js', 'assets/js/pages/complaints.js'],
    'tenant/profile' => ['assets/js/auth/auth.js'],
];
foreach ($pageScripts[$page] ?? [] as $script):
?>
<script type="module" src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
<script type="module" src="assets/js/core/theme.js"></script>
</body>
</html>
