function ImageUpload(fileInputParentForm, // Form containing the <file input>
					  fileInput, // The file input itself
					  previewImage, // The <img> element displaying the current image

					  defaultImage, // Image to show when nothing selected (placeholder)
					  currentImage, // Image currently set

					  deleteImageInput, // <hidden> input containing "true" when delete request
					  deleteImage, // Delete image <button> (or <a>)

					  errorMessage, // Error text (<p>)
					  errorMessageFileTypeInvalid, // Error message file type invalid
					  errorMessageFileTooHeavy // Error message file too heavy
					) {

	var _this = this;

	this.m_imagePreview = new ImagePreview(fileInputParentForm,
											fileInput,
											previewImage,
											defaultImage,
											currentImage,
											deleteImageInput,
											deleteImage
										   );

	this.m_fileInputParentForm = fileInputParentForm;
	this.m_fileInput = fileInput;
	this.m_previewImage = previewImage;

	this.m_defaultImage = defaultImage;
	this.m_currentImage = currentImage;

	this.m_deleteImageInput = deleteImageInput;
	this.m_deleteImage = deleteImage;

	this.m_errorMessage = errorMessage;

	this.m_errorMessageFileTypeInvalid = errorMessageFileTypeInvalid;
	this.m_errorMessageFileTooHeavy = errorMessageFileTooHeavy;


	this.m_previewImage.addEventListener('clear', function() {
		_this.m_errorMessage.classList.remove('shown');
		_this.m_errorMessage.innerHTML = '';
	}, false);

	this.m_previewImage.addEventListener('filetypeinvalid', function() {
		_this.m_errorMessage.classList.add('shown');
		_this.m_errorMessage.innerHTML = _this.m_errorMessageFileTypeInvalid;
	}, false);

	this.m_previewImage.addEventListener('filetooheavy', function() {
		_this.m_errorMessage.classList.add('shown');
		_this.m_errorMessage.innerHTML = _this.m_errorMessageFileTooHeavy;
	}, false);

	this.m_previewImage.addEventListener('loadsuccessful', function() {
		_this.m_errorMessage.classList.remove('shown');
		_this.m_errorMessage.innerHTML = '';
	}, false);
}
