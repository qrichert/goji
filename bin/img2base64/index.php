<?php

	if (isset($_FILES['image'])) {

		$imageInfos = pathinfo($_FILES['image']['name']);
			$imageExtension = $imageInfos['extension'];

		if (preg_match("#^jpe?g|png|gif|bmp$#i", $imageExtension)) {

			$data = file_get_contents($_FILES['image']['tmp_name']);

			header('Content-Type: text/txt; charset=utf8');
			header('Content-Disposition: attachment; filename="' . $imageInfos['filename'] . '.txt"');

			echo 'data:' . $_FILES['image']['type'] . ';base64,' . base64_encode($data);

			exit;
		}
	}

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Image to base64 txt</title>
	</head>
	<body>
		<form action="#" method="post" enctype="multipart/form-data">
			<input type="file" name="image">
			<input type="submit">
		</form>
	</body>
</html>
