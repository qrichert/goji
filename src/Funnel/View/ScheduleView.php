<main>
	<section class="text">
		<h1><?= $tr->_('SCHEDULE_MAIN_TITLE'); ?></h1>
		<?php
		$scheduleText = $tr->_('SCHEDULE_TEXT');
		$scheduleText = \Goji\Rendering\TemplateExtensions::ctaToHTML(
			$scheduleText,
			$this->m_app->getRouter()->getLinkForPage('home')
		);

		echo $scheduleText;
		?>
	</section>
</main>
