<?php

	foreach ($this->m_app->getLanguages()->getSupportedLocales() as $locale) {

		if ($locale == $this->m_app->getLanguages()->getCurrentLocale())
			continue;

		$page = null; // Current page

			if ($this->m_app->getRouter()->getCurrentPage() == 'blog-post')
				$page = 'blog';
			else if ($this->m_app->getRouter()->getCurrentPage() == 'verify-email')
				$page = 'sign-up';
			else if ($this->m_app->getRouter()->getCurrentPage() == 'reset-password')
				$page = 'login';

		echo '<a href="' . $this->m_app->getRouter()->getLinkForPage($page, $locale) . '" data-lang="' . $locale . '">'
		     . $this->m_app->getLanguages()->getConfigurationLocales()[$locale]
		     . '</a>',
		PHP_EOL;
	}
