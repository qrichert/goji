Tips And Tricks
===============

1. [Working With Symlinks](#working-with-symlinks)

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

Just head to `project.gitignore`, under `# Ignore Goji files` to quicly get an up-to-date list of
files that could prove handy to be replace by a symlink. For now we have:

- `/bin/`
- `/docs/`
- `/lib/Goji/`
- `/public/css/lib/Goji/`
- `/public/js/lib/Goji/`

Maybe this script can help you (be careful with it though):

```bash
#!/usr/bin/env bash

read -p "What should your new project be called? " projectName

git clone https://github.com/qrichert/goji.git $projectName
cd $projectName
rm -rf .git
rm .gitignore && mv project.gitignore .gitignore

folders=('bin'
		'docs'
		'lib/Goji'
		'public/css/lib/Goji'
		'public/js/lib/Goji')

for folder in "${folders[@]}"
do
	read -p "Delete '$folder'? Type 'y' to confirm: " confirm

	if [ $confirm = 'y' ]
	then
		if [ -e $folder ]; then
			rm -rf "$folder"
		fi
		ln -s ~/Sites/goji/$folder/ $folder
	else
		echo "Did not replace '$folder' by a symlink"
	fi
done
```
