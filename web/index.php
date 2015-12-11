<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require('../vendor/autoload.php');
//require 'db.php';




function getConnection() {
  $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
  $dbhost=$url["host"];
  $dbuser=$url["user"];
  $dbpass=$url["pass"];
  $dbname=substr($url["path"], 1);

  $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
  //$dbh->exec("set names utf8");
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $dbh;
}

$app = new \Slim\App;

//category
$app->get('/categories', function ($request, $response, $args) {
  $sql = "select c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c";
  try {
    $db = getConnection();
    $stmt = $db->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($categories);
    exit;
  } catch(Exception $e) {
    $app = \Slim\Slim::getInstance();
    $app->log->error('getCategories-'.$e->getMessage());
    echo json_encode($categories);
    exit;
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

$app->run();
