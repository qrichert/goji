/*
	Takes regular form
	Prevents submit and sends it via ajax instead,
	Then fires events submisucces / submiterror containing the response.
	Response from the server must be JSON with at least one key called 'status' containing 'SUCCESS' or 'ERROR'

	While uploading a class called 'loading' is added to the sumbit button
	When loading has finished it is replace by either 'loaded' or 'loading-error'
	Classes are removed 1,5s after laoding ended

	If a progress bar was specified it will update according to upload progress (width in %)
*/

function Form(parent,
			  submitButton,
			  progressBar = null) {

	var _this = this;

	this.m_parent = parent;

		this.m_parent.addEventListener('submit', function(e) {
			_this.submit(e);
		}, false);

	this.m_submitButton = submitButton;

	this.m_progress = 0;
	this.m_progressBar = progressBar;

	this.m_xhr = new SimpleXMLHttpRequest();
	this.m_loadingInProgress = false;

	this.setDisabled = function(disabled) {
		// /!\ Disabled inputs are not POSTed so we use read-only
		_this.m_parent.querySelectorAll('input').forEach(function(el) { el.readOnly = disabled; });
		_this.m_parent.querySelectorAll('textarea').forEach(function(el) { el.readOnly = disabled; });
		_this.m_parent.querySelectorAll('button').forEach(function(el) { el.readOnly = disabled; });
		_this.m_parent.querySelectorAll('select').forEach(function(el) { el.readOnly = disabled; });
		_this.m_parent.querySelectorAll('button[type=reset]').forEach(function(el) { el.disabled = disabled; });
	};

	this.submit = function(e = null) {

		if (e !== null)
			e.preventDefault();

		if (_this.m_loadingInProgress === true)
			return;

		_this.m_loadingInProgress = true;
		_this.setDisabled(true);
		_this.m_submitButton.classList.add('loading');

		if (_this.m_progressBar !== null) {
			_this.m_progressBar.classList.add('shown');
		}

		var data = new FormData(_this.m_parent);
			//data.append('ajax-request', true);

		// SimpleXMLHttpRequest uses 'encodeURI'. If we don't decode it first
		// It will be encoded twice, thus producing errors
		// Ex : http://localhost/Some%20Folder/project
		// Would become http://localhost/Some%2520Folder/project
		// -> 404, cause now the real URL would now have the %20
		var url = decodeURI(_this.m_parent.action);

		var obj = _this.m_xhr.postData(url, data);

			obj.addEventListener('load', function(e) {
				_this.submitLoad(e);
			}, false);

			obj.addEventListener('error', function(e) {
				_this.submitError(e);
			}, false);

			obj.addEventListener('progress', function(e) {
				_this.submitProgress(e);
			}, false);
	};

	this.submitLoad = function(e) {

		var response = e.detail.response;

		try {
			response = JSON.parse(response);
		} catch (e) {
			_this.submitError();
			return;
		}

		if (response.status != 'SUCCESS') {
			_this.submitError(response);
			return;
		}

		var successEvent = new CustomEvent('submitsuccess', {
				detail: {
					response: response
				}
			});

		_this.m_parent.dispatchEvent(successEvent);

		_this.m_loadingInProgress = false;
		_this.setDisabled(false);
		_this.m_submitButton.classList.remove('loading');
		_this.m_submitButton.classList.add('loaded');

			setTimeout(function() {
				_this.m_submitButton.classList.remove('loaded');
			}, 1500);

		_this.m_progress = 0;

		if (_this.m_progressBar !== null) {
			_this.m_progressBar.classList.remove('shown');
			_this.m_progressBar.style.width = null;
		}
	};

	this.submitError = function(e = null) {

		var response = e; // Either null or JSON Object from submitLoad();

		if (response !== null) {

			var errorEvent = new CustomEvent('submiterror', {
				detail: {
					response: response
				}
			});

			_this.m_parent.dispatchEvent(errorEvent);

		} else {

			var errorEvent = new CustomEvent('submiterror', {
				detail: {
					response: {
						status: 'ERROR',
						error_code: -1,
						error_text: 'ERROR_UNKNOWN'
					}
				}
			});

			_this.m_parent.dispatchEvent(errorEvent);
		}

		_this.m_loadingInProgress = false;
		_this.setDisabled(false);
		_this.m_submitButton.classList.remove('loading');
		_this.m_submitButton.classList.add('loading-error');

			setTimeout(function() {
				_this.m_submitButton.classList.remove('loading-error');
			}, 1500);

		_this.m_progress = 0;

		if (_this.m_progressBar !== null) {
			_this.m_progressBar.classList.remove('shown');
			_this.m_progressBar.style.width = null;
		}
	};

	this.submitProgress = function(e) {

		_this.m_progress = e.detail.loaded / e.detail.total; // Ratio, between 0 and 1

		if (_this.m_progressBar !== null) {
			_this.m_progressBar.style.width = (_this.m_progress * 100) + '%';
		}
	};
}
