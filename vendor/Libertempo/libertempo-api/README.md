# libertempo-api
[![BCH compliance](https://bettercodehub.com/edge/badge/Libertempo/libertempo-api?branch=develop)](https://bettercodehub.com/)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/c9248e3a815347209c8e56d2291f0da7)](https://www.codacy.com/app/Libertempo/libertempo-api?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Libertempo/libertempo-api&amp;utm_campaign=Badge_Grade)
[![Build Status](https://travis-ci.org/Libertempo/libertempo-api.svg?branch=master)](https://travis-ci.org/Libertempo/libertempo-api)
[![Dependency Status](https://gemnasium.com/badges/github.com/Libertempo/libertempo-api.svg)](https://gemnasium.com/github.com/Libertempo/libertempo-api)


API Libertempo
---

# Initialisation
L'API Libertempo doit être installée comme un domaine à part, autrement dit :
- api.libertempo.tld
- api.libertempo.mon-entreprise.tld

Et non pas comme un sous-répertoire de votre domaine existant :
- mon-entreprise.tld/libertempo/api
- libertempo.mon-entreprise.tld/api

C'est préférable pour l'isolation des systèmes (donc la sécurité), en plus d'être plus simple à gérer côté applicatif (plus de certitudes, donc moins de bugs).

Les échanges se font en JSON et nous suivons les codes HTTP standards.

# Requête
En tant qu'architecture REST, les échanges sont *sans-état*, ce qui signifie que le serveur ne stocke pas d'information pour se *souvenir* d'un client et n'induit rien. Cela implique que le client doit fournir toutes les informations nécessaires à la réalisation d'une action, passant tout d'abord par une *connexion*, puis la transmission à chaque requête du *token* reçu suite à cette connexion.

Les headers basiques, à transmettre pour toute requête, sont :
```
Content-Type: application/json
Accept: application/json
```

## Première requête
Afin de vérifier que l'installation de l'API s'est bien déroulée, nous avons mis en place une route sans authentification :
```
GET /hello_world
```

## Authentification
L'authentification s'appuie sur la méthode [Basic Access](https://en.wikipedia.org/wiki/Basic_access_authentication) :
```
GET /authentification
Authorization: Basic {base64(login:mot_de_passe)}
```

Si l'utilisateur existe et a le droit de se connecter, l'API enverra le token d'identification, dont la durée de validité est de 5min (repoussée à chaque échange).

## Échanges authentifiés
Une fois connecté, tous les échanges devront avoir le header :
```
Token: {token}
```

## Requêtes avec données
Lors d'un ordre avec données (POST | PUT), le corps de la requête doit ressembler à :
```JSON
{
    "propriété1": "valeur1",
    "propriétéN": "valeurN"
}
```

# Réponse

Les réponses de l'API se font sous la spécification jsend. Autrement dit :
```JSON
{
    "code": "codeHTTP",
    "status": "typeDeReponse",
    "message": "messageCorrespondantAuCode",
    "data": "donnéesDeRéponse"
}
```

# Routes disponibles
Suivant les règles de l'architecture REST, les routes disponibles à ce jour sont :
* `GET /plannings`
* `POST /plannings`
* `GET /plannings/{id}`
* `PUT /plannings/{id}`
* `DELETE /plannings/{id}`
* `GET /plannings/{id}/creneaux`
* `POST /plannings/{id}/creneaux`
* `GET /plannings/{id}/creneaux/{id}`
* `DELETE /plannings/{id}/creneaux/{id}`

# Versions

L'API suit `semver`, ce qui signifie qu'une route ne sera enlevée ou que ses spécifications ne seront changées que si la version passe `vM.0.0`.
Attention : pour le moment, le logiciel n'est pas encore en `v1.m.p`, donc ces [cassages de compatibilité](https://github.com/Prytoegrian/check-break#what-is-a-compatibility-break-) peuvent arriver à tout moment.
