<?php
include_once "../config.php";
include_once "../constantes.php";
include_once "../class.BaseDatos.php";
include_once "../class.Campo.php";
include_once "../class.Pagina.php";
include_once "../class.Request.php";
include_once "../class.Tabla.php";
include_once "../funciones.php";
include_once "../iniciar.php";

$arrWhereAdicional = null;

$REQ = Request::getInstancia();
$world = new BaseDatos($conf["world"]);
$mision = new Pagina(PAG_MISION);

switch ($REQ->get("accion")) {
    case "enviar":
        echo $mision->generarSQL();
        break;
    case "buscar":
        echo $mision->buscar();
        break;
    default:
        $mision->pagina();
}

/**
 * 
 * @global BaseDatos $world
 */
function serie()
{
    global $world;
    global $conf;
    global $locale;

    $REQ = Request::getInstancia();
    $id = $REQ->get("id");
    if (!$id) {
        return "";
    }
    $txtRetorno = "";
    //Empieza
    //Busca criatura
    $emp = $world->queryFirst("SELECT Name, Title, entry FROM `creature_queststarter` JOIN `creature_template_locale` ON `id` = `entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
    if ($emp) {
        $aviso = "";
        // Comprueba si falta el saludo del asignador de misiones
        $saludoIng = $world->queryCampo("SELECT Greeting FROM `quest_greeting` WHERE ID = " . $emp["entry"]);
        if ($saludoIng) {
            $saludoEsp = $world->queryCampo("SELECT Greeting FROM `quest_greeting_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $emp["entry"]);
            if (!$saludoEsp) {
                $aviso = "aviso";
            }
        }
        $txtRetorno .= "<div><a class=\"questStart $aviso\" target=\"creature\" href=\"creature_template.php?id=" . $emp["entry"] . "\">" . $locale[TXT_MIS_EMPIEZA] . "</a>: " . $emp["Name"] . "</div>";
    } else {
        $emp = $world->queryFirst("SELECT Name, entry FROM `gameobject_queststarter` JOIN `gameobject_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
        if ($emp) {
            $txtRetorno .= "<div><a class=\"questStart\" target=\"gameobject\" href=\"gameobject_template.php?id=" . $emp["entry"] . "\">" . $locale[TXT_MIS_EMPIEZA] . "</a>: " . $emp["Name"] . "</div>";
        }
    }
    //Termina
    $ter = $world->queryFirst("SELECT Name, Title, entry FROM `creature_questender` JOIN `creature_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
    if ($ter) {
        $txtRetorno .= "<a class=\"questEnd\" target=\"creature\" href=\"creature_template.php?id=" . $ter["entry"] . "\">" . $locale[TXT_MIS_TERMINA] . "</a>: " . $ter["Name"] . "<br>";
    } else {
        $ter = $world->queryFirst("SELECT Name, entry FROM `gameobject_questender` JOIN `gameobject_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
        if ($ter) {
            $txtRetorno .= "<a class=\"questEnd\" target=\"gameobject\" href=\"gameobject_template.php?id=" . $ter["entry"] . "\">" . $locale[TXT_MIS_TERMINA] . "</a>: " . $ter["Name"] . "<br>";
        }
    }

    //Serie
    $arrQuests = array();
    $quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE RewardNextQuest='$id'");
    while ($quest["RewardNextQuest"] != 0) {
        array_unshift($arrQuests, $quest["ID"]);
        $quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE RewardNextQuest='" . $quest["ID"] . "'");
    }

    $quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE ID='" . $id . "'");
    while ($quest["ID"] != 0) {
        $arrQuests[] = $quest["ID"];
        $quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE ID='" . $quest["RewardNextQuest"] . "'");
    }
    $arrTitulo = array();
    foreach ($arrQuests as $idQuest) {
        $icos = icoFaccionRazaClase($idQuest, "quest_template");
        if ($idQuest == $id) {
            $arrTitulo[] = "<li>$icos" . htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE `id` = '" . $idQuest . "' AND `locale` = '" . $conf["locale_target"] . "'")) . "</li>";
        } else {
            $arrTitulo[] = "<li><a href=\"?id=$idQuest\">$icos" . htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE `id` = '" . $idQuest . "' AND `locale` = '" . $conf["locale_target"] . "'")) . "</a></li>";
        }
    }
    if (count($arrTitulo) > 1) {
        $txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_SERIE] . " (" . count($arrTitulo) . ")</div><ol>";
        $txtRetorno .= implode("", $arrTitulo);
        $txtRetorno .= "</ol>";
    }
    // Requiere
    $requiere = $world->queryFirst("SELECT * FROM `quest_template_addon` WHERE ID='$id'");
    if ($requiere["PrevQuestID"] != 0) {
        $requiere["PrevQuestID"] = abs($requiere["PrevQuestID"]);
        $txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_REQUIERE] . "</div><ul>";
        $txtRequiere = htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE ID = '" . $requiere["PrevQuestID"] . "' AND locale = '" . $conf["locale_target"] . "'"));
        if (!$txtRequiere) {
            $txtRequiere = $requiere["PrevQuestID"];
        }
        $icos = icoFaccionRazaClase($requiere["PrevQuestID"], "quest_template");
        $txtRetorno .= "<li><a href=\"?id=" . $requiere["PrevQuestID"] . "\">$icos" . $txtRequiere . "</a></li>";
        $txtRetorno .= "</ul>";
    }
    // Abre
    $arrTitulo = array();
    $rsAbre = $world->query("SELECT * FROM `quest_template_addon` JOIN `quest_template_locale` ON quest_template_locale.ID = quest_template_addon.ID AND Locale = '" . $conf["locale_target"] . "' WHERE PrevQuestId = '$id'");
    while ($abre = $world->fetchArray($rsAbre)) {
        $icos = icoFaccionRazaClase($abre["ID"], "quest_template");
        if (!in_array($abre["ID"], $arrQuests)) {
            $arrTitulo[] = "<li><a href=\"?id=" . $abre["ID"] . "\">$icos" . htmlentities($abre["Title"]) . "</a></li>";
        }
    }
    if (count($arrTitulo) > 0) {
        $txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_ABRE] . " (" . count($arrTitulo) . ")</div><ul>";
        $txtRetorno .= implode("", $arrTitulo);
        $txtRetorno .= "</ul>";
    }

    return $txtRetorno;
}
