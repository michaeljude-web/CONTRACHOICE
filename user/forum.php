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

if ($user_id == 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = "You must be logged in to post, reply, or rate.";
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
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
  <style>
    :root {
      --bg:         #f5f0e8;
      --surface:    #fdfaf5;
      --surface-2:  #faf6ef;
      --border:     #e8dfd0;
      --text:       #4a3728;
      --muted:      #9b8776;
      --accent-blue:   #b8cfe8;
      --accent-blue-d: #6b9ab8;
      --accent-pink:   #f0d5d5;
      --accent-pink-d: #c47a7a;
      --accent-mint:   #cce8dc;
      --accent-mint-d: #5a9a7a;
      --accent-peach:  #f5ddd0;
      --accent-peach-d:#c47a55;
      --accent-lav:    #ddd5f0;
      --accent-lav-d:  #7a6ab8;
      --brown:      #7d5a4a;
      --brown-d:    #5a3a2a;
      --radius-sm:  12px;
      --radius-md:  18px;
      --radius-lg:  24px;
      --shadow-sm:  0 2px 8px rgba(120,80,50,.08);
      --shadow-md:  0 4px 16px rgba(120,80,50,.12);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      font-family: 'Nunito', sans-serif;
      color: var(--text);
      background-image:
        radial-gradient(circle at 15% 20%, rgba(184,207,232,.18) 0%, transparent 50%),
        radial-gradient(circle at 85% 75%, rgba(204,232,220,.15) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(240,213,213,.10) 0%, transparent 60%);
    }
    .cc-layout { display: flex; height: 100vh; overflow: hidden; }
    .cc-main   { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }

    .topbar {
      height: 56px;
      background: var(--surface);
      border-bottom: 1.5px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      flex-shrink: 0;
      font-size: 13px;
      color: var(--muted);
      font-family: 'Quicksand', sans-serif;
    }
    .topbar b {
      color: var(--brown);
      font-weight: 700;
    }
    .topbar-left {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }
    .topbar-sep {
      color: var(--border);
      font-size: 16px;
    }
    .topbar-page {
      color: var(--muted);
      font-weight: 500;
    }

    .content-area {
      flex: 1;
      overflow-y: auto;
      padding: 28px 28px 48px;
    }
    .content-area::-webkit-scrollbar { width: 5px; }
    .content-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

    .forum-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 24px;
      gap: 16px;
    }
    .forum-header h1 {
      font-family: 'Quicksand', sans-serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--brown-d);
      margin: 0;
    }
    .forum-actions { display: flex; gap: 12px; align-items: center; }
    .sort-select {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 40px;
      padding: 8px 18px;
      font-size: 13px;
      font-family: 'Nunito', sans-serif;
      font-weight: 500;
      color: var(--text);
    }
    .btn-new-post {
      background: var(--brown);
      border: none;
      border-radius: 40px;
      padding: 9px 22px;
      color: white;
      font-weight: 600;
      font-size: 13px;
      transition: background 0.2s, transform 0.1s;
      box-shadow: 0 3px 10px rgba(125,90,74,.3);
    }
    .btn-new-post:hover { background: var(--brown-d); transform: translateY(-1px); }

    .forum-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      margin-bottom: 20px;
      overflow: hidden;
      transition: box-shadow 0.2s, transform 0.15s;
    }
    .forum-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .post-header {
      padding: 18px 24px 10px;
      border-bottom: 1.5px solid var(--border);
    }
    .post-meta {
      font-size: 12px;
      color: var(--muted);
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      font-weight: 600;
    }
    .reply-count-clickable {
      cursor: pointer;
      transition: color 0.15s;
    }
    .reply-count-clickable:hover {
      color: var(--brown);
      text-decoration: underline;
    }
    .anonymous-badge {
      background: var(--surface-2);
      padding: 3px 12px;
      border-radius: 40px;
      font-size: 11px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      border: 1px solid var(--border);
      color: var(--brown);
    }
    .post-content {
      padding: 16px 24px;
      font-size: 14px;
      color: var(--text);
      line-height: 1.65;
      border-bottom: 1.5px solid var(--border);
      font-weight: 500;
    }
    .reply-section {
      padding: 20px 24px;
      background: var(--surface-2);
      display: none;
    }
    .reply-section.open {
      display: block;
    }
    .reply-list {
      margin-bottom: 20px;
      max-height: 350px;
      overflow-y: auto;
    }
    .reply-item {
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }
    .reply-item:last-child { border-bottom: none; }
    .reply-meta {
      font-size: 11px;
      color: var(--muted);
      margin-bottom: 6px;
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      font-weight: 600;
    }
    .reply-content {
      font-size: 13px;
      line-height: 1.55;
      color: var(--text);
      margin-bottom: 8px;
      font-weight: 500;
    }
    .star-rating {
      display: flex;
      align-items: center;
      gap: 6px;
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
      color: var(--muted);
      margin-left: 4px;
      font-weight: 600;
    }
    .reply-form textarea {
      width: 100%;
      border: 1.5px solid var(--border);
      border-radius: 18px;
      padding: 12px 16px;
      font-family: 'Nunito', sans-serif;
      font-size: 13px;
      resize: vertical;
      background: var(--surface);
    }
    .reply-form textarea:focus { outline: none; border-color: var(--brown); }
    .reply-form button {
      margin-top: 12px;
      background: var(--brown);
      border: none;
      padding: 8px 22px;
      border-radius: 40px;
      color: white;
      font-weight: 600;
      font-size: 12px;
      transition: background 0.15s;
    }
    .reply-form button:hover { background: var(--brown-d); }
    .reply-section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .close-replies-btn {
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 30px;
      transition: background 0.15s;
    }
    .close-replies-btn:hover {
      background: var(--surface);
      color: var(--brown);
    }
    .alert-custom {
      background: #eaf3de;
      border-radius: 16px;
      padding: 12px 20px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
      border: 1.5px solid #c8e0b0;
    }
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 32px;
      margin-bottom: 20px;
    }
    .pagination a, .pagination span {
      padding: 6px 14px;
      border-radius: 40px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      text-decoration: none;
      color: var(--text);
      font-size: 13px;
      font-weight: 600;
      transition: all 0.15s;
    }
    .pagination a:hover { background: var(--surface-2); border-color: var(--brown); color: var(--brown-d); }
    .pagination .active {
      background: var(--brown);
      color: white;
      border-color: var(--brown);
    }
    .empty-forum {
      text-align: center;
      padding: 60px 24px;
      background: var(--surface);
      border-radius: 24px;
      border: 1.5px solid var(--border);
    }
    .empty-forum i { font-size: 52px; color: var(--muted); margin-bottom: 16px; }
    .modal-custom .modal-content {
      border-radius: 24px;
      border: none;
      background: var(--surface);
    }
    .modal-custom .modal-header { border-bottom: 1.5px solid var(--border); padding: 20px 26px; }
    .modal-custom .modal-body { padding: 26px; }
    .modal-custom .form-control {
      border: 1.5px solid var(--border);
      border-radius: 18px;
      padding: 12px 18px;
      font-family: 'Nunito', sans-serif;
    }
    .modal-custom .btn-submit {
      background: var(--brown);
      border: none;
      border-radius: 40px;
      padding: 10px 28px;
      color: white;
      font-weight: 600;
    }
  </style>
