/**
 * PasswordsMatch
 * ==============
 *
 * Validator to check whether two passwords match in a form.
 */
class PasswordsMatch {

	constructor(password, passwordConfirmation, errorMessage) {

		this.m_password = password;
		this.m_passwordConfirmation = passwordConfirmation;
		this.m_errorMessage = errorMessage;

		this.m_password.addEventListener('keyup', () => { this.passwordsMatch(); }, false);
		this.m_passwordConfirmation.addEventListener('keyup', () => { this.passwordsMatch(); }, false);
	}

	passwordsMatch() {
		// If empty, let the 'required' handle it
		if (this.m_password.value === '' || this.m_passwordConfirmation.value === '')
			this.m_passwordConfirmation.setCustomValidity('');
		// Passwords not empty && match -> Good
		else if (this.m_password.value === this.m_passwordConfirmation.value)
			this.m_passwordConfirmation.setCustomValidity('');
		// Passwords not empty and no match -> Show error
		else
			this.m_passwordConfirmation.setCustomValidity(this.m_errorMessage);
	}
}
