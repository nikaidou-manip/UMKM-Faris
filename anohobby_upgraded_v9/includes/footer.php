</main>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo logo-footer">
                <img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>" class="logo-img">
            </div>
            <p class="muted">Toko hobby online terpercaya untuk para builder, duelist, dan collector. Modelkit, TCG cards, PVC figure, tools rakit - semua lengkap di sini.</p>
            <div class="footer-socials">
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Kategori</h4>
            <ul>
                <li><a href="<?= url('category.php?slug=model-kit') ?>">Model Kit</a></li>
                <li><a href="<?= url('category.php?slug=tcg-cards') ?>">TCG Cards</a></li>
                <li><a href="<?= url('category.php?slug=pvc-figure') ?>">PVC Figure</a></li>
                <li><a href="<?= url('category.php?slug=tools-rakit') ?>">Tools Rakit</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Customer Service</h4>
            <ul>
                <li><a href="#">Cara Pemesanan</a></li>
                <li><a href="#">Cara Pembayaran</a></li>
                <li><a href="#">Info Pengiriman</a></li>
                <li><a href="#">Kebijakan Return</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Hubungi Kami</h4>
            <p><i class="fas fa-map-marker-alt"></i> <?= STORE_ADDRESS ?></p>
            <p><i class="fas fa-phone"></i> <?= STORE_PHONE ?></p>
            <p><i class="fas fa-envelope"></i> <?= STORE_EMAIL ?></p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            &copy; <?= date('Y') ?> <?= STORE_NAME ?>. All rights reserved. Made with <i class="fas fa-heart accent"></i> for hobbyist.
        </div>
    </div>
</footer>

<!-- Floating side tab -->
<div class="side-tab">
    <a href="<?= url('cart.php') ?>"><i class="fas fa-shopping-bag"></i> Cart</a>
</div>

<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extra_js)): ?><script><?= $extra_js ?></script><?php endif; ?>
</body>
</html>
