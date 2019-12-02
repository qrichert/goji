/**
 * Class Terminal
 *
 * How to use it:
 * --------------
 *
 * <div id="terminal" data-action="/xhr-terminal"></div>
 *
 * new Terminal(document.querySelector('#terminal'));
 */
class Terminal {

	constructor(parent) {

		this.m_parent = parent;

			this.m_output = null;
			this.m_prompt = null;
				this.m_promptInfo = null;
					this.m_promptInfoUser = null;
					this.m_promptInfoSeparator = null;
					this.m_promptInfoPath = null;
					this.m_promptInfoWaitingForCommand = null;
					this.m_promptInfoWaitingForResponse = null;
				this.m_promptCommand = null;
					this.m_input = null;

		this.m_apiUrl = this.m_parent.dataset.action || location.href;

		this.m_isWaitingForResponse = false;
		this.m_isWaitingForResponseIntervalHandle = null;

		this.m_isPasswordMode = false;

		this.m_commandHistory = [];
		this.m_commandHistoryCount = 0;
		this.m_commandHistoryIndex = 0;

		this.buildTerminalView();

		this.ehlo();

		this.m_input.focus();
	}

	/**
	 * @private
	 */
	buildTerminalView() {

		this.m_parent.classList.add('terminal');

		let docFrag = document.createDocumentFragment();

			this.m_output = document.createElement('div');
				this.m_output.classList.add('terminal__output');
					docFrag.appendChild(this.m_output);

			this.m_prompt = document.createElement('div');
				this.m_prompt.classList.add('terminal__prompt');
					docFrag.appendChild(this.m_prompt);

				this.m_promptInfo = document.createElement('div');
					this.m_promptInfo.classList.add('terminal__prompt--info');
						this.m_prompt.appendChild(this.m_promptInfo);

					this.m_promptInfoUser = document.createElement('span');
						this.m_promptInfoUser.classList.add('user');
							this.m_promptInfo.appendChild(this.m_promptInfoUser);

					this.m_promptInfoSeparator = document.createElement('span');
						this.m_promptInfoSeparator.classList.add('separator');
							this.m_promptInfo.appendChild(this.m_promptInfoSeparator);

					this.m_promptInfoPath = document.createElement('span');
						this.m_promptInfoPath.classList.add('path');
							this.m_promptInfo.appendChild(this.m_promptInfoPath);

					this.m_promptInfoWaitingForCommand = document.createElement('span');
						this.m_promptInfoWaitingForCommand.classList.add('waiting-for-command');
						this.m_promptInfoWaitingForCommand.textContent = '$';
							this.m_promptInfo.appendChild(this.m_promptInfoWaitingForCommand);

					this.m_promptInfoWaitingForResponse = document.createElement('span');
						this.m_promptInfoWaitingForResponse.classList.add('waiting-for-response');
						this.m_promptInfoWaitingForResponse.textContent = '';
							this.m_promptInfo.appendChild(this.m_promptInfoWaitingForResponse);

					this.m_promptInfo.appendChild(document.createTextNode(String.fromCharCode(160))); // &nbsp;

				this.m_promptCommand = document.createElement('div');
					this.m_promptCommand.classList.add('terminal__prompt--command');
						this.m_prompt.appendChild(this.m_promptCommand);

					this.m_input = document.createElement('input');
						this.m_input.autocapitalize = 'off';
						this.m_input.spellcheck = 'false';
						this.m_input.style.visibility = 'hidden';
						this.m_input.addEventListener('keydown', e => { this.inputKeyEvent(e); }, false);
							this.m_promptCommand.appendChild(this.m_input);

		this.m_parent.appendChild(docFrag);
	}

	/**
	 * @param e
	 * @private
	 */
	inputKeyEvent(e) {

		if (e.key === 'Enter') {
			this.command();
			return;
		}

		if (e.key === 'ArrowUp') {
			this.commandHistoryPrevious();
			return;
		}

		if (e.key === 'ArrowDown') {
			this.commandHistoryNext();
			return;
		}
	}

	commandHistoryPrevious() {

		if (this.m_isPasswordMode || this.m_commandHistoryCount === 0)
			return;

		this.m_commandHistoryIndex--;

		if (this.m_commandHistoryIndex < 0) { // At start
			this.m_commandHistoryIndex = 0;
		}

		this.m_input.value = this.m_commandHistory[this.m_commandHistoryIndex];
	}

