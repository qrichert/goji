<?php if (!$this->m_app->getUser()->isLoggedIn()
          && !$this->m_app->getFirewall()->authenticationRequiredFor($this->m_app->getRouter()->getCurrentPage())): ?>

	<?php /* Ex: Google Analytics */ ?>

<?php endif; ?>
