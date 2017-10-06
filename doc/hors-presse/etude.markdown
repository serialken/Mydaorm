# Ebauche de proposition pour la gestion des prestations Hors Presse

## Les différents types de prestation

### A la charge des tournées

#### Fichier client fourni

Le fichier du client comporte ou non le nombre de BAL par adresse.

Dans le cas contraire:

-> Trouver une solution pour enrichir la BDD de cette information.

Piste évoquée: les infos portage.

Le fichier client est géocodé et normalisé. La zone de couverture comprend les adresses communes entre le référentiel MRoad et les adresses fournies par le client exception faite des adresses Vigik et des adresses avec BAL Gardien et BAL commune.

Un fichier doit être généré avec une colonne OUI/NON indiquant si l'adresse est portée.

Au sein de chaque centre concerné par l'opération (à voir sur la base de l'adresse portée) on détermine les tournées concernées (sur la base jours-type de l'opération + adresse à livrer).
2 limites doivent être prises en compte:

1. nombre maximal de produits HP à distribuer par tournée 
2. nombre maximal de produits HP à distribuer par jour, pour un centre

Un export vers Geoconcept des tournées concernées (composées uniquement des adresses livrables) doit être effectué.

Pour préserver l'intégrité des tournées et ne pas mettre en péril la distribution des produits presse, plusieurs précautions devront être mises en place:

1. Les tournées seront sauvegardées
2. Les tournées sauvegardées devront évoluer **en paralèlle** de *leur équivalent hors-presse*

(voir *Sauvegarde des tournées*)

Les feuilles de portage doivent prendre en compte le produit HP. Par conséquent, la table sur laquelle s'appuie la distribution (CASL) comportera les enregistrements liés à ces produits HP.
A la différence d'un produit presse, les noms des abonnés seront remplacés par *"Distribution toute boite"*.

Autre difficulté qu'il convient de prendre en compte: il est prévu d'effectuer la distribution des produits hors-presse sur plusieurs jours.
Il faudra s'assurer que la période effective de distribution ne dépasse pas la date de fin de l'opération. Car c'est cette date qui sera utilisée pour la restauration de la version 100% presse des tournées.

**Pré-requis:**

* Création des produits HP dans MRoad (utilisation d'un discriminant "hors_presse" au niveau du type de produit)
* Normalisation des adresses fournies par le client et conservation dans une table dédiée.
* Création de l'opération avec un statut "en étude" de façon à traiter l'ensemble des opérations d'avant-vente dans l'interface.

#### Pas de fourniture de fichier client

Nous donnons au client un potentiel d'adresses livrables sur un ou plusieurs codes INSEE. 

Une adresse est intéressante si elle a un minimum de X BAL, qu'il n'y a pas de Vigik ni BAL gardien/commune.
Comment récupérer ces informations ?

#### Portage adressé nominatif

Ex. The kooples

### Tournées dédiées

### Cabotage


## Impact sur les process MRoad existants

### Référentiels sociétés / produits

#### Création des produits hors presse

###### Risque identifié

La création d'une société pour chaque produit hors presse risque de polluer MROAD.

###### Solution préconisée

Une société unique "Hors presse" (code HP) sera mise en place afin d'éviter l'intégration de multiples sociétés. Chaque produit hors presse doit être intégré avec cette société. 
Son type devra faire partie des types de produit hors presse.

### Distribution / Alimentation

#### Typage des hors presse


###### Risque identifié

Il faut pouvoir différencier un produit presse d'un produit hors presse afin d'exécuter des traitements spécifiques.

###### Solution préconisée

Un attribut *hors_presse* (booléen) sera ajouté dans la table produit_type pour différencier les produits hors presse pour lesquels des traitements spécifiques sont nécessaires. Prévoir une initialisation à 1 pour les types ne commençant pas par "Presse" et supprimer le type "Hors presse" (réaffecter pour Nouvelle Attitude).

#### Format de fichier à intégrer

###### Risque identifié

Dans le cas d'un fichier fourni, celui-ci doit faire référence à la société "Hors presse".

###### Solution préconisée
 
Le fichier fourni à MROAD devra contenir le code société "HP". Fichier à retravailler ?

#### Purge lors de l'intégration d'un fichier

###### Risque identifié

Afin de prévoir la relance de l'intégration d'un fichier, le script d'intégration des clients à servir purge les enregistrements déjà existants pour la combinaison société/date du fichier en cours d'intégration. 

Les produits hors presse faisant référence à une unique société "Hors Presse", lors de l'intégration de plusieurs produits hors presse pour la même date, le script risque de supprimer l'historique pour les produits intégrés auparavant pour le même jour. 

###### Solution préconisée

Pour éviter cela, modifier le script d'intégration des clients à servir pour :

* **produits presse** : supprimer les enregistrements déjà existants pour la combinaison société/date du fichier en cours d'intégration
* **produits hors presse** : supprimer les enregistrements déjà existants pour la combinaison produit/date du fichier en cours d'intégration

### Physionomie des tournées

#### Sauvegardes des tournées

###### Risques identifiés

####### Pollution de TD

La table tournee_detail sera polluée par les tournées incluant les hors presse. Les ordres seront modifiés, or le calcul des tournées futures ne doit pas reposer sur ces tournées modifiées. 

###### Solution préconisée

Les tournées 100% presse seront sauvegardées au déclenchement de l'intégration de produits hors presse afin de facilement reprendre leur forme initiale une fois l'opération hors-presse terminée (cas des distributions de produits HP sur des tournées existantes, avec création de points de livraison).

De plus, durant toute la durée de l'opération hors presse, les modifications classiques de la tournée (classement automatique, déplacement manuel de points de livraison...) doivent être opérées à la fois sur la version HP et sur la version 100% des tournées concernées.

Nous aurons donc lors d'une opération hors presse jusqu'à 3 versions d'une tournée:

1. tournée *version HP* (en vigueur, mais dont la date d'expiration est connue)
2. tournée *version 100% presse*, d'origine (juste avant l'intégration des produits hors presse)
3. tournée *version 100% presse dérivée* (qui prend en compte les modifications opérées depuis l'intégration des produits hors presse sur la tournée).

Il est déjà entendu de stocker les tournées 100% presse en suspens (la version d'origine et la version évoluée) dans une table séparée, voire dans un autre système de stockage de données. Cependant, certains points doivent être éclaircis:

1. Quels changements donnent lieu à une évolution de la tournée 100% presse, si tous ne sont pas concernés ?
2. Les tournées version HP qui sont donc en vigueur lors de l'opération hors presse, doivent elles être impactées par ces mêmes changements ? A priori, oui...

####### Pollution des exports vers Geoconcept

Lors de la préparation d'une optimisation de tournée, les enregistrements exportés s'appuient sur l'historique de distribution contenu dans la table *client_a_servir_logist (CASL)*. 

###### Solution préconisée

Les produits hors-presse doivent être filtrés afin de ne pas apparaitre dans les exports.

En revanche, la mise en place d'une souplesse au niveau du requêteur permettrait à l'opérateur d'activer les produits hors-presse au niveau du sélecteur de produit pourrait nous aider à répondre au besoin (non exprimé) de pérenniser une tournée mixte (mi-presse, mi-hors-presse). Dans ce cas les étapes pourraient être:

1. Création d'une opération HP
2. Distribution des produits HP sur des tournées 100% presse
3. Fin de l'opération
4. Décision de poursuivre la distribution de ces produits au sein de tournées mixtes
5. Mise en place d'une optimisation des tournées sélectionnées avec prise en compte des produits HP
6. Application de l'optimisation
7. Alimentation des clients à servir
8. Distribution


#### Schéma global de répartition et exceptions

##### Risque identifié

Selon l'expression des besoins (page 7)

> Il est possible sur certaines adresses d’avoir 2 centres et 2 tournées (1 pour le jour et 1 pour la nuit).

Cette possibilité semble aller à l'encontre des développements en cours sur le schéma de répartition et les exceptions. En effet, selon les nouvelles règles, un INSEE ne peut être desservi que par un dépôt et au travers d'un seul flux. Ce réglage peut être défini à trois niveaux:

1. exception produit
2. exception société
3. schéma global

Le niveau de priorité du réglage à appliquer se fait dans l'ordre dans lequel les niveaux ont été cités (de l'exception produit vers le schéma global).

Quel que soit le réglage appliqué, il n'est pas possible dans ces conditions, que le même produit, pour deux adresses différentes appartenant au même INSEE soit distribué par 2 dépôts différents ou à travers 2 flux différents.

##### Solution préconisée

ND.

******************

### Cartographie

#### Classement automatique

#### Affichage des tournées

### Historisation des tournées

### Prestations concernées

* Portage adressé nominatif


