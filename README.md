# Démo PHP Data Object (PDO)

Une démo de l'extension PDO en PHP pour interagir avec des bases de données relationnelles et une synthèse des choses à savoir pour utiliser l'extension. La démo utilise ici une base de données SQLite par soucis de simplicité.

- [Démo PHP Data Object (PDO)](#démo-php-data-object-pdo)
  - [Installation](#installation)
  - [Ouvrir une connexion (avec SQLite)](#ouvrir-une-connexion-avec-sqlite)
  - [Classes de l'extension PDO](#classes-de-lextension-pdo)
  - [Executer des requêtes SQL](#executer-des-requêtes-sql)
  - [Executer des requêtes préparées (en deux temps)](#executer-des-requêtes-préparées-en-deux-temps)
  - [Utiliser les transactions](#utiliser-les-transactions)
  - [Accéder à la démo](#accéder-à-la-démo)
  - [En résumé](#en-résumé)
  - [Liens utiles](#liens-utiles)


## Installation

1. [Installer PHP 8+](https://www.php.net/downloads);
2. Vérifier que le module `PDO` et le pilote `PDOSQlite` sont installés avec la commande suivante :

~~~sh
php -m | grep -E "pdo|PDO"
~~~

Vous devriez obtenir un résultat similaire à celui-ci :

~~~bash
PDO
pdo_mysql
pdo_sqlite
~~~

> Le module `PDO` et le pilote `PDOSQLite` sont installés et activés par défaut.

## Ouvrir une connexion (avec SQLite)

Pour ouvrir une connexion à une base de données, vous devez instancier un objet de type `PDO` et lui fournir une [**Data Source Name (DSN)**](https://www.php.net/manual/fr/pdo.construct.php). [Pour SQLite](https://www.php.net/manual/fr/ref.pdo-sqlite.connection.php), elle est de la forme :

~~~bash
sqlite:/path/to/database.sq3
~~~

> SQLite ne dispose pas d'instruction `CREATE DATABASE`. En effet, en SQLite, un fichier est égal à une base de données.

## Classes de l'extension PDO

L'extension `pdo` définit les classes suivantes **à connaître** :

- `PDO` : Représente une connexion entre PHP et un serveur de base de données;
- `PDOStatement` : Représente une requête préparée et, une fois exécutée, le jeu de résultats associé. Retourné par [`PDO::query()`](https://www.php.net/manual/fr/pdo.query.php)
- `PDOException` : Représente une erreur émise par PDO. **Vous ne devez pas lancer une exception PDOException depuis votre propre code**, seulement les gérer.

## Executer des requêtes SQL

`PDO::exec()` exécute une requête SQL dans un appel d'une seule fonction, retourne le nombre de lignes affectées par la requête. **Ne retourne pas de résultat** pour une requête `SELECT`. Pour cela, utiliser `PDO::query()`.

~~~php
$result = $pdo->exec("CREATE TABLE IF NOT EXISTS Article(id INT, title VARCHAR(255), body TEXT)");
~~~

`PDO::query()` prépare et execute une requête SQL **en un seul appel** de fonction et retourne un objet de type `PDOStatement`. Cet objet contient les méthodes nécessaires pour consulter la requête initiale, les résultats, les erreurs, etc.


## Executer des requêtes préparées (en deux temps)


`PDO::prepare()`


## Utiliser les transactions


## Accéder à la démo

Lancer la démo :

~~~sh
php index.php
~~~

Inspecter [le code source commenté](./index.php), le modifier et le tester.

## En résumé

Un objet PDO représente une connexion à une base de données via l'interface `PDO`, implémentée pour chaque SGBD majeur (aussi appelé *pilote*). Il faut lui fournir une DSN pour établir la connexion.

Une fois la connexion établie, on peut exécuter des requêtes directement depuis l'objet `PDO` avec les méthodes `PDO::exec()` et `PDO::query()`. `exec()` est plus limitée, elle ne permet que d'accéder au nombre de lignes altérées par la requête. Pour une requête de projection (`SELECT`), utiliser `query()`.

On peut également préparer des requêtes *sans* les executer (pour les paramétrer et les réutiliser, [voir les avantages des requêtes préparées](https://www.php.net/manual/fr/pdo.prepared-statements.php)) avec la méthode `PDO::prepare()`. La requête préparée est retournée sous forme d'objet de type `PDOStatement`. Pour l’exécuter, on utilise la méthode `PDOStatement::execute()`.

Pour consulter les résultats, l'objet de type `PDOStatement` offre plusieurs **méthodes de récupération** (`fetch()`, `fetchAll()` et `fetchObject()`) et plusieurs [**modes de récupération**](https://www.php.net/manual/fr/pdo.constants.php) (`PDO::FETCH_BOTH` (par défaut), `PDO::FETCH_ASSOC`, etc.) 


[Les méthodes de `PDO`](https://www.php.net/manual/fr/class.pdo.php) **à connaître** :

- `exec()`
- `query()`, retourne un `PDOStatement`
- `prepare()`, retourne un `PDOStatement`
- `commit()`
- `rollback()`

[Les méthodes de `PDOStatement`](https://www.php.net/manual/fr/class.pdostatement.php) **à connaître** :

- bindValue()
- execute()
- fetch()
- fetchAll()
- fetchObject()

[Les modes de récupération (constantes)](https://www.php.net/manual/fr/pdo.constants.php) **à connaître** :

- PDO::FETCH_BOTH
- PDO::FETCH_ASSOC

## Liens utiles


- [PHP Data Objects](https://www.php.net/manual/fr/book.pdo.php)
- [Constantes pré-définies](https://www.php.net/manual/fr/pdo.constants.php)
- [Connexions et gestionnaire de connexion](https://www.php.net/manual/fr/pdo.connections.php), documentation sur la gestion des connexions notamment des [connexions persistantes](https://www.php.net/manual/fr/pdo.constants.php#pdo.constants.attr-persistent)
- [SQLite - Documentation](https://www.sqlite.org/docs.html)