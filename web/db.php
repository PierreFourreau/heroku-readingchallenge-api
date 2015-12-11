<?php

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
echo $url;
die();
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

?>
