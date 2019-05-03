<header>
	<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
		<img src="img/goji__text--light.svg" alt="Logo">
	</a>

	<nav>
		<a href="#">About</a>
		<a href="#">Contact</a>
		<a href="#">Blog</a>
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
</header>
