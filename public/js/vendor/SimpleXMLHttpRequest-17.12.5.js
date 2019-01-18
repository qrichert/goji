/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script makes basic XMLHttpRequests much easier.
	* You can use it to GET the content of a page or POST data to a page (and read the response).

	Instructions (GET):

		1. Create a new SimpleXMLHttpRequest object.

			new SimpleXMLHttpRequest()

		2. Call [Object] SimpleXMLHttpRequest::fileGetsContents(string url)

			(new SimpleXMLHttpRequest()).fileGetsContents('index.php');

		3. This method returns an object which you have to attach an event listener to.
		   It will fire a "load" event if the request was successful.
		   If the request failed, it will fire an "error" event.
		   Progress can be read with the "progress" event, and (event).detail.loaded | (event).detail.total

			[Object].addEventListener("load",		function(event)	{}, false);
			[Object].addEventListener("progress",	function(event)	{}, false);
			[Object].addEventListener("error",		function()		{}, false);

		4. The content is accessible by reading event.detail.contents in "load" event.

	Example:

		(new SimpleXMLHttpRequest()).fileGetContents('index.php').addEventListener("load", function(e) {
			alert(e.detail.contents);
		}, false);

	// Or, if you want to reuse the SimpleXMLHttpRequest object, you can store it in a variable
	   and call fileGetContents() as many times as you want.

	   	var xhr = new SimpleXMLHttpRequest();

		xhr.fileGetContents('index.php').addEventListener("load", function(e) {
			alert(e.detail.contents);
		}, false);

		xhr.fileGetContents('other_page.php').addEventListener("load", function(e) {
			alert(e.detail.contents);
		}, false);

	// If you want to listen to "error" or "progress," you'll have to store the object:

		var xhr = new SimpleXMLHttpRequest();

		var obj = xhr.fileGetContents('index.php');

			obj.addEventListener("load", function(e) {
				alert(e.detail.contents);
			}, false);

			obj.addEventListener("load", function(e) {
				console.log(e.detail.loaded + '/' e.detail.total);
			}, false);

			obj.addEventListener("error", function() {
				alert("Error!");
			}, false);

// ***************************************************************** //

	Instructions (POST):

		1. Create a new SimpleXMLHttpRequest object.

			new SimpleXMLHttpRequest()

		2. Call [Object] SimpleXMLHttpRequest::postData(string url, FormData data)

			(new SimpleXMLHttpRequest()).postData('index.php', new FormData());

		3. This method returns an object which you have to attach an event listener to.
		   It will fire a "load" event if the request was successful.
		   If the request failed, it will fire an "error" event.
		   Progress can be read with the "progress" event, and (event).detail.loaded | (event).detail.total

			[Object].addEventListener("load",		function(event) {},	false);
			[Object].addEventListener("progress",	function(event) {},	false);
			[Object].addEventListener("error",		function()		{},	false);

		4. The response text is accessible by reading event.detail.response in "load" event.

	Example:

		(new SimpleXMLHttpRequest()).postData('index.php', new FormData()).addEventListener("load", function(e) {
			alert(e.detail.response);
		}, false);

	// Or, if you want to reuse the SimpleXMLHttpRequest object, you can store it in a variable
	   and call postData() as many times as you want.

	   	var xhr = new SimpleXMLHttpRequest();

		xhr.postData('index.php', new FormData(querySelector("#my-form"))).addEventListener("load", function(e) {
			alert(e.detail.response);
		}, false);

		xhr.postData('other_page.php', new FormData()).addEventListener("load", function(e) {
			alert(e.detail.response);
		}, false);

	// If you want to listen to "error" or "progress," you'll have to store the object:

		var data = new FormData();
			data.append("name", "Quentin Richert"); // Will create a $_POST['name'] variable in PHP containing "Quentin Richert"

		var xhr = new SimpleXMLHttpRequest();

		var obj = xhr.postData('index.php', data);

			obj.addEventListener("load", function(e) {
				alert(e.detail.response);
			}, false);

			obj.addEventListener("load", function(e) {
				console.log(e.detail.loaded + '/' e.detail.total);
			}, false);

			obj.addEventListener("error", function() {
				alert("Error!");
			}, false);
*/

(function () { // Polyfill for CustomEvent
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

function SimpleXMLHttpRequest() {

	this.fileGetContents = function(url) {

		url = encodeURI(url);

		var eventParent = document.createElement('div');

		var xhr = new XMLHttpRequest();

		xhr.open('GET', url);

		xhr.addEventListener('load', function(e) {
			var contentReady = new CustomEvent('load', {
					detail: {
						contents: xhr.responseText
					}
				});

			eventParent.dispatchEvent(contentReady);
		}, false);

		xhr.addEventListener('progress', function(e) {
			var progress = new CustomEvent('progress', {
				detail: {
					loaded: e.loaded,
					total: e.total
				}
			});

			eventParent.dispatchEvent(progress);
		}, false);

		xhr.addEventListener('error', function(e) {
			eventParent.dispatchEvent(new CustomEvent('error'));
		}, false);

		xhr.send(null);

		return eventParent;
	};

	this.postData = function(url, data) {

		url = encodeURI(url);

		var eventParent = document.createElement('div');

		var xhr = new XMLHttpRequest();

		xhr.open('POST', url);

		xhr.addEventListener('load', function(e) {
			var postSuccess = new CustomEvent('load', {
					detail: {
						response: xhr.responseText
					}
				});

				eventParent.dispatchEvent(postSuccess);
		}, false);

		xhr.upload.addEventListener('progress', function(e) {
			var progress = new CustomEvent('progress', {
				detail: {
					loaded: e.loaded,
					total: e.total
				}
			});

			eventParent.dispatchEvent(progress);
		}, false);

		xhr.addEventListener('error', function(e) {
			eventParent.dispatchEvent(new CustomEvent('error'));
		}, false);


		xhr.send(data);

		return eventParent;
	};
}