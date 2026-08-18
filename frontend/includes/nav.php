<?php
/**
 * nav.php — role-based navigation chrome (sidebar on desktop, bottom nav on mobile).
 * $user must be set before including (via guard.php or app_chrome()).
 */

require_once __DIR__ . '/icons.php';

$navRole = $user['role'] ?? '';
$navItems = [];

if ($navRole === 'admin') {
    $navItems = [
        ['href' => 'admin/dashboard.php',      'icon' => 'dashboard', 'label' => 'Dashboard'],
        ['href' => 'admin/route-map.php',      'icon' => 'map',       'label' => 'Route Map'],
        ['href' => 'admin/teams.php',          'icon' => 'users',     'label' => 'Teams'],
        ['href' => 'shared/notifications.php', 'icon' => 'bell',      'label' => 'Notifications'],
    ];
} else {
    $navItems = [
        ['href' => 'client/home.php',          'icon' => 'home',      'label' => 'Home'],
        ['href' => 'client/my-bookings.php',   'icon' => 'calendar',  'label' => 'My Bookings'],
        ['href' => 'shared/notifications.php', 'icon' => 'bell',      'label' => 'Notifications'],
        ['href' => 'shared/profile.php',       'icon' => 'user',      'label' => 'Profile'],
    ];
}
?>
<!-- ================= Sidebar (desktop) ================= -->
<aside class="sidebar">
  <div class="brand">
    <div class="brand-logo">G</div>
    <span class="heading-sm">Glorikar</span>
  </div>
  <nav>
    <?php foreach ($navItems as $item): ?>
      <a href="<?= e($item['href']) ?>" data-nav-link>
        <?= icon($item['icon'], 'outline') ?><?= icon($item['icon'], 'filled') ?>
        <span><?= e($item['label']) ?></span>
        <?php if ($item['icon'] === 'bell'): ?>
          <span class="caption" data-notif-count style="margin-left:auto;background:var(--accent);color:var(--white);border-radius:999px;padding:2px 8px;"></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="spacer"></div>
  <div class="user-chip">
    <div class="user-avatar" id="user-chip-initials"></div>
    <div class="col grow">
      <span class="label-sm" id="user-chip-name" data-name="<?= e($user['full_name']) ?>"><?= e($user['full_name']) ?></span>
      <span class="caption text-disabled"><?= e(ucfirst($navRole)) ?></span>
    </div>
    <button class="btn btn-ghost btn-sm" data-logout><?= icon('logout', 'outline') ?></button>
  </div>
</aside>

<!-- ================= Bottom nav (mobile) ================= -->
<nav class="bottom-nav">
  <?php foreach ($navItems as $item): ?>
    <a href="<?= e($item['href']) ?>" data-nav-link>
      <?= icon($item['icon'], 'outline') ?><?= icon($item['icon'], 'filled') ?>
      <span><?= e($item['label']) ?></span>
      <?php if ($item['icon'] === 'bell'): ?>
        <span class="caption hidden" data-notif-count style="position:absolute;margin-top:-34px;background:var(--accent);color:var(--white);border-radius:999px;padding:1px 6px;min-width:18px;text-align:center;"></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</nav>