	commandHistoryNext() {

		if (this.m_isPasswordMode || this.m_commandHistoryCount === 0)
			return;

		this.m_commandHistoryIndex++;

		if (this.m_commandHistoryIndex >= this.m_commandHistoryCount) {
			this.m_commandHistoryIndex = this.m_commandHistoryCount;
			this.m_input.value = ''; // If overflow, clear input (back to normal)
		} else {
			this.m_input.value = this.m_commandHistory[this.m_commandHistoryIndex];
		}
	}

	/**
	 * @private
	 */
	switchToCommandInterface() {
		this.m_isPasswordMode = false;
		this.m_input.type = 'text';
		this.m_input.name = 'terminal[command]';
	}

	/**
	 * @private
	 */
	switchToPasswordInterface() {
		this.m_isPasswordMode = true;
		this.m_input.type = 'password';
		this.m_input.name = 'terminal[password]';
	}

	/**
	 * @private
	 */
	startWaitingForResponse() {

		let loadingCharsSequence = ['/', '—', '\\', '|'];
		let currentChar = 0;
		let nbChars = loadingCharsSequence.length;

		this.m_promptInfoWaitingForResponse.textContent = loadingCharsSequence[currentChar];

		this.m_isWaitingForResponse = true;
		this.m_isWaitingForResponseIntervalHandle = setInterval(() => {

			currentChar++;

			if (currentChar > nbChars - 1)
				currentChar = 0;

			this.m_promptInfoWaitingForResponse.textContent = loadingCharsSequence[currentChar];

		}, 250);

		this.m_promptInfoWaitingForCommand.style.display = 'none';
		this.m_promptInfoWaitingForResponse.style.display = 'inline';
		this.m_input.disabled = true;
	}

	/**
	 * @private
	 */
	stopWaitingForResponse() {

		this.m_promptInfoWaitingForCommand.style.display = 'inline';
		this.m_promptInfoWaitingForResponse.style.display = 'none';
		this.m_input.disabled = false;
		this.m_input.value = '';

		this.m_isWaitingForResponse = false;
		clearInterval(this.m_isWaitingForResponseIntervalHandle);
	}

	/**
	 * @param {String} output
	 * @param {String|null} lastCommand
	 * @private
	 */
	printOutput(output, lastCommand = null) {

		let docFrag = document.createDocumentFragment();

			if (typeof lastCommand === 'string' || lastCommand instanceof String) { // typeof new String('') = object

				let promptInfo = document.createElement('div');
					promptInfo.classList.add('terminal__prompt--info');
						docFrag.appendChild(promptInfo);

					let promptInfoUser = this.m_promptInfoUser.cloneNode(true);
						promptInfo.appendChild(promptInfoUser);

					let promptInfoSeparator = this.m_promptInfoSeparator.cloneNode(true);
						promptInfo.appendChild(promptInfoSeparator);

					let promptInfoPath = this.m_promptInfoPath.cloneNode(true);
						promptInfo.appendChild(promptInfoPath);

					let promptInfoWaitingForCommand = this.m_promptInfoWaitingForCommand.cloneNode(true);
						promptInfoWaitingForCommand.style.display = 'inline';
							promptInfo.appendChild(promptInfoWaitingForCommand);

					promptInfo.appendChild(document.createTextNode(String.fromCharCode(160))); // &nbsp;

					let lastCommandText = document.createElement('span');
						lastCommandText.textContent = lastCommand;
							promptInfo.appendChild(lastCommandText);
			}

			let commandResult = document.createElement('div');
				commandResult.innerHTML = output;
					docFrag.appendChild(commandResult);

		this.m_output.appendChild(docFrag);
	}

	/**
	 * @private
	 */
	clearOutput() {
		this.m_output.textContent = '';
	}

	/**
	 * @param {String} user
	 * @param {String} path
	 * @private
	 */
	setPromptInfo(user, path) {
		this.m_promptInfoUser.textContent = user;
		this.m_promptInfoSeparator.textContent = ':';
		this.m_promptInfoPath.textContent = path;
	}

