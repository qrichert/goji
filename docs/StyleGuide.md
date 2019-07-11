Style Guide
===========

1. [Files](#files)
2. [Variables](#variables)
3. [Widgets](#widgets)
4. [Buttons](#buttons)
5. [Special Form Elements](#special-form-elements)

***Note:** Modifiers are just classes you add. For example a `section` with a text modifier is just
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

- **`.hidden`**: Display none
- **`.spacer`**: Adds a `--gutter-medium` margin on top of the element
- **`.scalable`**: Element gets scaled by a 1.023 factor on hover
- **`.rounded`**: Rounds corners at `--border-radius-medium`
- **`.pre-heading`**: Like a sub title but before the title. Could be used for the date of an article
  or the category.
- **`.video-wrapper`**: To put around an `<iframe>` video so that you can scale it while maintaining a
  16:9 aspect ratio
- **`.loading-dots`**: (on empty `div`) Animated dots to show loading:
  ```html
  <div class="loading-dots loading"></div>
  ```
  Use the `.loading` modifier as show above to trigger the loading animation

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

- **`.centered`**: Content is centered horizontally.
- **`.no-padding`**: Section that has no padding, you can be more specific with:
    - **`.no-padding.h`**: No padding left and right only (horizontal)
    - **`.no-padding.v`**: No padding top and bottom only (vertical)
    - **`.no-padding.t`**: No padding top only
    - **`.no-padding.r`**: No padding right only
    - **`.no-padding.b`**: No padding bottom only
    - **`.no-padding.l`**: No padding left only
- **`.error`**: Made for HTTP error pages. Content is centered horizontally. Should be combined with
  widget area's `centered` modifier and contain an `h1` (error) and a `p` (description).
- **`.text`**: Ideal for reading, like articles. Content is 800px wide max. and centered horizontally.
- **`.side-by-side`**: Puts two blocks side by side and centers them. Ideally used with two `div` children.
  When the screen gets tighter, the blocks are displayed in column.
  You can add several modifiers to it:
    - **`.reverse-on-squeeze`**: Invert the order of the elements in columns mode
    - **`.image`**: To be applied to the children. Gives the contained image(s) a max width of 250px and
      centers it horizontally when in column mode
- **`.video`**: Perfect for welcoming a `.video-wrapper > iframe`

Buttons
-------

*(Part of `lib/Goji/inputs.css`)*

There are three versions of buttons: `<button>`, `<input>` and `<a class="link-button">` which
transforms a link into a button.

#### Modifiers

Modifiers work for the `<button>`, the `<input>` and the `<a class="link-button">` version.

- **`.highlight`**: Special color button for special actions
- **`.delete`**: Button for deletion (delete action)

The following modifiers should be used in combination with `.highlight` or `.delete`.

- **`.add`**: Button with a + sign on hover (add action)
- **`.loader`**: Button with loading capacity, append the following classes to change the state:
    - **`.loading`**: Loading in progress
    - **`.loaded`**: Loading was successful
    - **`.failed`**: Loading has failed

#### Other Button-Like Elements

There are other classes that imitate buttons:

- **`.call-to-action`**: A big button. You can wrap it inside a `div.call-to-action__wrapper` so
  Goji can add variations depending on the situation (like centering it).
    - **`.small`**: Same, but small
    - **`.smaller`**: Same, but even smaller

Special Form Elements
---------------------

*(Part of `lib/Goji/inputs.css`)*

- **`.select-wrapper > select`**: To style `select` inputs
- **`input[type=checkbox] + label > span`**: Styled checkbox
    - **`.toggle`**: A toggle style checkbox
  ```html
  <input type="checkbox" id="cb" class="toggle"><label for="cb"><span></span>Toggle me!</label>
  ```
- **`input[type=radio] + label > span`**: Styled radio button
- **`.progress-bar > .progress`**: Progress bar, the progress is the with of the child in %
  ```html
  <div class="progress-bar"><div class="progress"></div></div>
  ```
  Add a `.shown` modifier to reveal it (`visibility: hidden;` by default)
