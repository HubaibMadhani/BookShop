	</main>
	<footer class="site-footer">
		<div class="container">
			<div>© BookShop - All rights reserved</div>
			<div class="social">Follow us: <a href="#">Twitter</a> • <a href="#">Instagram</a></div>
		</div>
	</footer>
	<?php
	// Use a root-relative path so scripts load correctly from subfolders (admin/*)
	$jsPath = '/my_website_xampp_ready/assets/js/script.js';
	$jsFile = __DIR__ . '/../assets/js/script.js';
	$jsVer = file_exists($jsFile) ? filemtime($jsFile) : time();
	?>
	<script src="<?php echo $jsPath ?>?v=<?php echo $jsVer ?>"></script>
</body>
</html>