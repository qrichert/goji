<footer class="footer__main">
	<div class="footer__container">
		<!-- Left -->
		<div class="footer__main-content">
			<!-- Children aligned in row (flex: row) -->
			<!--<p>© <?= date('Y'); ?> Goji, Inc.</p>-->
			<div>
				<!-- Sub-children aligned in column if parent is a <div> (flex: column) -->
				<a href="https://github.com/qrichert/goji" target="_blank">
					<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>
				</a>

				<a href="<?= $this->m_app->getRouter()->getLinkForPage('privacy-and-terms'); ?>" rel="nofollow">
					<?= $tr->_('FOOTER_PRIVACY_AND_TERMS'); ?>
				</a>
			</div>
			<?php
				if ($this->m_app->getUser()->isLoggedIn()) {
				?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('logout'); ?>" rel="nofollow"><?= $tr->_('FOOTER_LOG_OUT'); ?></a>
				<?php
				} else {
				?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>" rel="nofollow"><?= $tr->_('FOOTER_LOG_IN'); ?></a>
				<?php
				}

				if ($this->m_app->getUser()->isLoggedIn()
				    && $this->m_app->getMemberManager()->memberIs('editor')) {
				?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>" rel="nofollow"><?= $tr->_('FOOTER_ADMIN'); ?></a>
				<?php
				}
			?>
			<p>Made with ❤️ and PHP</p>
		</div>
		<!-- Right -->
		<div class="footer__explore">
			<?php
				foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

					if ($locale == $this->m_app->getLanguages()->getCurrentLocale())
						continue;

					$page = null; // Current page

						if ($this->m_app->getRouter()->getCurrentPage() == 'blog-post')
							$page = 'blog';
						else if ($this->m_app->getRouter()->getCurrentPage() == 'verify-email')
							$page = 'sign-up';

					echo '<a href="' . $this->m_app->getRouter()->getLinkForPage($page, $locale) . '" data-lang="' . $locale . '">'
					     . $this->m_app->getLanguages()->getConfigurationLocales()[$locale]
					     . '</a>',
					PHP_EOL;
				}
			?>
			<a href="https://github.com/qrichert/goji" target="_blank">
				<img src="<?= $template->rsc('img/goji__berries--grayscale.svg'); ?>" alt="<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>" width="35">
			</a>
		</div>
	</div>
</footer>
