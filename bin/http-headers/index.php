<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex,nofollow">
		<title>HTTP Headers</title>
	</head>
	<body>

<?php
	$form = '<form action="#" method="post" id="top">
				<input type="text" name="url" placeholder="url">
				<input type="submit" value="Envoyer">
			</form>';

	if (isset($_POST['url'])) {
		$headers = get_headers($_POST['url']);

		echo $form . '<hr>';
		echo '<ul>';
			foreach($headers as $h) {
				echo '<li>' . $h . '</li>';
			}
		echo '</ul>';
		echo '<br><br><hr><a href="#top">Back to top</a>';
		echo '<script>document.querySelector("#top [type=text]").value="' . $_POST['url'] . '";</script>';
	}

	else {
		echo $form;
	}
?>

	</body>
</html>
