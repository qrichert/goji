/**
 * Class GObject
 *
 * Modeled after QObject https://doc.qt.io/qt-5/qobject.html
 */
class GObject {

	/**
	 * @param parent
	 */
	constructor(parent = null) {
		this.m_parent = parent;
	}

	/**
	 * @returns {*}
	 */
	getParent() {
		return this.m_parent;
	}

	/**
	 * @param parent
	 */
	setParent(parent) {
		this.m_parent = parent;
	}

	/**
	 * @param sender
	 * @param signal
	 * @param slot
	 * @param stopPropagation (optional)
	 * @param stopImmediatePropagation (optional)
	 */
	static connect(sender, signal, slot, stopPropagation = true, stopImmediatePropagation = true) {

		sender.addEventListener(signal, e => {

			if (stopPropagation)
				e.stopPropagation();

			if (stopImmediatePropagation)
				e.stopImmediatePropagation();

			slot(e);

		}, false);
	}

	/**
	 * @param sender
	 * @param signal
	 * @param slot
	 */
	static disconnect(sender, signal, slot) {
		sender.removeEventListener(signal, slot);
	}
}
