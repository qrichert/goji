/**
 * Dialog class
 *
 * Takes the dialog, adds the necessary classes, puts it inside a parent and adds open/close events.
 *
 * You can set multiple open/close triggers by giving an array of elements as parameter.
 */
class Dialog {

	/**
	 * @param {Element} dialog
	 * @param {Element|Array} triggerOpen
	 * @param {Element|Array} triggerClose
	 */
	constructor(dialog, triggerOpen, triggerClose = null) {

		this.m_parent = document.createElement('div');
			this.m_parent.classList.add('dialog__parent');

		this.m_dialog = dialog;
			this.m_dialog.classList.add('dialog');

		this.m_triggerOpen = triggerOpen;

			if (!Array.isArray(this.m_triggerOpen))
				this.m_triggerOpen = [this.m_triggerOpen];

		this.m_triggerClose = triggerClose;

			if (this.m_triggerClose !== null && !Array.isArray(this.m_triggerClose))
				this.m_triggerClose = [this.m_triggerClose];

		this.m_dialogOpen = false;

		// Replace dialog with dialog inside parent
		this.m_dialog.parentNode.replaceChild(this.m_parent, this.m_dialog);
		this.m_parent.appendChild(this.m_dialog);

		this.attachEvents();
	}

	/**
	 * @private
	 */
	attachEvents() {

		// Open
		for (let el of this.m_triggerOpen)
			el.addEventListener('click', e => this.openDialog(e), false);

		// Close
		this.m_parent.addEventListener('click', e => this.closeDialog(e), false);
		if (this.m_triggerClose !== null)
			for (let el of this.m_triggerClose)
				el.addEventListener('click', e => this.closeDialog(e), false);

		document.addEventListener('keydown', e => {

			if (this.m_dialogOpen && e.key === 'Escape')
				this.closeDialog();

		}, false);

		// Cancel events
		this.m_dialog.addEventListener('click', e => e.stopPropagation(), false);
	}

	/**
	 * @public
	 * @param e
	 */
	openDialog(e = null) {

		if (e !== null) {
			e.preventDefault();
			e.stopPropagation();
		}

		this.m_parent.classList.add('shown');
		this.m_dialogOpen = true;
	}

	/**
	 * @public
	 * @param e
	 */
	closeDialog(e = null) {

		if (e !== null) {
			e.preventDefault();
			e.stopPropagation();
		}

		this.m_parent.classList.remove('shown');
		this.m_dialogOpen = false;
	}
}
