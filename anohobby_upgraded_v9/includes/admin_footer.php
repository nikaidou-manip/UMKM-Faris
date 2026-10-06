        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.admin-sidebar').classList.toggle('open');
});
</script>
<?php if (isset($extra_js)): ?><script><?= $extra_js ?></script><?php endif; ?>
</body>
</html>
