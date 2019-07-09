/**
 * Form class
 *
 * Takes regular form, prevents submit and sends it via AJAX instead,
 *
 * How to use it:
 * --------------
 *
 * The server must return a JSON response with a 'status' parameter set to 'SUCCESS'.
 * If the response isn't valid JSON or has no 'status' property or 'status' !== 'SUCCESS',
 * the error callback will be called.
 *
 * let success = response => {
 *     console.log(response);
 * };
 *
 * new Form(document.querySelector('form.form__login'), // <form> element
 *         success, // success callback
 *         null, // no failure callback
 *         document.querySelector('form.form__login > button.loader'), // Loading button
 *         document.querySelector('form.form__login > .progress-bar') // Progress bar
 * );
 *
 * While uploading a class called 'loading' is added to the status bearer
 * When loading has finished it is replaced by either 'loaded' or 'failed'
 *
 * Classes are removed 1.5s after loading ended
 *
 * If a progress bar is given it will update according to the upload progress (width in %)
 * And add a class 'shown' to it while loading
 *
 */
class Form {

	/**
	 * @param parent
	 * @param callbackSuccess
	 * @param callbackError
	 * @param statusBearer
	 * @param progressBar <div class="progress-bar"><div class="progress"></div></div>
	 */
	constructor(parent, callbackSuccess = null, callbackError = null, statusBearer = null, progressBar = null) {

		this.m_parent = parent;
			this.m_parent.addEventListener('submit', e => { this.submit(e); }, false);

		this.m_callbackSuccess = callbackSuccess;
		this.m_callbackError = callbackError;

		this.m_statusBearer = statusBearer;
		this.m_progressBar = progressBar;
		this.m_progressBarProgress = progressBar.querySelector('.progress');

		this.m_currentXHR = null;
		this.m_loadingInProgress = false;

		this.m_loadingStatus = {
			NONE: 0,
			LOADING: 1,
			LOADED: 2,
			FAILED: 3
		};
	}

	/**
	 * @public
	 * @param disabled
	 */
	setDisabled(disabled) {

		// /!\ Disabled inputs are not POSTed so we use read-only
		this.m_parent.querySelectorAll('input').forEach(el => { el.readOnly = disabled; });
		this.m_parent.querySelectorAll('textarea').forEach(el => { el.readOnly = disabled; });
		this.m_parent.querySelectorAll('button').forEach(el => { el.readOnly = disabled; });
		this.m_parent.querySelectorAll('select').forEach(el => { el.readOnly = disabled; });
		this.m_parent.querySelectorAll('button[type=reset]').forEach(el => { el.disabled = disabled; });
	}

	/**
	 * @private
	 */
	clear() {

		setTimeout(() => {
			this.setLoadingStatus(this.m_loadingStatus.NONE);
		}, 1500);
	}

	/**
	 * @private
	 * @param status
	 */
	setLoadingStatus(status) {

		switch (status) {
			case this.m_loadingStatus.NONE:
				this.setDisabled(false);
				this.m_currentXHR = null;
				this.m_loadingInProgress = false;
				this.m_progressBar.classList.remove('shown');
				break;
			case this.m_loadingStatus.LOADING:
				this.setDisabled(true);
				this.m_loadingInProgress = true;
				this.m_progressBar.classList.add('shown');
				break;
			case this.m_loadingStatus.LOADED:
				this.setDisabled(false);
				this.m_currentXHR = null;
				this.m_loadingInProgress = false;
				this.m_progressBar.classList.remove('shown');
				break;
			case this.m_loadingStatus.FAILED:
				this.setDisabled(false);
				this.m_currentXHR = null;
				this.m_loadingInProgress = false;
				this.m_progressBar.classList.remove('shown');
				break;
		}

		if (this.m_statusBearer === null)
			return;

		this.m_statusBearer.classList.remove('loading');
		this.m_statusBearer.classList.remove('loaded');
		this.m_statusBearer.classList.remove('failed');

		switch (status) {
			case this.m_loadingStatus.LOADING:
				this.m_statusBearer.classList.add('loading');
				break;
			case this.m_loadingStatus.LOADED:
				this.m_statusBearer.classList.add('loaded');
				break;
			case this.m_loadingStatus.FAILED:
				this.m_statusBearer.classList.add('failed');
				break;
		}
	}

	/**
	 * @public
	 * @param e
	 */
	submit(e = null) {

		if (e !== null)
			e.preventDefault();

		if (this.m_loadingInProgress)
			return;

		this.setLoadingStatus(this.m_loadingStatus.LOADING);

		if (this.m_progressBar !== null)
			this.m_progressBar.classList.add('shown');

		let uri = this.m_parent.action; // Already encoded by the browser
		let data = new FormData(this.m_parent);
			data.append('ajax-http-request', 'true')

		this.m_currentXHR = SimpleRequest.post(uri,
											   data,
											   r => { this.load(r); },
											   e => { this.error(); },
											   e => { this.abort(); },
											   (l, t) => { this.progress(l, t); },
											   {
											       encode_uri: false,
											       get_json: true
											   });
	}

	/**
	 * @private
	 * @param response
	 */
	load(response) {

		if (response === null
			|| response.status !== 'SUCCESS') {

			this.error(response);
			return;
		}

		this.setLoadingStatus(this.m_loadingStatus.LOADED);
		this.clear();

		if (this.m_callbackSuccess !== null)
			this.m_callbackSuccess(response);
	}

	/**
	 * @private
	 * @param response
	 */
	error(response = null) {

		this.setLoadingStatus(this.m_loadingStatus.FAILED);
		this.clear();

		if (this.m_callbackError !== null)
			this.m_callbackError(response);
	}

	/**
	 * @public
	 */
	abort() {

		if (this.m_currentXHR !== null)
			this.m_currentXHR.abort();

		this.setLoadingStatus(this.m_loadingStatus.NONE);
	}

	progress(loaded, total) {

		if (this.m_progressBar === null)
			return;

		let progress = (loaded / total) * 100;
		this.m_progressBarProgress.style.width = progress + '%';
	}
}
