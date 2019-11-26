<main class="centered">
	<section class="error">
		<h1><?= $this->m_httpErrorCode; ?></h1>
		<p>
			<?= $tr->_('ERROR_DESCRIPTION')[$this->m_httpErrorCode] ?? ''; ?>
		</p>
	</section>
</main>
