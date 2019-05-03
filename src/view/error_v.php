<main class="error">
	<h1><?= $this->m_httpErrorCode; ?></h1>
	<p>
		<?= ERROR_DESCRIPTION[$this->m_httpErrorCode]; ?>
	</p>
</main>
