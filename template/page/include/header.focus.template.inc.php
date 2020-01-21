<header class="header__main">
	<div class="header__container">
		<?php if ($template->getSpecial('is-funnel-page')): ?>
			<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="header__logo">
		<?php else: ?>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
				<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="">
			</a>
		<?php endif; ?>
	</div>
</header>
