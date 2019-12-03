/**
 * VideoScroll class
 *
 * Works like ElementScroll, but plays video given scroll percentage.
 *
 * Only smooth on Safari...
 *
 * /!\ You must set the Key Frame Distance to 1 for it to work in any browser other
 * than Safari (Safari works in any case).
 *
 * The keyframe distance is the maximum number of frames before the encoder inserts an i-frame.
 *
 * From StackOverflow: https://stackoverflow.com/questions/55795461/video-is-lagging-when-using-currenttime
 * This setting was not ticked, and was set to 72. So depending on the FPS, it equals to 1-2 seconds.
 * To make all the frames seekable, I've set this to 1 and it worked.
 */
class VideoScroll {

	/**
	 * @param parent
	 * @param callback
	 */
	constructor(parent, callback = null) {

		this.m_parent = parent;
		this.m_callback = callback;

		this.m_scroll = 0;
		this.m_duration = null;
		this.m_playheadPosition = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent();

		this.m_parent.addEventListener('loadedmetadata', () => { this.setVideoDuration(); }, false);
		this.m_parent.addEventListener('durationchange', () => { this.setVideoDuration(); }, false);

		this.setVideoDuration();
	}

	setVideoDuration() {

		let duration = this.m_parent.duration;

		if (!isNaN(duration))
			this.m_duration = duration;
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

		this.setPlayHeadPosition();

		if (this.m_callback !== null)
			this.m_callback(output);
	}

	/**
	 * @private
	 */
	setPlayHeadPosition() {

		// Not loaded
		if (this.m_duration === null)
			return;

		this.m_playheadPosition = this.m_duration * this.m_scroll;

		this.m_parent.currentTime = this.m_playheadPosition;
	}
}
