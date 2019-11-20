<header class="header__main">
	<div class="header__container">
		<?php
			// If funnel -> no link
			if ($template->getSpecial('is-funnel-page')) {
			?>
				<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="" class="header__logo">
			<?php
			// If not funnel, logo redirects to home page
			} else {
			?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('home'); ?>" class="header__logo">
					<img src="<?= $template->rsc('img/goji__berries.svg'); ?>" alt="">
				</a>
			<?php
			}
		?>
	</div>
</header>
