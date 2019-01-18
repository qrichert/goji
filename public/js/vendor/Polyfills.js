// Element.closest();

(function() {
	if (!Element.prototype.matches) {
		Element.prototype.matches = Element.prototype.msMatchesSelector ||
									Element.prototype.webkitMatchesSelector;
	}

	if (!Element.prototype.closest) {
		Element.prototype.closest = function(s) {
			var el = this;
			if (!document.documentElement.contains(el)) { return null; }
			do {
				if (el.matches(s)) { return el; }
				el = el.parentElement || el.parentNode;
			} while (el !== null);
			return null;
		};
	}
})();

// CustomEvent

(function() {
	if (typeof window.CustomEvent === "function" )
		return false;

	function CustomEvent(event, params) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };
		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
})();

// NodeList.forEach()

(function() {
	if (window.NodeList && !NodeList.prototype.forEach) {

		NodeList.prototype.forEach = function (callback, thisArg) {

			thisArg = thisArg || window;

			for (var i = 0; i < this.length; i++) {
				callback.call(thisArg, this[i], i, this);
			}
		};
	}
})();
