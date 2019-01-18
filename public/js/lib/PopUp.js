function PopUp(parent,
			   trigger,
			   popUp,
			   triggerClosesPopUp = true) { // parent = big container, trigger = click div, popup = popup

	var _this = this;

	this.isShown = false;

	this.triggerClosesPopUp = triggerClosesPopUp;

	this.m_parent = parent;

		this.m_parent.addEventListener(TOUCH_EVENT, function(e) { // Pop up blocker
			e.stopPropagation();
		}, false);

	this.m_trigger = [];

		if (!Array.isArray(trigger)) { // If 'trigger' is not an array

			if (NodeList.prototype.isPrototypeOf(trigger)) { // NodeList, needs conversion

				for (let i = 0; i < trigger.length; i++) {
					this.m_trigger.push(trigger[i]);
				}

			} else { // Regular variable (single DOM Object)

				this.m_trigger.push(trigger);
			}

		} else { // If it is an Array we're good
			this.m_trigger = trigger;
		}

		this.m_trigger.forEach(function(el) {

			el.addEventListener(TOUCH_EVENT, function(e) {

				e.stopPropagation(); // So the click doesn't bubble up to document
									 // and trigger 'requestpopupclose' event

				if (!_this.isShown) {
					_this.showPopUp();
				} else if (_this.isShown && _this.triggerClosesPopUp) {
					_this.hidePopUp();
				}

			}, false);
		});

	this.m_popUp = popUp;

		this.setPopUp = function(popUp) {
			_this.m_popUp = popUp;
		};

	this.showPopUp = function() {

		if (_this.isShown)
			return;

		_this.closeAllPopUps();

		_this.isShown = true;
		_this.m_popUp.classList.add('shown');

		_this.m_parent.dispatchEvent(new CustomEvent('popupshown'));
	};

	this.hidePopUp = function() {

		if (!_this.isShown)
			return;

		_this.isShown = false;

		// Start transition
		_this.m_popUp.style.opacity = 0;

		setTimeout(function() { // Wait till transition is over
			_this.m_popUp.style.opacity = null;
			_this.m_popUp.classList.remove('shown');
		}, 100); // Value from .popup CSS opacity transition

		_this.m_parent.dispatchEvent(new CustomEvent('popuphidden'));
	};

	this.closeAllPopUps = function() {
		document.dispatchEvent(new CustomEvent('requestpopupclose'));
	};

		document.addEventListener(TOUCH_EVENT, function(e) {
			_this.closeAllPopUps();
		}, false);

		document.addEventListener('requestpopupclose', function() {
			_this.hidePopUp();
		}, false);
}


function Dialog(parent,
				trigger,
				dialog) { PopUp.call(this, parent, trigger, dialog, true); // Inherits from PopUp

	var _this = this;

	this.m_dialog = dialog;

	this.setPopUp(this.m_parent); // In PopUp the changes are made to the pop up
						 		  // Here we want it on the frame

	// Dialog parent (overlay) closes PopUps
	this.m_parent.addEventListener(TOUCH_EVENT, function(e) {
		_this.hidePopUp();
	}, false);

	// But not if we click on the dialog itself
	this.m_dialog.addEventListener(TOUCH_EVENT, function(e) {
		e.stopPropagation();
	}, false);

}

