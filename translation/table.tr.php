<?php

	define('DEFAULT_LANGUAGE', "en");
	define('ACCEPTED_LANGUAGES', array('en', 'fr'));

	/*
		Make sure you don't use the same name for a same page in different languages
		If so, the first to be declared will be used.
	*/

	define('PAGES', array(

		'en' => array(
			'home'	=> 'home',
			'error'	=> 'home' // If language change on error page redirect to home
		),

		'fr' => array(
			'home'	=> 'accueil',
			'error'	=> 'accueil'
		)
	));
