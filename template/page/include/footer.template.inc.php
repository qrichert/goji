<?php if (in_array($this->m_app->getRouter()->getCurrentPage(), ['home', 'blog', 'blog-post', 'contact'])): ?>
	<div class="footer__about">
		<section class="footer__about-container side-by-side right-bigger">
			<figure class="image">
				<img src="<?= $template->rsc('img/placeholder?w=1500&h=1250'); ?>" alt="" class="scalable">
			</figure>
			<div>
				<h2>Lorem ipsum</h2>
				<p>
					Lorem ipsum dolor sit amet, consectetur adipisicing elit. A ab, accusantium ad cum dignissimos
					dolore ea enim eos est harum, iusto necessitatibus nulla odio possimus sunt ut voluptatum!
					Blanditiis, perspiciatis.
				</p
				<p class="call-to-action__wrapper">
					<a href="#" class="call-to-action smaller transparent"><?= $tr->_('LEARN_MORE'); ?></a>
				</p>
			</div>
		</section>
	</div>
<?php endif; ?>
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

			<div>
				<?php if ($this->m_app->getUser()->isLoggedIn()): ?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('logout'); ?>" rel="nofollow"><?= $tr->_('FOOTER_LOG_OUT'); ?></a>
				<?php else: ?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>" rel="nofollow"><?= $tr->_('FOOTER_LOG_IN'); ?></a>
				<?php endif; ?>

				<?php if ($this->m_app->getUser()->isLoggedIn()): ?>
					<a href="<?= $this->m_app->getRouter()->getLinkForPage('settings'); ?>" rel="nofollow"><?= $tr->_('FOOTER_SETTINGS'); ?></a>
				<?php endif; ?>
			</div>

			<?php if ($this->m_app->getUser()->isLoggedIn()): ?>
				<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>" rel="nofollow"><?= $tr->_('FOOTER_ADMIN'); ?></a>
			<?php endif; ?>

			<p>Made with ❤️ and PHP</p>
		</div>
		<!-- Right -->
		<div class="footer__explore">
			<?php require_once $template->getTemplate('page/include/translation-links'); ?>

			<a href="https://github.com/qrichert/goji" target="_blank">
				<img src="<?= $template->rsc('img/goji__berries--grayscale.svg'); ?>" alt="<?= $tr->_('FOOTER_GOJI_ON_GITHUB'); ?>" width="35">
			</a>
		</div>
	</div>
</footer>
