<p align="center">
	<a href="#" target="_blank">
		<img src="public/img/goji__text--dark.svg"
			 alt="Goji"
			 width="150"
			 height="150">
	</a>
</p>

<p align="center">
	A simple full stack framework for the web.
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
- **`bin`**, *contains helper tools.*
	- **`http-headers`**, *reads HTTP headers from given URL.*
	- **`img2base64`**, *converts image file to base64 string.*
	- **`password-maker`**, *generates strong passwords.*
	- **`newproject.sh`**, *automates cloning Goji into a new project (sharing library files).*
- **`config`**, *contains project configuration files.*
- **`docs`**, *contains Goji's documentation files written in Markdown.*
- **`lib`**, *your project libraries.*
	- **`Goji`**, *reserved for Goji source files (Goji core library).*
	- **`AutoLoad.php`**, *auto-loading functions, add your own if needed.*
	- **`RootPath.php`**, *sets constants containing root and webroot paths.*
	- **`Settings.php`**, *basic PHP project settings.*
- **`public`**, *contains all files accessible from the Web.*
	- **`css`**, *everything that is linked to styling.*
	- **`img`**, *images that are part of the content (not styling).*
	- **`js`**, *JavaScript files.*
	- **`upload`**, *public uploads are stored here.*
- **`src`**, *contains user code (yours).*
	- **`Controller`**, *controller files.*
	- **`Model`**, *model files.*
	- **`View`**, *view files.*
- **`template`**, *contains template files (*e.g.* page templates).*
- **`translation`**, *contains all files related to translation.*
- **`var`**, *for data storage (*e.g.* cache files or metrics files).*
- **`vendor`**, *for external libraries.*

Documentation
-------------

It it's the first time you use Goji, please read the [Getting Started guide](docs/index.md).

<p align="center">
	<a href="#" target="_blank">
		<img src="public/img/goji__berries.svg"
			 width="Goji Berries"
			 width="75"
			 height="75">
	</a>
</p>
