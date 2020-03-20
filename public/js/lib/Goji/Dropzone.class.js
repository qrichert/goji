/**
 * Item inside dropzone
 */
class DropzoneItem {

	constructor(dropzone, file, action, callbackSuccess, inputName, defaultFileIcon) {

		this.m_parent = null;
		this.m_dropzone = dropzone;
		this.m_file = file;
		this.m_action = action;
		this.m_callbackSuccess = callbackSuccess;
		this.m_inputName = inputName;
		this.m_defaultFileIcon = defaultFileIcon;
		this.m_progress = null;

		this.m_xhr = null;
		this.m_uploadInProgress = false;

		this.m_fileTypesImages = [
			'image/gif',
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/svg+xml'
		];

		this.m_fileTypesVideos = [
			'video/mp4'
		];

		this.buildElement();

		this.m_dropzone.appendChild(this.m_parent);

		this.startUpload();
	}

	extractFileName(file) {

		let fileName = file.name.match(/^(.+)\.([^.]+)$/);

		if (fileName !== null)
			return fileName[1];

		return '';
	}

	readFile(file, icon) {

		let reader = new FileReader();

			reader.addEventListener('error', e => {
				e.preventDefault();

				let replacementIcon = document.createElement('p');
					replacementIcon.classList.add('dropzone__item-icon');
					replacementIcon.textContent = file.name;

				if (typeof icon.parentElement === 'undefined' || icon.parentElement === null)
					icon = replacementIcon;
				else
					icon.parentElement.replaceChild(replacementIcon, icon);

			}, false);

			reader.addEventListener('load', () => {
				icon.src = reader.result;
			}, false);

		reader.readAsDataURL(file);
	}

	buildElement() {

		// Preparations
		let file = this.m_file.get('file');
		let icon = null;

		if (this.m_fileTypesImages.includes(file.type) && file.size < 30000000) { // If image we can display

			icon = document.createElement('img');
				icon.alt = '';

			this.readFile(file, icon);

		} else if (this.m_fileTypesVideos.includes(file.type) && file.size < 30000000) {

			icon = document.createElement('video');
				icon.autoplay = true;
				icon.loop = true;
				icon.muted = true;
				icon.volume = 0;
				icon.setAttribute('type', file.type);
				icon.setAttribute('webkit-playsinline', '');
				icon.setAttribute('playsinline', '');
				icon.playsinline = true;

				icon.addEventListener('canplaythrough', () => {
					if (icon.tagName === 'video')
						icon.play();
				}, false);

			this.readFile(file, icon);

		} else { // Can't display

			if (this.m_defaultFileIcon !== null) {
				icon = document.createElement('div');

				let iconFileIcon = document.createElement('img');
					iconFileIcon.src = this.m_defaultFileIcon;
					iconFileIcon.alt = '';
						icon.appendChild(iconFileIcon);

				let iconFileDescription = document.createElement('p');
					iconFileDescription.textContent = this.extractFileName(file);
						icon.appendChild(iconFileDescription);
			}

			else {
				icon = document.createElement('p');
					icon.textContent = this.extractFileName(file);
			}
		}

		// Building
		let item = document.createElement('div');
			item.classList.add('dropzone__item');

			item.addEventListener('click', e => {
				e.preventDefault();
				e.stopPropagation();
			}, false);

		// let icon = document.createElement(...);
			icon.classList.add('dropzone__item-icon');
				item.appendChild(icon);

		let progressBar = document.createElement('div');
			progressBar.classList.add('dropzone__item-progress');
				item.appendChild(progressBar);

		this.m_progress = document.createElement('div');
			progressBar.appendChild(this.m_progress);

		let cancelButton = document.createElement('div');
			cancelButton.classList.add('dropzone__item-cancel');
				item.appendChild(cancelButton);

			cancelButton.addEventListener('click', () => { this.cancelUpload(); }, false);

		this.m_parent = item;
	}

	uploadLoad(e) {

		if (!this.m_uploadInProgress || this.m_xhr === null)
			return;

		if (this.m_xhr.readyState !== 4
			|| parseInt(this.m_xhr.status, 10) !== 200) {
			this.uploadError(e);
			return;
		}

		let response = null;

		try {
			response = JSON.parse(this.m_xhr.responseText);
		} catch (e) {
			this.uploadError(e);
			return;
		}

		if (this.m_callbackSuccess !== null)
			this.m_callbackSuccess(response);

		this.endUpload();
	}

