<?php
// Conecta con las bases de datos
$world = new BaseDatos($conf["world"]);
$htwow = new BaseDatos($conf["htwow"]);

// Carga los textos de la aplicación
$locale = array();
// Locale por defecto esES
$textos = $htwow->query("SELECT * FROM `texto_locale` WHERE locale = 'esES'");
while($txt = $htwow->fetchArray($textos)) {
    $locale[$txt["idTexto"]] = $txt["texto"];
}
// Locale indicada en $conf
$textos = $htwow->query("SELECT * FROM `texto_locale` WHERE locale = '" . $conf["locale"] . "'");
while($txt = $htwow->fetchArray($textos)) {
    $locale[$txt["idTexto"]] = $txt["texto"];
}
