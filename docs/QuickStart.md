Quick Start
===========

1. [Create a Project](#create-a-project)
2. [Edit Pages](#edit-pages)
3. [Add Pages](#add-pages)
3. [Remove Pages](#remove-pages)

Create a Project
----------------

1. Clone the repo from [https://github.com/qrichert/](https://github.com/qrichert/) (or download as `.zip`)
2. Rename the folder to your project name (optional)
3. Replace the `.gitignore` file by `project.gitignore` to remove Goji files from versioning (optional).

```sh
git clone https://github.com/qrichert/goji.git
mv goji your-project
cd your-project
rm .gitignore && mv project.gitignore .gitignore
```

Edit Pages
----------

In the example, files are in two parts: the template and the view.

The template is like a frame that never changes, and the content is what is unique to every page.
To learn more about templating in goji, refer to the [Templating](Templating.md) section.

To edit the default home page content, go to `/src/view/home_v.php` (The `_v` stands for View).

Add Pages
---------

To add a new page, first edit the `/config/routes.json5` file.

To add a basic route, add a page ID, with a `route` and a `controller`.

```json5
"new-page-id": {
    route: "/new-page",
    controller: "NewPageController"
}
```

Next step is to create a controller class in `/src/controller/` called `NewPageController` in a
file named `NewPageController.class.php` (so `/src/controller/NewPageController.class.php`).

See [Basic Flow](BasicFlow.md) for more information on controllers.

Remove Pages
------------

To remove a page, just do the [Add Pages](#add-pages) process in reverse.
