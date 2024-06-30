<?php

/**
 * Une démonstration de PHP Data Object (PDO) avec une base
 * de données SQLite
 */


//On ouvre une connexion à une base de données SQLite en DRAM
$pdo = new PDO("sqlite::memory:");

echo "* exec()" . PHP_EOL;

//Effectuer une requête SQL avec exec
//PDO::exec() exécute une requête SQL dans un appel d'une seule fonction, retourne le nombre de lignes affectées par la requête. 
//PDO::exec() ne retourne pas de résultat pour une requête SELECT. Pour cela, utiliser query()
$result = $pdo->exec("CREATE TABLE IF NOT EXISTS Article(id INT, title VARCHAR(255), body TEXT)");
var_dump($result);


//PDO::query() prépare et execute une requête SQL en un seul appel de fonction et retourne un PDOStatement
//Lister toutes les tables de la base de données
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");

echo "* query()" . PHP_EOL;

var_dump($stmt);
var_dump($stmt->columnCount());
//Récupère la ligne de résultat suivante (le nom de la table). Mode FETCH_BOTH par défaut
var_dump($stmt->fetch());
//Il n'y a plus aucun autre résultat dans le set (le curseur a avancé)
var_dump($stmt->fetch());
var_dump($stmt->fetch());

//Insertion d'un jeu de données dans la table Article

$sql = <<< SQL
INSERT INTO Article(id, title, body) 
VALUES (1, 'Foo', 'Lorem ipsum'), 
(2, 'Bar', 'Lorem ipsum'), 
(3, 'Baz', 'Lorem ipsum'),
(4, 'Foo', 'Lorem ipsum')
SQL;

$result = $pdo->exec($sql);
var_dump($result);

echo "*fetch()" . PHP_EOL;

//FETCH_BOTH retourne la ligne dans un tableau indexé par les noms des colonnes ainsi que leurs numéros, comme elles sont retournées dans le jeu de résultats correspondant, en commençant à 0
$stmt = $pdo->query("SELECT id, title, body FROM Article");
var_dump($stmt->fetch());

//Remarquer l'ordre dans lequel fetch retourne les résultats et les indices
$stmt = $pdo->query("SELECT title, body, id FROM Article");
var_dump($stmt->fetch());

//Remarquer les indices du tableau de résultats avec le mode FETCH_ASSOC
$stmt = $pdo->query("SELECT title, body, id FROM Article", PDO::FETCH_ASSOC);
var_dump($stmt->fetch());

echo "*fetchAll(PDO::FETCH_ASSOC)" . PHP_EOL;

$stmt = $pdo->query("SELECT title, body, id FROM Article", PDO::FETCH_ASSOC);
foreach ($stmt as $row) {
    print $row['title'] . "\t";
    print  $row['body'] . "\t";
    print $row['id'] . "\n";
}


echo "*fetchAll(PDO::FETCH_OBJ)" . PHP_EOL;

//A chaque fois que fetch ou fetchAll est appelé, l'itérateur sur l'ensemble des résultats avance.
//Aussi, il n'est pas possible d'accéder deux fois de suite aux résultats via fetch ou fetchAll.
//Pour reparcourir les résultats si besoin, il faut refaire la requête.
$stmt->execute();

//PDO::FETCH_OBJ retourne chaque ligne sous forme d'objet de type stdClass (objet anonyme).
foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
    print_r($row);
}

echo "*fetchAll(PDO::FETCH_UNIQUE)" . PHP_EOL;

//PDO::FETCH_UNIQUE récupère uniquement les valeurs uniques. L'unicité est déterminée par la valeur
//de la PREMIERE COLONNE
$stmt = $pdo->query("SELECT title, id, body FROM Article");
foreach ($stmt->fetchAll(PDO::FETCH_UNIQUE) as $row) {
    print_r($row);
}

echo "*fetchAll(PDO::FETCH_CLASS)" . PHP_EOL;

//Mode PDO::FETCH_CLASS pour instancier des objets directement à partir des données tabulaires (ORM !)

class Article
{
    private int $id;
    private string $title;
    private string $body;

    public function __construct()
    {
        if (isset($this->id)) {
            echo "Objet instancié avec l'id=$this->id" . PHP_EOL;
        } else {
            echo "L'objet est instancié mais n'a pas encore d'id" . PHP_EOL;
        }
    }
}


