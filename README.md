<p align="center">
	<a href="#" target="_blank">
		<img src="public/img/goji__text--dark.svg"
			 alt="Goji"
			 width="87"
			 height="87">
	</a>
</p>

<p align="center">
	A simple full stack framework for the web.
</p>

Installation
------------

1. Download the source files.
   ```sh
   git clone -–depth 1 https://github.com/qrichert/goji.git project-name
   rm -rf project-name/.git
   ```
2. Make your domain points to the `public` folder (document root).
3. Done.

**Requirements:**
- PHP 7.3+
- Apache&#42;
- No dependencies, works right out of the box

*&#42;Nginx works too if you model the `nginx.conf` after the `.htaccess` files.*

Aren’t there enough frameworks already?
---------------------------------------

Yeah, but...

**&#35;1 - I have fun doing this, it’s a hobby**

**&#35;2 - Minimum configuration**. Because frameworks like Symfony or Django are really great...
if you have access to the command line. Goji is designed to work with even the cheapest host
provider. There are no dependencies and no extra software to install—it's plug & play!

**&#35;3 - It’s fast, modular, and full-stack**. When you install it you get a fully working "demo".
Virtually everything you need to make basic websites is already there, plus more. Move things around,
remove what you don't need, that's it. You've got back-end PHP libraries, front-end JavaScript libraries,
a solid HTML template and a Bootstrap-like CSS stylesheet and additional CSS plug-ins. If you need
other libraries, you can add them too.

**&#35;4 - It’s customerveloper focused**. The one benefit of CMSs like WordPress is that customers
can edit content themselves if they need to. But those are usually clunky gas factories, where writing
code is more like an added bonus-functionality than a core design choice. Goji is made for developers
who care about their customers: it starts with the code, so you don't have to hack the system to make
quality products. It is also lean, lightweight and fast by default. And it still provides tools for
customers to edit content.

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
