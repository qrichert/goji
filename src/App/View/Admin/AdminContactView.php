<main>
	<section class="text">
		<h1><?= $tr->_('ADMIN_CONTACT_MAIN_TITLE'); ?></h1>

		<div id="admin-contact__messages-list"></div>

		<p>
			<a href="<?= $this->m_app->getRouter()->getLinkForPage('admin'); ?>"><?= $tr->_('GO_BACK_TO_ADMIN_AREA'); ?></a>
		</p>
	</section>
</main>

<script>
	(function () {

		const NO_MESSAGES = '<?= addcslashes($tr->_('ADMIN_CONTACT_NO_MESSAGES'), "'"); ?>';
		const DATE_FORMAT = '<?= addcslashes($tr->_('ADMIN_CONTACT_MESSAGE_DATE'), "'"); ?>';
		const SENDER_NAME = '<?= addcslashes($tr->_('ADMIN_CONTACT_MESSAGE_SENDER_NAME'), "'"); ?>';
		const SENDER_EMAIL = '<?= addcslashes($tr->_('ADMIN_CONTACT_MESSAGE_SENDER_EMAIL'), "'"); ?>';
		const TEXT_UNWRAP = '<?= addcslashes($tr->_('TEXT_UNWRAP'), "'"); ?>';
		const TEXT_WRAP = '<?= addcslashes($tr->_('TEXT_WRAP'), "'"); ?>';

		let messagesList = document.querySelector('#admin-contact__messages-list');

		let appendMessages = messages => {

			if (messages.length === 0) {
				if (!messagesList.firstChild) {
					let message = document.createElement('p');
						message.textContent = NO_MESSAGES;
							messagesList.appendChild(message);
				}
				return;
			}

			if (messagesList.firstChild)
				messagesList.appendChild(document.createElement('hr'));

			let docFrag = document.createDocumentFragment();

			for (let message of messages) {

				if (docFrag.firstChild)
					docFrag.appendChild(document.createElement('hr'));

				let date = DATE_FORMAT;
					date = date.replace('%{YEAR}', message.date_sent.year);
					date = date.replace('%{MONTH}', message.date_sent.month);
					date = date.replace('%{DAY}', message.date_sent.day);
					date = date.replace('%{HOUR}', message.date_sent.hour);
					date = date.replace('%{MIN}', message.date_sent.min);

				let messageContainer = document.createElement('div');
					messageContainer.classList.add('admin-contact__message');

					if (message.unopened)
						messageContainer.classList.add('unopened');

						docFrag.appendChild(messageContainer);

					if (message.name !== '' || message.email !== '') {

						let senderDetail = document.createElement('p');

						if (message.name !== '') {
							let name = document.createElement('strong');
								name.textContent = SENDER_NAME + ' ';
									senderDetail.appendChild(name);

							senderDetail.appendChild(document.createTextNode(message.name));
						}

						if (message.name !== '' && message.email !== '')
							senderDetail.appendChild(document.createElement('br'));

						if (message.email !== '') {
							let email = document.createElement('strong');
								email.textContent = SENDER_EMAIL + ' ';
									senderDetail.appendChild(email);

							let emailLink = document.createElement('a');
								emailLink.href = `mailto:${message.email}`;
								emailLink.textContent = message.email;
									senderDetail.appendChild(emailLink);
						}

						messageContainer.appendChild(senderDetail);
					}

					let messageDate = document.createElement('p');
						messageDate.classList.add('sub-heading');
						messageDate.classList.add('admin-contact__message-date');
						messageDate.textContent = date;
							messageContainer.appendChild(messageDate);

					let messageBody = document.createElement('p');
						messageBody.classList.add('admin-contact__message-body');
						messageBody.textContent = message.message;
						messageBody.style.whiteSpace = 'pre-wrap';
						messageBody.style.wordWrap = 'break-word';
							messageContainer.appendChild(messageBody);

					if (message.message.length > 700) {
						messageBody.classList.add('wrapped');

						let readMore = document.createElement('a');
							readMore.classList.add('admin-contact__message-read-more');
							readMore.classList.add('wrapped__unwrap-button');
							readMore.textContent = TEXT_UNWRAP;
								messageContainer.appendChild(readMore);

						readMore.addEventListener('click', e => {
							e.preventDefault();

							if (messageBody.classList.toggle('wrapped'))
								readMore.textContent = TEXT_UNWRAP;
							else
								readMore.textContent = TEXT_WRAP;
						}, false);
					}

			}

			messagesList.appendChild(docFrag);
		};

		appendMessages(<?= json_encode($messages); ?>);
	})();
</script>
