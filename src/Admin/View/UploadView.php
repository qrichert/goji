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
	<section class="text" id="upload__uploads">
	</section>
</main>

<?php
$template->linkFiles([
	'js/lib/Goji/Dropzone.class.min.js'
]);
?>
<script>
	(function () {
		let dropzone = document.querySelector('#upload__dropzone');
		let fileinput = document.querySelector('#upload__file');
		let uploadsFrame = document.querySelector('#upload__uploads');

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
				img.alt = '';

				img.addEventListener('click', () => {
					alert(`web:/${fileName}`)
				}, false);

			uploadsFrame.insertBefore(img, uploadsFrame.firstChild);
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
	})();

	(function () {
		let uploadsFrame = document.querySelector('#upload__uploads');
		let uploads = <?= json_encode($uploads); ?>;

		let docFrag = document.createDocumentFragment();

		for (let upload of uploads) {
			let img = document.createElement('img');
				img.src = upload.path + upload.thumb;
				img.alt = '';
					docFrag.appendChild(img);

				img.addEventListener('click', () => {
					alert(`web:/${upload.path + upload.name}`);
				}, false);
		}

		uploadsFrame.appendChild(docFrag);
	})();
</script>
