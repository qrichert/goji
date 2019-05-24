<?php

	require_once '../../lib/Goji/Toolkit/SwissKnife.class.php';
	require_once '../../lib/Goji/Security/Passwords.class.php';

	use Goji\Toolkit\SwissKnife;
	use Goji\Security\Passwords;

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex,nofollow">
		<title>Générateur de mot de passe</title>
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
						error_log($nbChars . ': ' . mb_strlen($pwd));
						$pwd = htmlspecialchars($pwd);
					?>
					<input type="text" id="pwd-<?= $i; ?>" value="<?= $pwd; ?>">
					<a href="#" id="cpy-<?= $i; ?>" >Copy</a>
				</div>
				<script>
					(function() {
						var pwd = document.querySelector('#pwd-<?= $i; ?>');
						document.querySelector('#cpy-<?= $i; ?>').addEventListener('click', function(e) {
							e.preventDefault();
							pwd.select();
							document.execCommand("copy");
						}, false);
					})();
				</script>
			<?php
			}
		?>
	</body>
</html>
