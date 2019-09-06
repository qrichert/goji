#!/usr/bin/env bash

BASE_DIR=~/Sites  # Goji's parent dir

if [ -n "$1" ]; then
	BASE_DIR=$1
fi

if [ ! -e "$BASE_DIR" ]; then
	echo -e "'$BASE_DIR' doesn't exist.\nYou can set the directory with a parameter like 'bash newproject.sh ~/Sites'."
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
mv "$BASE_DIR/$projectName/project.gitignore" "$BASE_DIR/$projectName/.gitignore"
rm "$BASE_DIR/$projectName/update.sh"

files=('bin'
	'docs'
	'lib/Goji'
	'public/css/goji.css'
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

		ln -si "$BASE_DIR/goji/$file" "$BASE_DIR/$projectName/$file"
	else
		echo "Did not replace '$file' with a symlink."
	fi
done
