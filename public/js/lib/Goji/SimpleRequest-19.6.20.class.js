/**
 * SimpleRequest class
 *
 * How to use it:
 * --------------
 *
 * Use it like:
 *
 * Request.method(); // They're all static methods
 *
 * There is Request.
 * - get()
 * - post()
 * - put()
 * - delete()
 *
 * get() and delete() work the same, and post() and put() have both an additional parameter
 * in second position: the data you want to send along the request (like FormData or Blob).
 *
 * All these methods return the original XMLHttpRequest object.
 *
 * Callbacks:
 * ----------
 *
 * The next three parameters are callback functions for the following events:
 * - load(response text|binary data|json|null)
 * - error(event)
 * - abort(event)
 * - progress(loaded, total)
 *
 * ```javascript
 * // GET
 * SimpleRequest.get('page.html', (response) => { alert(response); });
 *
 * // POST
 * let formData = new FormData();
 *   formData.append('foo', 'bar');
 *
 * let xhr = SimpleRequest.post('page.html', formData);
 * ...
 * xhr.abort(); // Abort the request
 * ```
 *
 * Options:
 * --------
 *
 * Options are passed as an object. Those ignored will be set as default (as in example).
 *
 * options = {
 *     encode_uri: true, // Encore
 *     get_binary: false, // Download response as binary data (Blob)
 *     get_json: false // Download response as JSON object
 * };
 *
 * If both get_binary and get_json are set to true, get_binary will prevail.
 */
class SimpleRequest {

	/**
	 * Null-coalescing polyfill.
	 *
	 * In PHP you could do '$object['property'] ?? defaultValue' and it would work
	 * as expected. But in JavaScript 'object.property || defaultValue' doesn't work
	 * if 'property' doesn't exist.
	 *
	 * So basically this function means:
	 * - If object exists and property exists in object, return it.
	 * - Else, return default value.
	 *
	 * @private
	 * @param object
	 * @param property
	 * @param defaultValue
	 * @returns {*}
	 */
	static coalesce(object, property, defaultValue) {

		if (typeof object == 'undefined' || object === null)
			return defaultValue;

		if (!object.hasOwnProperty(property))
			return defaultValue;

		return object[property];
	}

	/**
	 * Attach events to XHR.
	 *
	 * (Since they all share the same).
	 *
	 * @private
	 * @param xhr
	 * @param load
	 * @param error
	 * @param abort
	 * @param progress
	 * @param options
	 */
	static attachEvents(xhr, load = null, error = null, abort = null, progress = null, options = null) {

		xhr.addEventListener('load', e => {

			if (xhr.readyState != 4 && xhr.status != 200) {

				if (error !== null)
					error(e);
			}

			if (load !== null) {

				if (options.getBinary) {

					load(xhr.response);

				} else if (options.getJSON) {

					try {
						let json = JSON.parse(xhr.responseText);
						load(json);
					} catch (e) {
						load(null);
					}

				} else {

					load(xhr.responseText);
				}
			}

		}, false);

		xhr.addEventListener('error', e => {

			if (error !== null)
				error(e);

		}, false);

		xhr.addEventListener('abort', e => {

			if (abort !== null)
				abort(e);

		}, false);

		xhr.addEventListener('progress', e => {

			if (progress !== null)
				progress(e.loaded, e.total);

		}, false);
	}

	/**
	 * Sanitize the options.
	 *
	 * @private
	 * @param options
	 */
	static prepareOptions(options) {

		let preparedOptions = {};

			preparedOptions.encodeURI = this.coalesce(options, 'encode_uri', true);
			preparedOptions.getBinary = this.coalesce(options, 'get_binary', false);
			preparedOptions.getJSON = this.coalesce(options, 'get_json', false);

		return preparedOptions;
	}

	/**
	 * HTTP GET request
	 *
	 * @public
	 * @param uri Request URI
	 * @param load Load callback function (response text|blob)
	 * @param error Error callback function (e)
	 * @param abort Abort callback function (e)
	 * @param progress Progress callback function (loaded, total)
	 * @param options
	 * @returns {XMLHttpRequest}
	 */
	static get(uri, load = null, error = null, abort = null, progress = null, options = null) {

		options = this.prepareOptions(options);

		if (options.encodeURI)
			uri = encodeURI(uri);

		let xhr = new XMLHttpRequest();

			this.attachEvents(xhr, load, error, abort, progress, options);

			xhr.open('GET', uri);
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			if (options.getBinary)
				xhr.responseType = 'blob';

			xhr.send();

		return xhr;
	}

	/**
	 * HTTP POST request
	 *
	 * @public
	 * @param uri Request URI
	 * @param data Data to be sent, like FormData or Blob
	 * @param load Load callback function (response text|blob)
	 * @param error Error callback function (e)
	 * @param abort Abort callback function (e)
	 * @param progress Progress callback function (loaded, total)
	 * @param options
	 * @returns {XMLHttpRequest}
	 */
	static post(uri, data = null, load = null, error = null, abort = null, progress = null, options = null) {

		options = this.prepareOptions(options);

		if (options.encodeURI)
			uri = encodeURI(uri);

		let xhr = new XMLHttpRequest();

			this.attachEvents(xhr, load, error, abort, progress, options);

			xhr.open('POST', uri);
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			if (options.getBinary)
				xhr.responseType = 'blob';

			xhr.send(data);

		return xhr;
	}

	/**
	 * HTTP PUT request
	 *
	 * @public
	 * @param uri Request URI
	 * @param data Data to be sent, like FormData or Blob
	 * @param load Load callback function (response text|blob)
	 * @param error Error callback function (e)
	 * @param abort Abort callback function (e)
	 * @param progress Progress callback function (loaded, total)
	 * @param options
	 * @returns {XMLHttpRequest}
	 */
	static put(uri, data = null, load = null, error = null, abort = null, progress = null, options = null) {

		options = this.prepareOptions(options);

		if (options.encodeURI)
			uri = encodeURI(uri);

		let xhr = new XMLHttpRequest();

			this.attachEvents(xhr, load, error, abort, progress, options);

			xhr.open('PUT', uri);
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			if (options.getBinary)
				xhr.responseType = 'blob';

			xhr.send(data);

		return xhr;
	}

	/**
	 * HTTP DELETE request
	 *
	 * @public
	 * @param uri Request URI
	 * @param load Load callback function (response text|blob)
	 * @param error Error callback function (e)
	 * @param abort Abort callback function (e)
	 * @param progress Progress callback function (loaded, total)
	 * @param options
	 * @returns {XMLHttpRequest}
	 */
	static delete(uri, load = null, error = null, abort = null, progress = null, options = null) {

		options = this.prepareOptions(options);

		if (options.encodeURI)
			uri = encodeURI(uri);

		let xhr = new XMLHttpRequest();

			this.attachEvents(xhr, load, error, abort, progress, options);

			xhr.open('DELETE', uri);
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			if (options.getBinary)
				xhr.responseType = 'blob';

			xhr.send();

		return xhr;
	}
}
