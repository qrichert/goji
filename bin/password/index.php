<?php

	require_once '../../lib/Goji/Toolkit/SwissKnife.class.php';
	require_once '../../lib/Goji/Security/Passwords.class.php';

	use Goji\Security\Passwords;

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex,nofollow">
		<title>Password Maker</title>
		<style>
			input[type=text],
			input[type=password] {
				font-size: 18px;
			}
			p {
				font-size: 11px;
				margin: 0;
				padding: 0;
			}
		</style>
	</head>
	<body>
		<?php
			for ($i = 0; $i < 7; $i++) {
				$nbChars = 12 + $i;
			?>
				<div>
					<p><?= $nbChars; ?> chars</p>
					<?php
						$pwd = Passwords::generatePassword($nbChars);
						$pwd = htmlspecialchars($pwd);
					?>
					<input type="text" id="pwd-<?= $i; ?>" value="<?= $pwd; ?>">
					<a href="#" id="cpy-<?= $i; ?>">Copy</a>
				</div>
				<script>
					(function() {
						let pwd = document.querySelector('#pwd-<?= $i; ?>');

						document.querySelector('#cpy-<?= $i; ?>').addEventListener('click', e => {
							e.preventDefault();
							pwd.select();
							document.execCommand("copy");
						}, false);
					})();
				</script>
			<?php
			}
		?>
		<div>
			<p>Browser password</p>
			<input type="password" id="pwd-text" placeholder="Let your browser generate a password">
			<a href="#" id="cpy-pwd-text">Copy</a> -
			<a href="#" id="cnvrt-pwd-text">Text</a>
		</div>
		<script>
			(function() {
				let pwd = document.querySelector('#pwd-text');

				document.querySelector('#cnvrt-pwd-text').addEventListener('click', e => {

					if (pwd.type == 'password') {
						pwd.type = 'text';
						e.target.textContent = 'Password';
					} else {
						pwd.type = 'password';
						e.target.textContent = 'Text';
					}
				}, false);

				document.querySelector('#cpy-pwd-text').addEventListener('click', e => {
					e.preventDefault();
					pwd.select();
					document.execCommand("copy");
				}, false);
			})();
		</script>
	</body>
</html>
