<main class="centered">
	<section class="text">
		<h1><?= $tr->_('REDIRECTION_MAIN_TITLE'); ?></h1>
		<p id="redirection-text">
			<?php
			echo $tr->_('REDIRECTION_IN_PROGRESS') . '<br>';
			echo str_replace('%{LINK}', htmlspecialchars($this->getRedirectTo()), $tr->_('REDIRECTION_NOT_WORKING'));
			?>
		</p>
	</section>
</main>

<script>
	(function (){
		setTimeout(() => {
			let redirectTo = document.querySelector('#redirection-text  > a');
			if (redirectTo !== null && redirectTo.href !== '')
				location.href = redirectTo.href;
		}, 1300);
	})();
</script>
