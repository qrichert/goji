<script>

	/* <CONSTANTS> */

	const PAGE = '<?= $this->m_app->getRouter()->getCurrentPage(); ?>';

	const DARK_MODE = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

//	const TOUCH_DEVICE = 'ontouchstart' in window ? true : false;

//	let SCREEN_WIDTH = window.innerWidth;
//	let SCREEN_HEIGHT = window.innerHeight;
//
//	window.addEventListener('resize', function() {
//		SCREEN_WIDTH = window.innerWidth;
//		SCREEN_HEIGHT = window.innerHeight;
//	}, false);

	/* <FUNCTIONS> */

//	/*
//		Returns scroll ratio. (0 = top || 1 = bottom)
//	*/
//	function getWindowScroll() {
//		let s = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
//		let d = document.body.clientHeight;
//		let c = window.innerHeight;
//
//		return s / (d - c);
//	}

//	/*
//		Sometimes you want to deactivate CSS transitions for one move,
//		you can do that with el.style.transition = 'none'; The problem
//		is when you do the opposite (e.g.: el.style.transition = null;)
//		the animation will trigger automatically. To bypass this behaviour
//		you need to provoke a reflow (flush) of the CSS before deactivation
//		to cancel any pending animation. This function does exactly that.
//	*/
//	function flushCSSElement(el) {
//		el.offsetHeight;
//	}

</script>

<?php

	$template->linkFiles([
		'js/lib/Goji/Polyfills.min.js',
		'js/lib/Goji/SimpleRequest.class.min.js'
	]);

	if ($this->m_app->getAppMode() === \Goji\Core\App::DEBUG)
		$template->linkFiles('js/lib/Goji/WindowSizeDisplay.min.js');