	uploadError(e) {
		this.endUpload();
		this.m_parent.classList.add('error');
	}

	uploadAbort(e) {
		this.endUpload();
		this.m_dropzone.removeChild(this.m_parent);
	}

	uploadProgress(e) {

		if (!e.lengthComputable)
			return;

		let progress = e.loaded / e.total;

		if (isNaN(progress))
			progress = 0;

		this.m_progress.style.width = (progress * 100) + '%';

		if (progress >= 1) {
			this.m_parent.classList.remove('cancelable');
		}
	}

	startUpload() {

		this.m_parent.classList.add('uploading');
		this.m_parent.classList.add('cancelable');
		this.m_progress.style.width = '0%';

		let data = new FormData();
			data.append(this.m_inputName, this.m_file.get('file'));

		this.m_xhr = new XMLHttpRequest();

			this.m_xhr.addEventListener('load', e => { this.uploadLoad(e); }, false);
			this.m_xhr.addEventListener('error', e => { this.uploadError(e); }, false);
			this.m_xhr.addEventListener('abort', e => { this.uploadAbort(e); }, false);
			this.m_xhr.upload.addEventListener('progress', e => { this.uploadProgress(e); }, false);

			this.m_xhr.open('POST', this.m_action);
			this.m_xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			this.m_xhr.send(data);

		this.m_uploadInProgress = true;
	}

	endUpload() {

		this.m_parent.classList.remove('uploading');
		this.m_parent.classList.remove('cancelable');
		this.m_progress.style.width = '0%';

		this.m_uploadInProgress = false;
		this.m_xhr = null;
	}

	cancelUpload() {
		if (this.m_uploadInProgress)
			this.m_xhr.abort();
	}
}


/**
 * Options:
 * - default_file_icon (string) Image to use when file type not recognized (default = empty, no icon)
 */
class Dropzone {

	/**
	 * @param dropzone
	 * @param fileInput
	 * @param callbackSuccess
	 * @param callbackErrorFileTypeInvalid
	 * @param callbackErrorFileTooHeavy
	 * @param options
	 */
	constructor(dropzone,
	            fileInput,
	            callbackSuccess = null,
	            callbackErrorFileTypeInvalid = null,
	            callbackErrorFileTooHeavy = null,
	            options = null) {

		this.m_dropzone = dropzone;
			this.m_dropzone.classList.add('waiting-for-files');

		this.m_fileInput = fileInput;
			this.m_fileInput.style.display = 'none';
			this.m_fileInput.multiple = true;

		this.m_callbackSuccess = callbackSuccess;

		this.m_callbackErrorFileTooHeavy = callbackErrorFileTooHeavy;
		this.m_callbackErrorFileTypeInvalid = callbackErrorFileTypeInvalid;

		this.m_form = null;

			let inspectedElement = this.m_fileInput;

			while (this.m_form === null) {

				inspectedElement = inspectedElement.parentElement;

				if (typeof inspectedElement === 'undefined' || inspectedElement === null) // end of document or problem
					break;
				else if (inspectedElement.tagName.toLowerCase() === 'form')
					this.m_form = inspectedElement;
				else if (inspectedElement.tagName.toLowerCase() === 'html') // <html> element, end of document
					break;
			}

			if (this.m_form === null)
				throw '<form> parent of file input not found.';

			this.m_form.enctype = 'multipart/form-data';

		this.m_action = this.m_form.action;

			if (this.m_action === '' || this.m_action === null)
				this.m_action = location.href;

		this.m_files = [];

		if (options === null)
			options = {};

		this.m_fileTypesAllowed = this.m_fileInput.accept || '';

			if (this.m_fileTypesAllowed === '')
				this.m_fileTypesAllowed = [];
			else
				this.m_fileTypesAllowed = this.m_fileTypesAllowed.split(',');

		this.m_maxFileSize = this.m_fileInput.dataset.maxFileSize || -1;
			this.m_maxFileSize = parseInt(this.m_maxFileSize, 10);

		this.m_defaultFileIcon = this.coalesce(options, 'default_file_icon', null);

		this.m_successfulDropHappened = false; // To clear dropzone or not

		this.addEventListeners();
	}

