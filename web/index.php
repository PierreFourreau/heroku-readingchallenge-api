<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require('../vendor/autoload.php');
//require 'db.php';


$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
echo $url;
exit();
const DB_SERVER = $url["host"];
const DB_USER = $url["user"];
const DB_PASSWORD = $url["pass"];
const DB = substr($url["path"], 1);

function getConnection() {
	$dbhost=DB_SERVER;
	$dbuser=DB_USER;
	$dbpass=DB_PASSWORD;
	$dbname=DB;

echo $dbhost;
echo $dbuser;
echo $dbpass;
echo $dbname;

	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	//$dbh->exec("set names utf8");
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

$app = new \Slim\App;
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello boy, $name");

    return $response;
});

$app->get('/categories', 'getCategories');

$app->run();


function getCategories() {
	$sql = "select c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c";
echo $sql;
  try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$categories = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($categories);
		exit;
	} catch(Exception $e) {
		//$app = \Slim\Slim::getInstance();
		//$app->log->error('getCategories-'.$e->getMessage());
		echo json_encode($categories);
		exit;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}
