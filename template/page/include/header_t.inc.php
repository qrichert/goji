<header>
	<div class="header__container">
		<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
			<img src="img/goji__text--dark.svg" alt="<?= $tr->_('NAV_HOME'); ?>">
		</a>

		<nav>
			<a href="#"><?= $tr->_('NAV_HOME'); ?></a>
			<a href="#"><?= $tr->_('NAV_ABOUT'); ?></a>
			<a href="#"><?= $tr->_('NAV_CONTACT'); ?></a>
			<a href="#"><?= $tr->_('NAV_BLOG'); ?></a>
		</nav>

		<div class="nav__burger-menu">
			<div></div>
			<div></div>
			<div></div>
		</div>

		<script>
			(function () {
				// 'var' for browser support as this is not compiled
				var navBurgerMenu = document.querySelector('.nav__burger-menu');
				var nav = document.querySelector('nav');

				// Toggle menu visibility in burger menu mode
				navBurgerMenu.addEventListener('click', function() {
					// toggle()'s second parameter hasn't great support in older browsers
					if (this.classList.toggle('cross'))
						nav.classList.add('shown');
					else
						nav.classList.remove('shown');
				}, false);

				// var navLinks = document.querySelectorAll('nav > a');
				//
				// // Hide menu on item click (useful for same-page anchor links)
				// navLinks.forEach(function(el) {
				// 	el.addEventListener('click', function() {
				// 		if (nav.classList.contains('shown')) {
				// 			nav.classList.remove('shown');
				// 			navBurgerMenu.classList.remove(('cross'));
				// 		}
				// 	}, false);
				// });
			})();
		</script>
	</div>
</header>
