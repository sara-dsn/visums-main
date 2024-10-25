<?php
// Active le mode strict pour imposer les types de paramètres et de retour dans les méthodes et fonctions.
// Par exemple, si une fonction attend un argument de type int, 
// passer une valeur de type float ou string provoquera une erreur de type. 
declare(strict_types=1);

namespace Visums\Core;

use Visums\Interfaces\IDb;
use Visums\Config;
use PDO;
use PDOException;

class Db implements IDb {
    // Propriétés pour stocker la connexion PDO et la requête préparée
    protected $connexion;
    protected $statement;

    // Constructeur de la classe Db qui initialise la connexion à la base de données.
    public function __construct() {
        try {
            // Récupère le Data Source Name (DSN) depuis la configuration.
            $dsn = Config::getValue('DSN');

            // Si le DSN est nul, il faut créer une configuration de connexion manuellement.
            if (is_null($dsn)) {
                // Tableau contenant les paramètres de connexion à la base de données.
                $dbconnect = [
                    'dbtype' => '', 'dbuser' => '', 'dbpassword' => '',
                    'dbhost' => '', 'dbport' => '', 'db' => '',
                ];

                // Remplit le tableau avec les valeurs de configuration pour chaque paramètre de connexion.
                foreach ($dbconnect as $k => $v) {
                    $dbconnect[$k] = Config::getValue($k);
                }

                // Filtre les éléments vides du tableau.
                $dbconnect = array_filter($dbconnect);

                // Vérifie que tous les paramètres nécessaires sont présents, sinon déclenche une exception.
                if (count($dbconnect) < 6) {
                    throw new \Exception('Incomplete Db Configuration');
                }

                // Formate le DSN pour PDO en fonction des informations de connexion disponibles.
                $dsn = sprintf(
                    '%s:host=%s;port=%d;dbname=%s',
                    $dbconnect['dbtype'], $dbconnect['dbhost'], $dbconnect['dbport'], $dbconnect['db']
                );
            }

            // Initialise la connexion PDO en utilisant les options de gestion des erreurs et le mode de récupération.
            $this->connexion = new PDO($dsn, Config::getValue('dbuser'), Config::getValue('dbpassword'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lève des exceptions pour chaque erreur SQL.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Utilise un tableau associatif pour le mode de fetch par défaut.
                PDO::ATTR_PERSISTENT => true // Active une connexion persistante pour améliorer la performance (facultatif).
            ]);

        } catch (PDOException $e) {
            // Gère les erreurs de connexion en levant une exception avec un message descriptif.
            throw new \Exception('Connection failed: ' . $e->getMessage());
        }
    }

    // Méthode pour exécuter une requête SQL avec des paramètres.
    public function query(string $sql, array $data = []) {
        try {
            // Prépare la requête SQL en utilisant la connexion PDO.
            $this->statement = $this->connexion->prepare($sql);
            
            // Vérifie si la préparation a échoué et lance une exception.
            if ($this->statement === FALSE) {
                throw new \Exception('Query Prepare Error: ' . $sql);
            }

            // Exécute la requête préparée avec les données fournies.
            $this->statement->execute($data);

        } catch (PDOException $e) {
            // Gère les erreurs d'exécution de la requête SQL en lançant une exception.
            throw new \Exception('Query Execution Error: ' . $e->getMessage());
        }
    }

    // Méthode pour récupérer tous les résultats de la requête sous forme de tableau associatif.
    public function fetchAll() {
        try {
            // Récupère tous les résultats de la requête.
            return $this->statement->fetchAll();
        } catch (PDOException $e) {
            // Gère les erreurs de récupération de données en lançant une exception.
            throw new \Exception('Fetch All Error: ' . $e->getMessage());
        }
    }

    // Méthode pour récupérer un seul enregistrement de la requête.
    public function fetch() {
        try {
            // Récupère un seul résultat de la requête.
            return $this->statement->fetch();
        } catch (PDOException $e) {
            // Gère les erreurs de récupération d'un seul enregistrement.
            throw new \Exception('Fetch Error: ' . $e->getMessage());
        }
    }
}
