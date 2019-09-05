# API Controllers

This is in case you want to have a regular website AND and API running.

You can either create API-specific Controllers and bind them with, for instance,
routes like `/api/users/42` directly in the same app. Or, you could completely
detach them into two separate apps.

In the second case, you could make three folders: one containing the API, another
containing the website, and a third one containing all files in common. In other
words, share all the Models (data handling) but have separate Controllers and
Views as they are likely not the same.

API Controllers and Views would require stateless data processing and JSON output,
whereas website Controllers and Views would need to manage states (or sessions)
and output HTML.

Second solution is better for large scale services with multiple apps (web and
native mobile phone apps for instance). In smaller apps, "API" would more likely
be used for Ajax requests. At small scale it is easier to manage them when they
are in the same app environment.

To make Model sharing easier, you could simply replace the default Model folder
with a symlink to the shared Model folder. So, no matter what project you are
working on, everything would stay up-to-date. No juggling between projects.

There is yet another option, which is treating the website like any other
front-end-only app and make API calls on http://localhost/. But this creates
some overhead because you'd have to JSON-encode and JSON-decode the response.
This last option might be the way to go if you plan to scale and ultimately
run the API and the website on two different servers, as the code would already
be designed like that from the start.
