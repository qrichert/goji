(function() {

	/*
		Use this if URLs are translated.
		It is not mandatory, but it preserves query strings and path format
	*/

	var languageSelector = document.querySelector('#language-selector');

		languageSelector.querySelectorAll('a').forEach(function(el) {

			if (location.pathname.endsWith('/')) { // = no-page

				// If no page is set (ex: http://website.com/)
				// We don't want the language change to add it back
				// We want it to be consistent, so we change the language with AJAX and reload the page

				el.addEventListener(TOUCH_EVENT, function(e) {

					e.preventDefault();

					var xhr = new SimpleXMLHttpRequest();

					// ex: lang-en?ajax=true
					var obj = xhr.fileGetContents('lang-' + el.dataset.lang + '?ajax=true');

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

			} else { // page is part of URL, so we can just switch to it

				el.addEventListener(TOUCH_EVENT, function(e) {

					e.preventDefault();

					location.href = el.href + location.search;

				}, false);
			}
		});

})();
