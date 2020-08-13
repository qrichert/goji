<?php

/*
 * Block only on pages requiring authentication:
 * !$this->m_app->getFirewall()->authenticationRequiredFor($this->m_app->getRouter()->getCurrentPage()))
 *
 * Block only on pages requiring a certain role (e.g. block admin pages but keep logged in user accessible content):
 * $this->m_app->getFirewall()->roleRequiredFor($this->m_currentPage)) == 'admin'
 */
if (!$this->m_app->getUser()->isLoggedIn()):
?>

	<?php /* Ex: Google Analytics */ ?>

<?php endif; ?>
