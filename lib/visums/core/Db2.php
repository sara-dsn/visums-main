<?php
// On autorise que 1 type par fonction ( INT, VARCHAR, FLOAT, ...)
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

    // Constructeur qui initialise la connexion à la base de données.
    public function __construct() {
        // try {
        // Récupère le DSN depuis config
        $dsn = Config::getValue('DSN');

        // Si le DSN est null, on lui affecte le tableau de connexion
        if (is_null($dsn)) {
            $dbconnect = [
                'dbtype' => '', 'dbuser' => '', 'dbpassword' => '',
                'dbhost' => '', 'dbport' => '', 'db' => '',
            ];

            // et on le remplit le tableau 
            foreach ($dbconnect as $k => $v) {
                $dbconnect[$k] = Config::getValue($k);
            }

            // Filtre les éléments vides du tableau.
            $dbconnect = array_filter($dbconnect);

            // Vérif qu'il y a tout les parametres necessaires
            if (count($dbconnect) < 6) {
                // TODO : Incomplete Configuration
                // sinon alerte
                throw new \Exception('Incomplete Db Configuration');
            }

            // imposer expression reguliere dsn
            $dsn = sprintf(
                '%s://%s:%p@%s:%s/%s',
                $dbconnect['dbtype'], $dbconnect['dbuser'], $dbconnect['dbpassword'],
                $dbconnect['dbhost'], $dbconnect['dbport'], $dbconnect['db']
              );
        }

        // on creer la connection
        $this->connexion = new PDO($dsn, Config::getValue('dbuser'), Config::getValue('dbpassword'));

        // $this->connexion = new PDO($dsn, Config::getValue('dbuser'), Config::getValue('dbpassword'), [
        //     // si erreur SQL alors alerte
        //     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        //     // on recup les données sous forme de tableau
        //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        //     // la connection reste ouverte, même après fermeture php
        //     PDO::ATTR_PERSISTENT => true 
        // ]);

        // }
        // catch (PDOException $e) {
        //     // Gère les erreurs de connexion en levant une exception avec un message descriptif.
        //     throw new \Exception('Connection failed: ' . $e->getMessage());
        // }
    }

 


    public function query(string $sql, array $data=[]){

        // recuperation avec requete dans Base de donnée
        $this->statement = $this->connexion->prepare($sql);

        if($this->statement === FALSE){
          // TODO : Manage error
        //  si erreur alors alerte
          throw new \Exception(sprintf('Query Prepare Error : %s', $sql));
        }
        // associe la demande aux bonnes données
        $this->statement->execute($data);
      }
    
      public function fetchAll(){

        // pour on recupe toute les donnees sous forme de tableau
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
      }
    
      public function fetch(){
        //pour on recupe 1 donnee
       return $this->statement->fetch(PDO::FETCH_ASSOC);
      }

//    // Méthode pour exécuter une requête SQL avec des paramètres.
//     public function query(string $sql, array $data = []) {
//         try {
//             // Prépare la requête SQL en utilisant la connexion PDO.
//             $this->statement = $this->connexion->prepare($sql);
            
//             // Vérifie si la préparation a échoué et lance une exception.
//             if ($this->statement === FALSE) {
//                 throw new \Exception('Query Prepare Error: ' . $sql);
//             }

//             // Exécute la requête préparée avec les données fournies.
//             $this->statement->execute($data);

//         }
//         catch (PDOException $e) {
//             // Gère les erreurs d'exécution de la requête SQL en lançant une exception.
//             throw new \Exception('Query Execution Error: ' . $e->getMessage());
//         }
//     }

    // // Méthode pour récupérer tous les résultats de la requête sous forme de tableau associatif.
    // public function fetchAll() {
    //     try {
    //         // Récupère tous les résultats de la requête.
    //         return $this->statement->fetchAll();
    //     }
    //     catch (PDOException $e) {
    //         // Gère les erreurs de récupération de données en lançant une exception.
    //         throw new \Exception('Fetch All Error: ' . $e->getMessage());
    //     }
    // }

    // // Méthode pour récupérer un seul enregistrement de la requête.
    // public function fetch() {
    //     try {
    //         // Récupère un seul résultat de la requête.
    //         return $this->statement->fetch();
    //     }
    //     catch (PDOException $e) {
    //         // Gère les erreurs de récupération d'un seul enregistrement.
    //         throw new \Exception('Fetch Error: ' . $e->getMessage());
    //     }
    // }
}
