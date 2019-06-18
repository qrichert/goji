Style Guide
===========

1. [Files](#files)
2. [Variables](#variables)
3. [Widgets](#widgets)

***Note:** modifiers are just classes you add. For example a `section` with a text modifier is just
a `section.text` or `<section class="text">`.*

Files
-----

Goji comes with four CSS files:

- **`root.css`**
- **`goji.css`**
- **`main.css`**
- **`responsive.css`**

First, you don't have to use any of them. They are just here for convenience. If there's something
you don't want, just delete it.

**`root.css`** contains the default variables, which you can change too make site wide changes (see the
[variables part](#variables)).

**`goji.css`** is a modular base theme. It contains basic styling as well as some pre-made widgets you
can use (see the [widgets part](#widgets)). It is recommended not to modify this file directly (if you've
decided to use it), but rather overwrite the elements in `main.css`. But it's up to you.

**`main.css`** is where you put the project CSS and overwrite `goji.css` rules.

**`responsive.css`** is meant to contain all media queries. Everything that is linked to responsiveness.

There are also a **`lib`** and a **`vendor`** folder. **`lib`** is for reusable CSS modules that you have
made and **`vendor`** are for external CSS modules or libraries.

Variables
---------

**`:root`** contains all the styling variables. Variables are great for consistency and make changes
more easy (compared to search & replace).

Variables have good support, except in IE, even 11. If you want to support legacy browsers you can
minify the CSS (Goji has a built-in functionality for that).

Try to change `--gutter-default` to something else than `20px` for example. All the elements using
this property will change accordingly.

Classes
-------

- **`.pre-heading`**: Like a sub title but before the title. Could be used for the date of an article
  or the category.

Widgets
-------

### Widget Area

**`main`** acts as a widget area.

By default it is organized vertically at 100% width.

#### Modifiers

- **`.centered`**: Content is centered vertically in the page.

### Widget Elements

**`section`** are basic widgets that by default take up 100% width.

By default a widget has a `--gutter-default` (20px) padding and the first and the last child
don't have a top and bottom margin respectively.

#### Modifiers

- **`.error`**: Made for HTTP error pages. Content is centered horizontally. Should be combined with
  widget area's `centered` modifier and contain an `h1` (error) and a `p` (description).
- **`.text`**: Ideal for reading, like articles. Content is 800px wide max. and centered horizontally.
