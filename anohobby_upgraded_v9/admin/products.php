<?php
require_once __DIR__ . '/../includes/admin_header.php';
global $pdo;

$categories = get_categories($pdo);
$action = $_GET['action'] ?? 'list';     // list | add | edit
$edit_id = (int)($_GET['id'] ?? 0);

// ===== HANDLE POST (tambah/edit) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)str_replace(['Rp ', '.', ','], ['', '', '.'], $_POST['price'] ?? '0');
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $image_2 = trim($_POST['image_2'] ?? '') ?: null;
    $image_3 = trim($_POST['image_3'] ?? '') ?: null;
    $image_4 = trim($_POST['image_4'] ?? '') ?: null;
    $image_5 = trim($_POST['image_5'] ?? '') ?: null;
    $badge = trim($_POST['badge'] ?? '') ?: null;
    $release_date = trim($_POST['release_date'] ?? '');
    $brand = trim($_POST['brand'] ?? '') ?: null;

    // Flash Sale handling: jika badge = FLASH SALE, set flash_sale_ends_at + generate weighted random discount
    $flash_sale_ends_at = null;
    $flash_sale_discount = 0;
    if ($badge === 'FLASH SALE') {
        $fs_dt = trim($_POST['flash_sale_ends_at'] ?? '');
        if ($fs_dt !== '') {
            $flash_sale_ends_at = date('Y-m-d H:i:s', strtotime($fs_dt));
        }
        // jika kosong, default +3 hari
        if (!$flash_sale_ends_at) {
            $flash_sale_ends_at = date('Y-m-d H:i:s', strtotime('+3 days'));
        }
        // Generate weighted random discount 25-90% (atau pakai yang sudah ada jika valid)
        $existing_disc = (int)($edit_data['flash_sale_discount'] ?? 0);
        if ($existing_disc >= 25 && $existing_disc <= 90 && $action === 'edit') {
            $flash_sale_discount = $existing_disc;  // pertahankan kalau sudah ada saat edit
        } else {
            $flash_sale_discount = generate_weighted_flash_sale_discount();
        }
    }

    if ($name === '' || $category_id === 0 || $price <= 0) {
        set_flash('error', 'Nama, kategori, dan harga wajib diisi dengan benar.');
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, image_2=?, image_3=?, image_4=?, image_5=?, badge=?, flash_sale_ends_at=?, flash_sale_discount=?, release_date=?, brand=? WHERE id=?");
            $stmt->execute([$category_id, $name, $description, $price, $stock, $image, $image_2, $image_3, $image_4, $image_5, $badge, $flash_sale_ends_at, $flash_sale_discount, $release_date, $brand, $id]);
            $msg_extra = $badge === 'FLASH SALE' ? " (diskon flash sale: {$flash_sale_discount}%)" : '';
            set_flash('success', "Produk \"{$name}\" berhasil diperbarui{$msg_extra}.");
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image, image_2, image_3, image_4, image_5, badge, flash_sale_ends_at, flash_sale_discount, release_date, brand) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $description, $price, $stock, $image, $image_2, $image_3, $image_4, $image_5, $badge, $flash_sale_ends_at, $flash_sale_discount, $release_date, $brand]);
            $msg_extra = $badge === 'FLASH SALE' ? " (diskon flash sale: {$flash_sale_discount}%)" : '';
            set_flash('success', "Produk \"{$name}\" berhasil ditambahkan{$msg_extra}.");
        }
        header('Location: ' . url('admin/products.php'));
        exit;
    }
}

// ===== HANDLE DELETE =====
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $p = get_product($pdo, $del_id);
    if ($p) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$del_id]);
        set_flash('success', "Produk \"{$p['name']}\" telah dihapus.");
    }
    header('Location: ' . url('admin/products.php'));
    exit;
}

// ===== LOAD EDIT DATA =====
$edit_data = null;
if ($action === 'edit' && $edit_id) {
    $edit_data = get_product($pdo, $edit_id);
    if (!$edit_data) {
        set_flash('error', 'Produk tidak ditemukan.');
        header('Location: ' . url('admin/products.php'));
        exit;
    }
}

