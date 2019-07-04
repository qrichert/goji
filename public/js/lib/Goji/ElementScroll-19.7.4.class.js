/**
 * ElementScroll class
 *
 * How to use it:
 * --------------
 *
 * ElementScroll::construct(element, callback = null);
 *
 * let elementScroll = new ElementScroll(
 *     document.querySelector('#element-to-watch'),
 *     scroll => console.log(scroll)
 * );
 *
 * You don't necessarily need a callback. You can use ElementScroll.getScroll(): float to get
 * the scroll at any given time. (This is not a static method!)
 *
 * Explanation:
 * ------------
 * 
 * ElementScroll gets the scroll of a single element across the page.
 *
 * Page:
 *      <elem> <------ At the moment the element fully disappears (top: elementHeight), scroll = 1
 *  --------------
 * |              |
 * |              |
 * |              |
 * |    <elem> <------ When the element is in the middle across the page, scroll = 0.5
 * |              |
 * |              |
 * |              |
 *  --------------
 *      <elem> <------ At the moment the element appears (bottom: -elementHeight), scroll = 0
 */
class ElementScroll {

	/**
	 * @param parent
	 * @param callback
	 */
	constructor(parent, callback = null) {

		this.m_parent = parent;
		this.m_callback = callback;

		this.m_scroll = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent()
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

		if (this.m_callback !== null)
			this.m_callback(output);
	}
}
