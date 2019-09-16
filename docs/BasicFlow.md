Basic Flow
==========

1. [Request Chain](#request-chain)
2. [App](#app)
3. [Controllers](#controllers)

Request Chain
-------------

Requests follow this path:

`.htaccess` &rarr; `index.php` &rarr; `App.class.php` &rarr; `YourController.class.php`

In theory you have nothing to do but create a controller and set a route for it in the config
file. The following part about the [App](#app) is just there so you can understand the inner
workings of the framework, you can skip to the [controllers](#controllers) part if you prefer.

Understanding how the different pieces work together (there are just a few) is great. Because if
you do you can access them from your code, and use them for your own purposes. That's what they
are made for.

There are also requests to static files that go like this:

`.htaccess` &rarr; `static.php`.

App
---

`App` is to Goji what mayonnaise is to a sandwich.

It keeps it all together. It is the link between all the the different parts.

All the following classes we will see communicate together through the global `App` object.
`App` is declared once in `index.php` and is then passed along to the different constructors.

For example, we will talk about the `Languages` and the `Router` class. Just to illustrate
how `App` is used, let's look at how `Router` knows the language it should be using:

```php
// Within the App class
$this->m_languages = new Languages($this);
$this->m_router = new Router($this); // $this points to App

// Within the Router class
$this->m_app->getLanguages()->getCurrentLocale();
```

`Router` has an attribute `$this->m_app` that points to the global `App` object (passed to the
constructor). Now it can use this reference to call `App::getLanguages(): Languages` that
returns the `Languages` object attached to the `App`, and access `Languages::getCurrentLocale()`.

Here `App` doesn't have any information in and of itself. It simply enables `Router` to communicate
with the `Languages` object. The same can be done with all classes whose constructor asks for an `App`
parameter. This includes all controller classes, so you can access all these objects from within
your controllers.

Of course, you don't have to use this class, and go through the following process manually—*i.e.*
call the different objects by hand. `App` is here for convenience and covers most use cases,
but if you want to do something specific, you can just omit it.

### Launching the App

```php
$app = new App();
    $app->exec();
```

Basically what this does is it creates an `App` object, loads the database from your config file (if any)
and starts the routing process.

App automatically uses a `\Goji\Core\RequestHandler` object (accessible via `App::getRequestHandler()`),
a `\Goji\HumanResources\User` object (accessible via `App::getUser()`), an `\Goji\HumanResources\Authentication` object
(accessible via `App::getAuthentication()`), and a `\Goji\Core\Firewall` object (accessible via `App::getFirewall()`).

- `RequestHandler` analyzes the HTTP request and extracts (and sanitizes) some useful information like
the request URI, request page, raw query string, query string as array, script name, root folder, etc.

- `User` represents any visitor, logged in or not.

- `Authentication` deals with user authentication.

- `Firewall` can detect if a requested page is forbidden for a user (not logged in for example, or
page forbidden for logged in users).

The database will be accessible via `App::getDataBase(): PDO`. Really it's a `\Goji\Core\DataBase`
object which is returned, but that's just a child of PDO that reads your config file. So really it's
just a regular `PDO`.

To access the database in your controllers, just do `$app->getDatabase(?id)`...

If you provide an ID (matching your config file), the corresponding database parameters will be used.
If no ID is given, the first database appearing in the config and that works is selected.

Usually you would have the production first and the local second. So if you're on your production server,
the production DB will be loaded. And if you're on the local server, the production won't load and will
fail, and the local one will be selected automatically.

`$app->exec()` starts the routing process. The routing ends with the calling of the `render()` method
of your controller. It goes like this:

1. Create a `\Goji\Core\Languages` object, accessible via `App::getLanguages(): Languages`. This class
   handles language preferences. It takes the languages accepted by the browser and those supported by
   your app and finds the best match.
2. Create a `\Goji\Core\Router` object, accessible via `App::getRouter(): Router`.
3. Call `Router::route()`.
	- If everything is okay, `Router` will match the request path with the routes you have set in your
	  config file. Once the right route is found, `Router` calls the `render()` method of the appropriate
	  controller.
	- If there's an error somewhere (like a 404), `Router::requestErrorDocument()` will be called which
	  will ultimately render the `\App\Controller\HttpErrorController` (that you can modify because it's
	  not part of the lib, it's in the `/src/Controller/` folder).

`App::exec()` should always be the last instruction. This is because `App::exec()` calls  `Router::route()`,
and `Router::route()` calls `exit;` just after rendering the controller. `Controller::render()` is meant to
render the view, so it should be the last thing you do anyway.

Controllers
-----------

Controllers must:

1. Be in the `\App\Controller` namespace
2. Extend the `Goji\Blueprints\ControllerAbstract` abstract class, so:
	- Have a `public function __construct(App $app);` method if you overload it
	- Have a `public function render();` method

By default, `Goji\Blueprints\ControllerAbstract` gives you a `$this->m_app` attribute, so you can access
the main `App` object from your controller, but you're not forced to use it. On the other hand, if you
choose to overload the `__construct()` method, then it is required is to accept an `App` object as parameter,
since `Router` passes the global `App` object to the controller constructor, like:

```php
$controller = new \App\Controller\YourController($app);
    $controller->render();
```

If you overload the constructor, always call `parent::__construct($app);` inside the constructor so the
initialization will be taken care of for you.

The `render()` method is also mandatory, since `Router` calls this method automatically (as shown above).
`render()` is where you should call your model and your view.
