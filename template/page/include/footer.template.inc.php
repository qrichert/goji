<footer class="footer__main">
	<div class="footer__container">
		<!-- Left -->
		<div class="footer__main-content">
			<!-- Children aligned in row -->
			<!--<p>© <?= date('Y'); ?> Goji, Inc.</p>-->
			<div>
				<!-- Sub-children aligned in column -->
				<p>
					<a href="https://github.com/qrichert/goji" target="_blank">
						<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>
					</a>
				</p>
				<p>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('privacy-and-terms'); ?>">
						<?= $tr->_('FOOTER_PRIVACY_AND_TERMS'); ?>
					</a>
				</p>
			</div>
			<p>
				<?php
					if ($this->m_app->getUser()->isLoggedIn()) {
					?>
						<a href="<?= $this->m_app->getRouter()->getLinkForPage('logout'); ?>"><?= $tr->_('FOOTER_LOG_OUT'); ?></a>
					<?php
					} else {
					?>
						<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>"><?= $tr->_('FOOTER_LOG_IN'); ?></a>
					<?php
					}
				?>
			</p>
			<!--<p><a href="#">Terms</a></p>-->
			<!--<p><a href="#">Privacy</a></p>-->
			<p>Made with ❤️ and PHP</p>
		</div>
		<!-- Right -->
		<div class="footer__explore">
			<?php
				foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

					if ($locale == $this->m_app->getLanguages()->getCurrentLocale())
						continue;

					echo '<a href="' . $this->m_app->getRouter()->getLinkForPage(null, $locale) . '" data-lang="' . $locale . '">'
					     . $this->m_app->getLanguages()->getConfigurationLocales()[$locale]
					     . '</a>',
					PHP_EOL;
				}
			?>
			<a href="https://github.com/qrichert/goji" target="_blank">
				<img src="<?= $template->getWebRoot(); ?>/img/goji__berries--grayscale.svg" alt="<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>" width="35">
			</a>
		</div>
	</div>
</footer>
