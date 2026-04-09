<?php
$active_page = $active_page ?? 'dashboard';
?>
<style>
    .admin-sidebar {
        width: 260px;
        background: var(--surface);
        border-right: 1px solid var(--border-soft);
        padding: 24px 16px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
    }
    .admin-sidebar .logo {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        margin-bottom: 32px;
        padding-left: 12px;
    }
    .admin-sidebar .nav-item {
        display: block;
        padding: 10px 12px;
        border-radius: 12px;
        color: #2c2b28;
        text-decoration: none;
        margin-bottom: 4px;
    }
    .admin-sidebar .nav-item:hover, .admin-sidebar .nav-item.active {
        background: #eef2f0;
        color: #185FA5;
    }
</style>
<div class="admin-sidebar">
    <div class="logo">Administrator</div> <hr>
    <nav>
        <a href="dashboard.php" class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="manage_methods.php" class="nav-item <?= $active_page === 'manage_methods' ? 'active' : '' ?>"><i class="fas fa-tablets me-2"></i> Manage Methods</a>
        <!-- <a href="comparison_settings.php" class="nav-item <?= $active_page === 'comparison_settings' ? 'active' : '' ?>"><i class="fas fa-sliders-h me-2"></i> Comparison Settings</a> -->
        <!-- <a href="../user/comparison.php" target="_blank" class="nav-item"><i class="fas fa-eye me-2"></i> View Comparison Guide</a> -->
        <hr class="my-3">
        <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </nav>
</div>