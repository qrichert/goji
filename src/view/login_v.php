<main class="centered">
	<h1 class="hidden" aria-hidden="true">Login</h1>
	<section class="centered no-padding h">
		<img src="img/goji__berries.svg" alt="" class="form__logo" aria-hidden="true">
		<form action="<?= $this->m_app->getRouter()->getLinkForPage('login'); ?>" method="post" class="form__login">
			<label for="login__email"><?= $tr->_('LOGIN_FORM_EMAIL'); ?></label>
			<input type="email" name="login[email]" id="login__email" placeholder="<?= $tr->_('LOGIN_FORM_EMAIL_PLACEHOLDER'); ?>">
			<div class="form__label-relative">
				<label for="login__password"><?= $tr->_('LOGIN_FORM_PASSWORD'); ?></label>
				<a href="#" class="form__side-info"><?= $tr->_('LOGIN_FORGOT_PASSWORD'); ?></a>
			</div>
			<input type="password" name="login[email]" id="login__password" placeholder="<?= $tr->_('LOGIN_FUN_MESSAGE', mt_rand(1, 3)); ?>">
			<div class="progress-bar hidden"><div class="progress"></div></div>
			<button class="spacer highlight loader"><?= $tr->_('LOGIN_FORM_LOG_IN_BUTTON'); ?></button>
		</form>
		<div>
			<p><?= $tr->_('LOGIN_NO_ACCOUNT'); ?> <a href="#"><?= $tr->_('LOGIN_SIGN_UP'); ?></a></p>
		</div>
	</section>
</main>
