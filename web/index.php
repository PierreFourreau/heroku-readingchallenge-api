<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require('../vendor/autoload.php');
//require 'db.php';


$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

print_r($url);
const DB_SERVER = $url["host"];
const DB_USER = $url["user"];
const DB_PASSWORD = $url["pass"];
print_r($url);
//const DB = substr($url["path"], 1);





$app = new \Slim\App;
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello boy, $name");

    return $response;
});

//$app->get('/categories', 'getCategories');

$app->run();


/*function getCategories() {
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
}*/
