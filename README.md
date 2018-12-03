 ![Logo](http://libertempo.tuxfamily.org/Logo-Libertempo.png)


[![BCH compliance](https://bettercodehub.com/edge/badge/libertempo/web?branch=develop)](https://bettercodehub.com/)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ed902981f4fb40bda7b90c199a0b4da1)](https://www.codacy.com/app/libertempo/web)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/ed902981f4fb40bda7b90c199a0b4da1)](https://www.codacy.com/app/libertempo/web)
![build_status](https://travis-ci.org/libertempo/web.svg?branch=master)
[![licence](https://img.shields.io/badge/licence-GPL2-green.svg)](https://github.com/libertempo/web/blob/develop/LICENSE)


# Présentation

Libertempo est une application web interactive de gestion des congés du personnel. Elle a pour objectif de rendre la gestion des congés accessible à tous.

Libertempo se veut être au plus proche des règles inhérentes aux réglementations françaises tout en restant paramétrable afin de répondre aux particularités et conventions des entreprises et des administrations.

Plus d'informations sont disponibles sur le [blog](http://libertempo.tuxfamily.org) et la [documentation](http://libertempo.tuxfamily.org/Documentation).

# Initialisation
Avant tout, il vous faut installer `npm`, le [gestionnaire de paquet](https://www.npmjs.com/get-npm) de Node.

Ensuite, l'installation sous sa forme la plus simple se résume à faire :
```sh
git clone --single-branch -b master git@github.com:libertempo/web.git
cd web
ln -sf `pwd`/App/Tools/post-checkout .git/hooks/post-checkout
make install-dep
make install
```

Chaque nouvelle version est mise à disposition sur [github](https://github.com/Libertempo/Libertempo-web/releases) et sur [le blog du logiciel](http://libertempo.tuxfamily.org/downloads/)

# Support minimum
| Logiciel | Version |
|-------|-----|
| php   | 7.1 |
| mysql | 8.0 |
| apache| 2.4 |


# Contact
Nous sommes joignable par mail : 	libertempo@lists.tuxfamily.org.
ou directement par irc à l'adresse : irc://irc.tuxfamily.org/Libertempo. Accès direct par webclient [ici](https://client02.chat.mibbit.com/?url=irc%3A%2F%2Firc.tuxfamily.org%2FLibertempo).
