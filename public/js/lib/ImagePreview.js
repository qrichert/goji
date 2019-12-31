function ImagePreview(parentForm, // <form>
					  fileInput, // <input type="file">
					  previewImage, // <img src="">
					  placeholderImage, // Image to show when nothing is set (!= currentImage, this is for when there is NO image set)
					  currentImage = null, // Value of previewImage on page load (ex : current profile picture)
					  deleteImageInput = null, // Contains 'true' if image need to be deleted
					  deleteImageButton = null) { // Image delete button

	var _this = this;

	this.m_parentForm = parentForm;

		this.m_parentForm.addEventListener('reset', function() {
			_this.reset();
		}, false);

	this.m_fileInput = fileInput;

		this.m_fileInput.addEventListener('change', function(e) {
			_this.changeEvent(e);
		}, false);

	this.m_previewImage = previewImage;
	this.m_previewImageBlob = null;

	this.m_placeholderImage = placeholderImage;

	this.m_currentImage = currentImage;

		if (this.m_currentImage == this.m_placeholderImage)
			this.m_currentImage = null;

	this.m_deleteImageInput = deleteImageInput;

	this.m_deleteImageButton = deleteImageButton;

		if (this.m_deleteImageButton !== null
		   && this.m_deleteImageInput !== null) { // Cant use delete button without this input) {

			if (this.m_currentImage === null)
				this.m_deleteImageButton.style.display = 'none';

		} else {
			this.m_deleteImageButton = null; // Just make sure it's null
		}

		this.m_deleteImageButton.addEventListener('click', function() {
			_this.deleteImage();
		}, false);

	this.clearFileInput = function() {
		// Only way to clear a file input is to replace it with a new one.

		// Copying properties
		var newInput = document.createElement('input');
			newInput.type	= 'file';
			newInput.name	= fileInput.name;
			newInput.id		= fileInput.id;
			newInput.accept	= fileInput.accept;

		// Unlinking old file input
		fileInput.removeEventListener('change', _this.changeEvent, false);
		fileInput.parentNode.replaceChild(newInput, fileInput);
		fileInput = newInput;
		fileInput.addEventListener('change', _this.changeEvent, false);


		if (_this.m_currentImage !== null // Image
			&& _this.m_deleteImageButton !== null) {

			_this.m_deleteImageInput.value = 'false'; // Don't delete
			_this.m_deleteImageButton.style.display = null; // Show button

		} else if (_this.m_currentImage === null // No image
			&& _this.m_deleteImageButton !== null) {

			_this.m_deleteImageInput.value = 'false'; // Don't delete
			_this.m_deleteImageButton.style.display = 'none'; // Hide button
		}

		// Back to page load state

		_this.m_previewImageBlob = null;

		if (_this.m_currentImage !== null)
			previewImage.src = _this.m_currentImage
		else
			previewImage.src = _this.m_placeholderImage;

		// Not on file input because it can get deleted on clear
		_this.m_previewImage.dispatchEvent(new CustomEvent('clear'));
	};

	this.changeEvent = function(e) {

		var file = e.target.files[0];

		if (!file.type.match(/^image\/(bmp|gif|jpe?g|png)$/i)) {
			_this.clearFileInput();
			_this.m_previewImage.dispatchEvent(new CustomEvent('filetypeinvalid'));

			return;
		}

		if (file.size > 7340032) { // IMG: 7 MB, 7 * 1024 * 1024
			_this.clearFileInput();
			_this.m_previewImage.dispatchEvent(new CustomEvent('filetooheavy'));

			return;
		}

		var reader = new FileReader();

		// Convert file (binary) to base64 + MIME (DataUrl format)
		reader.readAsDataURL(file);

		reader.addEventListener('load', function(e) {
			_this.m_previewImage.dispatchEvent(new CustomEvent('loadsuccessful'));

			if (_this.m_deleteImageButton !== null) { // Image or no image

				_this.m_deleteImageInput.value = 'false'; // Don't delete
				_this.m_deleteImageButton.style.display = null; // Unhide button if previously hidden
			}

			_this.m_previewImageBlob = e.target.result;
			previewImage.src = _this.m_previewImageBlob;
		}, false);
	};

	this.deleteImage = function() {

		_this.clearFileInput();

		if (_this.m_deleteImageButton !== null) { // Only possible when image != default

			_this.m_deleteImageInput.value = 'true'; // Delete
			_this.m_deleteImageButton.style.display = 'none'; // Hide button
		}

		_this.m_previewImageBlob = null;
		_this.m_previewImage.src = _this.m_placeholderImage;
	};

	this.reset = function() {
		_this.clearFileInput();
	};

	this.makeCurrentStateDefault = function() {
		/*
			When form is submitted, we want to make the new current state
			the default state (as if the page got reloaded).
		*/

		if (_this.m_previewImageBlob !== null) { // If new image
			_this.m_currentImage = _this.m_previewImageBlob;
		} else { // If no blob, means image is empty because it has been deleted
			_this.m_currentImage = null;
		}

		_this.clearFileInput();
	};
}
