<main>
	<h1><?= HELLO_WORLD; ?></h1>

	<!-- URLs translated -->
	<p id="language-selector">
		<a href="<?= PAGES['fr'][CURRENT_PAGE]; ?>" data-lang="fr">Français</a>
		<a href="<?= PAGES['en'][CURRENT_PAGE]; ?>" data-lang="en">English</a>
	</p>

	<!-- URLs not translated -->
<!--
	<p id="language-selector">
		<a href="lang-fr" rel="nofollow">Français</a>
		<a href="lang-en" rel="nofollow">English</a>
	</p>
-->
</main>

<!--
	Use this if URLs are translated.
	It is not mandatory, but it preserves query strings and path format
-->
<script src="js/lib/SwitchLangURLsTranslated.js"></script>
<!--
	Use this if URLs are not translated
-->
<!--<script src="js/lib/SwitchLangURLsNotTranslated.js"></script>-->
