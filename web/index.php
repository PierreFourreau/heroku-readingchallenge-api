<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require('../vendor/autoload.php');
//require 'db.php';


$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
print_r($url);
