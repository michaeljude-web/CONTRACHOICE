<?php
require_once '../includes/db_connection.php';
require_once '../includes/admin/auth.php';
requireAdminLogin();

$page_title = 'Admin Dashboard';
$active_page = 'dashboard';

$methods_count = $conn->query("SELECT COUNT(*) as c FROM contraceptive_methods")->fetch_assoc()['c'];
$forum_posts = $conn->query("SELECT COUNT(*) as c FROM forum_posts")->fetch_assoc()['c'];
$forum_replies = $conn->query("SELECT COUNT(*) as c FROM forum_replies")->fetch_assoc()['c'];
$questionnaires = $conn->query("SELECT COUNT(*) as c FROM questionnaire_responses")->fetch_assoc()['c'];
$users_count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ContraChoice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        :root {
            --bg-dirty: #f8f6f0;
            --surface: #ffffff;
            --border-soft: #e8e4dc;
            --text-primary: #2c2b28;
            --text-secondary: #6b6b67;
            --blue-600: #185FA5;
            --blue-50: #e6f1fb;
        }
        body {
            background: var(--bg-dirty);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 24px 28px;
        }
        .topbar {
            background: var(--surface);
            border-radius: 20px;
            padding: 16px 24px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-soft);
        }
        .welcome-text h4 {
            margin: 0;
            font-weight: 500;
        }
        .welcome-text p {
            margin: 0;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .admin-badge {
            background: #eef2f0;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            padding: 24px 20px;
            transition: all 0.2s ease;
            text-align: center;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.05);
            border-color: var(--blue-50);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            background: var(--blue-50);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: var(--blue-600);
            margin: 0 auto 16px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 16px; }
            .stats-grid { gap: 14px; }
            .stat-number { font-size: 28px; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/admin/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <div class="welcome-text">
                <h4>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?></h4>
                <p>Overview of your contraceptive platform</p>
            </div>
            <div class="admin-badge">
                <i class="fas fa-user-shield me-1"></i> Administrator
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tablets"></i></div>
                <div class="stat-number"><?= $methods_count ?></div>
                <div class="stat-label">Contraceptive Methods</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?= $users_count ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div class="stat-number"><?= $forum_posts ?></div>
                <div class="stat-label">Forum Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-reply-all"></i></div>
                <div class="stat-number"><?= $forum_replies ?></div>
                <div class="stat-label">Total Replies</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-number"><?= $questionnaires ?></div>
                <div class="stat-label">Questionnaire Responses</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>