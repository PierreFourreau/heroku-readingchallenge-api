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

$app->get('/categories', 'getCategories');
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
    file_put_contents("php://stderr", "error getCategories : " . $e->getMessage() . "\n");
		exit;
	}
}

//category
/*$app->get('/categories', function ($request, $response, $args) {
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
}*/

$app->get('/categoriesByLevel/{level}', function ($request, $response, $args) {
  $sql = "SELECT c.id, c.libelle_fr, c.libelle_en, c.description_fr, c.description_en, c.image FROM categories c where c.niveau<=:level";
  try {
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("level", $args['level']);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($categories);
    exit;
  } catch(Exception $e) {
    file_put_contents("php://stderr", "error categoriesByLevel : " . $e->getMessage() . "\n");
  }
});

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
file_put_contents("php://stderr", "error categories : " . $e->getMessage() . "\n");
  }
});

/*$app->post('/categories', function ($request, $response, $args) {
  //$request = Slim::getInstance()->request();
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
}*/

$app->get('/suggestionsByCategory/{id}', function ($request, $response, $args) {
  $sql = "SELECT s.id, s.libelle_fr, s.libelle_en, s.categorie_id FROM suggestions s WHERE s.categorie_id=:id";
  	try {
  		$db = getConnection();
  		$stmt = $db->prepare($sql);
  		$stmt->bindParam("id", $args['id']);
  		$stmt->execute();
  		$suggestions = $stmt->fetchAll(PDO::FETCH_OBJ);
  		$db = null;
  		echo json_encode($suggestions);
  		exit;
  	} catch(Exception $e) {
file_put_contents("php://stderr", "error suggestionsByCategory : " . $e->getMessage() . "\n");
  	}
});

$app->post('/propositions', function ($request, $response, $args) {
  //$request = \Slim\Slim::getInstance()->request();
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


  /*  $mail = new PHPMailer(); // create a new object
$mail->IsSMTP(); // enable SMTP
$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username = "readingchallenge.contact@gmail.com";
$mail->Password = "76hdfrb8";
$mail->SetFrom("readingchallenge.contact@gmail.com");
$mail->Subject = "Readingchallenge - ajout proposition";
$message = '<html><body>';
$message .= 'Nouvelle proposition ajoutée<br/><br/>';
$message .= 'Libelle fr : ' . $params['libelle_fr'].'<br/>';
$message .= 'Libelle en : ' . $params['libelle_en'];
$message .= "<br/><br/><a href='http://pierrefourreau.fr/readingchallenge/readingchallenge-admin/propositions'>Admin</a>";
$message .= '</body></html>';
$mail->Body = $message;
$mail->AddAddress("readingchallenge.contact@gmail.com");

$mail->Send();*/

$mail = new PHPMailer(); // create a new object
$mail->IsSMTP(); // enable SMTP
$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username = "readingchallenge.contact@gmail.com";
$mail->Password = "76hdfrb8";
$mail->SetFrom("example@gmail.com");
$mail->Subject = "Test";
$mail->Body = "hello";
$mail->AddAddress("readingchallenge.contact@gmail.com");

$mail->Send();


    exit;
  } catch(Exception $e) {
file_put_contents("php://stderr", "error add propositions : " . $e->getMessage() . "\n");
  }
});

$app->run();
