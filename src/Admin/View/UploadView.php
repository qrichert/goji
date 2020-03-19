<main>
	<section class="text">
		<h1><?= $tr->_('UPLOAD_MAIN_TITLE'); ?></h1>

		<?= $uploadForm->render(); ?>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>">
				<?= $tr->_('GO_BACK_TO_ADMIN_AREA'); ?>
			</a>
		</p>
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/Dropzone.class.js'
]);
?>
<script>
	let dropzone = document.querySelector('#upload__dropzone');
	let fileinput = document.querySelector('#upload__file');

	let success = response => {

		if (response === null)
			return;

		if (typeof response.file_path === 'undefined'
		    && response.file_path === null) {
			return;
		}

		if (typeof response.file_name === 'undefined'
		    && response.file_name === null) {
			return;
		}

		let fileName = response.file_path + response.file_name;
		let thumbName = response.file_path + 'thumb_' + response.file_name;

		let img = document.createElement('img');
			img.src = thumbName;

		img.addEventListener('click', () => {
			alert(`%{WEBROOT}${fileName}`)
		}, false);

		document.querySelector('section').appendChild(img);
		// Append child to uploads ordered flex-reverse
	};

	let errorFileTypeInvalid = fileType => {
		alert(fileType);
	};

	let errorFileTooHeavy = fileSize => {
		alert(fileSize);
	};

	new Dropzone(dropzone, fileinput, success, errorFileTypeInvalid, errorFileTooHeavy, {
		// default_file_icon: '/img/placeholder?w=200&h=200&t=F'
	});
</script>
