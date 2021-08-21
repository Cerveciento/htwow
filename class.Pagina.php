<?php

class Pagina
{
    /**
     * arrCampos
     *
     * @var array
     */
    private $arrCampos = array();
    /**
     * Título de la página en singular
     *
     * @var string
     */
    private $singular = "";
    /**
     * Título de la página en plural
     *
     * @var string
     */
    private $plural = "";
    /**
     * página de información en wowhead
     *
     * @var string
     */
    private $paginaInfo = "";
    /**
     * función específica para la página
     *
     * @var string
     */
    private $especifico = "";

    /**
     * construct
     *
     * @param int $idPagina 
     * @global BaseDatos $htwow
     * @global array $conf
     */
    public function __construct(int $idPagina)
    {
        /** @var BaseDatos $htwow */
        global $htwow;
        /** @var array */
        global $conf;

        // Carga la información de la página
        $pagina = $htwow->queryFirst("SELECT * FROM `pagina` JOIN pagina_locale USING(idPagina) WHERE idPagina = " . $idPagina . " AND locale='" . $conf["locale"] . "'");
        $this->singular = $pagina["singular"];
        $this->plural = $pagina["plural"];
        $this->paginaInfo = $pagina["paginaInfo"];
        $this->especifico = $pagina["especifico"];

        // Carga los campos de la página
        $campos = $htwow->query("SELECT * FROM `campo` WHERE idPagina = " . $idPagina . " ORDER BY orden");
        while ($campo = $htwow->fetchArray()) {
            $this->arrCampos[] = new Campo($campo);
        }
    }
    /**
     * Genera las estadísticas de los textos traducidos.
     *
     * @return string
     */
    public function estadisticas()
    {
        global $world;
        global $conf;
        global $locale;

        $totIng = 0;
        $totEsp = 0;
        $txt = "<pre>";
        /** @var Campo $campo  */
        foreach ($this->arrCampos as $campo) {
            $ing = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getCampoOrigen() . "` != ''");
            $esp = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getCampoDestino() . "` != '' AND locale = '" . $conf["locale_target"] . "'");
            $txt .= mb_sprintf("%-15s: %5d/%5d - %6.2f%%\n", $campo->getTitulo(), $esp, $ing, round(100 / $ing * $esp, 2));
            $totEsp += $esp;
            $totIng += $ing;
        }
        $txt .= sprintf("%-15s: %5d/%5d - %6.2f%%", $locale[TXT_TOTALES], $totEsp, $totIng, round(100 / $totIng * $totEsp, 2));
        $txt .= "</pre>";

        return $txt;
    }
    /**
     * Genera la página HTML
     *
     * @return void
     */
    public function pagina()
    {
        global $conf;
        global $world;
        global $htwow;

        $REQ = Request::getInstancia();
        $id = $REQ->get("id");
        $multiId = $REQ->get("multiId");

        echo "<html><head>
        <meta charset=\"UTF-8\">
        <LINK REL=StyleSheet HREF=\"../htwow.css\" TYPE=\"text/css\">
        <script src=\"../js.js\"></script>
        <script src=\"../class.Ajax.js\"></script>
        <title>";
        $campo = $this->arrCampos[0];
        $titulo = quitaDollarTexto($world->queryCampo("SELECT " . $campo->getCampoDestino() . " FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getIdDestino() . "` = '$id' AND locale = '" . $conf["locale_target"] . "'"));
        echo $titulo;
        echo " </title>
        </head>
        <body onload='actualizar();'>";

        // Parte de arriba
        echo "<div class=\"parteSuperior\">";
        // Título y formulario
        echo "<div class=\"caja\">";
        echo $this->formulario($id, $multiId);
        echo "</div>";

        // Informacion
        $classic = $htwow->queryCampo("SELECT classic FROM `mision` WHERE idMision='$id'");
        $tbc = $htwow->queryCampo("SELECT tbc FROM `mision` WHERE idMision='$id'");
        $checkedClassic = "";
        $checkedTBC = "";
        $checkedRetail = "";
        if ($classic) {
            $checkedClassic = "checked";
        }
        if ($tbc) {
            $checkedTBC = "checked";
        }
        if ($titulo) {
            $checkedRetail = "checked";
        }
        $idioma = substr($conf["locale_target"], 0, 2);
        if ($idioma === "en") {
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
        echo "<a href=\"https://" . $prefijoRetail . ".wowhead.com/$this->paginaInfo=$id\" target=\"wowhead\">WowHead</a> https://" . $prefijoRetail . ".wowhead.com/$this->paginaInfo=$id";
        echo "<br>";
        echo "<input type=\"checkbox\" id=\"chkTBC\" $checkedTBC onchange=\"actualizar();\"></input>";
        echo "<a href=\"https://" . $prefijoTBC . ".wowhead.com/$this->paginaInfo=$id\" target=\"wowhead\">TBC</a> https://" . $prefijoTBC . ".wowhead.com/$this->paginaInfo=$id";
        echo "<br>";
        echo "<input type=\"checkbox\" id=\"chkClassic\" $checkedClassic onchange=\"actualizar();\"></input>";
        echo "<a href=\"https://" . $prefijoClassic . ".wowhead.com/$this->paginaInfo=$id\" target=\"wowhead\">Classic</a> https://" . $prefijoClassic . ".wowhead.com/$this->paginaInfo=$id";

        echo "<br><br>";
        echo "<a href=\"https://tcubuntu.northeurope.cloudapp.azure.com/aowow/?$this->paginaInfo=$id\" target=\"aowow\">AoWoW</a>";
        echo "&nbsp;&nbsp;&nbsp;";
        echo "<a href=\"http://web.archive.org/web/20100415110831/http://" . $prefijoRetail . ".wowhead.com/$this->paginaInfo=$id\" target=\"archivo\">www.archive.org</a>";

        if ($conf["locale_target2"]) {
            echo "<br><br>";
            echo "<input type=\"checkbox\" id=\"chkEsMX\" checked onchange=\"actualizar();\"></input>";
            echo $conf["locale_target2"];
        }
        echo "</div>";
        // Especifico
        if (isset($this->especifico)) {
            $especifico = $this->especifico;
            $txtEspecifico = $especifico();
            if ($txtEspecifico) {
                echo "<div class=\"caja\" style=\"overflow:auto;max-height:" . (count($this->arrCampos) * 2 + 2) . "ch;\">";
                echo $txtEspecifico;
                echo "</div>";
            }
        }
        // Estadisticas
        echo "<div class=\"caja\" style=\"flex-grow:0;\">";
        echo $this->estadisticas();
        echo "</div>";
        echo "</div>";

        // Parte Central
        echo "<div class=\"parteInferior\">";
        // Tabla de traducción
        echo "<div class=\"caja\" style=\"flex-grow:0;\">";
        echo $this->tablaTraduccion();
        echo "</div>";

        echo "<div class=\"apiladoVertical\">";    // Zona derecha
        // Solo en misiones
        if ($this->arrCampos[0]->getTablaOrigen() === "quest_template") {
            // Zona
            $txtZona = encontrarZona($this->arrCampos);
            if ($txtZona) {
                echo "<div class=\"caja\" style=\"flex-grow: 0;\">";
                echo $txtZona;
                echo "</div>";
            }
            // Items
            $txtItems = mostrarItems($this->arrCampos);
            if ($txtItems) {
                echo "<div class=\"caja\" style=\"flex-grow:0;\">";
                echo $txtItems;
                echo "</div>";
            }
            // Recompensas
            $txtRecompensas = mostrarRecompensas($this->arrCampos);
            if ($txtRecompensas) {
                echo "<div class=\"caja\" style=\"flex-grow:0;\">";
                echo $txtRecompensas;
                echo "</div>";
            }
        }
        // SQL
        echo "<div class=\"caja\">";
        echo "<span id=\"sql\" style=\"font-family:monospace;\"></span>";
        echo "</div>";

        echo "</div>";      // Fin zona derecha

        echo "</div>";      // Fin parte central

        // Parte inferior
        echo "<div class=\"parteInferior\">";
        // Ver también
        $txtVerTambien = verTambien($this->arrCampos);
        if ($txtVerTambien) {
            echo "<div class=\"caja\" style=\"flex-grow: 0;\">";
            echo $txtVerTambien;
            echo "</div>";
        }
        // Buscador
        $txtBuscador = $this->buscador();
        if ($txtBuscador) {
            echo "<div class=\"caja\">";
            echo $txtBuscador;
            echo "</div>";
        }
        echo "</div>";      // Fin parte inferior

        echo "</body></html>";
    }

    public function buscador(): string
    {
        global $locale;
        $txt =  $locale[TXT_BUSCAR] . ": <input type=\"text\" id=\"buscado\" name=\"buscado\" value=\"\"></input>";
        $txt .= "<input type=\"button\" value=\"" . $locale[TXT_BUSCAR] . "\" onclick=\"buscar();\"></input>";
        $txt .= "<div class=\"caja\" id=\"resulBuscar\"></div>";
        return $txt;
    }
    public function buscar(): string
    {
        global $conf;
        global $locale;
        global $world;
        global $htwow;

        $REQ = Request::getInstancia();
        $buscado = trim($REQ->get("buscado"));
        $buscado = str_replace("'", "\'", $buscado);
        if ($buscado == "") {
            return "";
        }
        $t = new Tabla("tResulBuscar");
        // Lugar
        $rsLugar = $htwow->query("SELECT al.idArea, al.texto, area_locale.texto AS nombreIng FROM `area_locale` AS al JOIN `area_locale` ON al.idArea=area_locale.idArea AND area_locale.locale='enUS'  WHERE al.locale='" . $conf["locale_target"] . "'  AND area_locale.texto LIKE '%$buscado%' LIMIT 10");
        while ($lugar = $htwow->fetchArray($rsLugar)) {
            $t->insFila();
            $t->insCelda($locale[TXT_LUGAR]);
            $t->insCelda($lugar["texto"]);
            $t->insCelda($lugar["nombreIng"]);
        }
        // POI
        $rsPOI = $world->query("SELECT ID, Name FROM `points_of_interest` WHERE Name LIKE '%$buscado%' LIMIT 15");
        while ($ing = $world->fetchArray($rsPOI)) {
            $encontrado = $world->queryFirst("SELECT ID, Name FROM `points_of_interest_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $ing["ID"]);
            if ($encontrado) {
                $t->insFila();
                $t->insCelda($locale[TXT_POI]);
                $t->insCelda($encontrado["Name"]);
                $t->insCelda($ing["Name"]);
            }
        }
        // Criaturas
        $rsCri = $world->query("SELECT entry, name FROM `creature_template` WHERE name LIKE '%$buscado%' LIMIT 30");
        while ($ing = $world->fetchArray($rsCri)) {
            $encontrado = $world->queryFirst("SELECT entry, name FROM `creature_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $ing["entry"]);
            if ($encontrado) {
                $t->insFila();
                $t->insCelda($locale[TXT_CRIATURA]);
                $t->insCelda($encontrado["name"]);
                $t->insCelda($ing["name"]);
            }
        }
        // Objeto 
        $rsObj = $world->query("SELECT entry, name FROM `gameobject_template` WHERE name LIKE '%$buscado%' LIMIT 30");
        while ($ing = $world->fetchArray($rsObj)) {
            $encontrado = $world->queryFirst("SELECT entry, name FROM `gameobject_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND entry = " . $ing["entry"]);
            if ($encontrado) {
                $t->insFila();
                $t->insCelda($locale[TXT_OBJETO]);
                $t->insCelda($encontrado["name"]);
                $t->insCelda($ing["name"]);
            }
        }
        // item
        $rsItem = $world->query("SELECT entry, name FROM `item_template` WHERE name LIKE '%$buscado%' LIMIT 30");
        while ($ing = $world->fetchArray($rsItem)) {
            $encontrado = $world->queryFirst("SELECT ID, Name FROM `item_template_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $ing["entry"]);
            if ($encontrado) {
                $t->insFila();
                $t->insCelda($locale[TXT_ITEM]);
                $t->insCelda($encontrado["Name"]);
                $t->insCelda($ing["name"]);
            }
        }
        return $t->getTabla();
    }

    function formulario($id, $multiId)
    {
        global $locale;

        $txtForm = "<span class=\"tituloPagina\">" . $locale[TXT_VERSION_WOW] . " " . $this->getTituloSingular() . " <a href=\"../index.php\">" . $locale[TXT_VOLVER] . "</a></span>";
        $txtForm .= "<br><br><br><form>"
            . $locale[TXT_ID] . " " . $this->getTituloSingular() . ": <input type=\"text\" id=\"id\" name=\"id\" value=\"" . $id . "\"></input>"
            . "<input type=\"submit\" name=\"enviar\" value=\"" . $locale[TXT_ENVIAR] . "\"></input>";
        $txtForm .= $this->botones($id);
        $txtForm .= "<br>" . $locale[TXT_MULTI_ID] . ": <input type=\"text\" style=\"width:40ch\" id=\"multiId\" name=\"multiId\" value=\"" . $multiId . "\"></input></form>";
        return $txtForm;
    }
    public function botones($id)
    {
        global $world;
        global $locale;
        global $conf;
        $idAnterior = null;
        $idSiguiente = null;
        $menor = 0;
        $mayor = PHP_INT_MAX;
        foreach ($this->arrCampos as $campo) {
            $idAnterior = $world->queryCampo("SELECT `" . $campo->getIdOrigen() . "` FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "` NOT IN(SELECT `" . $campo->getIdDestino() . "` FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getCampoDestino() . "` != '' AND locale='" . $conf["locale_target"] . "') AND `" . $campo->getIdOrigen() . "` < '$id' AND (" . $campo->getCampoOrigen() . "!='') ORDER BY " . $campo->getIdOrigen() . " DESC");
            $idSiguiente = $world->queryCampo("SELECT `" . $campo->getIdOrigen() . "` FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "` NOT IN(SELECT `" . $campo->getIdDestino() . "` FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getCampoDestino() . "` != '' AND locale='" . $conf["locale_target"] . "') AND `" . $campo->getIdOrigen() . "` > '$id' AND (" . $campo->getCampoOrigen() . "!='') ORDER BY " . $campo->getIdOrigen() . " ASC");
            if ($idAnterior && $idAnterior > $menor) {
                $menor = $idAnterior;
                $titleAnterior = $locale[TXT_ANTERIOR] . " " . $campo->getCampoDestino() . " ($idAnterior)";
            }
            if ($idSiguiente && $idSiguiente < $mayor) {
                $mayor = $idSiguiente;
                $titleSiguiente = $locale[TXT_SIGUIENTE] . " " . $campo->getCampoDestino() . " ($idSiguiente)";
            }
        }
        $campo = $this->arrCampos[0];
        if ($menor == 0) {
            $menor = $world->queryCampo("SELECT `" . $campo->getIdOrigen() . "` FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "` < '$id' ORDER BY " . $campo->getIdOrigen() . " DESC");
            $txtMenor = "&lt;=";
            $titleAnterior = $locale[TXT_ANTERIOR] . " ($menor)";
            if (!$menor) {
                $menor = $world->queryCampo("SELECT MIN(`" . $campo->getIdOrigen() . "`) FROM `" . $campo->getTablaOrigen() . "`");
                $txtMenor = "|&lt;";
                $titleAnterior = $locale[TXT_NO_ANTERIOR] . " ($menor)";
            }
        } else {
            $txtMenor = "&lt;-";
        }
        if ($mayor == PHP_INT_MAX) {
            $mayor = $world->queryCampo("SELECT `" . $campo->getIdOrigen() . "` FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "` > '$id' ORDER BY " . $campo->getIdOrigen() . " ASC");
            $txtMayor = "=&gt;";
            $titleSiguiente = $locale[TXT_SIGUIENTE] . " ($mayor)";
            if (!$mayor) {
                $mayor = $world->queryCampo("SELECT MAX(`" . $campo->getIdOrigen() . "`) FROM `" . $campo->getTablaOrigen() . "`");
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

    function tablaTraduccion()
    {
        global $world;
        global $conf;

        $REQ = Request::getInstancia();
        $id = $REQ->get("id");
        $t2 = new Tabla(null, null, array("style" => "mix-width: 1146px; max-width: 1146px;"));
        $primeraFila = true;
        foreach ($this->arrCampos as $num => $campo) {
            $datoRawIng = trim($world->queryCampo("SELECT " . $campo->getCampoOrigen() . " FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "` = '$id'"));
            $datoRawEsp = trim($world->queryCampo("SELECT " . $campo->getCampoDestino() . " FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getIdDestino() . "` = '$id' AND locale = '" . $conf["locale_target"] . "'"));
            $datoIng = quitaDollarTexto($world->queryCampo("SELECT " . $campo->getCampoOrigen() . " FROM `" . $campo->getTablaOrigen() . "` WHERE `" . $campo->getIdOrigen() . "`='$id'"));
            $datoEsp = quitaDollarTexto($world->queryCampo("SELECT " . $campo->getCampoDestino() . " FROM `" . $campo->getTablaDestino() . "` WHERE `" . $campo->getIdDestino() . "`='$id' AND locale = '" . $conf["locale_target"] . "'"));

            $ancho = 65;
            $altoIng = cuentaLineas($datoIng, $ancho) * 2;
            $altoEsp = cuentaLineas($datoEsp, $ancho) * 2;
            $alto = max($altoIng, $altoEsp);
            $checkedIns = "";
            $checkedUpd = "";
            $aviso = "";
            if ($datoRawEsp !== "" && $datoRawIng == $datoRawEsp) {
                $aviso = "avisoVerde";
            }
            //		if(strpos($datoRawEsp, "|n") > 0) {
            //			$aviso = "avisoAmarillo";
            //		}
            if ($datoIng !== "" && $datoEsp === "") {
                $cuenta = $world->queryCampo("SELECT COUNT(*) FROM `" . $campo->getTablaDestino() . "` WHERE`" . $campo->getIdDestino() . "` = '$id' AND locale = '" . $conf["locale_target"] . "'");
                if ($cuenta == 0) {
                    $checkedIns = "checked";
                } else {
                    $checkedUpd = "checked";
                }
                $aviso = "aviso";
            }
            if ($primeraFila) {
                $t2->insFila();
                $icos = icoFaccionRazaClase($id, $campo->getTablaOrigen());
                $t2->insCelda("<div>" . $icos . "<span class=\"primero\">" . $datoEsp . "</span></div>", null, "td", array("align" => "center", "colspan" => 5));
                $primeraFila = FALSE;
            }
            $t2->insFila($aviso);
            $t2->insCelda("U", null, "td", array("align" => "center"));
            $t2->insCelda("<span style=\"font-family: monospace;\">" . $campo->getTablaOrigen() . "." . $campo->getCampoOrigen() . " </span><b>" . $campo->getTitulo() . "</b><span style=\"font-family: monospace;\"> " . $campo->getTablaDestino() . "." . $campo->getCampoDestino() . "</span>", null, "td", array("align" => "center", "colspan" => 2));
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
    function generarSQL()
    {
        global $conf;

        global $arrWhereAdicional;

        $REQ = Request::getInstancia();
        $id = $REQ->get("id");
        $multiId = $REQ->get("multiId");

        if ($multiId) {
            $arrId = array_map("trim", explode(",", $multiId));
            $multiId = implode(", ", $arrId);
            $id = $arrId[0];
        } else {
            $arrId[0] = "@ID";
        }

        $txtSQL = "";
        // nombre
        if ($multiId) {
            $separador = "<br>";
            $txtSQL .= "-- " . $REQ->get("txt[0]") . "<br>";
            $txtSQL .= "-- " . $multiId . "<br>";
        } else {
            $separador = " ";
            if ($this->paginaInfo) {
                $txtSQL .= "-- $id " . $REQ->get("txt[0]") . "<br>";
            } else {
                $txtSQL .= "-- $id<br>";
            }
        }
        // Updates en inglés
        foreach ($this->arrCampos as $c => $campo) {
            $updIng = $REQ->get("updIng[$c]", false, Request::FILTRO_BOOL);
            if ($updIng) {
                $txtSQL .= "-- Notice: English text is also missing in " . $campo->getTablaOrigen() . "." . $campo->getCampoOrigen() . "<br>";
            }
        }
        if ($this->paginaInfo) {
            $idioma = substr($conf["locale_target"], 0, 2);
            if ($idioma === "en") {
                $prefijoRetail = "www";
                $prefijoTBC = "tbc";
                $prefijoClassic = "classic";
            } else {
                $prefijoRetail = $idioma;
                $prefijoTBC = $idioma . ".tbc";
                $prefijoClassic = $idioma . ".classic";
            }
            // wowhead
            if ($REQ->get("retail")) {
                $txtSQL .= "-- https://$prefijoRetail.wowhead.com/$this->paginaInfo=$id<br>";
            }
            // wowhead TBC
            if ($REQ->get("tbc")) {
                $txtSQL .= "-- https://$prefijoTBC.wowhead.com/$this->paginaInfo=$id<br>";
            }
            // wowhead classic
            if ($REQ->get("classic")) {
                $txtSQL .= "-- https://$prefijoClassic.wowhead.com/$this->paginaInfo=$id<br>";
            }
        }
        // ID
        if (!$multiId) {
            $txtSQL .= "SET @ID := $id;<br>";
        }
        // esMX
        $arrLocale = array($conf["locale_target"]);
        if ($REQ->get("esMX")) {
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
        foreach ($this->arrCampos as $c => $campo) {
            $arrTablas[$campo->getTablaDestino()][] = $c;
            $arrTablasIng[$campo->getTablaOrigen()][] = $c;
        }
        // Prepara las acciones en español
        foreach ($arrTablas as $nombreTabla => $campos) {
            $ins = false;
            $upd = false;
            foreach ($campos as $campo) {
                if ($REQ->get("ins[$campo]", false, Request::FILTRO_BOOL)) {
                    $ins = true;
                    $upd = false;
                    break;
                }
                if ($REQ->get("upd[$campo]", false, Request::FILTRO_BOOL)) {
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
                if ($REQ->get("updIng[$campo]", false, Request::FILTRO_BOOL)) {
                    $upd = true;
                }
            }
            $arrAccionesIng[$nombreTabla]["upd"] = $upd;
        }
        // procesa las tablas en inglés
        foreach ($arrTablasIng as $nombreTabla => $campos) {
            // UPDATE
            if ($arrAccionesIng[$nombreTabla]["upd"]) {
                $txtWhereAdicional = "";
                $txtSQL .= "UPDATE `$nombreTabla` ";
                $arrParejas = array();
                foreach ($this->arrCampos as $c => $campo) {
                    if ($campo->getTablaOrigen() === $nombreTabla && $REQ->get("updIng[$c]", false, Request::FILTRO_BOOL)) {
                        $txtDato = trim($REQ->get("txtIng[$c]"));
                        if (is_numeric($txtDato)) {
                            $arrParejas[] = "`" . $campo->getCampoOrigen() . "` = " . "<span class=\"importante\">" . $txtDato . "</span>";
                        } else {
                            $arrParejas[] = "`" . $campo->getCampoOrigen() . "` = " . "'<span class=\"importante\">" . dolariza($txtDato) . "</span>'";
                        }
                    }
                }
                $arrParejas[] = "`VerifiedBuild` = <span class=\"importante\">0</span>";
                $txtSQL .= "SET " . implode(", ", $arrParejas);
                if ($multiId) {
                    $txtSQL .= " WHERE `" . $this->arrCampos[$campos[0]]->getIdOrigen() . "` IN($multiId) $txtWhereAdicional";
                } else {
                    $txtSQL .= " WHERE `" . $this->arrCampos[$campos[0]]->getIdOrigen() . "` = @ID $txtWhereAdicional";
                }
                $txtSQL .= ";<br>";
            }
        }
        // procesa las tablas
        foreach ($arrTablas as $nombreTabla => $campos) {
            if (isset($arrWhereAdicional[$nombreTabla])) {
                $txtWhereAdicional = "AND `" . $arrWhereAdicional[$nombreTabla]["campo"] . "` = " . $arrWhereAdicional[$nombreTabla]["valor"];
            } else {
                $txtWhereAdicional = "";
            }
            // INSERT
            if ($arrAcciones[$nombreTabla]["ins"]) {
                if ($multiId) {
                    $txtSQL .= "DELETE FROM `$nombreTabla` WHERE `" . $this->arrCampos[$campos[0]]->getIdDestino() . "` IN($multiId) $txtWhereAdicional AND `locale` " . $whereMX;
                } else {
                    $txtSQL .= "DELETE FROM `$nombreTabla` WHERE `" . $this->arrCampos[$campos[0]]->getIdDestino() . "` = @ID $txtWhereAdicional AND `locale` " . $whereMX;
                }
                $txtSQL .= ";<br>";
                // Prepara los nombre de campo
                $arrNombreCampo = array($this->arrCampos[$campos[0]]->getIdDestino());
                if (isset($arrWhereAdicional[$nombreTabla])) {
                    $arrNombreCampo[] = $arrWhereAdicional[$nombreTabla]["campo"];
                }
                $arrNombreCampo[] = "locale";
                foreach ($this->arrCampos as $c => $campo) {
                    if ($campo->getTablaDestino() === $nombreTabla && $REQ->get("ins[$c]", false, Request::FILTRO_BOOL)) {
                        $arrNombreCampo[] = $campo->getCampoDestino();
                    }
                }
                $arrNombreCampo[] = "VerifiedBuild";
                // Prepara los valores
                $arrTodos = array();
                foreach ($arrLocale as $loc) {
                    foreach ($arrId as $id) {
                        $arrValores = array($id);
                        if (isset($arrWhereAdicional[$nombreTabla])) {
                            $arrValores[] = $arrWhereAdicional[$nombreTabla]["valor"];
                        }
                        $arrValores[] = "'<span class=\"importante\">" . $loc . "</span>'";
                        foreach ($this->arrCampos as $c => $campo) {
                            if ($campo->getTablaDestino() === $nombreTabla && $REQ->get("ins[$c]", false, Request::FILTRO_BOOL)) {
                                $txtDato = trim($REQ->get("txt[$c]"));
                                if (is_numeric($txtDato)) {
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
            if ($arrAcciones[$nombreTabla]["upd"]) {
                $txtSQL .= "UPDATE `$nombreTabla` ";
                $arrParejas = array();
                foreach ($this->arrCampos as $c => $campo) {
                    if ($campo->getTablaDestino() === $nombreTabla && $REQ->get("upd[$c]", false, Request::FILTRO_BOOL)) {
                        $txtDato = trim($REQ->get("txt[$c]"));
                        if (is_numeric($txtDato)) {
                            $arrParejas[] = "`" . $campo->getCampoDestino() . "` = " . "<span class=\"importante\">" . $txtDato . "</span>";
                        } else {
                            $arrParejas[] = "`" . $campo->getCampoDestino() . "` = " . "'<span class=\"importante\">" . dolariza($txtDato) . "</span>'";
                        }
                    }
                }
                $arrParejas[] = "`VerifiedBuild` = <span class=\"importante\">" . (int) $conf["verifiedBuid"] . "</span>";
                $txtSQL .= "SET " . implode(", ", $arrParejas);
                if ($multiId) {
                    $txtSQL .= " WHERE `" . $this->arrCampos[$campos[0]]->getIdDestino() . "` IN($multiId) $txtWhereAdicional AND `locale` " . $whereMX;
                } else {
                    $txtSQL .= " WHERE `" . $this->arrCampos[$campos[0]]->getIdDestino() . "` = @ID $txtWhereAdicional AND `locale` " . $whereMX;
                }
                $txtSQL .= ";<br>";
            }
        }
        $txtSQL .= "<br>";
        return $txtSQL;
    }

    /**
     * getTituloPlural
     *
     * @return string
     */
    public function getTituloPlural(): string
    {
        return $this->plural;
    }
    /**
     * getTituloSingular
     *
     * @return string
     */
    public function getTituloSingular(): string
    {
        return $this->singular;
    }
}
