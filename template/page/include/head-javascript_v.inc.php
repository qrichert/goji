<script>

	/* CONSTANTS */

	const PAGE = '<?= $this->m_app->getRouter()->getCurrentPage(); ?>';
//	var SCREEN_WIDTH  = window.innerWidth;
//	var SCREEN_HEIGHT = window.innerHeight;
	const TOUCH_EVENT = 'click'; //'ontouchstart' in window ? 'touchend' : 'click';
//	const TOUCH_DEVICE  = 'ontouchstart' in window ? true : false;
//
//	window.addEventListener('resize', function() {
//		SCREEN_WIDTH  = window.innerWidth;
//		SCREEN_HEIGHT = window.innerHeight;
//	}, false);

//	/*
//		Returns scroll ratio. (0 = top || 1 = bottom)
//	*/
//	function getWindowScroll() {
//		var s = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
//		var d = document.body.clientHeight;
//		var c = window.innerHeight;
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

	\Goji\Toolkit\SwissKnife::linkFiles('js', array(
		'js/vendor/Polyfills.js',
		'js/vendor/WindowSizeDisplay-18.12.8.js',
		'js/vendor/SimpleXMLHttpRequest-17.12.5.min.js'
	));
