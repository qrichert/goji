<div class="header__wrapper">
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

	<?php if ($this->m_app->getRouter()->getCurrentPage() == 'home'): ?>

		<!-- /!\ Takes retina displays @2x into account -->
		<img srcset="<?= $template->rsc('../img/placeholder?w=900&h=556&t=Goji'); ?> 900w,
					 <?= $template->rsc('../img/placeholder?w=1500&h=927&t=Goji'); ?> 1500w,
					 <?= $template->rsc('../img/placeholder?w=2500&h=1545&t=Goji'); ?> 2500w"
		     sizes="100vw"
		     src="<?= $template->rsc('../img/placeholder?w=2500&h=1545&t=Goji'); ?>"
		     alt="<?= $this->m_app->getSiteName(); ?>">

		<div class="header__home-hero">
			<div>
				<h2 class="header__home-hero-headline"><?= $tr->_('HEADER_HERO_HEADLINE'); ?></h2>
				<p class="header__home-hero-cta">
					<?=
					\Goji\Rendering\TemplateExtensions::ctaToHTML(
						$tr->_('HEADER_HERO_CTA'),
						'#',
						true
					);
					?>
				</p>
			</div>
			<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="<?= $this->m_app->getSiteName(); ?>">
		</div>

	<?php endif; ?>
</div>

<?php
if ($this->m_app->getRouter()->getCurrentPage() == 'home') {
	$template->linkFiles([
		'js/HomeHeroParallax.js'
	]);
}
?>
<script>
	(function() {

		/* <BURGER MENU> */

		let navBurgerMenu = document.querySelector('.nav__burger-menu');
		let nav = document.querySelector('.nav__main');

		// Toggle menu visibility in burger menu mode
		navBurgerMenu.addEventListener('click', () => {
			// toggle()'s second parameter hasn't great support in older browsers
			if (navBurgerMenu.classList.toggle('cross'))
				nav.classList.add('shown');
			else
				nav.classList.remove('shown');
		}, false);

		// let navLinks = document.querySelectorAll('.nav__main > a');
		//
		// // Hide menu on item click (useful for same-page anchor links)
		// navLinks.forEach(function(el) {
		// 	el.addEventListener('click', () => {
		// 		if (nav.classList.contains('shown')) {
		// 			nav.classList.remove('shown');
		// 			navBurgerMenu.classList.remove(('cross'));
		// 		}
		// 	}, false);
		// });
	})();
</script>
