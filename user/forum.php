<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/db_connection.php';
include '../includes/user/auth.php';

$page_title  = 'Anonymous Forum';
$active_page = 'forum';
$user_id     = $_SESSION['user_id'] ?? 0;

// Helper: kung hindi logged in, ipakita ang error at huwag iproseso ang POST
if ($user_id == 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = "You must be logged in to post, reply, or rate.";
    // Huwag nang ipagpatuloy ang POST processing
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_post') {
        $content = trim($_POST['content'] ?? '');
        if (!empty($content)) {
            $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, content) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $content);
            $stmt->execute();
            $stmt->close();
            $success = "Your anonymous question has been posted.";
        } else {
            $error = "Please fill in the question field.";
        }
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'add_reply') {
        $post_id = intval($_POST['post_id']);
        $content = trim($_POST['reply_content'] ?? '');
        if (!empty($content) && $post_id > 0) {
            $stmt = $conn->prepare("INSERT INTO forum_replies (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $post_id, $user_id, $content);
            $stmt->execute();
            $stmt->close();
            // Update reply_count using prepared statement (iwas SQL injection)
            $updateStmt = $conn->prepare("UPDATE forum_posts SET reply_count = reply_count + 1 WHERE post_id = ?");
            $updateStmt->bind_param("i", $post_id);
            $updateStmt->execute();
            $updateStmt->close();
            $success = "Reply posted anonymously.";
        } else {
            $error = "Reply cannot be empty.";
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'rate_reply') {
        $reply_id = intval($_POST['reply_id']);
        $rating   = intval($_POST['rating']);
        if ($reply_id > 0 && $rating >= 1 && $rating <= 5) {
            $stmt = $conn->prepare("
                INSERT INTO forum_reply_ratings (reply_id, user_id, rating)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating)
            ");
            $stmt->bind_param("iii", $reply_id, $user_id, $rating);
            $stmt->execute();
            $stmt->close();
            $success = "Your rating has been saved.";
        } else {
            $error = "Invalid rating value.";
        }
        // Wala nang redirect – magre-reload ang page sa normal na POST flow
    }
}

$sort = $_GET['sort'] ?? 'newest';
$order_by = ($sort === 'oldest') ? 'ASC' : 'DESC';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$total_result = $conn->query("SELECT COUNT(*) as total FROM forum_posts");
$total_posts = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $per_page);

