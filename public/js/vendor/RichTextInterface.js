function RichTextEdit(parent, maxLength = -1, singleLine = false) {
	var _this = this;

	this.m_parent = parent;
	this.m_maxLength = null;
	this.m_singleLine = null;

// MAX LENGTH

	this.getMaxLength = function() {
		return this.m_maxLength;
	};

	this.setMaxLength = function(maxLength) {
		if (maxLength <= 0 || maxLength == false)
			this.m_maxLength = -1;
		else
			this.m_maxLength = maxLength;
	};

		this.setMaxLength(maxLength);

// SINGLE LINE

	this.getSingleLine = function() {
		return this.m_singleLine;
	};

	this.setSingleLine = function(singleLine) {
		if (singleLine)
			this.m_singleLine = true;
		else
			this.m_singleLine = false;
	};

		this.setSingleLine(singleLine);

// PARENT

	this.m_parent.contentEditable = true;
		this.m_parent.innerHTML = this.m_parent.innerHTML.replace(/(\s+)$/, '');
		this.m_parent.innerHTML = this.m_parent.innerHTML.replace(/^(\s+)/, '');

// FUNCTIONS

	this.isChildOf = function(node, parent) {
		while (node != undefined && node != null && node.nodeName.toLowerCase() != 'body') {
			if (node == parent)
				return true;

			node = node.parentNode;
		}

		return false;
	};

	function isChildOfTextEdit(el) {
		return _this.isChildOf(el, _this.m_parent)
	}

// TEXT

	this.prependText = function(text) {

		_this.m_parent.focus();

		if (!window.getSelection)
			return false;

		var selection = window.getSelection();

		if (selection.rangeCount <= 0)
			return false;

		if (!isChildOfTextEdit(selection.anchorNode))
			return false;

		var range = selection.getRangeAt(0); // We care about the first range only (usually only one)
			var startNode = selection.anchorNode;
			var startOffset = range.startOffset;

			var boundaryRange = range.cloneRange();
			var startTextNode = document.createTextNode(text);
			var endTextNode = document.createTextNode('');

				boundaryRange.collapse(false);
				boundaryRange.insertNode(endTextNode);
				boundaryRange.setStart(startNode, startOffset);
				boundaryRange.collapse(true);
				boundaryRange.insertNode(startTextNode);

			// Putting the original selection back

			range.setStartAfter(startTextNode);
			range.setEndBefore(endTextNode);
			selection.removeAllRanges();
			selection.addRange(range);

		return true;
	};

	this.appendText = function(text) {

		_this.m_parent.focus();

		if (!window.getSelection)
			return false;

		var selection = window.getSelection();

		if (selection.rangeCount <= 0)
			return false;

		if (!isChildOfTextEdit(selection.focusNode))
			return false;

		var range = selection.getRangeAt(0); // We care about the first range only (usually only one)
			var startNode = selection.anchorNode;
			var startOffset = range.startOffset;

			var boundaryRange = range.cloneRange();
			var startTextNode = document.createTextNode('');
			var endTextNode = document.createTextNode(text);

				boundaryRange.collapse(false);
				boundaryRange.insertNode(endTextNode);
				boundaryRange.setStart(startNode, startOffset);
				boundaryRange.collapse(true);
				boundaryRange.insertNode(startTextNode);

			// Putting the original selection back

			range.setStartAfter(startTextNode);
			range.setEndBefore(endTextNode);
			selection.removeAllRanges();
			selection.addRange(range);

		return true;
	};

	this.surroundText = function(before, after) {
		if (_this.prependText(before) && _this.appendText(after))
			return true;
		else
			return false;
	};

	this.insertAtSelection = function(text) {

		_this.m_parent.focus();

		if (!window.getSelection)
			return false;

		var selection = window.getSelection();

		if (selection.rangeCount <= 0)
			return false;

		if (!isChildOfTextEdit(selection.anchorNode))
			return false;

		var range = selection.getRangeAt(0); // We care about the first range only (usually only one)
			var startNode = selection.anchorNode;
			var startOffset = range.startOffset;

			var boundaryRange = range.cloneRange();
			var startTextNode = document.createTextNode(text);
			var endTextNode = document.createTextNode('');

				range.deleteContents();
				boundaryRange.collapse(false);
				boundaryRange.insertNode(endTextNode);
				boundaryRange.setStart(startNode, startOffset);
				boundaryRange.collapse(true);
				boundaryRange.insertNode(startTextNode);

			// Putting the original selection back

			range.setStartAfter(startTextNode);
			range.setEndBefore(endTextNode);
			selection.removeAllRanges();
			selection.addRange(range);

		return true;
	};

// NODES

	this.surroundNode = function(node) {
		_this.m_parent.focus();

		if (!window.getSelection)
			return false;

		var selection = window.getSelection();

		if (selection.rangeCount <= 0)
			return false;

		if (!isChildOfTextEdit(selection.focusNode))
			return false;

		var range = selection.getRangeAt(0);

			node.appendChild(range.cloneContents()); // Appending the selection to the node

				range.deleteContents();
				range.insertNode(node);
				range.collapse(false);

		selection.removeAllRanges();
		selection.addRange(range);

		return true;
	};

	this.clearHTML = function() {
		_this.m_parent.textContent = _this.m_parent.textContent;

		return true;
	};

	this.getSelectionAnchorNode = function() { // Useful to test whether there is a certain parent

		_this.m_parent.focus();

		if (!window.getSelection)
			return null;

		var selection = window.getSelection();

		if (selection.rangeCount <= 0)
			return null;

		if (!isChildOfTextEdit(selection.focusNode))
			return null;

		return selection.anchorNode;
	};

// GETTERS

	this.getFormatedText = function() {
		return this.m_parent.innerHTML;
	};

	this.getPlainText = function() {
		return this.m_parent.textContent;
	};

	this.isEmpty = function() {
		return (this.m_parent.textContent == '' || this.m_parent.innerHTML == "<br>");
	};

	this.getLength = function() {
		return this.getPlainText().length;
	};

// EVENTS

	this.m_parent.addEventListener('keydown', function(e) {
		if (_this.m_singleLine && e.key == 'Enter') {
			e.preventDefault();
		}

		if (_this.m_maxLength != -1 && _this.m_parent.textContent.length >= _this.m_maxLength) {
			if (!e.metaKey && !e.ctrlKey) {
					if (e.key != 'Backspace'
					 && e.key != 'ArrowUp'
					 && e.key != 'ArrowRight'
					 && e.key != 'ArrowDown'
					 && e.key != 'ArrowLeft'
					) {
							e.preventDefault();
					}
			}
		}
	}, false);

	this.m_parent.addEventListener('paste', function(e) {

		if (_this.m_maxLength != -1) {

			var pastedText = undefined;

			if (window.clipboardData && window.clipboardData.getData) { // IE
				pastedText = window.clipboardData.getData('Text');
			} else if (e.clipboardData && e.clipboardData.getData) {
				pastedText = e.clipboardData.getData('text/plain');
			}

			if (_this.m_singleLine) {
				pastedText = pastedText.replace('<br>', '');
				pastedText = pastedText.replace('<br/>', '');
				pastedText = pastedText.replace('<br />', '');
				pastedText = pastedText.replace(/[\n\r|\n|\r]/g, '');
			}

			var parentTextLength = _this.m_parent.textContent.length;
			var pastedTextLength = pastedText.length;

			if ((parentTextLength + pastedTextLength) > _this.m_maxLength) {
				e.preventDefault();

				var overflow = (parentTextLength + pastedTextLength) - _this.m_maxLength; // Pasted text is 'overflow' too long
				var pastedText = pastedText.substr(0, pastedTextLength - overflow);

				_this.insertAtSelection(pastedText);
			}
		}
	}, false);

	this.m_parent.addEventListener('drop', function(e) {

		if (_this.m_maxLength != -1) {

			var pastedText = e.dataTransfer.getData('text/plain');

			if (_this.m_singleLine) {
				pastedText = pastedText.replace('<br>', '');
				pastedText = pastedText.replace('<br/>', '');
				pastedText = pastedText.replace('<br />', '');
				pastedText = pastedText.replace(/[\n\r|\n|\r]/g, '');
			}

			var parentTextLength = _this.m_parent.textContent.length;
			var pastedTextLength = pastedText.length;

			if ((parentTextLength + pastedTextLength) > _this.m_maxLength || true) {
				e.preventDefault();

				var overflow = (parentTextLength + pastedTextLength) - _this.m_maxLength; // Pasted text is 'overflow' too long
				var pastedText = pastedText.substr(0, pastedTextLength - overflow);

				_this.m_parent.insertAdjacentHTML('beforeend', pastedText);
			}
		}
	}, false);
}
