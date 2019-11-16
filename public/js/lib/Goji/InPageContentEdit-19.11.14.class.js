/**
 * InPageContentEdit
 */
class InPageContentEdit {

	/**
	 * @param parent
	 */
	constructor(parent) {

		this.m_parent = parent; // Container

			this.m_action = parent.dataset.action; // Page to send data to
			this.m_pageId = parent.dataset.pageId; // Page that hosts the content
			this.m_text = JSON.parse(parent.dataset.text);
			this.m_contentId = parent.dataset.contentId; // If of content element
			this.m_rawContent = parent.dataset.rawContent; // Content without formatting, as in database

		this.m_editableArea = parent.querySelector('.in-page-content-edit__editable-area');

			this.m_editableAreaDisplay = window.getComputedStyle(this.m_editableArea, null).getPropertyValue('display');

		this.m_editor = parent.querySelector('.in-page-content-edit__editor');
			this.m_editor.value = this.m_rawContent;
			this.m_editor.style.display = 'none';
			this.m_editor.style.resize = 'none';
			this.m_editor.style.overflow = 'hidden'; // Prevent scrollbars
			this.m_editor.style.width = '100%';
			this.m_editor.style.minHeight = this.getEditableAreaProperty('line-height');
			this.m_editor.style.fontSize = this.getEditableAreaProperty('font-size');
			this.m_editor.style.fontFamily = this.getEditableAreaProperty('font-family');
			this.m_editor.style.fontWeight = this.getEditableAreaProperty('font-weight');
			this.m_editor.style.lineHeight = this.getEditableAreaProperty('line-height');
			this.m_editor.style.color = this.getEditableAreaProperty('color');
			this.m_editor.style.textDecoration = this.getEditableAreaProperty('text-decoration');
			this.m_editor.style.padding = this.getEditableAreaProperty('padding');
			this.m_editor.style.margin = this.getEditableAreaProperty('margin');
			this.m_editor.style.border = this.getEditableAreaProperty('border');
			this.m_editor.style.boxShadow = this.getEditableAreaProperty('box-shadow');
			this.m_editor.style.borderRadius = this.getEditableAreaProperty('border-radius');

		this.m_buttons = parent.querySelector('.in-page-content-edit__buttons');
			this.m_buttons.style.display = 'none';

		this.m_buttonSave = this.m_buttons.querySelector('[data-action="save"]');
		this.m_buttonPreview = this.m_buttons.querySelector('[data-action="preview"]');
		this.m_buttonCancel = this.m_buttons.querySelector('[data-action="cancel"]');

		this.addListeners();

		this.checkIfEmpty();
	}

	/**
	 * @param property
	 * @return {string}
	 */
	getEditableAreaProperty(property) {
		return window.getComputedStyle(this.m_editableArea, null).getPropertyValue(property);
	}

	addListeners() {

		this.m_parent.addEventListener('click', () => {}, false);

		// Editable Area

		this.m_editableArea.addEventListener('click', () => { this.activateEditMode(); }, false);

		// Editor Auto-Resize

		// Quit if browser doesn't support Element.closest()
		if (!window.Element || !Element.prototype.closest) {
			return;
		}

		this.m_editor.addEventListener('change', () => { this.resizeEditor(); }, false);
		this.m_editor.addEventListener('keydown', () => { this.setModified(true); }, false);

		for (let e of ['cut', 'paste', 'drop', 'keydown', 'resizerequest']) { // resizerequest = CustomEvent
			this.m_editor.addEventListener(e, () => { this.resizeEditorDelayed(); }, false);
		}

		//this.resizeEditorDelayed();

		// Buttons

		this.m_buttonSave.addEventListener('click', () => { this.saveEdition(); }, false);
		this.m_buttonPreview.addEventListener('click', () => { this.previewEdition(); }, false);
		this.m_buttonCancel.addEventListener('click', () => { this.cancelEdition(); }, false);
	}

	/**
	 * Resize the TextArea after a resize-triggering event
	 */
	resizeEditor() {

		let x = window.scrollX;
		let y = window.scrollY;

		this.m_editor.style.height = 'auto';
		this.m_editor.style.height = this.m_editor.scrollHeight + 'px';

		window.scrollTo(x, y); // Restore scroll to avoid jumps
	}