$posts_query = "
    SELECT * FROM forum_posts
    ORDER BY created_at $order_by
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($posts_query);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> — ContraChoice</title>
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
      --blue-soft: #dceaf5;
      --blue-600: #185FA5;
      --blue-800: #0C447C;
    }
    body { background: var(--bg-dirty); font-family: 'Outfit', sans-serif; }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-dirty); }
    .topbar {
      height: 52px; background: var(--surface); border-bottom: 0.5px solid var(--border-soft);
      display: flex; align-items: center; justify-content: space-between; padding: 0 24px;
    }
    .topbar-title { font-family: 'Playfair Display', serif; font-size: 14px; }
    .topbar-title em { font-style: italic; color: var(--blue-600); }
    .topbar-user { font-size: 12px; background: #eef2f0; padding: 4px 12px; border-radius: 30px; }
    .content-area { flex: 1; overflow-y: auto; padding: 28px; }

    /* Forum header */
    .forum-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 24px;
      gap: 16px;
    }
    .forum-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      font-weight: 500;
      margin: 0;
    }
    .forum-actions { display: flex; gap: 12px; align-items: center; }
    .sort-select {
      background: var(--surface);
      border: 1px solid var(--border-soft);
      border-radius: 30px;
      padding: 8px 16px;
      font-size: 13px;
      font-family: inherit;
      color: var(--text-primary);
    }
    .btn-new-post {
      background: var(--blue-600);
      border: none;
      border-radius: 30px;
      padding: 8px 20px;
      color: white;
      font-weight: 500;
      font-size: 13px;
      transition: background 0.2s;
    }
    .btn-new-post:hover { background: var(--blue-800); }

    /* Forum cards */
    .forum-card {
      background: var(--surface);
      border: 1px solid var(--border-soft);
      border-radius: 20px;
      margin-bottom: 20px;
      overflow: hidden;
      transition: box-shadow 0.2s;
    }
    .forum-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .post-header {
      padding: 18px 24px 10px;
      border-bottom: 1px solid var(--border-soft);
    }
    .post-meta {
      font-size: 12px;
      color: var(--text-secondary);
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    .anonymous-badge {
      background: #eef2f0;
      padding: 2px 10px;
      border-radius: 30px;
      font-size: 11px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .post-content {
      padding: 16px 24px;
      font-size: 14px;
      color: var(--text-secondary);
      line-height: 1.6;
      border-bottom: 1px solid var(--border-soft);
    }
    .reply-section {
      padding: 20px 24px;
      background: #fefefc;
    }
    .reply-list {
      margin-bottom: 20px;
      max-height: 300px;
      overflow-y: auto;
    }
    .reply-item {
      padding: 12px 0;
      border-bottom: 1px solid var(--border-soft);
    }
    .reply-item:last-child { border-bottom: none; }
    .reply-meta {
      font-size: 11px;
      color: var(--text-secondary);
      margin-bottom: 6px;
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }
    .reply-content {
      font-size: 13px;
      line-height: 1.5;
      color: var(--text-primary);
      margin-bottom: 8px;
    }

    /* Star rating */
    .star-rating {
      display: flex;
      align-items: center;
      gap: 4px;
      margin-top: 6px;
    }
    .star-rating form { display: flex; align-items: center; gap: 2px; }
    .star-btn {
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
      font-size: 16px;
      line-height: 1;
      color: #d1cfc6;
      transition: color 0.15s, transform 0.1s;
    }
    .star-btn:hover,
    .star-btn.active { color: #f0a500; }
    .star-btn:hover { transform: scale(1.2); }
    .star-avg {
      font-size: 11px;
      color: var(--text-secondary);
      margin-left: 6px;
    }
    .star-filled { color: #f0a500; }

    .reply-form textarea {
      width: 100%;
      border: 1px solid var(--border-soft);
      border-radius: 16px;
      padding: 12px 16px;
      font-family: inherit;
      font-size: 13px;
      resize: vertical;
    }
    .reply-form button {
      margin-top: 10px;
      background: var(--blue-600);
      border: none;
      padding: 8px 20px;
      border-radius: 30px;
      color: white;
      font-weight: 500;
      font-size: 12px;
    }
    .alert-custom {
      background: #eaf3de;
      border-radius: 14px;
      padding: 12px 18px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 32px;
      margin-bottom: 20px;
    }
    .pagination a, .pagination span {
      padding: 6px 12px;
      border-radius: 30px;
      background: var(--surface);
      border: 1px solid var(--border-soft);
      text-decoration: none;
      color: var(--text-secondary);
      font-size: 13px;
    }
    .pagination .active {
      background: var(--blue-600);
      color: white;
      border-color: var(--blue-600);
    }
    .empty-forum {
      text-align: center;
      padding: 60px 24px;
      background: var(--surface);
      border-radius: 20px;
      border: 1px solid var(--border-soft);
    }
    .empty-forum i { font-size: 48px; color: var(--text-secondary); margin-bottom: 16px; }

    /* Modal */
    .modal-custom .modal-content {
      border-radius: 20px;
      border: none;
      background: var(--surface);
    }
    .modal-custom .modal-header { border-bottom: 1px solid var(--border-soft); padding: 20px 24px; }
    .modal-custom .modal-body { padding: 24px; }
    .modal-custom .form-control {
      border: 1px solid var(--border-soft);
      border-radius: 16px;
      padding: 12px 16px;
      font-family: 'Outfit', sans-serif;
    }
    .modal-custom .btn-submit {
      background: var(--blue-600);
      border: none;
      border-radius: 30px;
      padding: 10px 24px;
      color: white;
      font-weight: 500;
    }
  </style>
</head>
<body>
<div class="cc-layout">
  <?php include '../includes/user/sidebar.php'; ?>
  <div class="cc-main">
    <div class="topbar">
      <div class="topbar-left">
        <span class="topbar-title">ContraChoice</span>
        <span class="topbar-sep">›</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>
    <div class="content-area">

      <?php if (isset($success)): ?>
        <div class="alert-custom"><i class="fas fa-check-circle text-success"></i> <?= htmlspecialchars($success) ?></div>
      <?php elseif (isset($error)): ?>
        <div class="alert-custom" style="background:#fcebeb;"><i class="fas fa-exclamation-triangle text-danger"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="forum-header">
        <h1><i class="fas fa-comments me-2"></i> Anonymous Community Forum</h1>
        <div class="forum-actions">
          <select class="sort-select" onchange="window.location.href='?sort='+this.value+'&page=1'">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest first</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
          </select>
          <button class="btn-new-post" data-bs-toggle="modal" data-bs-target="#postModal">
            <i class="fas fa-pen-alt me-2"></i> Post Question
          </button>
        </div>
      </div>

      <?php if ($posts->num_rows === 0): ?>
        <div class="empty-forum">
          <i class="fas fa-comment-dots"></i>
          <h4>No questions yet</h4>
          <p class="text-muted">Be the first to ask anonymously about contraceptives, family planning, or women's health.</p>
          <button class="btn-new-post mt-2" data-bs-toggle="modal" data-bs-target="#postModal">📝 Start a discussion</button>
        </div>
      <?php else: ?>

        <?php while ($post = $posts->fetch_assoc()):
          $post_id = $post['post_id'];

          // Fetch replies with avg rating and user's own rating
          $reply_stmt = $conn->prepare("
            SELECT r.*,
              COALESCE(AVG(rt.rating), 0) AS avg_rating,
              COUNT(rt.rating)            AS rating_count,
              MAX(CASE WHEN rt.user_id = ? THEN rt.rating ELSE 0 END) AS user_rating
            FROM forum_replies r
            LEFT JOIN forum_reply_ratings rt ON rt.reply_id = r.reply_id
            WHERE r.post_id = ?
            GROUP BY r.reply_id
            ORDER BY r.created_at ASC
          ");
          $reply_stmt->bind_param("ii", $user_id, $post_id);
          $reply_stmt->execute();
          $replies = $reply_stmt->get_result();
        ?>
        <div class="forum-card" id="post-<?= $post_id ?>">
          <!-- Post header: no title, just meta -->
          <div class="post-header">
            <div class="post-meta">
              <span class="anonymous-badge"><i class="fas fa-user-secret"></i> Anonymous</span>
              <span><i class="far fa-calendar-alt"></i> <?= date('M d, Y g:i A', strtotime($post['created_at'])) ?></span>
              <span><i class="far fa-comment-dots"></i> <?= $post['reply_count'] ?> replies</span>
            </div>
          </div>

          <!-- Post content (the question itself) -->
          <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
          </div>

          <!-- Replies -->
          <div class="reply-section">
            <div class="reply-list">
              <?php if ($replies->num_rows === 0): ?>
                <p class="text-muted" style="font-size:13px;"><i class="far fa-comment"></i> No replies yet. Be the first to answer.</p>
              <?php else: ?>
                <?php while ($reply = $replies->fetch_assoc()):
                  $reply_id   = $reply['reply_id'];
                  $avg_rating = round($reply['avg_rating'], 1);
                  $user_rated = intval($reply['user_rating']);
                ?>
                <div class="reply-item">
                  <div class="reply-meta">
                    <span class="anonymous-badge"><i class="fas fa-user-secret"></i> Anonymous</span>
                    <span><i class="far fa-clock"></i> <?= date('M d, Y g:i A', strtotime($reply['created_at'])) ?></span>
                  </div>
                  <div class="reply-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></div>

                  <!-- Star rating -->
                  <div class="star-rating">
                    <form method="POST" style="display:flex;align-items:center;gap:2px;">
                      <input type="hidden" name="action"   value="rate_reply">
                      <input type="hidden" name="reply_id" value="<?= $reply_id ?>">
                      <?php for ($s = 1; $s <= 5; $s++): ?>
                        <button
                          type="submit"
                          name="rating"
                          value="<?= $s ?>"
                          class="star-btn <?= $s <= $user_rated ? 'active' : '' ?>"
                          title="<?= $s ?> star<?= $s > 1 ? 's' : '' ?>"
                          onmouseover="hoverStars(this, <?= $s ?>)"
                          onmouseout="resetStars(this.closest('form'), <?= $user_rated ?>)"
                        >★</button>
                      <?php endfor; ?>
                    </form>
                    <?php if ($reply['rating_count'] > 0): ?>
                      <span class="star-avg">
                        <?= $avg_rating ?> / 5
                        (<?= $reply['rating_count'] ?> <?= $reply['rating_count'] == 1 ? 'rating' : 'ratings' ?>)
                      </span>
                    <?php else: ?>
                      <span class="star-avg">No ratings yet</span>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endwhile; ?>
              <?php endif; ?>
            </div>

            <!-- Reply form -->
            <form method="POST" class="reply-form">
              <input type="hidden" name="action"   value="add_reply">
              <input type="hidden" name="post_id"  value="<?= $post_id ?>">
              <textarea name="reply_content" rows="2" placeholder="Write a reply (anonymous)..." required></textarea>
              <div class="text-end">
                <button type="submit"><i class="fas fa-reply-all me-1"></i> Reply anonymously</button>
              </div>
            </form>
          </div>
        </div>
        <?php $reply_stmt->close(); endwhile; ?>
      <?php endif; ?>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?sort=<?= $sort ?>&page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i> Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="?sort=<?= $sort ?>&page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
          <a href="?sort=<?= $sort ?>&page=<?= $page+1 ?>">Next <i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- Post Modal (no title field) -->
<div class="modal fade modal-custom" id="postModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-pen-alt me-2"></i> Post a new question</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="add_post">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Your Question</label>
            <textarea class="form-control" name="content" rows="5" placeholder="Write your question here... (no personal details)" required></textarea>
          </div>
          <div class="text-muted small">
            <i class="fas fa-shield-alt"></i> Your identity remains hidden. Your question will be shown as "Anonymous".
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Post anonymously</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<script>
function hoverStars(btn, val) {
  const btns = btn.closest('form').querySelectorAll('.star-btn');
  btns.forEach((b, i) => b.style.color = i < val ? '#f0a500' : '#d1cfc6');
}
function resetStars(form, userRated) {
  const btns = form.querySelectorAll('.star-btn');
  btns.forEach((b, i) => b.style.color = i < userRated ? '#f0a500' : '#d1cfc6');
}
</script>
<?php include '../includes/user/chatbot_widget.php'; ?>
</body>
</html>