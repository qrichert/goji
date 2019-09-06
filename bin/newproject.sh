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