	/**
	 * Triggers the resize after a small delay.
	 *
	 * Without the delay, the resize would not work properly after pasting & shit
	 */
	resizeEditorDelayed() {
		setTimeout(() => { this.resizeEditor(); }, 7);
	}

	setModified(modified) {

		if (modified)
			this.m_parent.classList.add('modified');
		else
			this.m_parent.classList.remove('modified');
	}

	/**
	 * What to do if no content.
	 */
	checkIfEmpty() {

		// Use editor, because raw content isn't always updated (like with pause)
		if (!this.m_editor.value.match(/^\s*$/))
			return;

		// Set placeholder text
		this.m_editableArea.textContent = this.m_text.placeholder;
	}

	activateEditMode() {

		this.m_editableArea.style.display = 'none';
		this.m_editor.style.display = this.m_editableAreaDisplay;
		this.m_editor.focus();
		this.m_editor.setSelectionRange(this.m_editor.value.length, this.m_editor.value.length); // Put cursor at end
		this.m_buttons.style.display = 'block';

		this.resizeEditorDelayed();
	}

	deactivateEditMode() {
		this.m_editableArea.style.display = null;
		this.m_editor.style.display = 'none';
		this.m_buttons.style.display = 'none';
	}

	/**
	 * @param {Boolean} lock
	 */
	lockEditor(lock) {
		this.m_editor.disabled = lock;
		this.m_buttonSave.disabled = lock;
		this.m_buttonPreview.disabled = lock;
		this.m_buttonCancel.disabled = lock;
	}

	xhrSuccess(response, callbackSuccess = null, callbackError = null) {

		try {
			response = JSON.parse(response);

			if (response.status === 'ERROR')
				throw 0;

			this.m_editableArea.innerHTML = response.content;

			if (callbackSuccess !== null)
				callbackSuccess();

			this.deactivateEditMode();
			this.lockEditor(false);

		} catch (e) {
			this.xhrError(callbackError);
		}

		this.checkIfEmpty();
	}

	xhrError(callbackError = null) {

		if (callbackError !== null)
			callbackError();

		this.lockEditor(false);
	}

	xhrPost(action, callbackSuccess = null, callbackError = null) {

		let data = new FormData();
			data.append('content-id', this.m_contentId);
			data.append('page-id', this.m_pageId);
			data.append('action', action);
			data.append('content', this.m_editor.value);

		SimpleRequest.post(this.m_action, data,
			(response) => {
				this.xhrSuccess(response, callbackSuccess, callbackError);
			},
			() => {
				this.xhrError(callbackError);
			}
		);
	}

	/**
	 * Show modifications, but don't save them (neither online, nor locally)
	 *
	 * Push temporary text online to get formatted version & display it
	 */
	previewEdition() {

		let onSuccess = () => {

			this.m_buttonPreview.classList.remove('loading');
			this.m_buttonPreview.classList.add('loaded');

			setTimeout(() => {
				this.m_buttonPreview.classList.remove('loaded');
			}, 1500);
		};

		let onError = () => {

			this.m_buttonPreview.classList.remove('loading');
			this.m_buttonPreview.classList.add('failed');

			setTimeout(() => {
				this.m_buttonPreview.classList.remove('failed');
			}, 1500);
		};

		this.lockEditor(true);
		this.m_buttonPreview.classList.add('loading');
		this.xhrPost('get-formatted-content', onSuccess, onError);
	}

	/**
	 * Save and update everything
	 */
	saveEdition() {

		let onSuccess = () => {

			this.setModified(false);

			this.m_buttonSave.classList.remove('loading');
			this.m_buttonSave.classList.add('loaded');

			setTimeout(() => {
				this.m_buttonSave.classList.remove('loaded');
			}, 1500);
		};

		let onError = () => {

			this.m_buttonSave.classList.remove('loading');
			this.m_buttonSave.classList.add('failed');

			setTimeout(() => {
				this.m_buttonSave.classList.remove('failed');
			}, 1500);
		};

		this.m_rawContent = this.m_editor.value;
		this.lockEditor(true);
		this.m_buttonSave.classList.add('loading');
		this.xhrPost('save-content', onSuccess, onError);
	}

	/**
	 * Cancel everything, go back to previous state
	 */
	cancelEdition() {
		this.m_editor.value = this.m_rawContent;
		this.setModified(false);
		this.checkIfEmpty();

		this.deactivateEditMode();
	}
}