// ===== LOAD LIST =====
$search = trim($_GET['q'] ?? '');
$cat_filter = $_GET['cat'] ?? '';
$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND p.name LIKE ?"; $params[] = "%$search%"; }
if ($cat_filter) { $sql .= " AND c.slug = ?"; $params[] = $cat_filter; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'Data Produk';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- ============ FORM TAMBAH/EDIT PRODUK ============ -->
    <div class="panel" style="max-width:780px;">
        <div class="panel-header">
            <h2><i class="fas fa-<?= $action==='edit'?'edit':'plus' ?>"></i> <?= $action==='edit'?'Edit Produk':'Tambah Produk Baru' ?></h2>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <form method="post" action="<?= url('admin/products.php') ?>">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? 0 ?>">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($edit_data['name'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori <span class="req">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($edit_data['category_id'] ?? 0)==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand" class="form-control">
                    <option value="">-- Tidak ada / Generic --</option>
                    <?php foreach (['Bandai', 'Bootleg', 'Third Party', 'Mochin'] as $b): ?>
                        <option value="<?= e($b) ?>" <?= ($edit_data['brand'] ?? '')===$b?'selected':'' ?>><?= e($b) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Opsional. Saat ini hanya ditampilkan &amp; bisa difilter di halaman kategori Model Kit.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= e($edit_data['description'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Harga (Rp) <span class="req">*</span></label>
                    <input type="text" name="price" class="form-control" value="<?= isset($edit_data['price']) ? rupiah($edit_data['price']) : '' ?>" required>
                    <div class="form-hint">Contoh: 750000 atau Rp 750.000</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok <span class="req">*</span></label>
                    <input type="number" name="stock" class="form-control" value="<?= e($edit_data['stock'] ?? 0) ?>" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">URL Gambar Utama (Cover)</label>
                <input type="text" name="image" class="form-control" value="<?= e($edit_data['image'] ?? '') ?>" placeholder="https://...">
                <div class="form-hint">Tempel URL gambar cover (wajib). Akan tampil sebagai foto utama di kartu produk &amp; galeri detail.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Galeri Foto Tambahan (opsional, maks. 4)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <input type="text" name="image_2" class="form-control" value="<?= e($edit_data['image_2'] ?? '') ?>" placeholder="Foto 2 URL">
                    <input type="text" name="image_3" class="form-control" value="<?= e($edit_data['image_3'] ?? '') ?>" placeholder="Foto 3 URL">
                    <input type="text" name="image_4" class="form-control" value="<?= e($edit_data['image_4'] ?? '') ?>" placeholder="Foto 4 URL">
                    <input type="text" name="image_5" class="form-control" value="<?= e($edit_data['image_5'] ?? '') ?>" placeholder="Foto 5 URL">
                </div>
                <div class="form-hint">Total maksimal 5 foto (1 cover + 4 tambahan). Kosongkan yang tidak terpakai - jumlah foto yang tampil di halaman produk otomatis menyesuaikan (boleh kurang dari 5, tergantung barang).</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Badge (opsional)</label>
                    <select name="badge" id="badge_select" class="form-control" onchange="toggleFlashSale()">
                        <option value="">-- Tidak ada --</option>
                        <?php foreach (['HOT','SALE','PRE-ORDER','NEW','FLASH SALE'] as $b): ?>
                            <option value="<?= $b ?>" <?= ($edit_data['badge'] ?? '')===$b?'selected':'' ?>><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">
                        <strong>NEW</strong>: Otomatis hilang setelah 7 hari dari tanggal ditambahkan.<br>
                        <strong>FLASH SALE</strong>: Wajib isi tanggal berakhir di bawah.
                    </div>
                </div>
                <div class="form-group" id="flash_sale_group" <?= (($edit_data['badge'] ?? '')!=='FLASH SALE'?'style="display:none;"':'') ?>>
                    <label class="form-label">Flash Sale Berakhir Pada <span class="req">*</span></label>
                    <input type="datetime-local" name="flash_sale_ends_at" class="form-control" value="<?= !empty($edit_data['flash_sale_ends_at']) ? date('Y-m-d\TH:i', strtotime($edit_data['flash_sale_ends_at'])) : date('Y-m-d\TH:i', strtotime('+3 days')) ?>">
                    <div class="form-hint">Set tanggal & jam flash sale berakhir. Setelah lewat, badge akan otomatis hilang.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status / Tanggal Rilis</label>
                    <input type="text" name="release_date" class="form-control" value="<?= e($edit_data['release_date'] ?? '') ?>" placeholder="Ready Stock / Release: Dec 2026">
                </div>
            </div>
            <script>
            function toggleFlashSale() {
                var sel = document.getElementById('badge_select');
                var grp = document.getElementById('flash_sale_group');
                if (sel.value === 'FLASH SALE') {
                    grp.style.display = '';
                } else {
                    grp.style.display = 'none';
                }
            }
            </script>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> <?= $action==='edit'?'Update':'Tambah' ?> Produk</button>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-lg">Batal</a>
        </form>
    </div>

<?php else: ?>
    <!-- ============ LIST PRODUK ============ -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fas fa-box"></i> Tabel Data Produk (<?= count($products) ?>)</h2>
            <div style="display:flex;gap:8px;">
                <a href="<?= url('admin/flash_sale.php') ?>" class="btn btn-outline"><i class="fas fa-bolt"></i> Atur Flash Sale</a>
                <a href="<?= url('admin/products.php?action=add') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Produk</a>
            </div>
        </div>

        <!-- Filter -->
        <form method="get" action="" class="filter-bar" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama produk..." class="form-control" style="max-width:280px;">
            <select name="cat" class="form-control" style="max-width:200px;">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['slug']) ?>" <?= $cat_filter===$c['slug']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</a>
        </form>

        <?php if (empty($products)): ?>
            <div class="empty-state"><i class="fas fa-box-open"></i><h3>Belum ada produk</h3><p>Tambahkan produk pertama Anda!</p></div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Brand</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Badge</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><img src="<?= e($p['image']) ?>" alt="" class="thumb"></td>
                                <td style="max-width:280px;">
                                    <strong><?= e($p['name']) ?></strong><br>
                                    <small class="text-muted"><?= e($p['release_date'] ?: 'Ready Stock') ?></small>
                                </td>
                                <td><?= e($p['category_name']) ?></td>
                                <td><?= $p['brand'] ? e(ucwords($p['brand'])) : '<span class="text-muted">-</span>' ?></td>
                                <td class="text-primary" style="font-weight:700;"><?= rupiah($p['price']) ?></td>
                                <td>
                                    <?php if ($p['stock'] > 0): ?>
                                        <span class="text-success"><?= $p['stock'] ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['badge'])): ?>
                                        <span class="status-badge status-<?= e($p['badge']==='FLASH SALE'?'pending':($p['badge']==='NEW'?'completed':'pending')) ?>"><?= e($p['badge']) ?></span>
                                        <?php if ($p['badge']==='FLASH SALE' && !empty($p['flash_sale_ends_at'])): ?>
                                            <br><small class="text-muted">sd <?= date('d/m H:i', strtotime($p['flash_sale_ends_at'])) ?></small>
                                            <?php if (!empty($p['flash_sale_discount']) && (int)$p['flash_sale_discount'] > 0): ?>
                                                <br><small class="text-success">-<?= (int)$p['flash_sale_discount'] ?>% off</small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($p['badge']==='NEW' && !empty($p['created_at'])): ?>
                                            <br><small class="text-muted"><?= max(0, 7 - floor((time() - strtotime($p['created_at'])) / 86400)) ?> hari lagi</small>
                                        <?php endif; ?>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= url('admin/products.php?action=edit&id=' . $p['id']) ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" target="_blank" title="Lihat"><i class="fas fa-eye"></i></a>
                                        <a href="<?= url('admin/products.php?delete=' . $p['id']) ?>" class="danger" data-confirm="Yakin hapus produk &quot;<?= e($p['name']) ?>&quot;? Stok & data terkait akan terhapus."><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
