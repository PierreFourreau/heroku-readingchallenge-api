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
$app->get('/categories', 'getCategories');
$app->get('/categoriesByLevel/:level', 'getCategoriesByLevel');
//$app->get('/categories/:id',	'getCategorie');
$app->get('/categories/{id}', function ($request, $response, $args) {
  $sql = "SELECT c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c WHERE c.id=:id";
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $args['id']);
    $stmt->execute();
    $categorie = $stmt->fetchObject();
    $db = null;
    echo json_encode($categorie);
    exit;
  } catch(Exception $e) {
    $app = \Slim\Slim::getInstance();
    $app->log->error('getCategorie-'.$e->getMessage());
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
});

$app->get('/categories/search/:query', 'findBylabel');
$app->post('/categories', 'addCategorie');
$app->put('/categories/:id', 'updateCategorie');
$app->delete('/categories/:id',	'deleteCategorie');

//suggestion
$app->get('/suggestionsByCategory/:id', 'getSuggestionsByCategoryId');
$app->get('/suggestions/:id',	'getSuggestionById');
$app->get('/suggestions/search/:query', 'findSuggestionBylabel');
$app->post('/suggestions', 'addSuggestion');
$app->put('/suggestions/:id', 'updateSuggestion');
$app->delete('/suggestions/:id',	'deleteSuggestion');

//proposition
$app->post('/propositions', 'addProposition');

$app->run();


/**********************************************************************/
/*********				category							  *********/
/**********************************************************************/
function getCategories() {
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
function getCategoriesByLevel($level) {
	$sql = "SELECT c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c where c.niveau<=:level";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("level", $level);
		$stmt->execute();
		$categories = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($categories);
    exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('getCategoriesByLevel-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getCategorie($id) {
	$sql = "SELECT c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c WHERE c.id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$categorie = $stmt->fetchObject();
		$db = null;
		echo json_encode($categorie);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('getCategorie-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function addCategorie() {
	$request = Slim::getInstance()->request();
	$categorie = json_decode($request->getBody());
	$sql = "INSERT INTO categories(libelle_en, libelle_fr, description_en, description_fr, image) VALUES (:libelle_en, :libelle_fr, :description_en, :description_fr, :image)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("libelle_en", $categorie->libelle_en);
		$stmt->bindParam("libelle_fr", $categorie->libelle_fr);
		$stmt->bindParam("description_en", $categorie->description_en);
		$stmt->bindParam("description_fr", $categorie->description_fr);
		$stmt->bindParam("image", $categorie->image);
		$stmt->execute();
		$categorie->id = $db->lastInsertId();
		$db = null;
		echo json_encode($categorie);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('addCategorie-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function updateCategorie($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$categorie = json_decode($body);
	$sql = "UPDATE categories SET libelle_en=:libelle_en, libelle_fr=:libelle_fr, description_en=:description_en, description_fr=:description_fr WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("libelle_en", $categorie->libelle_en);
		$stmt->bindParam("libelle_fr", $categorie->libelle_fr);
		$stmt->bindParam("description_en", $categorie->description_en);
		$stmt->bindParam("description_fr", $categorie->description_fr);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode($categorie);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('updateCategorie-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function deleteCategorie($id) {
	$sql = "DELETE FROM categories WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('deleteCategorie-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function findBylabel($query) {
	$sql = "SELECT c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM c categories WHERE UPPER(c.libelle_en) LIKE :query OR UPPER(c.libelle_fr) LIKE :query ORDER BY c.libelle_en,c.libelle_fr";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$categories = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($categories);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('findBylabel-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

/**********************************************************************/
/*********				suggestion							  *********/
/**********************************************************************/
function getSuggestionById($id) {
	$sql = "SELECT s.id, s.libelle_fr, s.libelle_en, s.categorie_id FROM suggestions s WHERE s.id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$suggestion = $stmt->fetchObject();
		$db = null;
		echo json_encode($suggestion);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('getSuggestionById-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getSuggestionsByCategoryId($id) {
	$sql = "SELECT s.id, s.libelle_fr, s.libelle_en, s.categorie_id FROM suggestions s WHERE s.categorie_id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$suggestions = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($suggestions);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('getSuggestionsByCategoryId-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function addSuggestion() {
	$request = \Slim\Slim::getInstance()->request();
	$suggestion = json_decode($request->getBody());
	$sql = "INSERT INTO suggestions(libelle_en, libelle_fr, categorie_id) VALUES (:libelle_en, :libelle_fr, :id)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("libelle_en", $suggestion->libelle_en);
		$stmt->bindParam("libelle_fr", $suggestion->libelle_fr);
		$stmt->bindParam("id", $suggestion->categorie_id);
		$stmt->execute();
		$suggestion->id = $db->lastInsertId();
		$db = null;
		echo json_encode($suggestion);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('addSuggestion-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function updateSuggestion($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$suggestion = json_decode($body);
	$sql = "UPDATE suggestions SET libelle_en=:libelle_en and libelle_fr=:libelle_fr WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("libelle_en", $suggestion->libelle_en);
		$stmt->bindParam("libelle_fr", $suggestion->libelle_fr);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode($suggestion);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('updateSuggestion-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function deleteSuggestion($id) {
	$sql = "DELETE FROM suggestions WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('deleteSuggestion-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function findSuggestionBylabel($query) {
	$sql = "SELECT s.id, s.libelle_fr, s.libelle_en, s.categorie_id FROM suggestions s WHERE UPPER(s.libelle_en) LIKE :query or UPPER(s.libelle_fr) LIKE :query ORDER BY s.libelle_en, s.libelle_fr";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$suggestions = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($suggestions);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('findSuggestionBylabel-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


/**********************************************************************/
/*********				propositions							  *********/
/**********************************************************************/
function addProposition() {
	$request = \Slim\Slim::getInstance()->request();
	$proposition = json_decode($request->getBody());
	$sql = "INSERT INTO propositions(libelle_en, libelle_fr, categorie_id, created, modified) VALUES (:libelle_en, :libelle_fr, :id, :dateNow, :dateNow)";
	parse_str($request->getBody(), $params);
	$dateNow = date("Y-m-d H:i:s");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("libelle_en", $params['libelle_en']);
		$stmt->bindParam("libelle_fr", $params['libelle_fr']);
		$stmt->bindParam("id", $params['categorie_id']);
		$stmt->bindParam("dateNow", $dateNow);
		$stmt->execute();
		$id = $db->lastInsertId();
		$db = null;
		echo json_encode($id);
		//send email

		$headers = "From: ReadingChallenge\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$email = 'readingchallenge.contact@gmail.com';
		$subject = 'Readingchallenge - ajout proposition';
		$message = '<html><body>';
		$message .= 'Nouvelle proposition ajoutée<br/><br/>';
		$message .= 'Libelle fr : ' . $params['libelle_fr'].'<br/>';
		$message .= 'Libelle en : ' . $params['libelle_en'];
		$message .= '<br/><br/><a href="http://pierrefourreau.fr/readingchallenge/readingchallenge-admin/propositions">Admin</a>';
		$message .= '</body></html>';
		mail($email, $subject, $message, $headers);
		exit;
	} catch(Exception $e) {
		$app = \Slim\Slim::getInstance();
		$app->log->error('addProposition-'.$e->getMessage());
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}
