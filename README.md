<p align="center">
	<a href="#" target="_blank">
		<img src="public/img/goji__text--dark.svg"
			 alt="Goji"
			 width="87"
			 height="87">
	</a>
</p>

<p align="center">
	A simple full-stack framework for the web.
</p>

Installation
------------

1. Download the source files.
   ```sh
   git clone --depth 1 https://github.com/qrichert/goji.git <project-name>
   rm -rf <project-name>/.git
   ```
2. Make sure your domain points to the `public` folder (web root).
3. Done.

**Requirements:**
- PHP 7.3+
- Apache&#42;
- No dependencies, works right out of the box

*&#42;Nginx works too if you model the `nginx.conf` after the `.htaccess` files.*

### Docker

You can run Goji directly inside a Docker container.
```sh
bin/console buildnrun
```

Documentation
-------------

If it's the first time you use Goji, please read the [Getting Started guide](docs/index.md).

<p align="center">
	<a href="#" target="_blank">
		<img src="public/img/goji__berries.svg"
			 width="Goji Berries"
			 width="55"
			 height="55">
	</a>
</p>
