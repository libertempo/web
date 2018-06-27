## 2018-02-17
* Dans le processus d'écriture de routes pour l'API, la table `conges_groupe_resp` fait apparaître une relation N-N ; problème, rien est encore fait dans ce sens et le [« package par feature »](http://www.codingthearchitecture.com/2015/03/08/package_by_component_and_architecturally_aligned_testing.html) nous ennuie un peu. Suivant la règle de 3 et le principe selon lequel on doit repousser les décisions importantes plus tard, je vise la simplicité et manipule l'entité « Utilisateur/UtilisateurEntite » suite à la requête. Quand nous en saurons plus on changera.

~ Prytoegrian

## 2017-11-04
* À l'origine, j'avais mis les routes au pluriel car la sémantique était meilleure (avec `GET /plannings`, je veux la liste des plannings), mais l'idée atteint ses limites quand on se confronte à la langue (ex : journaux). Obliger à tenir une map serait inutilement lourd, aussi je vais au plus simple et je mets toutes les routes au singulier.

~ Prytoegrian

## 2017-09-12

* Je renomme les modèles du design MVC en *entités*. Dans un contexte métier, la couche métier ne se résume pas à une seule classe et je ne veux pas perdre le lecteur en créant la confusion dans sa tête si une classe porte ce nom.

~ Prytoegrian


## 2017-09-24

* Afin de suivre complètement Psr4, j'ai donné un préfixe au namespace global. Dans un contexte d'import, ça permet de bien séparer les packages. Ce faisant, \App devient inutile, je remonte donc tout d'un niveau

~ Prytoegrian

## 2016-10-29

* Histoire de faciliter la transmission des connaissances et des intentions, j'amorce la création de ce fichier, en m'appuyant sur cette [proposition](http://akazlou.com/posts/2015-11-09-every-project-should-have-decisions.html).

* Convaincu de la nécessité de séparer les responsabilités de l'application malgré une apparence de simplicité, je commence la création d'une API.
L'un des risques qu'il pourrait y avoir est la dispersion et un ralentissement dans le processus de production,
puisqu'il faut qu'un projet soit à jour pour que l'autre puisse avancer.
Je suppose que ça ne sera vrai que le temps que l'API gagne en puissance.

* Comme l'évoque [cette issue](https://github.com/wouldsmina/Libertempo/issues/134), un framework permet de se concentrer sur son cœur de métier et d'avancer rapidement.
Vu les besoins de l'API, Slim semble faire l'affaire. Le risque évident est la possibilité qu'il ne suffise pas aux besoins de l'application front
et qu'on se retrouve avec deux framework.
J'ai étudié les alternatives Lumen et Laravel, mais tous les frameworks semblent s'être pris d'amour pour le pattern ActiveRecord,
qui est une horreur en terme de principe SOLID et je me refuse à coder ça...

* Je tente quelque chose dans l'arborescence de fichiers.
Bien que l'on suive le pattern MVC, je ne suis pas la traditionnelle arbo avec les répertoires Models - Views - Controllers. À mon sens,
ce n'est pas ça que doit présenter l'arbo de premier abord. Une archi doit présenter l'intention de l'appli ; aussi, les unités de connaissances métiers sont mis en lumière, à l'instar de packages.

~ Prytoegrian
