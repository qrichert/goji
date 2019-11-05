#!/usr/bin/env bash

BASE_DIR=~/Sites  # Goji's parent dir

if [ -n "$1" ]; then
	BASE_DIR=$1

	if [[ "$BASE_DIR" != "/"* && "$BASE_DIR" != "~/"* ]]; then
		echo "Give an absolute path, starting with '/' or '~/'"
		exit 1
	fi
fi

if [ ! -e "$BASE_DIR" ]; then
	echo "'$BASE_DIR' doesn't exist."
	echo "You can set the directory with a parameter like 'bash newproject.sh ~/Sites'."
	echo "/!\\ Bash doesn't expand '~' if given in quotes like './newproject.sh \"~/Sites/some folder\"\'."
	echo "    Do './newproject.sh ~/Sites/\"some folder\" instead."

	exit 1
fi

read -p "What should your new project be called? " projectName

if [ -e "$BASE_DIR/$projectName" ]; then
	echo "Error: '$projectName' already exists. Won't overwrite it."
	exit 1
fi

git clone https://github.com/qrichert/goji.git "$BASE_DIR/$projectName"

rm -rf "$BASE_DIR/$projectName/.git"
rm "$BASE_DIR/$projectName/.gitignore"
rm "$BASE_DIR/$projectName/LICENSE"
mv "$BASE_DIR/$projectName/project.gitignore" "$BASE_DIR/$projectName/.gitignore"
> "$BASE_DIR/$projectName/README.md"
> "$BASE_DIR/$projectName/TODO.txt"
echo -e "#!/usr/bin/env bash\n\ngit pull && git push" > "$BASE_DIR/$projectName/update.sh"

read -p "Project created, share library files with a clean Goji repository ? (y/n): " useSymlinks

if [ $useSymlinks = 'y' ];
then
	if [ ! -d "$BASE_DIR/goji" ]; then
		git clone https://github.com/qrichert/goji.git "$BASE_DIR/goji"
	fi

	files=('bin'
		'docs'
		'lib/Goji'
		'public/css/goji.css'
		'public/css/reset.css'
		'public/css/lib/Goji'
		'public/js/lib/Goji')

	for file in "${files[@]}"
	do
		read -p "Replace '$file' with a symlink? Type 'y' to confirm: " confirm

		if [ $confirm = 'y' ]
		then
			if [ -e "$BASE_DIR/$projectName/$file" ]; then
				rm -rf "$BASE_DIR/$projectName/$file"
			fi

			ln -sf "$BASE_DIR/goji/$file" "$BASE_DIR/$projectName/$file"
		else
			echo "Did not replace '$file' with a symlink."
		fi
	done
fi

echo "Done."
echo "New project created in '$BASE_DIR/$projectName'"