</head>
<body>
<div class="cc-layout">
  <?php include '../includes/user/sidebar.php'; ?>
  <div class="cc-main">
    <div class="topbar">
      <div class="topbar-left">
        <span><b>ContraChoice</b></span>
        <span class="topbar-sep">/</span>
        <span class="topbar-page"><?= htmlspecialchars($page_title) ?></span>
      </div>
    </div>
    <div class="content-area">

      <?php if (isset($success)): ?>
        <div class="alert-custom"><i class="fas fa-check-circle text-success"></i> <?= htmlspecialchars($success) ?></div>
      <?php elseif (isset($error)): ?>
        <div class="alert-custom" style="background:#fcebeb; border-color:#f0c0c0;"><i class="fas fa-exclamation-triangle text-danger"></i> <?= htmlspecialchars($error) ?></div>
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
          <div class="post-header">
            <div class="post-meta">
              <span class="anonymous-badge"><i class="fas fa-user-secret"></i> Anonymous</span>
              <span><i class="far fa-calendar-alt"></i> <?= date('M d, Y g:i A', strtotime($post['created_at'])) ?></span>
              <span class="reply-count-clickable" data-post-id="<?= $post_id ?>" onclick="toggleReplies(<?= $post_id ?>)">
                <i class="far fa-comment-dots"></i> <?= $post['reply_count'] ?> replies
              </span>
            </div>
          </div>
          <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
          </div>
          <div class="reply-section" id="replies-<?= $post_id ?>">
            <div class="reply-section-header">
              <span class="anonymous-badge" style="background:var(--surface);"><i class="fas fa-reply-all"></i> Replies</span>
              <button class="close-replies-btn" onclick="toggleReplies(<?= $post_id ?>)">
                <i class="fas fa-times"></i> Close
              </button>
            </div>
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
function toggleReplies(postId) {
  const repliesDiv = document.getElementById('replies-' + postId);
  if (repliesDiv) {
    repliesDiv.classList.toggle('open');
  }
}

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