<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
global $pdo;

$user_id = $_SESSION['user_id'];

// Auto-cleanup expired user vouchers
try {
    cleanup_expired_user_vouchers($pdo);
} catch (Throwable $e) {}

// ===== HANDLE CLAIM VOUCHER =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'claim') {
    $voucher_id = (int)($_POST['voucher_id'] ?? 0);
    if ($voucher_id > 0) {
        list($ok, $msg) = claim_voucher($pdo, $user_id, $voucher_id);
        set_flash($ok ? 'success' : 'error', $msg);
    }
    header('Location: ' . url('my_vouchers.php'));
    exit;
}

// Load data
$claimable_vouchers = get_claimable_vouchers($pdo);
$my_vouchers_available = get_user_vouchers($pdo, $user_id, 'available');
$my_vouchers_used = get_user_vouchers($pdo, $user_id, 'used');
$my_vouchers_expired = get_user_vouchers($pdo, $user_id, 'expired');
$user_points = get_user_points($pdo, $user_id);

$page_title = 'Voucher Saya';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <a href="<?= url('profile.php') ?>">Profile</a>
        <span class="sep">/</span>
        <span>Voucher Saya</span>
    </div>
</div>

<!-- ============ MY VOUCHERS HERO ============ -->
<section class="mv-hero">
    <div class="mv-hero-bg"></div>
    <div class="mv-hero-pattern"></div>
    <div class="mv-hero-content">
        <span class="hb-badge yellow"><i class="fas fa-ticket-alt"></i> MY VOUCHERS</span>
        <h1 class="hb-title-lg">Voucher & Koin AnoHobby</h1>
        <p class="hb-subtitle">Klaim voucher gratis, pakai saat checkout, dan kumpulkan koin untuk diskon tambahan.</p>
        <div class="mv-hero-stats">
            <span><i class="fas fa-ticket-alt"></i> <strong><?= count($my_vouchers_available) ?></strong> voucher aktif</span>
            <span><i class="fas fa-coins" style="color:#FFD700"></i> <strong><?= number_format($user_points) ?></strong> koin</span>
        </div>
    </div>
</section>

<!-- ============ CLAIMABLE VOUCHERS ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-gift accent"></i> Voucher Tersedia untuk Diklaim</h2>
    </div>

    <?php if (empty($claimable_vouchers)): ?>
        <div class="empty-state">
            <i class="fas fa-ticket-alt"></i>
            <h3>Belum ada voucher untuk diklaim</h3>
            <p>Pantau terus halaman ini untuk voucher baru!</p>
        </div>
    <?php else: ?>
        <div class="voucher-claim-grid">
            <?php foreach ($claimable_vouchers as $v):
                $already_claimed = has_user_claimed_voucher($pdo, $user_id, $v['id']);
                $quota_left = !empty($v['claim_quota']) ? max(0, (int)$v['claim_quota'] - (int)$v['claimed_count']) : null;
            ?>
                <div class="voucher-claim-card">
                    <div class="vcc-left">
                        <div class="vcc-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="vcc-amount">
                            <?php if ($v['discount_type'] === 'percent'): ?>
                                <span class="vcc-pct"><?= (float)$v['discount_value'] ?>%</span>
                            <?php else: ?>
                                <span class="vcc-rp"><?= rupiah($v['discount_value']) ?></span>
                            <?php endif; ?>
                            <small>OFF</small>
                        </div>
                    </div>
                    <div class="vcc-right">
                        <div class="vcc-code"><?= e($v['code']) ?></div>
                        <div class="vcc-desc"><?= e($v['description'] ?: 'Voucher diskon') ?></div>
                        <div class="vcc-meta">
                            <span><i class="fas fa-shopping-bag"></i> Min. <?= rupiah($v['min_purchase']) ?></span>
                            <?php if (!empty($v['max_discount'])): ?>
                                <span><i class="fas fa-cap"></i> Max <?= rupiah($v['max_discount']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($quota_left !== null): ?>
                            <div class="vcc-quota">Sisa <strong><?= $quota_left ?></strong> dari <?= (int)$v['claim_quota'] ?> voucher</div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <input type="hidden" name="action" value="claim">
                            <input type="hidden" name="voucher_id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm" <?= $already_claimed?'disabled':'' ?>>
                                <i class="fas fa-<?= $already_claimed?'check':'hand-holding' ?>"></i>
                                <?= $already_claimed ? 'Sudah Diklaim' : 'Klaim Voucher' ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ============ MY ACTIVE VOUCHERS ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-wallet accent"></i> Voucher Aktif Saya (<?= count($my_vouchers_available) ?>)</h2>
    </div>

    <?php if (empty($my_vouchers_available)): ?>
        <div class="empty-state">
            <i class="fas fa-wallet"></i>
            <h3>Belum punya voucher aktif</h3>
            <p>Klaim voucher di atas untuk mulai menghemat!</p>
        </div>
    <?php else: ?>
        <div class="my-vouchers-grid">
            <?php foreach ($my_vouchers_available as $uv):
                $expires_in_days = max(0, floor((strtotime($uv['expires_at']) - time()) / 86400));
            ?>
                <div class="my-voucher-card">
                    <div class="mvc-left">
                        <div class="mvc-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="mvc-amount">
                            <?php if ($uv['discount_type'] === 'percent'): ?>
                                <span class="mvc-pct"><?= (float)$uv['discount_value'] ?>%</span>
                            <?php else: ?>
                                <span class="mvc-rp"><?= rupiah($uv['discount_value']) ?></span>
                            <?php endif; ?>
                            <small>OFF</small>
                        </div>
                    </div>
                    <div class="mvc-right">
                        <div class="mvc-code"><?= e($uv['voucher_code_snapshot']) ?></div>
                        <div class="mvc-desc"><?= e($uv['description_snapshot'] ?: user_voucher_discount_label($uv)) ?></div>
                        <div class="mvc-meta">
                            <span><i class="fas fa-shopping-bag"></i> Min. <?= rupiah($uv['min_purchase']) ?></span>
                            <span class="<?= $expires_in_days <= 3 ? 'text-danger' : 'text-muted' ?>">
                                <i class="fas fa-clock"></i> Berlaku <?= $expires_in_days ?> hari lagi
                            </span>
                        </div>
                        <a href="<?= url('checkout.php') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-shopping-cart"></i> Pakai Sekarang
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ============ USED & EXPIRED ============ -->
<?php if (!empty($my_vouchers_used) || !empty($my_vouchers_expired)): ?>
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-history accent"></i> Riwayat Voucher</h2>
    </div>
    <div class="panel" style="background:white;border-radius:12px;padding:20px;border:1px solid var(--border-light);overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Diskon</th>
                    <th>Min. Belanja</th>
                    <th>Status</th>
                    <th>Diklaim</th>
                    <th>Dipakai/Berakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_merge($my_vouchers_used, $my_vouchers_expired) as $uv): ?>
                    <tr>
                        <td><strong style="font-family:monospace;"><?= e($uv['voucher_code_snapshot']) ?></strong></td>
                        <td><?= e(user_voucher_discount_label($uv)) ?></td>
                        <td><?= rupiah($uv['min_purchase']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $uv['status']==='used'?'completed':'cancelled' ?>">
                                <?= e(ucfirst($uv['status'])) ?>
                            </span>
                        </td>
                        <td><?= tgl_indo($uv['claimed_at']) ?></td>
                        <td>
                            <?php if ($uv['used_at']): ?>
                                <?= tgl_indo($uv['used_at']) ?>
                            <?php elseif ($uv['expires_at']): ?>
                                <span class="text-muted"><?= tgl_indo($uv['expires_at']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
