<?php
$conf = array();
// Idioma usado para los textos en la aplicación.
// Permitidos: "esES", "enUS", "esMX".
// Language used for the texts in the application.
// Allowed: "esES", "enUS", "esMX".
$conf["locale"] = "esES";
// Idioma al que se traduce.
// Permitidos: cualquier código de 4 letras como "esES", "esMX", "frFR", "ruRU", "deDE", "koKR", "zhCN", "zhTW".
// Language to which it is translated.
// Allowed: any 4 letter code like "esES", "esMX", "frFR", "ruRU", "deDE", "koKR", "zhCN", "zhTW".
$conf["locale_target"] = "esES";
// Idioma al que se traduce 2. (Opcional)
// Permitidos: "esES", "esMX", "frFR", "ruRU", "deDE", "koKR", "zhCN", "zhTW", "".
// Language to which it is translated. (Optional)
// Allowed: "esES", "esMX", "frFR", "ruRU", "deDE", "koKR", "zhCN", "zhTW", "".
$conf["locale_target2"] = "esMX";

// Conexión a la base de datos "world"
// Connection to "world" database 
$conf["world"]["server"]   = "localhost";
$conf["world"]["user"]     = "wow";
$conf["world"]["password"] = "wow";
$conf["world"]["database"] = "tc_wotlk_world";
$conf["world"]["charset"]  = "utf8";

// Conexión a la base de datos "htwow"
// Connection to "htwow" database 
$conf["htwow"]["server"]    = "localhost";
$conf["htwow"]["user"]      = "htwow";
$conf["htwow"]["password"]  = "htwow";
$conf["htwow"]["database"]  = "htwow";
$conf["htwow"]["charset"]   = "utf8";

// Valor para la columna verifiedBuid
// Value to verifiedBuild
$conf["verifiedBuid"] = 0;