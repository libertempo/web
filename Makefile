.DEFAULT_GOAL := help

#
# Thanks to https://blog.theodo.fr/2018/05/why-you-need-a-makefile-on-your-project/
#

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## Installation
setup: ## Crée l'application
	App/Tools/setup ${nom_instance}

check: ## Controle les prérequis et l'intégrité
	App/Tools/check

update: install-dep save ## Met l'application à la toute dernière version (patch compris)
	App/Tools/update

createHR: ## Créé un utilisateur avec les droits HR et administrateur
	App/Tools/createHR ${login} ${nom} ${prenom} ${courriel} ${hash}

install: install-dep check setup update createHR check setferies ## Installe la nouvelle instance

destroy: ## Détruit l'instance
	App/Tools/destroy

reinstall: destroy install ## Reset usine

install-dep: ## Installe les dépendances composer et node
	php composer.phar install
	npm update

## Administration
save: ## Sauvegarde la DB
	App/Tools/savedb

restore: destroy check ## Restaure la dernière sauvegarde
	App/Tools/restore

configure: ## Paramètre une option de configuration
	App/Tools/configure ${option} ${valeur}

setferies: ## insert dans la bdd les jours fériés français
	App/Tools/setJoursFeries ${annee} ${force}

## CI
test: ## Lance les tests unitaires
	vendor/bin/atoum -ulr

stan: ## Découvre des bugs d'analyse statique
	vendor/bin/phpstan analyze -l 0 App/ ./admin/ ./edition/ ./export/ ./hr/ ./includes/ Public/ responsable/ utilisateur/ --memory-limit 200M
