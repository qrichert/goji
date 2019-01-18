Mémento git
===========

Le tutoriel complet est disponible sur [OpenClassrooms](https://openclassrooms.com/courses/gerez-vos-codes-source-avec-git).

Commandes git
-------------

- **`git status`**, *liste des fichiers modifiés*
- **`git diff`**, *détail des modifications effectuées*
- **`git log`**, *historique des commits*
- **`git add <file|.>`**, *ajouter un nouveau fichier au dépôt*
- **`git add -A`**, *tout ajouter (à utiliser si des fichiers ont été déplacés)*
- **`git commit -a`**, *commiter toutes les modifications*
- **`git commit -am "Message."`**, *commiter sans passer par Vim*
- **`git branch`**, *liste des branches + branche courante*
- **`git branch <branch>`**, *créer une branche*
- **`git branch -d <branch>`**, *supprimer une branche déjà fusionnée*
- **`git branch -D <branch>`**, *supprimer une branche pas fusionnée*
- **`git checkout <branch>`**, *changer de branche*
- **`git checkout <file>`**, *annuler les modifications du fichier*
- **`git merge <branch>`**, *fusionner avec la branche courante*
- **`git push`**, *envoyer sur le serveur*
- **`git pull`**, *télécharger du serveur*
- **`git stash`**, *mettre de côté les changements*
- **`git stash apply`**, *rétablir les changements*

Alias
-----

- **`git status`** → **`git st`**
- **`git commit`** → **`git ci`**
- **`git checkout`** → **`git co`**
- **`git branch`** → **`git br`**


Commandes générales
-------------------

- **`cd <folder>`**, *changer de dossier*
  - **`cd ~/Sites/projet`**, *dossier du projet*
- **`ls`**, *lister les fichiers du dossier courant*
- **`ls -a`**, *idem + fichiers cachés*
- **`man git`**, *manuel de git*

Utilisation de Vim (commits)
----------------------------

Il y a deux modes, le mode *commandes* et le mode *insertion* :

- Appuyer sur **`i`** pour passer en mode insertion (taper du texte)
- Appuyer sur **`Échap`** pour passer en mode commandes (**`Entrée`** pour valider)

### Quelques commandes :

- **`i`**, *mode insertion*
- **`:w`**, *enregistrer*
- **`:q`**, *quitter*
- **`:q!`**, *quitter sans sauvegarder*
- **`:wq`**, *enregistrer et quitter*

En général pour commiter on tape **`git commit -a`** puis **`i`** puis on tape le texte du commit et on finit par **`Échap`** → **`:wq`**.

-------------------------------------------------------------

Extensions Brackets
===================

- **Apache Syntax for Brackets**, *Coloration syntaxique pour .htaccess.* (Terry Ryan)
- **Brackets Color Palette**, *Codes couleur à partir d'images.* (Amin Ullah Khan)
- **Brackets Exclude Indexing FileTree**, *Désindexe les fichiers du .gitignore.* (Dimitris K)
- **Brackets Icons**, *Ajoute des icônes à la liste de fichiers.* (Ivo Gabe de Wolff)
- **Color Highlighter**, *Surligne les noms de couleurs avec la couleur.* (Alexander Taratin)
- **Emmet**, *Raccourcis d'écriture HTML* (Sergey Chikuyonok)
- **Markdown Preview**, *Prévisualier le code Markdown.* (Glenn Ruehle)
- **Name-a-project**, *Renommer le projet et lui attribuer une couleur.* (Pete Nykänen)
- **Open project in terminal**, *Ouvre une fenête de terminal au projet.* (Ranjan Datta)
- **Ouverture Rapide**, *Rechercher un fichier (Cmd/Ctrl + Shift + O)* (Intégré à Brackets)
- **PHP SmartHints**, *Autocomplétion documentée pour PHP.* (Andrew MacKenzie)
- **Scroll Past EOF**, *Permet de scroller plus loin en fin de document.* (Lajos Meszaros)
- **SVG Preview**, *Live preview of SVG images.* (Peter Flynn)
- **Trailingspace**, *Souligne les espaces blancs "whitespace" en rouge.* (Scientech LLC)
