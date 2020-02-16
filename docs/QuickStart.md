Quick Start
===========

1. [Create a Project](#create-a-project)
2. [Edit Pages](#edit-pages)
3. [Add Pages](#add-pages)
3. [Remove Pages](#remove-pages)
4. [Log In](#log-in)

Create a Project
----------------

1. Clone the repo from [https://github.com/qrichert/](https://github.com/qrichert/) (or download as `.zip`)
2. Remove versioning
3. Replace the `.gitignore` file by `project.gitignore` to remove Goji files from versioning (optional).

```sh
git clone --depth 1 https://github.com/qrichert/goji.git project-name
cd project-name
rm -rf .git
rm .gitignore && mv project.gitignore .gitignore
```

You should remove versioning because you won't be working on Goji itself, but your independent project.
If you want to version your project, just initialize it after, as you always do:

```sh
git add .
git commit -am "Initial commit."
```

Note the `--depth 1` option, it tells Git to only download the latest version of the code, without the
whole project history (shallow clone). It's not essential but makes the download faster since you'll
delete all Git history right after anyway.

Edit Pages
----------

In the example, files are in two parts: the template and the view.

The template is like a frame that never changes, and the content is what is unique to every page.
To learn more about templating in Goji, refer to the [Templating](Templating.md) section.

To edit the default home page content, go to `/src/App/View/HomeView.php`.

Add Pages
---------

To add a new page, first edit the `/config/routes.json5` file.

To add a basic route, add a page ID, with a `route` and a `controller`.

```json5
"new-page-id": {
    route: "/new-page",
    controller: "App/NewPageController"
}
```

Next step is to create a controller class in `/src/App/Controller/` called `NewPageController` in a
file named `NewPageController.class.php` (so `/src/App/Controller/NewPageController.class.php`).

See [Basic Flow](BasicFlow.md) for more information on controllers.

Remove Pages
------------

To remove a page, just go through the [Add Pages](#add-pages) process in reverse.

Log In
------

To log in with the demo account use these credentials on the `/login` page:

- Username: `root@users.goji`
- Password: `goji`

These can be edited in the default database `/var/db/goji.sqlite3`.
