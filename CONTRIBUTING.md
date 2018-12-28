# Contribuer à Libertempo

Contribuer à Libertempo peut se faire de différentes manières :
* voter pour des fonctionnalités ou correction de bug
* rapporter un bug,
* proposer une amélioration ou une fonctionnalité,
* proposer du code,
* en parler autour de vous.

## Voter pour des contributions
La plus impactante des contributions parmi les plus faciles est de voter pour les propositions des autres. Pour cela, notre site de retour d'expérience https://feedback.libertempo.fr/home/ vous liste toutes les contributions à propos desquelles vous pouvez donner votre avis, via des commentaires ou des votes.
Dans le cas des votes, vous disposez de 20 votes que vous pouvez répartir comme vous l'entendez afin de donner une priorité à tel ou tel sujet. Ce mécanisme nous sert à sélectionner les tickets qui seront développés durant les prochaines versions. Les votes vous seront recrédités quand les tickets seront cloturés.

## Rapporter un bug
Un bug est toujours un sujet sensible, c'est un comportement qui *ne devrait pas arriver*, et est par conséquent incompréhensible. Pour nous permettre de cerner au mieux ce qu'il se passe, nous vous avons préparé [un patron](https://github.com/libertempo/web/issues/new?template=rapport-de-bug.md
) contenant les informations essentielles.
Remplissez-le avec soin car un bug qui n'est pas reproductible ne sera pas corrigé et nous devrons fermer le ticket.

Pour nous permettre de prioriser et factoriser les sujets, nous vous invitons à rédiger votre rapport de bug sur https://feedback.libertempo.fr/home/category/1/anomalies.

## Proposer une amélioration
Tout comme pour les bugs, nous vous avons préparé un [patron](https://github.com/libertempo/web/issues/new?template=demande-de-fonctionnalit-.md).
L'objectif ici est de nous permettre de comprendre votre besoin. Aussi, il est essentiel de détailler le *pourquoi* et c'est nous qui vous proposerons le *comment* en fonction de ce que nous pouvons faire, des autres besoins, etc.
Là encore, le rapport est à saisir sur https://feedback.libertempo.fr/home/category/2/amliorations pour que nous puissions nous organiser.


## Proposer du code
Que vous ayez rapporté personnellement un bug ou non, tous nos développements s'appuient sur les tickets. Un bon point de départ peut être de choisir un [ticket adapté aux débutants](https://github.com/libertempo/web/labels/Help%20Wanted).
Passer par un ticket vous assure que le sujet a été discuté en amont et que solution technique a été choisie. Mais vous pouvez tout aussi bien créer du code sur un nouveau sujet. Tout code proposé doit l'être sous forme de PR et hérité de la branche `develop`.

### Processus de validation
La production du code de LT est basé sur le principe qu'une ligne éditoriale doit être suivie pour que la qualité aille croissante et que le code ne soit pas [un joyeux bazar](https://fr.wikipedia.org/wiki/La_Cath%C3%A9drale_et_le_Bazar).
Pour cette raison, nous avons mis en place un processus de go / no-go à des étapes clés :
- un code est proposé dans github via le système de PRs, et soumis à validation en ajoutant les étiquettes « à relire » / « à tester ». Ces étiquettes sont destinées aux autres membres du repo.
- l'un de ces autres membres relit ou teste le code et remplace ces étiquettes par « à retoucher » en justifiant s'il trouve des défauts. Sinon, il enlève les étiquettes et approuve la PR.
- la PR approuvée, elle est fusionnée par le producteur.

Dans un monde idéal, chacun apportant son expérience durant les phases de production, la livraison va croissante et il n'y a pas de temps mort. Seulement, dans le cas de libertempo, nous ne sommes que deux, avec des disponibilités aléatoires et ce processus ne peut être tenu car il génère des blocages.
Je propose donc une amélioration de ce process.

Nous pouvons essayer de conserver le processus actuel, mais en ajoutant les règles suivantes : 
- s'il a pas eu de d'intervention sur la PR passé 5 jours, alors le producteur est autorisé à faire son auto-relecture et son auto-test. S'il estime avoir besoin d'un regard extérieur, il peut le demander explicitement via un commentaire.
- une PR doit pouvoir être mergée dans les 10 jours pour éviter les nombreux travails en simultanée, les changements de contexte et donc les problèmes de merge.

Pour nous aider à conserver la qualité du logiciel tout en améliorant la réactivité, nous pouvons nous faire aider de nos bots CI.
Seul travis est un pré-requis pour que la PR soit mergée, mais il est à la discrétion du producteur de suivre les propositions des autres bots dans la mesure du possible, en fonction de la complexité initiale de la PR et de la nature des propositions.

Nous essaierons de communiquer davantage pour conserver la connaissance de ce fait l'autre. À ce titre, le passage par des PRs est plus que nécessaire ; elles doivent être mises dans le backlog de Github.
