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

Goji comes with four main CSS files:

- **`reset.css`**
- **`root.css`**
- **`goji.css`**
- **`main.css`**

First, you don't have to use any of them. They are just here for convenience. If there's something
you don't want, just delete it.

**`reset.css`** gives default styling to elements to make design consistant across browsers.
See [Eric Meyer's website](https://meyerweb.com/eric/tools/css/reset/).

**`root.css`** contains the default variables, which you can change too make site wide changes (see the
[variables part](#variables)).

**`goji.css`** is a modular base theme. It contains basic styling as well as some pre-made widgets you
can use (see the [widgets part](#widgets)). It is recommended not to modify this file directly (if you've
decided to use it), but rather overwrite the elements in `main.css`. But it's up to you.

**`main.css`** is where you put the project CSS and overwrite `goji.css` rules.

There is also a **`lib`** folder. **`lib`** is for reusable CSS modules that you have made, or external
CSS modules and libraries.

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
- **`.aligned--left`**: Text align left
- **`.aligned--right`**: Text align right
- **`.aligned--center`**: Text align center
- **`.pre-heading`**: Like a sub title but before the title. Could be used for the date of an article
  or the category.
- **`.sub-heading`**: Same as `.pre-heading`, but like a... sub heading. Under the title.
- **`.video-wrapper`**: To put around an `<iframe>` video so that you can scale it while maintaining a
  16:9 aspect ratio
- **`.loading-dots`**: (on empty `div`) Animated dots to show loading:
  ```html
  <div class="loading-dots loading"></div>
  ```
  Use the `.loading` modifier as shown above to trigger the loading animation
- **`.tooltip`**: A "?" button showing some text on hover. The tooltip can take a `.left` or `.right` modifier.
  ```css
  div.tooltip >
        div.tooltip__button:empty
      + .tooltip__text /* Can be a <div> to contain sub-elements or directly a <p>. */
  ```
- **`.dialog`**: A dialog window. Ideally should be used with the `Dialog` JavaScript class. If not,
  `.dialog` must be contained inside a `.dialog__parent` and can be toggled with the `.show` modifier.
  (`Dialog` does all that automatically).
- **`.toolbar`**: Made to put buttons or actions inside, which will be displayed in rows. Add a `.main-toolbar`
  modifier to add a margin to it (top-of-the page, main toolbar style).
- **`.table`**: Mimics `table` element.
    - **`.table__tr`**: Table row `tr`
    - **`.table__th`**: Table header `th`
    - **`.table__td`**: Table dara `td`
- **`.action-item`**: Action item like in admin panel. Best used with `ActionItem` JavaScript class.
  The structure should be:
  ```css
  .action-item__wrapper >
      .action-item >
            div.action-item__progress:empty
          + img.action-item__icon
          + span.action-item__caption
      /* etc... */
  ```

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

- **`.centered`**: Content is centered horizontally and vertically (if applicable).
- **`.fullscreen`**: Section is at least as high as the viewport (min-height: 100vh).
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
    - **`.with-outline`**: If view is larger than 1405px `section.text.with-outline > .outline` is fixed
      at the left of the text. Otherwise it remains normal.
- **`.side-by-side`**: Puts two blocks side by side and centers them. Ideally used with two `div` children.
  When the screen gets tighter, the blocks are displayed in column.
  You can add several modifiers to it:
    - **`.reverse-on-squeeze`**: Invert the order of the elements in columns mode
    - **`.image`**: To be applied to the children. Gives the contained image(s) a max width of 400px and
      centers it horizontally when in column mode. You may consider using `figure` and `figcaption` instead
      of a regular `div` parent when displaying an image.
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
If not, you can add a **`.dark`** modifier for a dark icon version.

- **`.add`**: Button with a + sign on hover (add action)
- **`.loader`**: Button with loading capacity, append the following classes to change the state:
    - **`.loading`**: Loading in progress
    - **`.loaded`**: Loading was successful
    - **`.failed`**: Loading has failed

#### Other Button-Like Elements

There are other classes that imitate buttons:

- **`.call-to-action`**: A big button. You can wrap it inside a `div.call-to-action__wrapper` so
  Goji can add variations depending on the situation, like centering it (`span` works too if you
  need an inline element).
    - **`.small`**: Same, but small
    - **`.smaller`**: Same, but even smaller

Special Form Elements
---------------------

*(Part of `lib/Goji/inputs.css`)*

- **`.select-wrapper > select`**: To style `select` inputs
- **`input[type=checkbox] + label > span`**: Styled checkbox
    - **`.squared`**: A tag-like checkbox
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