	/**
	 * @private
	 */
	clearPromptInfo() {
		this.m_promptInfoUser.textContent = '';
		this.m_promptInfoSeparator.textContent = '';
		this.m_promptInfoPath.textContent = '';
	}

	/**
	 * Initial, set-up request
	 *
	 * @private
	 */
	ehlo() {
		let data = new FormData();
			data.append('request', 'ehlo');

		let end = () => {
			this.stopWaitingForResponse();
			this.m_input.focus();
		};

		let error = () => {
			end();
		};

		let load = (r) => {

			if (r === null || r.status === 'ERROR') {
				error();
				return;
			}

			if (typeof r.response === 'undefined' || r.response === null) {
				error();
				return;
			}

			this.m_input.style.visibility = 'visible';

			if (r.response === 'ready') {
				this.setPromptInfo(r.user, r.path);
				this.switchToCommandInterface();
			} else {
				this.switchToPasswordInterface();
			}

			if (typeof r.output !== 'undefined' && r.output !== null)
				this.printOutput(r.output);

			end();
		};

		this.startWaitingForResponse();

		SimpleRequest.post(
			this.m_apiUrl,
			data,
			load,
			error,
			error,
			null,
			{ get_json: true }
		);
	}

	/**
	 * @private
	 */
	logIn() {
		let data = new FormData();
			data.append('request', 'log-in');
			data.append('password', this.m_input.value);

		let end = () => {
			this.stopWaitingForResponse();
			this.m_input.focus();
		};

		let error = (r = null) => {

			if (r !== null && typeof r.output !== 'undefined' && r.output !== null)
				this.printOutput(r.output);
			else if (typeof r === 'string' || r instanceof String)
				this.printOutput(r);

			end();
		};

		let load = (r) => {

			if (r === null || r.status === 'ERROR') {
				error(r);
				return;
			}

			if (typeof r.response === 'undefined' || r.response === null) {
				error(r);
				return;
			}

			if (r.response === 'ready') {
				this.clearOutput();
				this.setPromptInfo(r.user, r.path);
				this.switchToCommandInterface();
			} else {
				error(r);
				return;
			}

			if (typeof r.output !== 'undefined' && r.output !== null)
				this.printOutput(r.output);

			end();
		};

		this.startWaitingForResponse();

		SimpleRequest.post(
			this.m_apiUrl,
			data,
			load,
			error,
			error,
			null,
			{ get_json: true }
		);
	}

	/**
	 * @private
	 */
	command() {
		if (this.m_isWaitingForResponse)
			return;

		if (this.m_isPasswordMode) {
			this.logIn();
			return;
		}

		let command = this.m_input.value.trim().toLowerCase();

		this.m_commandHistory.push(command);
		this.m_commandHistoryCount = this.m_commandHistory.length;
		this.m_commandHistoryIndex = this.m_commandHistoryCount; // last (will be --; before display)

		if (command === 'clear') {
			this.clearOutput();
			// Doesn't need to be started to be stopped
			// We use it to reset the prompt
			this.stopWaitingForResponse();
			return;
		}

		this.startWaitingForResponse();

		let data = new FormData();
			data.append('request', 'command');
			data.append('command', command);


		let end = () => {
			this.stopWaitingForResponse();
			this.m_input.focus();
		};

		let error = (r = null) => {

			if (r !== null && typeof r.output !== 'undefined' && r.output !== null)
				this.printOutput(r.output);
			else if (typeof r === 'string' || r instanceof String)
				this.printOutput(r);

			end();
		};

		let load = (r) => {

			if (r === null || r.status === 'ERROR') {
				error(r);
				return;
			}

			if (typeof r.response === 'undefined' || r.response === null) {
				error(r);
				return;
			}

			if (typeof r.output !== 'undefined' && r.output !== null)
				this.printOutput(r.output, command);

			if (typeof r.path !== 'undefined' && r.path !== null)
				this.m_promptInfoPath.textContent = r.path;

			if (r.response === 'exit-required') { // User is logged out, reload to reset terminal
				this.clearPromptInfo();
				this.switchToPasswordInterface();
			}

			end();
		};

		this.startWaitingForResponse();

		SimpleRequest.post(
			this.m_apiUrl,
			data,
			load,
			error,
			error,
			null,
			{ get_json: true }
		);
	}
}
