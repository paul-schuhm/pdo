# Démo PHP Data Object (PDO)

Une démo de l'extension PDO en PHP pour interagir avec des bases de données relationnelles et une synthèse des choses à savoir pour utiliser l'extension. La démo utilise ici une base de données SQLite par soucis de simplicité.

- [Démo PHP Data Object (PDO)](#démo-php-data-object-pdo)
  - [Installation](#installation)
  - [Ouvrir une connexion (avec SQLite)](#ouvrir-une-connexion-avec-sqlite)
  - [Classes de l'extension PDO](#classes-de-lextension-pdo)
  - [Exécuter des requêtes SQL](#exécuter-des-requêtes-sql)
  - [Parcourir les résultats](#parcourir-les-résultats)
  - [Exécuter des requêtes préparées (en deux temps)](#exécuter-des-requêtes-préparées-en-deux-temps)
  - [Utiliser les transactions](#utiliser-les-transactions)
  - [Accéder à la démo](#accéder-à-la-démo)
  - [En résumé](#en-résumé)
  - [Références](#références)


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
- `PDOStatement` : Représente une requête préparée et, une fois exécutée, le jeu de résultats associé. Retourné par [`PDO::query()`](https://www.php.net/manual/fr/pdo.query.php) et [`PDO::prepare()`](https://www.php.net/manual/fr/pdo.prepare.php);
- `PDOException` : Représente une erreur émise par PDO. **Vous ne devez pas lancer une exception PDOException depuis votre propre code**, seulement les gérer.

## Exécuter des requêtes SQL

`PDO::exec()` exécute une requête SQL dans un appel d'une seule fonction, **retourne le nombre de lignes affectées** par la requête. **Ne retourne pas de résultats** pour une requête `SELECT`. Pour cela, il faut utiliser `PDO::query()`.

~~~php
$result = $pdo->exec("CREATE TABLE IF NOT EXISTS Article(id INT, title VARCHAR(255), body TEXT)");
$sql = <<< SQL
INSERT INTO Article(id, title, body) 
VALUES (1, 'Foo', 'Lorem ipsum'), 
(2, 'Bar', 'Lorem ipsum'), 
(3, 'Baz', 'Lorem ipsum'),
(4, 'Foo', 'Lorem ipsum')
SQL;

$result = $pdo->exec($sql);
~~~

`PDO::query()` **prépare et execute** une requête SQL **en un seul appel** de fonction et retourne un objet de type `PDOStatement`. Cet objet contient les méthodes nécessaires pour consulter la requête initiale, les résultats, les erreurs, etc.

~~~php
$stmt = $pdo->query("SELECT id, title, body FROM Article");
~~~

## Parcourir les résultats

Une fois la requête exécutée, on peut parcourir les résultats à l'aide des méthodes `PDOStatement::fetch()` ou `PDOStatement::fetchAll()` :

~~~php
$firstRow = $stmt->fetch();
$remainingRows = $stmt->fetchAll();
~~~

Ou alors en itérant sur l'objet `PDOStatement` directement (car il implémente l'interface `IteratorAggregate`) :

~~~php
foreach ($stmt as $row) {
    echo $row['title'] . "\t";
    echo $row['body'] . "\t";
    echo $row['id'] . PHP_EOL;
}
~~~

## Exécuter des requêtes préparées (en deux temps)


`PDO::prepare()` permet de préparer une requête *paramétrée* :

~~~php
$stmt = $pdo->prepare('SELECT id, title, body FROM Article WHERE title = :title AND id = :id');
$stmt->execute(['title' => 'Foo', 'id' => 1]);
~~~

Ou avec `bindValue()` :

~~~php
$stmt = $pdo->prepare('SELECT id, title, body FROM Article WHERE title = :title AND id = :id');
$stmt->bindValue('title', 'Foo');
$stmt->bindValue('id', 1);
$stmt->execute();
~~~

> Le paramètre peut être nommé ou interrogatif (?). Voir la documentation


## Utiliser les transactions

Par défaut, PDO s’exécute en mode `autocommit`, chaque requête est implicitement une transaction. Pour initialiser une transaction, il faut utiliser la méthode `PDO::beginTransaction()`. Ensuite effectuer les requêtes contenues dans la transaction puis la terminer avec la méthode `PDO::commit()` ou l'annuler avec la méthode `PDO::rollback()`

~~~php
$pdo->beginTransaction();
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (5, 'Baz', 'Lorem ipsum')");
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (6, 'Baz', 'Lorem ipsum')");
$pdo->commit();
~~~

> [En savoir plus sur les transactions](https://www.php.net/manual/fr/pdo.transactions.php).

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

- `exec()`;
- `query()` : **Prépare** et **exécute** une requête SQL. Retourne un `PDOStatement` contenant les résultats;
- `prepare()` : **Prépare** une requête SQL. Retourne un `PDOStatement`;
- `beginTransaction()` : Ouvre une transaction;
- `commit()` : Valide la transaction;
- `rollback()` : Annule la transaction.

[Les méthodes de `PDOStatement`](https://www.php.net/manual/fr/class.pdostatement.php) **à connaître** :

- `bindValue()` : Associe une *valeur* à un paramètre (nommé ou interrogatif(`?`));
- `bindParam()` : Lie une variable PHP (référence) à un marqueur nommé ou interrogatif. Contrairement à `bindValue()`, la variable est liée en tant que référence et ne sera évaluée qu'au moment de l'appel à la fonction `execute()`.  (utile pour les procédures stockées qui retourne un résultat `INOUT`);
- `bindColumn()` : Lie une variable PHP (référence) à une colonne (par nom ou position). Chaque appel à `fetch` met à jour la variable;
- `execute()` : Exécute une requête préparée;
- `fetch()` : Récupère la ligne *suivante* d'un jeu de résultats PDO;
- `fetchAll()` : Récupère les lignes *restantes* d'un ensemble de résultats.

[Les modes de récupération (constantes)](https://www.php.net/manual/fr/pdo.constants.php) **à connaître** :

- `PDO::FETCH_BOTH` (défaut);
- `PDO::FETCH_ASSOC`;
- `PDO::FETCH_UNIQUE`;
- `PDO::FETCH_CLASS` (ORM);
- `PDO::FETCH_FUNC` (mapping des résultats via une callback).

## Références

- [PHP Data Objects](https://www.php.net/manual/fr/book.pdo.php), documentation officielle du module PDO;
- [Constantes pré-définies par le module PDO](https://www.php.net/manual/fr/pdo.constants.php), documente notamment les différents modes de récupération des données (`FETCH_*`);
- [PDOStatement::fetch](https://www.php.net/manual/en/pdostatement.fetch.php#example-1053), documentation des différents modes de récupération des données;
- [Connexions et gestionnaire de connexion](https://www.php.net/manual/fr/pdo.connections.php), documentation sur la gestion des connexions notamment des [connexions persistantes](https://www.php.net/manual/fr/pdo.constants.php#pdo.constants.attr-persistent);
- [PDO : Les erreurs et leur gestion](https://www.php.net/manual/fr/pdo.error-handling.php);
- [(The only proper) PDO tutorial](https://phpdelusions.net/pdo), un très bon site (maintenu) qui propose des tutoriels pour mieux comprendre le module PDO (la documentation n'est en effet pas toujours complète et explicite sur les différents paramètres du module);
- [SQLite - Documentation](https://www.sqlite.org/docs.html), documentation officielle de SQLite.