Tips And Tricks
===============

1. [Working With Symlinks](#working-with-symlinks)
2. [Remote Access](#remote-access)

Working With Symlinks
---------------------

If you have multiple projects that use Goji, and you want to keep them up to date without too
much hassle, you can use symlinks.

Take this:

```
- ~/Sites
    - goji
    - some-project
    - some-other-project
```

In this scenario, if you want to update Goji you have to manually copy the new files from
`~/Sites/goji/` to your other projects (basically overwrite all `lib/Goji` folders).

Instead you can use symlinks. For example, navigate to `~/Sites/some-project` and replace `/lib` by
a symlink (use `ln -s <source file> <target file>`):

```sh
cd ~/Sites/some-project
rm -rf lib/Goji
ln -s ~/Sites/goji/lib/Goji lib/Goji
```

Using aliases may not work (like with `File` &rarr; `Make Alias` on macOS). Symlinks and aliases are
different in that an alias merely points to a file (and knows how to find it wherever you move it).
Symlinks on the other don't point to files (they contain a path to a file) but they can act like if
they were the file they link to.

Just be careful about using absolute paths when creating them. If you use `~/Sites/goji/lib/Goji`
they will work even if you move them (at least on macOS), but the if use `../goji/lib/Goji` they won't
work if you move them, since the relative path will be broken.

So aliases may not work because Apache or PHP or whatever will try to read them but it can't since
it isn't the original file, it is merely a small file that points to it. But if you use a symlink
Apache can read the file because it will "pass through it," as if it read the original file directly.

Just head to `project.gitignore`, under `# Ignore Goji files` to quickly get an up-to-date list of
files that could prove handy to be replace by a symlink. For now we have:

- `/bin/`
- `/docs/`
- `/lib/Goji/`
- `/public/css/lib/Goji/`
- `/public/img/lib/Goji/`
- `/public/js/lib/Goji/`

Maybe the `bin/newproject.sh` script could help you (be careful with it though).

Remote Access
-------------

This is not specific to Goji, but it's a neat trick to test the app on another device
like a phone or a tablet, without any set up.

On your local machine, you are probably connecting to the server through an address
like `http://localhost:8888` or just `http://localhost`.

Sometimes you also see `127.0.0.1` instead of `localhost`, like `http://127.0.0.1`,
which is the same because `localhost` is an alias of `127.0.0.1`. Also, the port number
isn't always `8888`, and when none is specified it means it uses the default HTTP
port (`80`), in which case you don't need to include it in the address.

In any case, `localhost` or `127.0.0.1` just means *this computer*. If you want to
connect to another computer on the same network, you must replace the `localhost`
with the local network address (LAN address) of that computer.

So to connect to the server running on your development machine from, let's say a
smartphone, you just have to connect to `http://<the machine address>:<port>` 

There are different methods you can use to get this address. Just search for *lan ip mac*
or *lan ip windows* on Google. You'll probably also find it in your router settings.
This address looks something like `192.168.0.0`, `10.0.0.0` or `172.16.0.0`.

Once you have this address, let's say it's `192.168.0.12`, just replace the `localhost`
or `127.0.0.1` with it, like: `http://192.168.0.12:8888`, or `http://192.168.0.12`
(without the port). Note that this address may change. Usually it doesn't if you often
connect to the same network with your machine (like at home), but it also might forget
you and give you a new one.

This should work from any device connected to the same network (i.e. the same router) as
your development machine that runs the server. Just enter this address in the web browser,
and remember to use `http://` and not `https://`, unless you know what you are doing.

If it doesn't work, make sure your firewall doesn't block the port.

### Through the Internet

This method works straight out the box on your local network (LAN). You can also do it
through the Internet (WLAN), but it requires some additional configuration.

From the Internet, all machines connecting to the same router have the same IP address:
it's called NAT. So if you connect to your WLAN IP address, you'll actually connect to
your router, which won't know which computer to forward the request to.

To connect through the Internet, you must configure your router to forward requests on
a specific port to your computer. If your local server runs on port `8888`, and has LAN
address `192.168.0.12`, you must configure your router to redirect incoming requests
on port `8888` to the machine `192.168.0.12`.

How to do this depends on your specific router. Just search on Google for *nat port
forwarding your-router-brand*. You'll then be able to connect to your machine the same
way as before, using your WLAN IP address (which may change daily). To get your WLAN IP,
search for *what is my ip* on Google. It will look something like `http://93.23.180.172:8888`. 