$stmt = $pdo->query("SELECT id, title, body FROM Article");
//Remarque : FETCH_CLASS retourne une nouvelle instance de la classe demandée, liant les colonnes du jeu de résultats aux noms des propriétés de la classe et en appelant le constructeur par la suite. 
$articles = $stmt->fetchAll(PDO::FETCH_CLASS, 'Article');
var_dump($articles);


//On peut modifier cet ordre et appeler le constructeur AVANT que les propriétés ne soient initialisées avec le flag modificateur PDO::FETCH_PROPS_LATE

echo "*fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE)" . PHP_EOL;


$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Article');
var_dump($articles);

echo "*fetchAll(PDO::FETCH_FUNC)" . PHP_EOL;

class Article2
{

    public function __construct(
        readonly public int $id,
        readonly public string $title,
        readonly public string $body,
    ) {
    }
}

$stmt->execute();
//PDO::FETCH_FUNC permet d’exécuter n'importe quelle fonction de mapping
$articles = $stmt->fetchAll(PDO::FETCH_FUNC, function ($id, $title, $body) {
    return new  Article2($id, $title, $body);
});
var_dump($articles);


echo "*Requêtes préparées" . PHP_EOL;

//Avec paramètre nommé (:title)
$stmt = $pdo->prepare('SELECT id, title, body FROM Article WHERE title = :title AND id = :id');
//Bind en passant les valeurs des paramètres
$stmt->execute(['title' => 'Foo', 'id' => 1]);
var_dump($stmt->fetchAll());


echo "*Requêtes préparées : bindValue()" . PHP_EOL;

//Alternative : bind avant execute (en utilisant la position ici)
$stmt = $pdo->prepare('SELECT id, title, body FROM Article WHERE title = ? AND id = ?');
$stmt->bindValue(1, 'Foo');
$stmt->bindValue(2, 1);
$stmt->execute();
var_dump($stmt->fetchAll());

//Alternative : bind avant execute (en utilisant les paramètres nommés ici)
$stmt = $pdo->prepare('SELECT id, title, body FROM Article WHERE title = :title AND id = :id');
$stmt->bindValue('title', 'Foo');
$stmt->bindValue('id', 1);
$stmt->execute();
var_dump($stmt->fetchAll());


echo "*Transactions" . PHP_EOL;

//Commit (validé les requêtes dans une transaction : tout ou rien)
$pdo->beginTransaction();
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (5, 'Baz', 'Lorem ipsum')");
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (6, 'Baz', 'Lorem ipsum')");
$pdo->commit();


$stmt = $pdo->query("SELECT id, title, body FROM Article");
$results = $stmt->fetchAll();
var_dump($results);

//Rollback (annuler toutes les requêtes de la transaction)
$pdo->beginTransaction();
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (7, 'Baz', 'Lorem ipsum')");
$pdo->exec("INSERT INTO Article(id, title, body) VALUES (8, 'Baz', 'Lorem ipsum')");
$pdo->rollBack();

$stmt->execute();
var_dump($stmt->fetchAll());


echo "*Exceptions" . PHP_EOL;

//À partir de PHP 8.0.0, PDO::ERRMODE_EXCEPTION est le mode par défaut : une exception PDOException est
//levée dès qu'une erreur SQL se produit. 
//Pour modifier le mode d'exception, modifier l'attribut PDO::ATTR_ERRMODE via la méthode suivante : $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_*);

try {
    $pdo->beginTransaction();
    //Cette requête ne contient pas d'erreur
    $pdo->exec("INSERT INTO Article(id, title, body) VALUES (12, 'Foo', 'Lorem ipsum')");
    //Cette requête contient une erreur
    $pdo->exec("INSERT INTO Article(id, title, body) VALUES ('Baz', 'Lorem ipsum')");
    $pdo->commit();
} catch (PDOException $exception) {
    echo "PDOException, message = {$exception->getMessage()}";
    //Annuler toutes les requêtes de la transaction si une seule d'entre elles déclenche une erreur
    $pdo->rollBack();
}

$stmt->execute();
var_dump($stmt->fetchAll());
