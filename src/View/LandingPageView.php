<main>
	<section class="text">
		<p class="pre-heading"><?= $tr->_('LANDING_PAGE_PRE_HEADING'); ?></p>
		<h1><?= $tr->_('LANDING_PAGE_MAIN_TITLE'); ?></h1>
	</section>

	<section class="side-by-side reverse-on-squeeze">
		<figure class="image">
			<img src="img/goji-berries__petr-kratochvil--public-domain-license.jpg" alt="<?= $tr->_('LANDING_PAGE_GOJI_BERRIES_LEGEND'); ?>" class="scalable rounded">
			<figcaption>
				<?= $tr->_('LANDING_PAGE_GOJI_BERRIES_LEGEND'); ?>
			</figcaption>
		</figure>
		<div>
			<?php
				$homeIntro = $tr->_('LANDING_PAGE_GOJI_BERRIES_COPY');
				$homeIntro = \Goji\Rendering\TemplateExtensions::ctaToHTML(
					$homeIntro,
					'#'
					//$this->m_app->getRouter()->getLinkForPage('vsl')
				);

				echo $homeIntro;
			?>
		</div>
	</section>
</main>
