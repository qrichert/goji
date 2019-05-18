<header>
	<div class="header__container">
		<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
			<img src="img/goji__text--dark.svg" alt="Logo">
		</a>

		<nav>
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
			document.querySelector('.nav__burger-menu').addEventListener('click', function() {
				document.querySelector('nav').classList.toggle('shown', this.classList.toggle('cross'));
			}, false);
		</script>
	</div>
</header>
