<footer>
	<div class="footer__container">
		<!-- Left -->
		<div class="footer__main">
			<!-- Children aligned in row -->
			<!--<p>© <?= date('Y'); ?> Goji, Inc.</p>-->
			<div>
				<!-- Sub-children aligned in column -->
				<p>
					<a href="https://github.com/qrichert/goji" target="_blank">
						<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>
					</a>
				</p>
				<!--<p>Try uncommenting me</p>-->
			</div>
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

					echo '<a href="' . $this->m_app->getRouter()->getLinkForPage(null, $locale) . '">'
					     . $this->m_app->getLanguages()->getConfigurationLocales()[$locale]
					     . '</a>',
					PHP_EOL;
				}
			?>
			<a href="https://github.com/qrichert/goji" target="_blank">
				<img src="img/goji__berries--grayscale.svg" alt="<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>" width="35">
			</a>
		</div>
	</div>
</footer>
