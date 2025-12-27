 <?php
include 'includes/config.php';
// include header after POST handling so AJAX responses can be returned as clean JSON

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Read raw inputs first, then validate/normalize.
	$name_raw = filter_input(INPUT_POST, 'name', FILTER_DEFAULT);
	$email_raw = filter_input(INPUT_POST, 'email', FILTER_DEFAULT);
	$message_raw = filter_input(INPUT_POST, 'message', FILTER_DEFAULT);

	$name = $name_raw !== null ? trim((string)$name_raw) : '';
	$email = $email_raw !== null ? trim((string)$email_raw) : '';
	$message = $message_raw !== null ? trim((string)$message_raw) : '';

	$errors = [];
	if ($name === '' || mb_strlen($name) < 2) $errors[] = 'Please enter a valid name.';
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
	if ($message === '' || mb_strlen($message) < 10) $errors[] = 'Message must be at least 10 characters.';

	if (empty($errors)) {
		// If reCAPTCHA is configured, verify the token
		if (!empty($recaptcha_secret)) {
			$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
			$verify = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptcha_secret) . '&response=' . urlencode($recaptcha_response));
			$ok = false;
			if ($verify !== false) {
				$jr = json_decode($verify, true);
				$ok = !empty($jr['success']);
			}
			if (!$ok) {
				$response['errors'] = ['reCAPTCHA verification failed. Please try again.'];
				if ($isAjax) { header('Content-Type: application/json'); echo json_encode($response); exit; }
			}
		}

		try {
			$stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
			$stmt->execute([$name, $email, $message]);
			$response['success'] = true;
			$response['message'] = "Thanks — we'll get back to you soon.";
		} catch (PDOException $e) {
			error_log('contact.php DB insert error: ' . $e->getMessage());
			$response['errors'] = ['Unable to save your message. Please try again later.'];
		}
	} else {
		$response['errors'] = $errors;
	}

	if ($isAjax) {
		header('Content-Type: application/json');
		echo json_encode($response);
		exit;
	}
}
include 'includes/header.php';
?>

<section class="container">
	<div class="contact-grid">
		<div class="contact-card">
			<h1>Contact Us</h1>
			<p class="muted">Have a question or feedback? Send us a message and we'll respond within 1–2 business days.</p>

			<div id="contact-alert" class="alert" style="display:none">
				<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($response['errors']) && !$response['success']): ?>
					<?php foreach($response['errors'] as $err): ?>
						<div class="alert error"><?php echo htmlspecialchars($err); ?></div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $response['success']): ?>
				<div class="alert success"><?php echo htmlspecialchars($response['message']); ?></div>
			<?php else: ?>
			<form id="contact-form" method="post" class="contact-form" novalidate>
				<div class="form-row">
					<div class="form-group">
						<label for="name">Name</label>
						<input id="name" name="name" type="text" required placeholder="Your name">
						<div class="form-error" data-for="name"></div>
					</div>

					<div class="form-group">
						<label for="email">Email</label>
						<input id="email" name="email" type="email" required placeholder="you@example.com">
						<div class="form-error" data-for="email"></div>
					</div>
				</div>

				<div class="form-group">
					<label for="message">Message</label>
					<textarea id="message" name="message" rows="6" required placeholder="Tell us how we can help"></textarea>
					<div class="form-error" data-for="message"></div>
				</div>

				<div class="form-actions">
					<button class="btn primary" type="submit">Send message</button>
					<button class="btn alt" type="button" id="contact-reset">Reset</button>
				</div>
			</form>
			<?php endif; ?>
		</div>

		<aside class="contact-info">
			<div class="info-card">
				<h3>Get in touch</h3>
				<p class="muted">You can also email us directly at <a href="mailto:info@example.com">info@example.com</a> or call <strong>+1 (555) 555-5555</strong>.</p>
				<h4>Visit</h4>
				<p class="muted">123 Book St, Booktown, BK 12345</p>
				<h4>Hours</h4>
				<p class="muted">Mon–Fri: 9am–5pm</p>
			</div>
		</aside>
	</div>
</section>

<?php include 'includes/footer.php'; ?>