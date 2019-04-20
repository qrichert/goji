<p align="center">
	<a href="#" target="_blank">
		<img src="https://drive.quentinrichert.com/files/goji-text.svg"
			 alt="Goji"
			 width="150"
			 height="150">
	</a>
</p>

<p align="center">
	<em>A simple and minimal PHP framework.</em>
</p>

Installation
------------

1. Download the source files.
2. Put them on your server.
2. Make your domain point to the `public` folder (*i.e.* document root).

**Requirements:**
* Apache
* PHP 7+

Directory Structure
-------------------

- **`_BACKUP_`**, *used to store backups of your files or databases.*
- **`_FACTORY_`**, *used to store work files unrelated to development (*e.g.* Photoshop or Illustrator files).*
- **`lib`**, *reserved for Goji source files.*
- **`public`**, *contains all files accessible from the Web.*
	- **`css`**, *everything that is linked to styling.*
	- **`img`**, *images that are part of the content (not styling).*
	- **`js`**, *JavaScript files.*
	- **`upload`**, *public uploads are stored here.*
- **`src`**, *contains user code (yours).*
	- **`controller`**, *controller files.*
	- **`include`**, *code that has been isolated (often from `index.php`) to make for smaller files.*
	- **`model`**, *model files.*
	- **`operator`**, *mini-controllers performing one specific action (*e.g.* log out or change language).*
	- **`static`**, *scripts for serving static files.*
	- **`view`**, *view files.*
	- **`xhr`**, *mini controllers specific to Ajax requests.*
- **`template`**, *contains template files (*e.g.* page templates).*
- **`translation`**, *contains all files related to translation.*
- **`upload`**, *private uploads are stored here.*
- **`var`**, *for data storage (*e.g.* cache files or metrics files).*
- **`vendor`**, *for external libraries.*

Documentation
-------------

It it's the first time you use Goji, please read the [Getting Started guide](#) (when it comes out).

<p align="center">
	<a href="#" target="_blank">
		<img src="https://drive.quentinrichert.com/files/goji-berries.svg"
			 width="Goji Berries"
			 width="75"
			 height="75">
	</a>
</p>
