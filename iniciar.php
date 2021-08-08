<?php
// Conecta con las bases de datos
$world = new BaseDatos($conf["world"]);
$htwow = new BaseDatos($conf["htwow"]);

// Carga los textos de la aplicación
$locale = array();
include "locale/esES.php";
include_once "locale/" . $conf["locale"] . ".php"; 
