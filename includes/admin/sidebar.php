<?php
$active_page = $active_page ?? 'dashboard';
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400;1,500&family=DM+Sans:wght@300;400;500;600&display=swap');

    :root {
        --rose:       #C1666B;
        --rose-deep:  #9E4A4F;
        --rose-blush: #FDF0F1;
        --rose-pale:  #F5DDE0;
        --ink:        #1C1A18;
        --muted:      #9A9289;
        --surface:    #ffffff;
        --surface2:   #F5F2ED;
        --border:     rgba(0,0,0,0.055);
        --border-md:  rgba(0,0,0,0.09);
        --admin-accent: #8B6FD4;
        --admin-bg:     #F0EDF8;
        --admin-deep:   #5A3F9E;
    }

    .admin-sidebar {
        width: 252px;
        background: var(--surface);
        border-right: 1px solid var(--border-md);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        font-family: 'DM Sans', sans-serif;
        z-index: 100;
    }

    .admin-sidebar .sidebar-logo {
        padding: 26px 20px 18px;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }

    .admin-sidebar .logo-wordmark {
        font-family: 'Cormorant Garamond', serif;
        font-size: 22px;
        font-weight: 600;
        color: var(--ink);
        letter-spacing: -0.5px;
        line-height: 1;
    }

    .admin-sidebar .logo-wordmark em {
        font-style: italic;
        color: var(--rose);
    }

    .admin-sidebar .logo-tagline {
        font-size: 10.5px;
        color: var(--muted);
        margin-top: 5px;
        letter-spacing: 0.04em;
        font-weight: 400;
    }

    .admin-sidebar .sidebar-nav {
        flex: 1;
        padding: 10px 10px;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .admin-sidebar .sidebar-nav::-webkit-scrollbar { display: none; }

    .admin-sidebar .nav-section-label {
        font-size: 9.5px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 14px 10px 5px;
        display: block;
        opacity: 0.7;
    }

    .admin-sidebar .nav-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 11px;
        border-radius: 11px;
        cursor: pointer;
        margin-bottom: 2px;
        transition: background 0.18s ease, color 0.18s ease;
        text-decoration: none;
        color: #6B6560;
        position: relative;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: -0.1px;
    }

    .admin-sidebar .nav-item:hover {
        background: var(--surface2);
        color: var(--ink);
        text-decoration: none;
    }

    .admin-sidebar .nav-item.active {
        background: var(--admin-bg);
        color: var(--admin-deep);
    }

    .admin-sidebar .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 20%;
        bottom: 20%;
        width: 3px;
        background: var(--admin-accent);
        border-radius: 0 3px 3px 0;
    }

    .admin-sidebar .nav-item.active i {
        color: var(--admin-accent);
    }

    .admin-sidebar .nav-item.active .nav-label { font-weight: 600; }

    .admin-sidebar .nav-item i {
        width: 18px;
        text-align: center;
        font-size: 14px;
        flex-shrink: 0;
        opacity: 0.85;
    }

    .admin-sidebar .nav-label { flex: 1; }

    .admin-sidebar .sidebar-footer {
        border-top: 1px solid var(--border);
        padding: 12px 10px;
        flex-shrink: 0;
    }

    .admin-sidebar .logout-btn {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 11px;
        border-radius: 11px;
        width: 100%;
        border: none;
        background: none;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 500;
        color: var(--rose-deep);
        text-decoration: none;
        transition: background 0.18s;
    }

    .admin-sidebar .logout-btn:hover {
        background: var(--rose-blush);
        text-decoration: none;
        color: var(--rose-deep);
    }

    .admin-sidebar .logout-btn i {
        width: 18px;
        text-align: center;
        font-size: 14px;
        flex-shrink: 0;
    }
</style>

<div class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="logo-wordmark">ADMINISTRATOR</div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Overview</span>
        <a href="dashboard.php" class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span class="nav-label">Dashboard</span>
        </a>

        <span class="nav-section-label">Content</span>
        <a href="manage_methods.php" class="nav-item <?= $active_page === 'manage_methods' ? 'active' : '' ?>">
            <i class="fas fa-tablets"></i>
            <span class="nav-label">Manage Methods</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>