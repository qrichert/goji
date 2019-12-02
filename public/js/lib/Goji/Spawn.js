/**
 * Class Spawn
 *
 * document.querySelectorAll('.spawn').forEach(el => { new Spawn(el); });
 *
 * Callback is called on spawn.
 *
 * Use CSS:
 * --------
 *
 * .spawn {
 *     transition: transform 1.3s ease,
 *                 opacity 1.3s ease;
 *     opacity: 1;
 * }
 *
 * .spawn.waiting {
 *     transform: translateY(3em);
 *     opacity: 0.3;
 * }
 */
class Spawn {

	/**
	 * @param parent
	 * @param callback
	 */
	constructor(parent, callback = null) {

		this.m_parent = parent;
		this.m_callback = callback;
		this.m_shown = false;

		this.m_scroll = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent();
	}

	/**
	 * Returns scroll (between 0 and 1)
	 *
	 * @public
	 * @returns {number} scroll
	 */
	getScroll() {
		return this.m_scroll;
	}

	/**
	 * Called on scroll or window resize
	 *
	 * @private
	 * @param e
	 */
	scrollEvent(e = null) {

		if (this.m_shown)
			return;

		let scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		let top = this.m_parent.offsetTop;
		let height = this.m_parent.offsetHeight;
		let windowHeight = window.innerHeight;

		let currentPos = top - scroll + height;

		const MIN = windowHeight + height;
		const MAX = 0;

		let output = null;

		if (currentPos < MAX)
			output = 1;
		else if (currentPos > MIN)
			output = 0;
		else
			output = 1 - (currentPos / MIN);

		this.m_scroll = output;

		if (this.m_scroll > 0) {

			this.m_parent.classList.remove('waiting');

			this.m_shown = true;

			if (this.m_callback !== null)
				this.m_callback(output);
		}
	}
}
