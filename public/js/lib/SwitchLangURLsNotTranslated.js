(function() {

	/*
		Use this if URLs are not translated
	*/

	var languageSelector = document.querySelector('#language-selector');

		languageSelector.querySelectorAll('a').forEach(function(el) {

			el.addEventListener(TOUCH_EVENT, function(e) {

				e.preventDefault();

				var xhr = new SimpleXMLHttpRequest();

				var obj = xhr.fileGetContents(el.href + '?ajax=true');

					obj.addEventListener('load', function(e) {

						var response = e.detail.contents;

						try {
							response = JSON.parse(response);
						} catch (e) {
							return;
						}

						if (response.status == 'SUCCESS') {
							location.reload(true);
						}

					}, false);

			}, false);
		});

})();
