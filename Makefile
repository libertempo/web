.DEFAULT_GOAL := help

#
# Thanks to https://blog.theodo.fr/2018/05/why-you-need-a-makefile-on-your-project/
#

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## Purge
destroy: ## Détruit l'instance
	App/Tools/destroy

## Mise à jour
update: ## Met l'application à la toute dernière version (patch compris)
	App/Tools/update

## Vérification de l'integrité avant installation
check: ## Controle les prérequis
	App/Tools/check

## Création haut responsable / administrateur
createHR: ## Créé un utilisateur avec les droits HR et administrateur
	App/Tools/createHR ${login} ${nom} ${prenom} ${courriel} ${hash}

## Installation
setup:
	App/Tools/setup ${nom_instance}

install: check setup update createHR check ## Installe la nouvelle instance

reinstall: destroy install ## Reset usine

## Test
test: ## Lance les tests unitaires
	vendor/bin/atoum -ulr

## CI
stan: ## Découvre des bugs d'analyse statique
	vendor/bin/phpstan analyze -l 0 App/ ./admin/ ./edition/ ./export/ ./hr/ ./includes/ Public/ responsable/ utilisateur/ --memory-limit 100M
