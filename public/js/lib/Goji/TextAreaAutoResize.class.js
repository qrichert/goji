/**
 * TextAreaAutoResize Class
 *
 * /!\ Doesn't work on IE9 and before because of Element.closest() /!\
 * Yes, there are polyfills for this, but come on. There's one polyfill
 * for IE9 only said to be 'fast' with the use of Element.matches(), and
 * another which works also on IE8 but is slow. But this functionality is
 * so trivial anyways, why bother with polyfills & shit. We just deactivate
 * it on older browsers (thos that don't support Element.closest()). Done. Next.
 *
 */
class TextAreaAutoResize {

	/**
	 * @param parent <textarea> DOM Element
	 */
	constructor(parent) {

		// Quit if browser doesn't support Element.closest()
		if (!window.Element || !Element.prototype.closest) {
			return;
		}

		this.m_parent = parent;
			this.m_parent.style.resize = 'none';
			this.m_parent.style.overflow = 'hidden'; // Prevent scrollbars

			this.m_parent.addEventListener('change', () => { this.resize(); }, false);

			for (let e of ['cut', 'paste', 'drop', 'keydown', 'resizerequest']) { // resizerequest = CustomEvent
				this.m_parent.addEventListener(e, () => { this.resizeDelayed(); }, false);
			}

			this.m_parent.closest('form').addEventListener('reset', () => { this.resizeDelayed(); }, false);

			this.resizeDelayed();
	}

	/**
	 * Resize the TextArea after a resize-triggering event
	 */
	resize() {

		let x = window.scrollX;
		let y = window.scrollY;

		this.m_parent.style.height = 'auto';
		this.m_parent.style.height = this.m_parent.scrollHeight + 'px';

		window.scrollTo(x, y); // Restore scroll to avoid jumps
	}

	/**
	 * Triggers the resize after a small delay.
	 *
	 * Without the delay, the resize would not work properly after pasting & shit
	 */
	resizeDelayed() {
		setTimeout(() => { this.resize(); }, 7);
	}
}
