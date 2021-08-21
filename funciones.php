<?php
function quitaDollar($arr)
{
    if (is_array($arr)) {
        foreach (array_keys($arr) as $clave) {
            $arr[$clave] = quitaDollarTexto($arr[$clave]);
        }
    }
    return $arr;
}
function quitaDollarTexto($txt)
{
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
function dolariza($txt)
{
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

function mostrarRecompensas($arrCampos)
{
    global $world;
    global $conf;
    global $locale;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if (!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    if ($campo->getTablaOrigen() !== "quest_template") {
        return;
    }
    $quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID=$id");
    $arrItems = array();
    for ($c = 1; $c < 5; $c++) {
        if ($quest["RewardItem" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RewardItem" . $c]);
            $arrItems[] = $quest["RewardAmount" . $c] . " <a href=\"item_template.php?id=" . $quest["RewardItem" . $c] . "\" target=\"item\">" . $nombre . "</a>";
        }
    }

    $arrElegirUno = array();
    // a elegir
    for ($c = 1; $c < 7; $c++) {
        if ($quest["RewardChoiceItemID" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RewardChoiceItemID" . $c]);
            $arrElegirUno[] = $quest["RewardChoiceItemQuantity" . $c] . " <a href=\"item_template.php?id=" . $quest["RewardChoiceItemID" . $c] . "\" target=\"item\">" . $nombre . "</a>";
        }
    }
    $txt = "";
    if (count($arrItems) > 0) {
        $txt .= implode("<br>", $arrItems);
    }
    if (count($arrElegirUno) > 0) {
        if ($txt) {
            $txt .= "<br>";
        }
        $txt .= $locale[TXT_MIS_ELEGIR_RECOMPENSA] . "<br>";
        $txt .= implode("<br>", $arrElegirUno);
    }
    // Dinerito
    if ($quest["RewardMoney"] > 0 || $quest["RewardBonusMoney"] > 0) {
        if ($txt) {
            $txt .= "<br>";
        }
        $txt .= monedas($quest["RewardMoney"]);
    }

    // Título
    if ($txt) {
        $txt = $locale[TXT_MIS_RECOMPENSA] . "<br>" . $txt;
    }
    return $txt;
}
function monedas(int $cantidad): string
{
    if ($cantidad === 0) {
        return "";
    }
    $oro = 0;
    $plata = 0;
    $cobre = 0;
    if ($cantidad >= 10000) {
        $oro = substr($cantidad, 0, -4);
        $cantidad = substr($cantidad, -4);
    }
    if ($cantidad >= 100) {
        $plata = substr($cantidad, -4, -2);
        $cantidad = substr($cantidad, -2);
    }
    if ($cantidad > 0) {
        $cobre = $cantidad;
    }
    $txt = "";
    if ($oro) {
        $txt .= "<span class=\"monedaOro\">" . $oro . "</span> ";
    }
    if ($plata) {
        $txt .= "<span class=\"monedaPlata\">" . $plata . "</span> ";
    }
    $txt .= "<span class=\"monedaCobre\">" . $cobre . "</span>";

    return $txt;
}
function mostrarItems($arrCampos)
{
    global $world;
    global $conf;
    global $locale;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if (!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    if ($campo->getTablaOrigen() !== "quest_template") {
        return;
    }
    $quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID = $id");
    $mision = $world->queryFirst("SELECT * FROM `quest_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = $id");
    $arrItems = array();
    for ($c = 1; $c < 7; $c++) {
        if ($quest["RequiredItemId" . $c] > 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RequiredItemId" . $c]);
            $arrItems[] = $quest["RequiredItemCount" . $c] . " <a href=\"item_template.php?id=" . $quest["RequiredItemId" . $c] . "\" target=\"item\">" . $nombre . "</a>";
        }
        if ($quest["RequiredItemId" . $c] < 0) {
            $nombre = $world->queryCampo("SELECT Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $quest["RequiredItemId" . $c]);
            $arrItems[] = "*** " . $quest["RequiredItemCount" . $c] . " <a href=\"\">" . $nombre . "</a>";
        }
    }
    // Objetivos del 1 al 4
    for ($c = 1; $c < 5; $c++) {
        $nombre = "";
        $matado = " " . $locale[TXT_MIS_MATADO];
        if ($mision["ObjectiveText" . $c] !== "") {
            $nombre = $mision["ObjectiveText" . $c];
            $matado = "";
        }
        if ($quest["RequiredNpcOrGo" . $c] > 0) {
            if (!$nombre) {
                $nombre = $world->queryCampo("SELECT Name FROM `creature_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $quest["RequiredNpcOrGo" . $c]);
            }
            $arrItems[] = $quest["RequiredNpcOrGoCount" . $c] . " <a href=\"creature_template.php?id=" . $quest["RequiredNpcOrGo" . $c] . "\" target=\"creature\">" . $nombre . "</a>$matado";
        }
        if ($quest["RequiredNpcOrGo" . $c] < 0) {
            $quest["RequiredNpcOrGo" . $c] = abs($quest["RequiredNpcOrGo" . $c]);
            if (!$nombre) {
                $nombre = $world->queryCampo("SELECT Name FROM `gameobject_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $quest["RequiredNpcOrGo" . $c]);
            }
            if (!$nombre) {
                $nombre = $quest["RequiredNpcOrGo" . $c];
            }
            $arrItems[] = $quest["RequiredNpcOrGoCount" . $c] . "<a href=\"gameobject_template.php?id=" . $quest["RequiredNpcOrGo" . $c] . "\" target=\"object\">" . $nombre . "</a>";
        }
    }
    $txt = "";
    if (count($arrItems) > 0) {
        $txt = implode("<br>", $arrItems);
    }
    // Título
    if ($txt) {
        $txt = $locale[TXT_MIS_OBJETIVO] . "<br>" . $txt;
    }
    return $txt;
}
function verTambien($arrCampos)
{
    global $world;
    global $conf;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if (!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    $datoEsp = str_replace("'", "\'", trim($world->queryCampo("SELECT " . $campo->getCampoDestino() . " FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getIdDestino() . "` = '$id' AND locale = '" . $conf["locale_target"] . "'")));
    if ($datoEsp == "") {
        return "";
    }
    $txtRetorno = "";
    $parecidos = $world->query("SELECT " . $campo->getCampoDestino() . "," . $campo->getIdDestino() . " FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getCampoDestino() . "`LIKE '%" . $datoEsp . "%' AND locale = '" . $conf["locale_target"] . "'");
    if ($parecidos->rowCount() > 1) {
        $txtRetorno .= "<ul>";
        while ($q = $world->fetchArray($parecidos)) {
            $icos = icoFaccionRazaClase($q[$campo->getIdDestino()], $campo->getTablaOrigen());
            if ($q[$campo->getIdDestino()] == $id) {
                $txtRetorno .= "<li>$icos<span>" . htmlentities($q[$campo->getCampoDestino()]) . "</span></li>";
            } else {
                $txtRetorno .= "<li><a href=\"?id=" . $q[$campo->getIdDestino()] . "\">$icos<span>" . htmlentities($q[$campo->getCampoDestino()]) . "</span></a></li>";
            }
        }
        $txtRetorno .= "</ul>";
    }
    return $txtRetorno;
}
function icoFaccionRazaClase($id, $tableSource)
{
    global $world;
    $arrEstilo = array();
    if ($id && $tableSource == "quest_template") {
        $lado = $world->queryCampo("SELECT AllowableRaces FROM `quest_template` WHERE `ID` = " . $id);

        if ($lado & RAZA_HORDA) {
            $arrEstilo[] = "iconoHorda";
            $arrTxt[] = "Horda";
        }
        if ($lado & RAZA_ALIANZA) {
            $arrEstilo[] = "iconoAlianza";
            $arrTxt[] = "Alianza";
        }

        if ($lado != RAZA_HORDA && $lado & RAZA_ORCO) {
            $arrEstilo[] = "iconoOrco";
            $arrTxt[] = "Orco";
        }
        if ($lado != RAZA_HORDA && $lado & RAZA_NO_MUERTO) {
            $arrEstilo[] = "iconoNoMuerto";
            $arrTxt[] = "No Muerto";
        }
        if ($lado != RAZA_HORDA && $lado & RAZA_TAUREN) {
            $arrEstilo[] = "iconoTauren";
            $arrTxt[] = "Tauren";
        }
        if ($lado != RAZA_HORDA && $lado & RAZA_TROL) {
            $arrEstilo[] = "iconoTrol";
            $arrTxt[] = "Trol";
        }
        if ($lado != RAZA_HORDA && $lado & RAZA_ELFO_SANGRE) {
            $arrEstilo[] = "iconoElfoSangre";
            $arrTxt[] = "Elfo de Sangre";
        }

        if ($lado != RAZA_ALIANZA && $lado & RAZA_HUMANO) {
            $arrEstilo[] = "iconoHumano";
            $arrTxt[] = "Humano";
        }
        if ($lado != RAZA_ALIANZA && $lado & RAZA_ENANO) {
            $arrEstilo[] = "iconoEnano";
            $arrTxt[] = "Enano";
        }
        if ($lado != RAZA_ALIANZA && $lado & RAZA_ELFO_NOCHE) {
            $arrEstilo[] = "iconoElfoNoche";
            $arrTxt[] = "Elfo de la Noche";
        }
        if ($lado != RAZA_ALIANZA && $lado & RAZA_GNOMO) {
            $arrEstilo[] = "iconoGnomo";
            $arrTxt[] = "Gnomo";
        }
        if ($lado != RAZA_ALIANZA && $lado & RAZA_DRAENEI) {
            $arrEstilo[] = "iconoDraenei";
            $arrTxt[] = "Draenei";
        }

        $clase = $world->queryCampo("SELECT AllowableClasses FROM `quest_template_addon` WHERE `ID` = " . $id);
        if ($clase & CLASE_BRUJO) {
            $arrEstilo[] = "iconoBrujo";
            $arrTxt[] = "Brujo";
        }
        if ($clase & CLASE_CABALLERO_MUERTE) {
            $arrEstilo[] = "iconoCaballeroMuerte";
            $arrTxt[] = "Caballero de la Muerte";
        }
        if ($clase & CLASE_CAZADOR) {
            $arrEstilo[] = "iconoCazador";
            $arrTxt[] = " Cazador";
        }
        if ($clase & CLASE_CHAMAN) {
            $arrEstilo[] = "iconoChaman";
            $arrTxt[] = "Chamán";
        }
        if ($clase & CLASE_DRUIDA) {
            $arrEstilo[] = "iconoDruida";
            $arrTxt[] = "Druida";
        }
        if ($clase & CLASE_GUERRERO) {
            $arrEstilo[] = "iconoGuerrero";
            $arrTxt[] = "Guerrero";
        }
        if ($clase & CLASE_MAGO) {
            $arrEstilo[] = "iconoMago";
            $arrTxt[] = "Mago";
        }
        if ($clase & CLASE_PALADIN) {
            $arrEstilo[] = "iconoPaladin";
            $arrTxt[] = "Paladín";
        }
        if ($clase & CLASE_PICARO) {
            $arrEstilo[] = "iconoPicaro";
            $arrTxt[] = "Pícaro";
        }
        if ($clase & CLASE_SACERDOTE) {
            $arrEstilo[] = "iconoSacerdote";
            $arrTxt[] = "Sacerdote";
        }
    }
    $arrHTML = array();
    foreach ($arrEstilo as $pos => $ico) {
        $arrHTML[] = "<span class=\"$ico\" title=\"" . $arrTxt[$pos] . "\" style=\"width:17px;\"> </span>";
    }
    return implode("", $arrHTML);
}
function encontrarZona($arrCampos)
{
    global $world;
    global $htwow;
    global $conf;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if (!$id) {
        return "";
    }
    $campo = $arrCampos[0];
    if ($campo->getTablaOrigen() !== "quest_template") {
        return;
    }
    $arrRetorno = array();
    $quest = $world->queryFirst("SELECT * FROM `quest_template` WHERE ID=$id");
    if (!$quest) {
        return;
    }
    $area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $quest["QuestSortID"] . " AND locale='" . $conf["locale_target"] . "'");
    if (!$area) {
        $area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $quest["QuestSortID"] . " AND locale='esES'");
    }
    $arrRetorno[] = $area["texto"];
    $idPadre = $area["idAreaPadre"];
    while ($idPadre) {
        $area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $area["idAreaPadre"] . " AND locale='" . $conf["locale_target"] . "'");
        if (!$area) {
            $area = $htwow->queryFirst("SELECT * FROM `area` JOIN area_locale USING(idArea) WHERE idArea=" . $idPadre . " AND locale='esES'");
        }
        $arrRetorno[] = $area["texto"];
        $idPadre = $area["idAreaPadre"];
    }
    if ($area["idContinente"] != null) {
        $continente = $htwow->queryCampo("SELECT texto FROM `continente_locale` WHERE idContinente=" . $area["idContinente"] . " AND locale='" . $conf["locale_target"] . "'");
        if (!$continente) {
            $continente = $htwow->queryCampo("SELECT texto FROM `continente_locale` WHERE idContinente=" . $area["idContinente"] . " AND locale='esES'");
        }
        $arrRetorno[]  = $continente;
    }
    $arrRetorno = array_reverse($arrRetorno);
    $txtRetorno = "";
    if (count($arrRetorno) > 0) {
        $txtRetorno = implode(" &gt; ", $arrRetorno);
    }
    return $txtRetorno;
}
function trocearTexto($txt, $tamano)
{
    $palabras = explode(" ", $txt);
    $lineas = array();
    $txt = "";
    foreach ($palabras as $palabra) {
        if (mb_strlen($txt . " " . $palabra, "UTF-8") < $tamano) {
            $txt .= " " . $palabra;
        } else {
            $lineas[] = $txt;
            $txt = $palabra;
        }
    }
    $lineas[] = $txt;
    return $lineas;
}
function cuentaLineas($txt, $tamano = 65)
{
    $lineas = 0;
    $trozos = explode("\n", trim($txt));
    foreach ($trozos as $linea) {
        $lineas += count(trocearTexto($linea, $tamano));
    }
    return $lineas;
}

// from php.net https://www.php.net/manual/es/function.sprintf.php#89020
if (!function_exists('mb_sprintf')) {
    function mb_sprintf($format)
    {
        $argv = func_get_args();
        array_shift($argv);
        return mb_vsprintf($format, $argv);
    }
}
if (!function_exists('mb_vsprintf')) {
    /**
     * Works with all encodings in format and arguments.
     * Supported: Sign, padding, alignment, width and precision.
     * Not supported: Argument swapping.
     */
    function mb_vsprintf($format, $argv, $encoding = null)
    {
        if (is_null($encoding))
            $encoding = mb_internal_encoding();

        // Use UTF-8 in the format so we can use the u flag in preg_split
        $format = mb_convert_encoding($format, 'UTF-8', $encoding);

        $newformat = ""; // build a new format in UTF-8
        $newargv = array(); // unhandled args in unchanged encoding

        while ($format !== "") {

            // Split the format in two parts: $pre and $post by the first %-directive
            // We get also the matched groups
            @list($pre, $sign, $filler, $align, $size, $precision, $type, $post) =
                preg_split(
                    "!\%(\+?)('.|[0 ]|)(-?)([1-9][0-9]*|)(\.[1-9][0-9]*|)([%a-zA-Z])!u",
                    $format,
                    2,
                    PREG_SPLIT_DELIM_CAPTURE
                );

            $newformat .= mb_convert_encoding($pre, $encoding, 'UTF-8');

            if ($type == '') {
                // didn't match. do nothing. this is the last iteration.
            } elseif ($type == '%') {
                // an escaped %
                $newformat .= '%%';
            } elseif ($type == 's') {
                $arg = array_shift($argv);
                $arg = mb_convert_encoding($arg, 'UTF-8', $encoding);
                $padding_pre = '';
                $padding_post = '';

                // truncate $arg
                if ($precision !== '') {
                    $precision = intval(substr($precision, 1));
                    if ($precision > 0 && mb_strlen($arg, $encoding) > $precision)
                        $arg = mb_substr($precision, 0, $precision, $encoding);
                }

                // define padding
                if ($size > 0) {
                    $arglen = mb_strlen($arg, $encoding);
                    if ($arglen < $size) {
                        if ($filler === '')
                            $filler = ' ';
                        if ($align == '-')
                            $padding_post = str_repeat($filler, $size - $arglen);
                        else
                            $padding_pre = str_repeat($filler, $size - $arglen);
                    }
                }

                // escape % and pass it forward
                $newformat .= $padding_pre . str_replace('%', '%%', $arg) . $padding_post;
            } else {
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
