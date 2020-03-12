<main>
	<h1 class="hidden" aria-hidden="true"><?= $tr->_('VSL_MAIN_TITLE'); ?></h1>

	<section class="text">
		<h2><?= $tr->_('VSL_STEP_ONE'); ?></h2>
	</section>

	<section class="video">
		<div class="video-wrapper">
			<iframe
				width="560"
				height="315"
				src="https://www.youtube.com/embed/zy_SOuWJSUk"
				frameborder="0"
				allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
				allowfullscreen
			></iframe>
		</div>
	</section>

	<section class="text">
		<h2><?= $tr->_('VSL_STEP_TWO'); ?></h2>

		<div class="vsl__step-two-text">
			<?php
			$vslStepTwoText = $tr->_('VSL_STEP_TWO_TEXT');
			$vslStepTwoText = \Goji\Rendering\TemplateExtensions::ctaToHTML(
				$vslStepTwoText,
				$this->m_app->getRouter()->getLinkForPage('offer-1-schedule')
			);

			echo $vslStepTwoText;
			?>
		</div>
	</section>
</main>