	coalesce(object, property, defaultValue) {
		if (typeof object == 'undefined' || object === null)
			return defaultValue;

		if (!object.hasOwnProperty(property))
			return defaultValue;

		return object[property];
	}

	addEventListeners() {

		this.m_dropzone.addEventListener('dragenter', e => {
			e.preventDefault();
			this.startDrag();
		}, false);

		this.m_dropzone.addEventListener('dragleave', e => {
			e.preventDefault();
			this.stopDrag();
		}, false);

		this.m_dropzone.addEventListener('dragover', e => {
			e.preventDefault();
		}, false);

		this.m_dropzone.addEventListener('drop', e => {
			e.preventDefault();
			this.stopDrag();
			this.drop(e);
		}, false);

		this.m_fileInput.addEventListener('change', e => {
			e.preventDefault();
			this.filesChanged();
		}, false);
	}

	startDrag() {
		this.m_dropzone.classList.add('hover');
	}

	stopDrag() {
		this.m_dropzone.classList.remove('hover');
	}

	extractFileType(file) {
		let fileType = file.name.match(/\.([^.]+)$/); // Hello.mp4 -> mp4

		if (fileType !== null) {
			fileType = fileType[1]; // First capture group
		} else {
			fileType = file.type.match(/\/([^.]+)$/); // video/mp4 -> mp4

			if (fileType !== null)
				fileType = fileType[1];
			else
				fileType = '?';
		}

		return '.' + fileType;
	}

	fileIsValid(file) {

		if (file.type === '') {

			if (this.m_callbackErrorFileTypeInvalid !== null)
				this.m_callbackErrorFileTypeInvalid('_folder_');

			return false;
		}

		if (this.m_fileTypesAllowed.length > 0) {

			let fileType = this.extractFileType(file);

			if (!this.m_fileTypesAllowed.includes(fileType)) {

				if (this.m_callbackErrorFileTypeInvalid !== null)
					this.m_callbackErrorFileTypeInvalid(fileType);

				return false;
			}
		}

		if (this.m_maxFileSize > 0 && file.size > this.m_maxFileSize) {

			if (this.m_callbackErrorFileTooHeavy !== null)
				this.m_callbackErrorFileTooHeavy(file.size);

			return false;
		}

		return true;
	}

	/**
	 * When dropping files into the dropzone.
	 *
	 * @param e
	 */
	drop(e) {

		let files = [];

		if (e.dataTransfer.items) {

			for (let item of e.dataTransfer.items) {
				if (item.kind === 'file') { // Text would be 'string' for example

					item = item.getAsFile();

					if (!this.fileIsValid(item))
						continue;

					let file = new Map();
						file.set('file', item);

					files.push(file);
				}
			}

		} else {

			for (let item of e.dataTransfer.files) {

				if (!this.fileIsValid(item))
					continue;

				let file = new Map();
					file.set('file', item);

				files.push(file);
			}
		}

		this.newFiles(files);
	}

	/**
	 * When choosing files through the system's dialog
	 */
	filesChanged() {
		let files = [];

		for (let i = 0; i < this.m_fileInput.files.length; i++) {

			let item = this.m_fileInput.files[i];

			if (!this.fileIsValid(item))
				continue;

			let file = new Map();
				file.set('file', item);

			files.push(file);
		}

		this.newFiles(files);
	}

	newFiles(files) {

		if (files.length <= 0)
			return;

		if (!this.m_successfulDropHappened) {
			this.m_successfulDropHappened = true;
			while (this.m_dropzone.firstChild)
				this.m_dropzone.removeChild(this.m_dropzone.firstChild);
			this.m_dropzone.classList.remove('waiting-for-files');
		}

		for (let file of files) {
			let dropzoneItem = new DropzoneItem(this.m_dropzone,
												file,
												this.m_action,
												this.m_callbackSuccess,
												this.m_fileInput.name,
												this.m_defaultFileIcon);
			file.set('dropzone_item', dropzoneItem);
		}

		this.m_files = this.m_files.concat(files);
	}
}
