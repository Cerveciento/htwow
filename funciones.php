<?php
function quitaDollar($arr) {
    if(is_array($arr)) {
        foreach(array_keys($arr) as $clave) {
            $arr[$clave] = quitaDollarTexto($arr[$clave]);
        }
    }
    return $arr;
}
function quitaDollarTexto($txt) {
    global $locale;
    
    $txt = str_replace("\$B", "\n", $txt);
    $txt = str_replace("\$b", "\n", $txt);
    $txt = str_replace("\$n", "&lt;" . strtolower($locale[TXT_NOMBRE]) . "&gt;", $txt);
    $txt = str_replace("\$N", "&lt;" . strtoupper($locale[TXT_NOMBRE]) . "&gt;", $txt);
    $txt = str_replace("\$c", "&lt;" . strtolower($locale[TXT_CLASE]) . "&gt;", $txt);
    $txt = str_replace("\$C", "&lt;" . strtoupper($locale[TXT_CLASE]) . "&gt;", $txt);
    $txt = str_replace("\$r", "&lt;" . strtolower($locale[TXT_RAZA]) . "&gt;", $txt);
    $txt = str_replace("\$R", "&lt;" . strtoupper($locale[TXT_RAZA]) . "&gt;", $txt);
    $txt = preg_replace('/\$[gG](.+?):(.+?);/', "&lt;$1/$2&gt;", $txt);
    $txt = str_replace("<", "&lt;", $txt);
    $txt = str_replace(">", "&gt;", $txt);
    
    return $txt;
}
function dolariza($txt) {
    global $locale;
    
    $txt = trim($txt);
    $txt = str_replace("'", "\'", $txt);
    $txt = str_replace("\\\'", "\'", $txt);
    $txt = str_replace("\n", '$B', $txt);
    $txt = str_replace("<" . strtolower($locale[TXT_NOMBRE]) . ">", '$n', $txt);
    $txt = str_replace("<" . strtoupper($locale[TXT_NOMBRE]) . ">", '$N', $txt);
    $txt = str_replace("<" . strtolower($locale[TXT_CLASE]) . ">", '$c', $txt);
    $txt = str_replace("<" . strtoupper($locale[TXT_CLASE]) . ">", '$C', $txt);
    $txt = str_replace("<" . strtolower($locale[TXT_RAZA]) . ">", '$r', $txt);
    $txt = str_replace("<" . strtoupper($locale[TXT_RAZA]) . ">", '$R', $txt);

    $txt = str_replace("…", "...", $txt);
    $txt = str_replace(" ...", "...", $txt);

    $txt = preg_replace("/<([^<.]+?)\/(.+?)>/", '$g$1:$2;', $txt);
    $txt = htmlentities($txt);
    return $txt;
}
function estadisticas($arrCampos) {
    global $world;
    global $conf;
    global $locale;

    $totIng = 0;
    $totEsp = 0;
    $txt = "<pre>";
    foreach ($arrCampos as $campo) {
        $ing = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo["taIng"] . "` WHERE `" . $campo["caIng"] . "` != ''");
        $esp = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["caEsp"] . "` != '' AND locale = '" . $conf["locale_target"] . "'");
        $txt .= mb_sprintf("%-15s: %5d/%5d - %6.2f%%\n", $campo["titulo"], $esp, $ing, round(100 / $ing * $esp, 2));
        $totEsp += $esp;
        $totIng += $ing;
    }
    $txt .= sprintf("%-15s: %5d/%5d - %6.2f%%", $locale[TXT_TOTALES], $totEsp, $totIng, round(100 / $totIng * $totEsp, 2));
    $txt .= "</pre>";

    return $txt;
}
function botones($arrCampos, $id) {
    global $world;
    global $locale;
    global $conf;
    $idAnterior = null;
    $idSiguiente = null;
    $menor = 0;
    $mayor = 9999999999;
    foreach ($arrCampos as $campo) {
        $idAnterior = $world->queryCampo("SELECT `" . $campo["idIng"] . "` FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "` NOT IN(SELECT `" . $campo["idEsp"] . "` FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["caEsp"] . "` != '' AND locale='" . $conf["locale_target"] . "') AND `" . $campo["idIng"] . "` < '$id' AND (" . $campo["caIng"] . "!='') ORDER BY " . $campo["idIng"] . " DESC");
        $idSiguiente = $world->queryCampo("SELECT `" . $campo["idIng"] . "` FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "` NOT IN(SELECT `" . $campo["idEsp"] . "` FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["caEsp"] . "` != '' AND locale='" . $conf["locale_target"] . "') AND `" . $campo["idIng"] . "` > '$id' AND (" . $campo["caIng"] . "!='') ORDER BY " . $campo["idIng"] . " ASC");
        if($idAnterior && $idAnterior > $menor) {
            $menor = $idAnterior;
            $titleAnterior = $locale[TXT_ANTERIOR] . " " . $campo["caEsp"] . " ($idAnterior)";
        }
        if($idSiguiente && $idSiguiente < $mayor) {
            $mayor = $idSiguiente;
            $titleSiguiente = $locale[TXT_SIGUIENTE] . " " . $campo["caEsp"] . " ($idSiguiente)";
        }
    }
    $campo = $arrCampos[0];
    if($menor == 0) {
        $menor = $world->queryCampo("SELECT `" . $campo["idIng"] . "` FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "` < '$id' ORDER BY " . $campo["idIng"] . " DESC");
        $txtMenor = "&lt;=";
        $titleAnterior = $locale[TXT_ANTERIOR] . " ($menor)";
        if(!$menor) {
            $menor = $world->queryCampo("SELECT MIN(`" . $campo["idIng"] . "`) FROM `" . $campo["taIng"] . "`");
            $txtMenor = "|&lt;";
            $titleAnterior = $locale[TXT_NO_ANTERIOR] . " ($menor)";
        }
    } else {
        $txtMenor = "&lt;-";
    }
    if($mayor == 9999999999) {
        $mayor = $world->queryCampo("SELECT `" . $campo["idIng"] . "` FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "` > '$id' ORDER BY " . $campo["idIng"] . " ASC");
        $txtMayor = "=&gt;";
        $titleSiguiente = $locale[TXT_SIGUIENTE] . " ($mayor)";
        if(!$mayor) {
            $mayor = $world->queryCampo("SELECT MAX(`" . $campo["idIng"] . "`) FROM `" . $campo["taIng"] . "`");
            $txtMayor = "&gt;|";
            $titleSiguiente = $locale[TXT_NO_SIGUIENTE] . " ($mayor)";
        }
    } else {
        $txtMayor = "-&gt;";
    }
    $txt = "<input type=\"button\" value=\"$txtMenor\" onclick=\"document.location='?id=$menor'\" title=\"$titleAnterior\"></input>";
    $txt .= "<input type=\"button\" value=\"$txtMayor\" onclick=\"document.location='?id=$mayor'\" title=\"$titleSiguiente\"></input>";
    return $txt;
}
function generarSQL($arrCampos) {
    global $conf;
    
    global $origenInfo;
    global $arrWhereAdicional;
    
    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    $multiId = $REQ->get("multiId");
    
    if($multiId) {
        $arrId = array_map("trim", explode(",", $multiId));
        $multiId = implode(", " , $arrId);
        $id = $arrId[0];
    } else {
        $arrId[0] = "@ID";
    }
    
    $txtSQL = "";
    // nombre
    if($multiId) {
        $separador = "<br>";
        $txtSQL .= "-- " . $REQ->get("txt[0]") . "<br>";
        $txtSQL .= "-- " . $multiId . "<br>";
    } else {
        $separador = " ";
        if($origenInfo) {
            $txtSQL .= "-- $id " . $REQ->get("txt[0]") . "<br>";
        } else {
            $txtSQL .= "-- $id<br>";
        }
    }
    // Updates en inglés
    foreach ($arrCampos as $c => $campo) {
        $updIng = $REQ->get("updIng[$c]", false, Request::FILTRO_BOOL);
        if($updIng) {
            $txtSQL .= "-- Notice: English text is also missing in " . $campo["taIng"] . "." . $campo["caIng"] . "<br>";
        }
    }
    if($origenInfo) {
        $idioma = substr($conf["locale_target"], 0, 2);
        if($idioma === "en") {
            $prefijoRetail = "www";
            $prefijoTBC = "tbc";
            $prefijoClassic = "classic";
        } else {
            $prefijoRetail = $idioma;
            $prefijoTBC = $idioma . ".tbc";
            $prefijoClassic = $idioma . ".classic";
        }
        // wowhead
        if($REQ->get("retail")) {
            $txtSQL .= "-- https://$prefijoRetail.wowhead.com/$origenInfo=$id<br>";
        }
        // wowhead TBC
        if($REQ->get("tbc")) {
            $txtSQL .= "-- https://$prefijoTBC.wowhead.com/$origenInfo=$id<br>" ;
        }
        // wowhead classic
        if($REQ->get("classic")) {
            $txtSQL .= "-- https://$prefijoClassic.wowhead.com/$origenInfo=$id<br>" ;
        }
    }
    // ID
    if(!$multiId) {
        $txtSQL .= "SET @ID := $id;<br>";
    }
    // esMX
    $arrLocale = array($conf["locale_target"]);
    if($REQ->get("esMX")) {
        $whereMX = "IN('<span class=\"importante\">" . $conf["locale_target"] . "</span>', '<span class=\"importante\">" . $conf["locale_target2"] . "</span>')";
        $arrLocale[] = $conf["locale_target2"];
        $separador = "<br>";
    } else {
        $whereMX = "= '<span class=\"importante\">" . $conf["locale_target"] . "</span>'";
    }
    // determina insert o update para cada tabla
    $arrTablas = array();
    $arrTablasIng = array();
    $arrAcciones = array();
    $arrAccionesIng = array();
    foreach ($arrCampos as $c => $campo) {
        $arrTablas[$campo["taEsp"]][] = $c;
        $arrTablasIng[$campo["taIng"]][] = $c;
    }
    // Prepara las acciones en español
    foreach ($arrTablas as $nombreTabla => $campos) {
        $ins = false;
        $upd = false;
        foreach ($campos as $campo) {
            if($REQ->get("ins[$campo]", false, Request::FILTRO_BOOL)) {
                $ins = true;
                $upd = false;
                break;
            }
            if($REQ->get("upd[$campo]", false, Request::FILTRO_BOOL)) {
                $upd = true;
            }
        }
        $arrAcciones[$nombreTabla]["ins"] = $ins;
        $arrAcciones[$nombreTabla]["upd"] = $upd;
    }
    // Prepara las acciones en inglés
    foreach ($arrTablasIng as $nombreTabla => $campos) {
        $upd = false;
        foreach ($campos as $campo) {
            if($REQ->get("updIng[$campo]", false, Request::FILTRO_BOOL)) {
                $upd = true;
            }
        }
        $arrAccionesIng[$nombreTabla]["upd"] = $upd;
    }
    // procesa las tablas en inglés
    foreach ($arrTablasIng as $nombreTabla => $campos) {
        // UPDATE
        if($arrAccionesIng[$nombreTabla]["upd"]) {
            $txtWhereAdicional = "";
            $txtSQL .= "UPDATE `$nombreTabla` ";
            $arrParejas = array();
            foreach ($arrCampos as $c => $campo) {
                if($campo["taIng"] === $nombreTabla && $REQ->get("updIng[$c]", false, Request::FILTRO_BOOL)) {
                    $txtDato = trim($REQ->get("txtIng[$c]"));
                    if(is_numeric($txtDato)) {
                        $arrParejas[] = "`" . $campo["caIng"] . "` = " . "<span class=\"importante\">" . $txtDato . "</span>";
                    } else {
                        $arrParejas[] = "`" . $campo["caIng"] . "` = " . "'<span class=\"importante\">" . dolariza($txtDato) . "</span>'";
                    }
                }
            }
            $arrParejas[] = "`VerifiedBuild` = <span class=\"importante\">0</span>";
            $txtSQL .= "SET " . implode(", " , $arrParejas);
            if($multiId) {
                $txtSQL .= " WHERE `" . $arrCampos[$campos[0]]["idIng"] . "` IN($multiId) $txtWhereAdicional";
            } else {
                $txtSQL .= " WHERE `" . $arrCampos[$campos[0]]["idIng"] . "` = @ID $txtWhereAdicional";
            }
            $txtSQL .= ";<br>";
        }
    }	
    // procesa las tablas
    foreach ($arrTablas as $nombreTabla => $campos) {
        if(isset($arrWhereAdicional[$nombreTabla])) {
            $txtWhereAdicional = "AND `" . $arrWhereAdicional[$nombreTabla]["campo"] . "` = " . $arrWhereAdicional[$nombreTabla]["valor"];
        } else {
            $txtWhereAdicional = "";
        }
        // INSERT
        if($arrAcciones[$nombreTabla]["ins"]) {
            if($multiId) {
                $txtSQL .= "DELETE FROM `$nombreTabla` WHERE `" . $arrCampos[$campos[0]]["idEsp"] . "` IN($multiId) $txtWhereAdicional AND `locale` " . $whereMX;
            } else {
                $txtSQL .= "DELETE FROM `$nombreTabla` WHERE `" . $arrCampos[$campos[0]]["idEsp"] . "` = @ID $txtWhereAdicional AND `locale` " . $whereMX;
            }
            $txtSQL .= ";<br>";
            // Prepara los nombre de campo
            $arrNombreCampo = array($arrCampos[$campos[0]]["idEsp"]);
            if(isset($arrWhereAdicional[$nombreTabla])) {
                $arrNombreCampo[] = $arrWhereAdicional[$nombreTabla]["campo"];
            }
            $arrNombreCampo[] = "locale";
            foreach ($arrCampos as $c => $campo) {
                if($campo["taEsp"] === $nombreTabla && $REQ->get("ins[$c]", false, Request::FILTRO_BOOL)) {
                    $arrNombreCampo[] = $campo["caEsp"];
                }
            }
            $arrNombreCampo[] = "VerifiedBuild";
            // Prepara los valores
            $arrTodos = array();
            foreach ($arrLocale as $loc) {
                foreach ($arrId as $id) {
                    $arrValores = array($id);
                    if(isset($arrWhereAdicional[$nombreTabla])) {
                        $arrValores[] = $arrWhereAdicional[$nombreTabla]["valor"];
                    }
                    $arrValores[] = "'<span class=\"importante\">" . $loc . "</span>'";
                    foreach ($arrCampos as $c => $campo) {
                        if($campo["taEsp"] === $nombreTabla && $REQ->get("ins[$c]", false, Request::FILTRO_BOOL)) {
                            $txtDato = trim($REQ->get("txt[$c]"));
                            if(is_numeric($txtDato)) {
                                $arrValores[] = "<span class=\"importante\">" . $txtDato . "</span>";
                            } else {
                                $arrValores[] = "'<span class=\"importante\">" . dolariza($txtDato) . "</span>'";
                            }
                        }
                    }
                    $arrValores[] = "<span class=\"importante\">" . (int) $conf["verifiedBuid"] . "</span>";
                    $arrTodos[] = implode(", ", $arrValores);
                }
            }
            
            $txtSQL .= "INSERT INTO `$nombreTabla` (`" . implode('`, `', $arrNombreCampo) . "`) VALUES$separador(" . implode("),<br>(", $arrTodos) . ")";
            $txtSQL .= ";<br>";
        }
        // UPDATE
        if($arrAcciones[$nombreTabla]["upd"]) {
            $txtSQL .= "UPDATE `$nombreTabla` ";
            $arrParejas = array();
            foreach ($arrCampos as $c => $campo) {
                if($campo["taEsp"] === $nombreTabla && $REQ->get("upd[$c]", false, Request::FILTRO_BOOL)) {
                    $txtDato = trim($REQ->get("txt[$c]"));
                    if(is_numeric($txtDato)) {
                        $arrParejas[] = "`" . $campo["caEsp"] . "` = " . "<span class=\"importante\">" . $txtDato . "</span>";
                    } else {
                        $arrParejas[] = "`" . $campo["caEsp"] . "` = " . "'<span class=\"importante\">" . dolariza($txtDato) . "</span>'";
                    }
                }
            }
            $arrParejas[] = "`VerifiedBuild` = <span class=\"importante\">" . (int) $conf["verifiedBuid"] . "</span>";
            $txtSQL .= "SET " . implode(", " , $arrParejas);
            if($multiId) {
                $txtSQL .= " WHERE `" . $arrCampos[$campos[0]]["idEsp"] . "` IN($multiId) $txtWhereAdicional AND `locale` " . $whereMX;
            } else {
                $txtSQL .= " WHERE `" . $arrCampos[$campos[0]]["idEsp"] . "` = @ID $txtWhereAdicional AND `locale` " . $whereMX;
            }
            $txtSQL .= ";<br>";
        }
        
    }
    $txtSQL .= "<br>";
    return $txtSQL;
}

function pagina($arrCampos) {
    global $conf;
    global $world;
    global $htwow;
    global $locale;
    
    global $origenInfo;
    global $especifico;
    
    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    $multiId = $REQ->get("multiId");
    
    echo "<html><head>
    <meta charset=\"UTF-8\">
    <LINK REL=StyleSheet HREF=\"../htwow.css\" TYPE=\"text/css\">
    <script src=\"../js.js\"></script>
    <script src=\"../class.Ajax.js\"></script>
    <title>";
    $campo = $arrCampos[0];
    $titulo = quitaDollarTexto($world->queryCampo("SELECT " . $campo["caEsp"] . " FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["idEsp"] . "` = '$id' AND locale = '" . $conf["locale_target"] . "'"));
    echo $titulo;
    echo " </title>
    </head>
    <body onload='actualizar();'>";

    // Parte de arriba
    echo "<div class=\"parteSuperior\">";
    // Título y formulario
    echo "<div class=\"caja\">";
    echo formulario($arrCampos, $id, $multiId);
    echo "</div>";

    // Origen de informacion
    $classic = $htwow->queryCampo("SELECT classic FROM `mision` WHERE idMision='$id'");
    $tbc = $htwow->queryCampo("SELECT tbc FROM `mision` WHERE idMision='$id'");
    $checkedClassic = "";
    $checkedTBC = "";
    $checkedRetail = "";
    if($classic) {
        $checkedClassic = "checked";
    }
    if($tbc) {
        $checkedTBC = "checked";
    }
    if($titulo) {
        $checkedRetail = "checked";
    }
    $idioma = substr($conf["locale_target"], 0, 2);
    if($idioma === "en") {
        $prefijoRetail = "www";
        $prefijoTBC = "tbc";
        $prefijoClassic = "classic";
    } else {
        $prefijoRetail = $idioma;
        $prefijoTBC = $idioma . ".tbc";
        $prefijoClassic = $idioma . ".classic";
    }
    echo "<div class=\"caja\">";
    echo "<input type=\"checkbox\" id=\"chkRetail\" $checkedRetail onchange=\"actualizar();\"></input>";
    echo "<a href=\"https://" . $prefijoRetail . ".wowhead.com/$origenInfo=$id\" target=\"wowhead\">WowHead</a> https://" . $prefijoRetail . ".wowhead.com/$origenInfo=$id";
    echo "<br>";
    echo "<input type=\"checkbox\" id=\"chkTBC\" $checkedTBC onchange=\"actualizar();\"></input>";
    echo "<a href=\"https://" . $prefijoTBC . ".wowhead.com/$origenInfo=$id\" target=\"wowhead\">TBC</a> https://" .$prefijoTBC . ".wowhead.com/$origenInfo=$id";
    echo "<br>";
    echo "<input type=\"checkbox\" id=\"chkClassic\" $checkedClassic onchange=\"actualizar();\"></input>";
    echo "<a href=\"https://" . $prefijoClassic . ".wowhead.com/$origenInfo=$id\" target=\"wowhead\">Classic</a> https://" . $prefijoClassic . ".wowhead.com/$origenInfo=$id";

    echo "<br><br>";
    echo "<a href=\"https://tcubuntu.northeurope.cloudapp.azure.com/aowow/?$origenInfo=$id\" target=\"aowow\">AoWoW</a>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<a href=\"http://web.archive.org/web/20100415110831/http://" . $prefijoRetail . ".wowhead.com/$origenInfo=$id\" target=\"archivo\">www.archive.org</a>";

    if($conf["locale_target2"]) {
        echo "<br><br>";
        echo "<input type=\"checkbox\" id=\"chkEsMX\" checked onchange=\"actualizar();\"></input>";
        echo $conf["locale_target2"];
    }
    echo "</div>";
    // Especifico
    if(isset($especifico)) {
        $txtEspecifico = $especifico();
        if($txtEspecifico) {
            echo "<div class=\"caja\" style=\"overflow:auto;max-height:" . (count($arrCampos)*2+2) . "ch;\">";
            echo $txtEspecifico;
            echo "</div>";
        }
    }
    // Estadisticas
    echo "<div class=\"caja\" style=\"flex-grow:0;\">";
    echo estadisticas($arrCampos);
    echo "</div>";
    echo "</div>";

    // Parte inferior
    echo "<div class=\"parteInferior\">";
    // Tabla de traducción
    echo "<div class=\"caja\" style=\"flex-grow:0;\">";
    echo tablaTraduccion($arrCampos);
    echo "</div>";

    echo "<div class=\"apiladoVertical\">";	// Zona derecha
    // Zona
    $txtZona = encontrarZona($arrCampos);
    if($txtZona) {
        echo "<div class=\"caja\" style=\"flex-grow: 0;\">";
        echo $txtZona;
        echo "</div>";
    }
    // Items
    $txtItems = mostrarItems($arrCampos);
    if($txtItems) {
        echo "<div class=\"caja\" style=\"flex-grow:0;\">";
        echo $txtItems;
        echo "</div>";
    }
    // Recompensas
    $txtRecompensas = mostrarRecompensas($arrCampos);
    if($txtRecompensas) {
        echo "<div class=\"caja\" style=\"flex-grow:0;\">";
        echo $txtRecompensas;
        echo "</div>";
    }
    // SQL
    echo "<div class=\"caja\">";
    echo "<span id=\"sql\" style=\"font-family:monospace;\"></span>";
    echo "</div>";

    echo "</div>";	// Fin zona derecha

    echo "</div>";	// Fin parte inferior

    // Ver también
    echo "<div class=\"parteInferior\">";
    $txtVerTambien = verTambien($arrCampos);
    if($txtVerTambien) {
        echo "<div class=\"caja\" style=\"flex-grow: 0;\">";
        echo $txtVerTambien;
        echo "</div>";
    }
    // Ayuda Adicional
    $txtAyudaAdicional = ayudaAdicional();
    if($txtAyudaAdicional) {
        echo "<div class=\"caja\">";
        echo $txtAyudaAdicional;
        echo "</div>";
    }
    echo "</div>";

    echo "</body></html>";
}

function buscar() {
    global $conf;
    global $locale;
    global $world;
    global $htwow;

    $REQ = Request::getInstancia();
    $buscado = trim($REQ->get("buscado"));
    $buscado = str_replace("'", "\'", $buscado);

    $arrEncontrado = array();
    $t = new Tabla("tResulBuscar");
    // Lugar
    $rsLugar = $htwow->query("SELECT al.idArea, al.texto, area_locale.texto AS nombreIng FROM `area_locale` AS al JOIN `area_locale` ON al.idArea=area_locale.idArea AND area_locale.locale='enUS'  WHERE al.locale='" . $conf["locale_target"] . "'  AND area_locale.texto LIKE '%$buscado%' LIMIT 10");
    while($lugar = $htwow->fetchArray($rsLugar)) {
        $t->insFila();
        $t->insCelda($locale[TXT_LUGAR]);
        $t->insCelda($lugar["texto"]);
        $t->insCelda($lugar["nombreIng"]);
    }
    // POI
    $rsPOI = $world->query("SELECT ID, Name FROM `points_of_interest` WHERE Name LIKE '%$buscado%' LIMIT 15");
    while($ing = $world->fetchArray($rsPOI)) {
        $encontrado = $world->queryFirst("SELECT ID, Name FROM `points_of_interest_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $ing["ID"]);
        if($encontrado) {
            $t->insFila();
            $t->insCelda($locale[TXT_POI]);
            $t->insCelda($encontrado["Name"]);
            $t->insCelda($ing["Name"]);
        }
    }
    // Criaturas
    $rsCri = $world->query("SELECT entry, name FROM `creature_template` WHERE name LIKE '%$buscado%' LIMIT 30");
    while($ing = $world->fetchArray($rsCri)) {
        $encontrado = $world->queryFirst("SELECT entry, name FROM `creature_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $ing["entry"]);
        if($encontrado) {
            $t->insFila();
            $t->insCelda($locale[TXT_CRIATURA]);
            $t->insCelda($encontrado["name"]);
            $t->insCelda($ing["name"]);
        }
    }
    // Objeto 
    $rsObj = $world->query("SELECT entry, name FROM `gameobject_template` WHERE name LIKE '%$buscado%' LIMIT 30");
    while($ing = $world->fetchArray($rsObj)) {
        $encontrado = $world->queryFirst("SELECT entry, name FROM `gameobject_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $ing["entry"]);
        if($encontrado) {
            $t->insFila();
            $t->insCelda($locale[TXT_OBJETO]);
            $t->insCelda($encontrado["name"]);
            $t->insCelda($ing["name"]);
        }
    }
    // item
    $rsItem = $world->query("SELECT entry, name FROM `item_template` WHERE name LIKE '%$buscado%' LIMIT 30");
    while($ing = $world->fetchArray($rsItem)) {
        $encontrado = $world->queryFirst("SELECT ID, Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $ing["entry"]);
        if($encontrado) {
            $t->insFila();
            $t->insCelda($locale[TXT_ITEM]);
            $t->insCelda($encontrado["Name"]);
            $t->insCelda($ing["name"]);
        }
    }
    return $t->getTabla();
}
function ayudaAdicional() {
    global $locale;
    $txt =  $locale[TXT_BUSCAR] . ": <input type=\"text\" id=\"buscado\" name=\"buscado\" value=\"\"></input>";
    $txt .= "<input type=\"button\" value=\"" . $locale[TXT_BUSCAR] . "\" onclick=\"buscar();\"></input>";
    $txt .= "<div class=\"caja\" id=\"resulBuscar\"></div>";
    return $txt;
}
function mostrarRecompensas($arrCampos) {
    global $world;
    global $conf;
    global $locale;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if(!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    if($campo["taIng"] !== "quest_template") {
        return;
    }
    $quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID=$id");
    $arrItems = array();
    for($c = 1; $c < 5; $c++) {
        if($quest["RewardItem" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RewardItem" . $c]);
            $arrItems[] = $quest["RewardAmount" . $c] . " <a href=\"item_template.php?id=" . $quest["RewardItem" . $c] . "\" target=\"item\">" . $nombre ."</a>";
        }
    }
    
    $arrElegirUno = array();
    // a elegir
    for($c = 1; $c < 7; $c++) {
        if($quest["RewardChoiceItemID" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RewardChoiceItemID" . $c]);
            $arrElegirUno[] = $quest["RewardChoiceItemQuantity" . $c] . " <a href=\"item_template.php?id=" . $quest["RewardChoiceItemID" . $c] . "\" target=\"item\">" . $nombre ."</a>";
        }
    }	
    $txt = "";
    if(count($arrItems) > 0) {
        $txt .= implode("<br>", $arrItems);
    }
    if(count($arrElegirUno) > 0) {
        if($txt) { $txt .= "<br>"; }
        $txt .= $locale[TXT_MIS_ELEGIR_RECOMPENSA] . "<br>";
        $txt .= implode("<br>", $arrElegirUno);
    }
    // Dinerito
    if($quest["RewardMoney"] > 0 || $quest["RewardBonusMoney"] > 0) {
        if($txt) { $txt .= "<br>"; }
//		$txt .= monedas($quest["RewardMoney"] + $quest["RewardBonusMoney"]);
        $txt .= monedas($quest["RewardMoney"]);
    }
    
    // Título
    if($txt) {
        $txt = $locale[TXT_MIS_RECOMPENSA] . "<br>" . $txt;
    }
    return $txt;
}
function monedas($cantidad) {
    $oro = 0;
    $plata = 0;
    $cobre = 0;
    if($cantidad >= 10000) {
        $oro = substr($cantidad, 0, -4);
        $cantidad = substr($cantidad, -4);
    }
    if($cantidad >= 100) {
        $plata = substr($cantidad, -4, -2);
        $cantidad = substr($cantidad, -2);
    }
    if($cantidad > 0) {
        $cobre = $cantidad;
    }
    $txt = "";
    if($oro) {
        $txt .= "<span class=\"monedaOro\">" . $oro . "</span> ";
    }
    if($plata) {
        $txt .= "<span class=\"monedaPlata\">" . $plata . "</span> ";
    }
    $txt .= "<span class=\"monedaCobre\">" . $cobre . "</span>";
    return $txt;
}
function mostrarItems($arrCampos) {
    global $world;
    global $conf;
    global $locale;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if(!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    if($campo["taIng"] !== "quest_template") {
        return;
    }
    $quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID = $id");
    $mision = $world->queryFirst("SELECT * FROM `quest_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = $id");
    $arrItems = array();
    for($c = 1; $c < 7; $c++) {
        if($quest["RequiredItemId" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RequiredItemId" . $c]);
            $arrItems[] = $quest["RequiredItemCount" . $c] . " <a href=\"item_template.php?id=" . $quest["RequiredItemId" . $c] . "\" target=\"item\">" . $nombre ."</a>";
        }
        if($quest["RequiredItemId" . $c] < 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RequiredItemId" . $c]);
            $arrItems[] = "*** " . $quest["RequiredItemCount" . $c] . " <a href=\"\">" . $nombre ."</a>";
        }
    }
    // Objetivos del 1 al 4
    for($c = 1; $c < 5; $c++) {
        $nombre = "";
        $matado = " " . $locale[TXT_MIS_MATADO];
        if($mision["ObjectiveText" . $c] !== "") {
            $nombre = $mision["ObjectiveText" . $c];
            $matado = "";
        }
        if($quest["RequiredNpcOrGo" . $c] > 0) {
            if(!$nombre) {
                $nombre = $world->queryCampo("SELECT Name FROM `creature_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $quest["RequiredNpcOrGo" . $c]);
            }
            $arrItems[] = $quest["RequiredNpcOrGoCount" . $c] . " <a href=\"creature_template.php?id=" . $quest["RequiredNpcOrGo" . $c] . "\" target=\"creature\">" . $nombre ."</a>$matado";
        }
        if($quest["RequiredNpcOrGo" . $c] < 0) {
            $quest["RequiredNpcOrGo" . $c] = abs($quest["RequiredNpcOrGo" . $c]);
            if(!$nombre) {
                $nombre = $world->queryCampo("SELECT Name FROM `gameobject_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $quest["RequiredNpcOrGo" . $c]);
            }
            if(!$nombre) {
                $nombre = $quest["RequiredNpcOrGo" . $c];
            }
            $arrItems[] = $quest["RequiredNpcOrGoCount" . $c] . "<a href=\"gameobject_template.php?id=" . $quest["RequiredNpcOrGo" . $c] . "\" target=\"object\">" . $nombre ."</a>";
        }
    }
    $txt = "";
    if(count($arrItems) > 0) {
        $txt = implode("<br>", $arrItems);
    }
    // Título
    if($txt) {
        $txt = $locale[TXT_MIS_OBJETIVO] . "<br>" . $txt;
    }
    return $txt;
}
function verTambien($arrCampos) {
    global $world;
    global $conf;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if(!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    $datoEsp = str_replace("'", "\'", trim($world->queryCampo("SELECT " . $campo["caEsp"] . " FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["idEsp"] . "` = '$id' AND locale = '" . $conf["locale_target"] . "'")));
    if($datoEsp == "") {
        return "";
    }
    $txtRetorno = "";
    $parecidos = $world->query("SELECT " . $campo["caEsp"] . "," . $campo["idEsp"] . " FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["caEsp"] . "`LIKE '%" . $datoEsp . "%' AND locale = '" . $conf["locale_target"] . "'");
    if($parecidos->rowCount() > 1) {
        $txtRetorno .= "<ul>";
        while($q = $world->fetchArray($parecidos)) {
            $icos = icoFaccionRazaClase($q[$campo["idEsp"]], $campo["taIng"]);
            if($q[$campo["idEsp"]] == $id) {
                $txtRetorno .= "<li>$icos<span>" . htmlentities($q[$campo["caEsp"]]) . "</span></li>";
            } else {
                $txtRetorno .= "<li><a href=\"?id=" . $q[$campo["idEsp"]] . "\">$icos<span>" . htmlentities($q[$campo["caEsp"]]) . "</span></a></li>";
            }
        }
        $txtRetorno .= "</ul>";
    }
    return $txtRetorno;
}
function icoFaccionRazaClase($id, $taIng) {
    global $world;
    $arrEstilo = array();
    if($id && $taIng == "quest_template") {
        $lado = $world->queryCampo("SELECT AllowableRaces FROM `quest_template` WHERE `ID` = " . $id);
        
        if($lado & RAZA_HORDA) { $arrEstilo[] = "iconoHorda"; $arrTxt[] = "Horda"; }
        if($lado & RAZA_ALIANZA) { $arrEstilo[] = "iconoAlianza"; $arrTxt[] = "Alianza"; }

        if($lado != RAZA_HORDA && $lado & RAZA_ORCO) { $arrEstilo[] = "iconoOrco"; $arrTxt[] = "Orco"; }
        if($lado != RAZA_HORDA && $lado & RAZA_NO_MUERTO) { $arrEstilo[] = "iconoNoMuerto"; $arrTxt[] = "No Muerto"; }
        if($lado != RAZA_HORDA && $lado & RAZA_TAUREN) { $arrEstilo[] = "iconoTauren"; $arrTxt[] = "Tauren"; }
        if($lado != RAZA_HORDA && $lado & RAZA_TROL) { $arrEstilo[] = "iconoTrol"; $arrTxt[] = "Trol"; }
        if($lado != RAZA_HORDA && $lado & RAZA_ELFO_SANGRE) { $arrEstilo[] = "iconoElfoSangre"; $arrTxt[] = "Elfo de Sangre"; }

        if($lado != RAZA_ALIANZA && $lado & RAZA_HUMANO) { $arrEstilo[] = "iconoHumano"; $arrTxt[] = "Humano"; }
        if($lado != RAZA_ALIANZA && $lado & RAZA_ENANO) { $arrEstilo[] = "iconoEnano"; $arrTxt[] = "Enano"; }
        if($lado != RAZA_ALIANZA && $lado & RAZA_ELFO_NOCHE) { $arrEstilo[] = "iconoElfoNoche"; $arrTxt[] = "Elfo de la Noche"; }
        if($lado != RAZA_ALIANZA && $lado & RAZA_GNOMO) { $arrEstilo[] = "iconoGnomo"; $arrTxt[] = "Gnomo"; }
        if($lado != RAZA_ALIANZA && $lado & RAZA_DRAENEI) { $arrEstilo[] = "iconoDraenei"; $arrTxt[] = "Draenei"; }

        $clase = $world->queryCampo("SELECT AllowableClasses FROM `quest_template_addon` WHERE `ID` = " . $id);
        if($clase & CLASE_BRUJO) { $arrEstilo[] = "iconoBrujo"; $arrTxt[] = "Brujo"; }
        if($clase & CLASE_CABALLERO_MUERTE) { $arrEstilo[] = "iconoCaballeroMuerte"; $arrTxt[] = "Caballero de la Muerte"; }
        if($clase & CLASE_CAZADOR) { $arrEstilo[] = "iconoCazador"; $arrTxt[] = " Cazador"; }
        if($clase & CLASE_CHAMAN) { $arrEstilo[] = "iconoChaman"; $arrTxt[] = "Chamán"; }
        if($clase & CLASE_DRUIDA) { $arrEstilo[] = "iconoDruida"; $arrTxt[] = "Druida"; }
        if($clase & CLASE_GUERRERO) { $arrEstilo[] = "iconoGuerrero"; $arrTxt[] = "Guerrero"; }
        if($clase & CLASE_MAGO) { $arrEstilo[] = "iconoMago"; $arrTxt[] = "Mago"; }
        if($clase & CLASE_PALADIN) { $arrEstilo[] = "iconoPaladin"; $arrTxt[] = "Paladín"; }
        if($clase & CLASE_PICARO) { $arrEstilo[] = "iconoPicaro"; $arrTxt[] = "Pícaro"; }
        if($clase & CLASE_SACERDOTE) { $arrEstilo[] = "iconoSacerdote"; $arrTxt[] = "Sacerdote"; }
    }
    $arrHTML = array();
    foreach ($arrEstilo as $pos => $ico) {
        $arrHTML[] = "<span class=\"$ico\" title=\"" . $arrTxt[$pos] . "\" style=\"width:17px;\"> </span>";
    }
    return implode("", $arrHTML);
}
function encontrarZona($arrCampos) {
	global $world;
	global $htwow;
	global $conf;

	$REQ = Request::getInstancia();
	$id = $REQ->get("id");
	if(!$id) {
		return "";
	}
	$campo = $arrCampos[0];
	if($campo["taIng"] !== "quest_template") {
		return;
	}
	$arrRetorno = array();
	$quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID=$id");
	if(!$quest) {
		return;
	}
	$area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $quest["QuestSortID"] . " AND locale='" . $conf["locale_target"] . "'");
	if(!$area) {
		$area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $quest["QuestSortID"] . " AND locale='esES'");
	}
	$arrRetorno[] = $area["texto"];
	$idPadre = $area["idAreaPadre"];
	while($idPadre) {
		$area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $area["idAreaPadre"] . " AND locale='" . $conf["locale_target"] . "'");
		if(!$area) {
			$area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $idPadre . " AND locale='esES'");
		}
		$arrRetorno[] = $area["texto"];
		$idPadre = $area["idAreaPadre"];
	}
	if($area["idContinente"]) {
		$continente = $htwow->queryCampo("SELECT texto FROM `continente_locale` WHERE idContinente=" . $area["idContinente"] . " AND locale='" . $conf["locale_target"] . "'");
		if(!$continente) {
			$continente = $htwow->queryCampo("SELECT texto FROM `continente_locale` WHERE idContinente=" . $area["idContinente"] . " AND locale='esES'");
		}
		$arrRetorno[]  = $continente;
	}
	$arrRetorno = array_reverse($arrRetorno);
	$txtRetorno = "";
	if(count($arrRetorno) > 0) {
		$txtRetorno = implode(" &gt; ", $arrRetorno);
	}
	return $txtRetorno;
}
function tablaTraduccion($arrCampos) {
    global $world;
    global $conf;
    
    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    $t2 = new Tabla(null, null, array("style" => "mix-width: 1146px; max-width: 1146px;"));
    $primeraFila = true;
    foreach ($arrCampos as $num => $campo) {
        $datoRawIng = trim($world->queryCampo("SELECT " . $campo["caIng"] . " FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "` = '$id'"));
        $datoRawEsp = trim($world->queryCampo("SELECT " . $campo["caEsp"] . " FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["idEsp"] . "` = '$id' AND locale = '" . $conf["locale_target"] . "'"));
        $datoIng = quitaDollarTexto($world->queryCampo("SELECT " . $campo["caIng"] . " FROM `" . $campo["taIng"] . "` WHERE `" . $campo["idIng"] . "`='$id'"));
        $datoEsp = quitaDollarTexto($world->queryCampo("SELECT " . $campo["caEsp"] . " FROM `" . $campo["taEsp"] . "` WHERE `" . $campo["idEsp"] . "`='$id' AND locale = '" . $conf["locale_target"] . "'"));

        $ancho = 65;
        $altoIng = cuentaLineas($datoIng, $ancho) * 2;
        $altoEsp = cuentaLineas($datoEsp, $ancho) * 2;
        $alto = max($altoIng, $altoEsp);
        $checkedIns = "";
        $checkedUpd = "";
        $aviso = "";
        if($datoRawEsp !== "" && $datoRawIng == $datoRawEsp) {
            $aviso = "avisoVerde";
        }
//		if(strpos($datoRawEsp, "|n") > 0) {
//			$aviso = "avisoAmarillo";
//		}
        if($datoIng !== "" && $datoEsp === "") {
            $cuenta = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo["taEsp"] . "` WHERE`" . $campo["idEsp"] . "` = '$id' AND locale = '" . $conf["locale_target"] . "'");
            if($cuenta == 0) {
                $checkedIns = "checked";
            } else {
                $checkedUpd = "checked";
            }
            $aviso = "aviso";
        }
        if($primeraFila) {
            $t2->insFila();
            $icos =icoFaccionRazaClase($id, $campo["taIng"]);
            $t2->insCelda("<div>" . $icos . "<span class=\"primero\">" .$datoEsp . "</span></div>", null, "td", array("align" => "center", "colspan" => 5));
            $primeraFila = FALSE;
        }
        $t2->insFila($aviso);
        $t2->insCelda("U", null, "td", array("align" => "center"));
        $t2->insCelda("<span style=\"font-family: monospace;\">" . $campo["taIng"] . "." . $campo["caIng"] . " </span><b>" . $campo["titulo"] . "</b><span style=\"font-family: monospace;\"> " . $campo["taEsp"] . "." . $campo["caEsp"] . "</span>", null, "td", array("align" => "center", "colspan" => 2));
        $t2->insCelda("I", null, "td", array("align" => "center"));
        $t2->insCelda("U", null, "td", array("align" => "center"));

        $t2->insFila();
        $t2->insCelda("<input name=\"updIng\" type=\"checkbox\" id=\"updIng[$num]\" onchange=\"actualizar();\"></input>");
        $t2->insCelda("<textarea name=\"txtIng\" id=\"txtIng[$num]\" style=\"width:" . $ancho . "ch;height:" . $alto . "ch;\" onchange=\"actualizar();\">" . $datoIng . "</textarea>");
        $t2->insCelda("<textarea name=\"txt\" id=\"txt[$num]\" style=\"width:" . $ancho . "ch;height:" . $alto . "ch;\" onchange=\"actualizar();\">" . $datoEsp . "</textarea>");
        $t2->insCelda("<input name=\"ins\" type=\"checkbox\" id=\"ins[$num]\" $checkedIns onchange=\"actualizar();\"></input>");
        $t2->insCelda("<input name=\"upd\" type=\"checkbox\" id=\"upd[$num]\" $checkedUpd onchange=\"actualizar();\"></input>");
    }
    return $t2->getTabla();
}
function trocearTexto($txt, $tamano) {
    $palabras = explode(" ", $txt);
    $lineas = array();
    $txt = "";
    foreach ($palabras as $palabra) {
        if(mb_strlen($txt . " " . $palabra, "UTF-8") < $tamano) {
            $txt .= " " . $palabra;
        } else {
            $lineas[] = $txt;
            $txt = $palabra;
        }
    }
    $lineas[] = $txt;
    return $lineas;
}
function cuentaLineas($txt, $tamano = 65) {
    $lineas = 0;
    $trozos = explode("\n", trim($txt));
    foreach ($trozos as $linea) {
        $lineas += count(trocearTexto($linea, $tamano));
    }
    return $lineas;
}
function formulario($arrCampos, $id, $multiId) {
    global $locale;
    
    $txtForm = "<span class=\"tituloPagina\">" . $locale[TXT_VERSION_WOW] . " " . $locale[TXT_MIS_MISION] . " <a href=\"index.php\">" . $locale[TXT_VOLVER] . "</a></span>";
    $txtForm .= "<br><br><br><form>"
        . $locale[TXT_ID] . " " . $locale[TXT_MIS_MISION] . ": <input type=\"text\" id=\"id\" name=\"id\" value=\"" . $id . "\"></input>"
        . "<input type=\"submit\" name=\"enviar\" value=\"" . $locale[TXT_ENVIAR] . "\"></input>";
    $txtForm .= botones($arrCampos, $id);
    $txtForm .= "<br>" . $locale[TXT_MULTI_ID] .": <input type=\"text\" style=\"width:40ch\" id=\"multiId\" name=\"multiId\" value=\"" . $multiId . "\"></input></form>";
    return $txtForm;
}

// from php.net
if (!function_exists('mb_sprintf')) {
  function mb_sprintf($format) {
      $argv = func_get_args() ;
      array_shift($argv) ;
      return mb_vsprintf($format, $argv) ;
  }
}
if (!function_exists('mb_vsprintf')) {
  /**
   * Works with all encodings in format and arguments.
   * Supported: Sign, padding, alignment, width and precision.
   * Not supported: Argument swapping.
   */
  function mb_vsprintf($format, $argv, $encoding = null) {
      if (is_null($encoding)) 
          $encoding = mb_internal_encoding();

      // Use UTF-8 in the format so we can use the u flag in preg_split
      $format = mb_convert_encoding($format, 'UTF-8', $encoding);

      $newformat = ""; // build a new format in UTF-8
      $newargv = array(); // unhandled args in unchanged encoding

      while ($format !== "") {
     
        // Split the format in two parts: $pre and $post by the first %-directive
        // We get also the matched groups
        @list ($pre, $sign, $filler, $align, $size, $precision, $type, $post) = 
            preg_split("!\%(\+?)('.|[0 ]|)(-?)([1-9][0-9]*|)(\.[1-9][0-9]*|)([%a-zA-Z])!u",
                       $format, 2, PREG_SPLIT_DELIM_CAPTURE) ;

        $newformat .= mb_convert_encoding($pre, $encoding, 'UTF-8');
       
        if ($type == '') {
          // didn't match. do nothing. this is the last iteration.
        }
        elseif ($type == '%') {
          // an escaped %
          $newformat .= '%%';
        }
        elseif ($type == 's') {
          $arg = array_shift($argv);
          $arg = mb_convert_encoding($arg, 'UTF-8', $encoding);
          $padding_pre = '';
          $padding_post = '';
         
          // truncate $arg
          if ($precision !== '') {
            $precision = intval(substr($precision,1));
            if ($precision > 0 && mb_strlen($arg,$encoding) > $precision)
              $arg = mb_substr($precision,0,$precision,$encoding);
          }
         
          // define padding
          if ($size > 0) {
            $arglen = mb_strlen($arg, $encoding);
            if ($arglen < $size) {
              if($filler==='')
                  $filler = ' ';
              if ($align == '-')
                  $padding_post = str_repeat($filler, $size - $arglen);
              else
                  $padding_pre = str_repeat($filler, $size - $arglen);
            }
          }
         
          // escape % and pass it forward
          $newformat .= $padding_pre . str_replace('%', '%%', $arg) . $padding_post;
        }
        else {
          // another type, pass forward
          $newformat .= "%$sign$filler$align$size$precision$type";
          $newargv[] = array_shift($argv);
        }
        $format = strval($post);
      }
      // Convert new format back from UTF-8 to the original encoding
      $newformat = mb_convert_encoding($newformat, $encoding, 'UTF-8');
      return vsprintf($newformat, $newargv);
  }
}
