<script>

	/* <CONSTANTS> */

	const PAGE = '<?= $this->m_app->getRouter()->getCurrentPage(); ?>';

	// const DARK_MODE = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

	// const TOUCH_DEVICE = 'ontouchstart' in window ? true : false;

	// let SCREEN_WIDTH = window.innerWidth;
	// let SCREEN_HEIGHT = window.innerHeight;
	//
	// window.addEventListener('resize', function() {
	// 	SCREEN_WIDTH = window.innerWidth;
	// 	SCREEN_HEIGHT = window.innerHeight;
	// }, false);

	/* <HELPERS> */

	/**
	 * jQuery-like selectors.
	 * $() selects one element (= document.querySelector)
	 * $$() selects multiple elements (= document.querySelectorAll)
	 */
	let $ = selector => document.querySelector(selector);
	let $$ = selector => document.querySelectorAll(selector);

	/**
	 * element.addEventListener() helper
	 *
	 * Normal:
	 *
	 * ```js
	 * document.addEventListener('click', e => {
	 *     console.log(e);
	 *     $('body').style.opacity = '0.5';
	 * }, false);
	 * ```
	 *
	 * With connect():
	 *
	 * ```js
	 * connect(document, 'click', e => {
	 *     console.log(e);
	 *     $('body').style.opacity = '0.5';
	 * });
	 * ```
	 *
	 * @param {Node|Element} element (target)
	 * @param {String} signal (event)
	 * @param {CallableFunction} slot (listener)
	 * @param {Boolean} useCapture (optional, default = false)
	 * @return VoidFunction
	 */
	let connect = (element, signal, slot, useCapture = false) => {
		element.addEventListener(signal, e => slot(e), useCapture);
	};

	/* <FUNCTIONS> */

//	/**
//	 * Returns scroll ratio. (0 = top || 1 = bottom)
// 	 * @return Float
//	 */
//	function getWindowScroll() {
//		let s = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
//		let d = document.body.clientHeight;
//		let c = window.innerHeight;
//
//		return s / (d - c);
//	}

//	/**
//	 * Sometimes you want to deactivate CSS transitions for one move,
//	 * you can do that with el.style.transition = 'none'; The problem
//	 * is when you do the opposite (e.g.: el.style.transition = null;)
//	 * the animation will trigger automatically. To bypass this behaviour
//	 * you need to provoke a reflow (flush) of the CSS before deactivation
//	 * to cancel any pending animation. This function does exactly that.
// 	 *
// 	 * @param {Node|Element} element
//	 * @return VoidFunction
//	 */
//	function flushCSSElement(element) {
//		element.offsetHeight;
//	}

</script>

<?php

	$template->linkFiles([
		'js/lib/Goji/Polyfills.min.js',
		'js/lib/Goji/SimpleRequest.class.min.js'
	]);

	if ($this->m_app->getAppMode() === \Goji\Core\App::DEBUG)
		$template->linkFiles('js/lib/Goji/WindowSizeDisplay.min.js');
