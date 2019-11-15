/**
 * InPageContentEdit
 */
class InPageContentEdit {

	/**
	 * @param parent
	 */
	constructor(parent) {

		this.m_states = {
			DEFAULT: 1,
			EDITING: 2
		};

		this.m_parent = parent; // Container

			this.m_rawContent = parent.dataset.rawContent;
			this.m_action = parent.dataset.action; // Page to send data

		this.m_editableArea = parent.querySelector('.in-page-content-edit__editable-area');

			this.m_editableAreaDisplay = window.getComputedStyle(this.m_editableArea, null).getPropertyValue('display');

		this.m_editor = parent.querySelector('.in-page-content-edit__editor');
			this.m_editor.style.display = 'none';
			this.m_editor.style.resize = 'none';
			this.m_editor.style.overflow = 'hidden'; // Prevent scrollbars
			this.m_editor.value = this.m_rawContent;
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

		this.m_currentState = this.m_states.DEFAULT;

		this.addListeners();
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

		for (let e of ['cut', 'paste', 'drop', 'keydown', 'resizerequest']) { // resizerequest = CustomEvent
			this.m_editor.addEventListener(e, () => { this.resizeEditorDelayed(); }, false);
		}

		this.resizeEditorDelayed();
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

	activateEditMode() {

		this.m_currentState = this.m_states.EDITING;

		this.m_editableArea.style.display = 'none';
		this.m_editor.style.display = this.m_editableAreaDisplay;
		this.m_editor.focus();
		this.m_editor.setSelectionRange(this.m_editor.value.length, this.m_editor.value.length); // Put cursor at end

		setTimeout(() => {this.deactivateEditMode();}, 3000);
	}

	deactivateEditMode() {

		this.m_currentState = this.m_states.DEFAULT;

		this.m_editableArea.style.display = null;
		this.m_editor.style.display = 'none';
	}
}
