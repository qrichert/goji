/**
 * ActionItem Class
 *
 * Example:
 *
 * let clearCache = document.querySelector('#admin__clear-cache');
 *
 * let clearCacheAction = new ActionItem(clearCache);
 *
 * clearCache.addEventListener('click', () => {
 *
 *     clearCacheAction.startAction();
 *
 *     // Do some stuff here, you can set progress if you need
 *     clearCacheAction.setProgress(0.5);
 *
 *     clearCacheAction.endAction();
 *
 * }, false);
 *
 */
class ActionItem {

	/**
	 * @param parent The .action-item
	 */
	constructor(parent) {

		this.m_parent = parent;

		this.m_progress = this.m_parent.querySelector('.action-item__progress');
			this.hideProgress(); // To be sure
			this.setProgress(0); // To be sure

		this.m_icon = this.m_parent.querySelector('.action-item__icon');
		this.m_caption = this.m_parent.querySelector('.action-item__caption');

		this.addListeners();
	}

	/**
	 * @private
	 */
	addListeners() {
		this.m_parent.addEventListener('click', () => { this.startAction(); }, false);
	}

	/**
	 * @private
	 */
	showProgress() {
		this.setProgress(0);
		this.m_progress.classList.add('loading');
		this.m_parent.classList.add('loading');
	}

	/**
	 * @private
	 */
	hideProgress() {
		this.m_progress.classList.remove('loading');
		this.m_parent.classList.remove('loading');
	}

	/**
	 * @public
	 * @param {int} progress
	 */
	setProgress(progress) {

		if (progress <= 0)
			progress = 0;
		else if (progress > 1)
			progress = 1;

		this.m_progress.style.width = `${progress * 100}%`;
	}

	/**
	 * @public
	 */
	startAction() {
		this.setProgress(0);
		this.showProgress();
	}

	/**
	 * End without fuss
	 * @public
	 */
	endClean() {
		this.hideProgress();
		this.setProgress(0);
	}

	/**
	 * End with success and show it
	 * @public
	 */
	endSuccess() {

		this.endClean();

		this.m_parent.classList.add('success');

		setTimeout(() => {
			this.m_parent.classList.remove('success');
		}, 1500);
	}

	/**
	 * End with error and show it
	 * @public
	 */
	endError() {

		this.endClean();

		this.m_parent.classList.add('error');

		setTimeout(() => {
			this.m_parent.classList.remove('error');
		}, 1500);
	}
}
