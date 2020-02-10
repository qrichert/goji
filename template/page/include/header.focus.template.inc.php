<header class="header__main">
	<div class="header__container">
		<?php if ($template->getSpecial('is-funnel-page')): ?>
			<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="header__logo">
			<!--<span class="header__logo">goji</span>-->
		<?php else: ?>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
				<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="">
				<!--goji-->
			</a>
		<?php endif; ?>
	</div>
</header>
