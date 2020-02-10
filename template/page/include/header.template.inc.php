<header class="header__main">
	<div class="header__container">
		<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
			<img src="<?= $template->rsc('img/goji__text--dark.svg'); ?>" alt="<?= $tr->_('NAV_HOME'); ?>">
			<!--goji-->
		</a>

		<nav class="nav__main">
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>"><?= $tr->_('NAV_HOME'); ?></a>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('blog'); ?>"><?= $tr->_('NAV_BLOG'); ?></a>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('contact'); ?>" rel="nofollow"><?= $tr->_('NAV_CONTACT'); ?></a>
		</nav>

		<div class="nav__burger-menu">
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
</header>

<script>
	(function () {

		/* <BURGER MENU> */

		let navBurgerMenu = document.querySelector('.nav__burger-menu');
		let nav = document.querySelector('.nav__main');

		// Toggle menu visibility in burger menu mode
		navBurgerMenu.addEventListener('click', function() {
			// toggle()'s second parameter hasn't great support in older browsers
			if (this.classList.toggle('cross'))
				nav.classList.add('shown');
			else
				nav.classList.remove('shown');
		}, false);

		// let navLinks = document.querySelectorAll('.nav__main > a');
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
