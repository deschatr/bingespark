<?php

define("API_ROOT_PATH", __DIR__ );
require API_ROOT_PATH . "/include/bootstrap.php";
require API_ROOT_PATH . "/controllers/favouritecontroller.php";

$controller = new FavouriteController;
$controller->processRequest();

?>