## 2017-09-12

* Je renomme les modèles du design MVC en *entités*. Dans un contexte métier, la couche métier ne se résume pas à une seule classe et je ne veux pas perdre le lecteur en créant la confusion dans sa tête si une classe porte ce nom.

~ Prytoegrian


## 2017-09-24

* Afin de suivre complètement Psr4, j'ai donné un préfixe au namespace global. Dans un contexte d'import, ça permet de bien séparer les packages. Ce faisant, \App devient inutile, je remonte donc tout d'un niveau

~ Prytoegrian

## 2016-10-29

* Histoire de faciliter la transmission des connaissances et des intentions, j'amorce la création de ce fichier, en m'appuyant sur cette [proposition](http://akazlou.com/posts/2015-11-09-every-project-should-have-decisions.html).

* Convaincu de la nécessité de séparer les reponsabilités de l'application malgré une apparence de simplicité, je commence la création d'une API.
L'un des risques qu'il pourrait y avoir est la dispersion et un ralentissement dans le processus de production,
puisqu'il faut qu'un projet soit à jour pour que l'autre puisse avancer.
Je suppose que ça ne sera vrai que le temps que l'API gagne en puissance.

* Comme l'évoque [cette issue](https://github.com/wouldsmina/Libertempo/issues/134), un framework permet de se concentrer sur son coeur de métier et d'avancer rapidement.
Vu les besoins de l'API, Slim semble faire l'affaire. Le risque évident est la possibilité qu'il ne suffise pas aux besoins de l'application front
et qu'on se retrouve avec deux framework.
J'ai étudié les alternatives Lumen et Laravel, mais tous les frameworks semblent s'être pris d'amour pour le pattern ActiveRecord,
qui est une horreur en terme de principe SOLID et je me refuse à coder ça...

* Je tente quelque chose dans l'arborescence de fichiers.
Bien que l'on suive le pattern MVC, je ne suis pas la traditionnelle arbo avec les répertoires Models - Views - Controllers. À mon sens,
ce n'est pas ça que doit présenter l'arbo de premier abord. Une archi doit présenter l'intention de l'appli ; aussi, les unités de connaissances métiers sont mis en lumière, à l'instar de packages.

~ Prytoegrian
