<p align="center">
	<a href="#" target="_blank">
		<img src="https://drive.quentinrichert.com/files/goji__text--dark.svg"
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
- Apache
- PHP 7.3+
- No dependencies, works right out of the box

Directory Structure
-------------------

- **`_BACKUP_`**, *used to store backups of your files or databases.*
- **`_FACTORY_`**, *used to store work files unrelated to development (*e.g.* Photoshop or Illustrator files).*
- **`config`**, *contains project configuration files.*
- **`lib`**, *your project libraries.*
	- **`Goji`**, *reserved for Goji source files (Goji core library).*
	- **`AutoLoad.php`**, *Auto-loading functions, add your own if needed.*
- **`public`**, *contains all files accessible from the Web.*
	- **`css`**, *everything that is linked to styling.*
	- **`img`**, *images that are part of the content (not styling).*
	- **`js`**, *JavaScript files.*
	- **`upload`**, *public uploads are stored here.*
- **`src`**, *contains user code (yours).*
	- **`controller`**, *controller files.*
	- **`model`**, *model files.*
	- **`static`**, *scripts for serving static files.*
	- **`view`**, *view files.*
- **`template`**, *contains template files (*e.g.* page templates).*
- **`translation`**, *contains all files related to translation.*
- **`upload`**, *private uploads are stored here.*
- **`var`**, *for data storage (*e.g.* cache files or metrics files).*
- **`vendor`**, *for external libraries.*

Documentation
-------------

It it's the first time you use Goji, please read the [Getting Started guide](docs/index.md).

<p align="center">
	<a href="#" target="_blank">
		<img src="https://drive.quentinrichert.com/files/goji__berries.svg"
			 width="Goji Berries"
			 width="75"
			 height="75">
	</a>
</p>
