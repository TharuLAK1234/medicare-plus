<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 style="font-size:1.5rem;margin:0">Patient Messages</h1>
    <a href="<?= url('doctor/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
  </div>

  <div class="row g-4" style="min-height:500px">

    <!-- Thread list -->
    <div class="col-lg-4">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-3 border-bottom" style="font-weight:700;font-size:.875rem">
          Conversations
          <?php
          $totalUnread = array_sum(array_column($threads, 'unread'));
          if ($totalUnread > 0): ?>
          <span class="badge" style="background:var(--mp-primary);color:#fff;border-radius:9999px;
                         font-size:.7rem;margin-left:.5rem"><?= $totalUnread ?></span>
          <?php endif; ?>
        </div>
        <?php if (empty($threads)): ?>
          <div class="text-center py-5" style="color:var(--mp-text-3);font-size:.875rem">
            <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-25"></i>
            No patient messages.
          </div>
        <?php else: ?>
          <?php foreach ($threads as $t):
            $isActive = $thread && $thread['id'] == $t['id'];
          ?>
          <a href="<?= url('messages/' . $t['id']) ?>" style="text-decoration:none">
            <div class="p-3 border-bottom"
                 style="background:<?= $isActive ? 'var(--mp-primary-light)' : 'transparent' ?>;
                        transition:background .1s"
                 onmouseover="<?= $isActive ? '' : "this.style.background='#f8fafc'" ?>"
                 onmouseout="<?= $isActive ? '' : "this.style.background='transparent'" ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div style="font-weight:600;font-size:.875rem;color:var(--mp-text-1)">
                  <?= e($t['other_name']) ?>
                </div>
                <?php if ($t['unread'] > 0): ?>
                <span style="background:var(--mp-primary);color:#fff;border-radius:9999px;
                             font-size:.7rem;font-weight:700;padding:.15rem .5rem">
                  <?= $t['unread'] ?>
                </span>
                <?php endif; ?>
              </div>
              <div style="font-size:.78rem;color:var(--mp-text-3);font-style:italic;margin:.1rem 0 .2rem">
                <?= e($t['subject']) ?>
              </div>
              <?php if ($t['last_msg']): ?>
              <div style="font-size:.75rem;color:var(--mp-text-3);
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= e(mb_substr($t['last_msg'], 0, 55)) ?>
              </div>
              <?php endif; ?>
              <?php if ($t['last_at']): ?>
              <div style="font-size:.7rem;color:var(--mp-text-3);text-align:right;margin-top:.2rem">
                <?= formatDate($t['last_at']) ?>
              </div>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Message pane -->
    <div class="col-lg-8">
      <?php if ($thread): ?>
      <div class="mp-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center"
             style="flex-shrink:0">
          <div>
            <div style="font-weight:700"><?= e($thread['patient_name']) ?></div>
            <div style="font-size:.8rem;color:var(--mp-text-3)"><?= e($thread['subject']) ?></div>
          </div>
          <span class="mp-badge pending">Patient</span>
        </div>
        <div id="msgScroll" style="flex:1;overflow-y:auto;padding:1.25rem;min-height:300px;max-height:420px">
          <?php foreach ($msgs as $m):
            $mine = $m['sender_id'] == Auth::id();
          ?>
          <div class="d-flex mb-3 <?= $mine ? 'justify-content-end' : '' ?>">
            <div style="max-width:75%">
              <?php if (!$mine): ?>
              <div style="font-size:.72rem;color:var(--mp-text-3);margin-bottom:.2rem">
                <?= e($m['sender_name']) ?>
              </div>
              <?php endif; ?>
              <div style="background:<?= $mine ? 'var(--mp-primary)' : '#f1f5f9' ?>;
                          color:<?= $mine ? '#fff' : 'var(--mp-text-1)' ?>;
                          border-radius:<?= $mine ? '14px 14px 4px 14px' : '14px 14px 14px 4px' ?>;
                          padding:.65rem 1rem;font-size:.875rem;line-height:1.6">
                <?= nl2br(e($m['body'])) ?>
              </div>
              <div style="font-size:.7rem;color:var(--mp-text-3);margin-top:.2rem;
                          text-align:<?= $mine ? 'right' : 'left' ?>">
                <?= date('d M H:i', strtotime($m['created_at'])) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="p-3 border-top" style="flex-shrink:0">
          <form method="POST" action="<?= url('messages/' . $thread['id'] . '/reply') ?>"
                class="d-flex gap-2">
            <?= CSRF::field() ?>
            <textarea name="body" class="form-control" rows="2"
                      placeholder="Reply to patient…" required
                      style="resize:none;border-radius:10px"></textarea>
            <button type="submit" class="btn btn-primary align-self-end px-3">
              <i class="bi bi-send-fill"></i>
            </button>
          </form>
        </div>
      </div>
      <?php else: ?>
      <div class="mp-card text-center" style="padding:4rem">
        <i class="bi bi-chat-text fs-1 d-block mb-3 opacity-25" style="color:var(--mp-text-3)"></i>
        <div style="font-weight:600">Select a conversation</div>
        <p style="color:var(--mp-text-3);font-size:.875rem">
          Patient messages will appear here.
        </p>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
</div>

<script>
const ms = document.getElementById('msgScroll');
if (ms) ms.scrollTop = ms.scrollHeight;
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
