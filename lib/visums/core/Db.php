<?php
declare(strict_types=1);

namespace Visums\Core;

use Visums\Interfaces\IDb;
use Visums\Config;
use PDO;

class Db implements IDb{
  protected $connexion;
  protected $statement;

  public function __construct(){
    $dsn = Config::getValue('DSN');
    if(is_null($dsn)){
      $dbconnect = [
        'dbtype' => '', 'dbuser' => '', 'dbpassword' => '',
        'dbhost' => '', 'dbport' => '', 'db' => '',
      ];

      foreach($dbconnect as $k => $v)
        $dbconnect[$k] = Config::getValue($k);

      $dbconnect = array_filter($dbconnect);
      if(count($dbconnect) < 6){
        // TODO : Incomplete Configuration
        throw new \Exception('Incomplete Db Configuration');
      }
      $dsn = sprintf(
        '%s://%s:%p@%s:%s/%s',
        $dbconnect['dbtype'], $dbconnect['dbuser'], $dbconnect['dbpassword'],
        $dbconnect['dbhost'], $dbconnect['dbport'], $dbconnect['db']
      );
    }

    $this->connexion = new PDO($dsn, Config::getValue('dbuser'), Config::getValue('dbpassword'));

  }

  public function query(string $sql, array $data=[]){
    $this->statement = $this->connexion->prepare($sql);
    if($this->statement === FALSE){
      // TODO : Manage error
     
      throw new \Exception(sprintf('Query Prepare Error : %s', $sql));
    }
    $this->statement->execute($data);
  }

  public function fetchAll(){
    return $this->statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function fetch(){
   return $this->statement->fetch(PDO::FETCH_ASSOC);
  }

}
