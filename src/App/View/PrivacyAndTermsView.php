<main>
	<section class="text with-outline">
		<h1><?= $tr->_('PRIVACY_AND_TERMS_MAIN_TITLE'); ?></h1>

		<?=
		str_replace('%{YEAR}', date('Y'),
			$tr->printRawTranslationResource('privacy-and-terms.%{LOCALE}.tr.html', false)
		);
		?>
	</section>
</main>
