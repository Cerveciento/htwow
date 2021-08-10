<?php
include_once "config.php";
include_once "constantes.php";
include_once "class.BaseDatos.php";
include_once "class.Request.php";
include_once "class.Tabla.php";
include_once "funciones.php";
include_once "iniciar.php";

$versionWow = "3.3.5a WotLK";
$origenInfo = "quest";
$especifico = "serie";
$arrWhereAdicional = null;
$arrCampos = array(
	array("titulo" => $locale[TXT_MIS_TITULO],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "LogTitle",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "Title"
		),
	array("titulo" => $locale[TXT_MIS_DESCRIPCION],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "QuestDescription",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "Details"
		),
	array("titulo" => $locale[TXT_MIS_OBJETIVOS],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "LogDescription",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "Objectives"
		),
	array("titulo" => $locale[TXT_MIS_OBJETIVO_1],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "ObjectiveText1",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "ObjectiveText1"
		),
	array("titulo" => $locale[TXT_MIS_OBJETIVO_2],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "ObjectiveText2",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "ObjectiveText2"
		),
	array("titulo" => $locale[TXT_MIS_OBJETIVO_3],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "ObjectiveText3",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "ObjectiveText3"
		),
	array("titulo" => $locale[TXT_MIS_OBJETIVO_4],
		"taIng" => "quest_template", "idIng" => "id", "caIng" => "ObjectiveText4",
		"taEsp" => "quest_template_locale", "idEsp" => "id", "caEsp" => "ObjectiveText4"
		),
	array("titulo" => $locale[TXT_MIS_PROGRESO],
		"taIng" => "quest_request_items", "idIng" => "id", "caIng" => "CompletionText",
		"taEsp" => "quest_request_items_locale", "idEsp" => "id", "caEsp" => "CompletionText"
		),
	array("titulo" => $locale[TXT_MIS_TERMINACION],
		"taIng" => "quest_offer_reward", "idIng" => "id", "caIng" => "RewardText",
		"taEsp" => "quest_offer_reward_locale", "idEsp" => "id", "caEsp" => "RewardText"
		)
);
$REQ = Request::getInstancia();
$world = new BaseDatos($conf["world"]);

switch($REQ->get("accion")) {
	case "enviar":
		echo generarSQL($arrCampos);
		break;
	case "estadisticas":
		echo estadisticas($arrCampos);
		break;
	case "buscar":
		echo buscar();
		break;
	default :
		pagina($arrCampos);
}

/**
 * 
 * @global BaseDatos $world
 */
function serie() {
	global $world;
	global $conf;
	global $locale;
	
	$REQ = Request::getInstancia();
	$id = $REQ->get("id");
	if(!$id) {
		return "";
	}
	$txtRetorno = "";
	//Empieza
	//Busca criatura
	$emp = $world->queryFirst("SELECT Name, Title, entry FROM `creature_queststarter` JOIN `creature_template_locale` ON `id` = `entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
	if($emp) {
		$aviso = "";
		// Comprueba si falta el saludo del asignador de misiones
		$saludoIng = $world->queryCampo("SELECT Greeting FROM `quest_greeting` WHERE ID = " . $emp["entry"]);
		if($saludoIng) {
			$saludoEsp = $world->queryCampo("SELECT Greeting FROM `quest_greeting_locale` WHERE locale = '" . $conf["locale_target"] . "' AND ID = " . $emp["entry"]);
			if(!$saludoEsp) {
				$aviso = "aviso";
			}
		}
		$txtRetorno .= "<div><a class=\"questStart $aviso\" target=\"creature\" href=\"creature_template.php?id=" . $emp["entry"] . "\">" . $locale[TXT_MIS_EMPIEZA] . "</a>: " . $emp["Name"] . "</div>";
	} else {
		$emp = $world->queryFirst("SELECT Name, entry FROM `gameobject_queststarter` JOIN `gameobject_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
		if($emp) {
			$txtRetorno .= "<div><a class=\"questStart\" target=\"gameobject\" href=\"gameobject_template.php?id=" . $emp["entry"] . "\">" . $locale[TXT_MIS_EMPIEZA] . "</a>: " . $emp["Name"] . "</div>";
		}
	}
	//Termina
	$ter = $world->queryFirst("SELECT Name, Title, entry FROM `creature_questender` JOIN `creature_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");
	if($ter) {
		$txtRetorno .= "<a class=\"questEnd\" target=\"creature\" href=\"creature_template.php?id=" . $ter["entry"] . "\">" . $locale[TXT_MIS_TERMINA] . "</a>: " . $ter["Name"] . "<br>";
	} else {
		$ter = $world->queryFirst("SELECT Name, entry FROM `gameobject_questender` JOIN `gameobject_template_locale` ON `id`=`entry` WHERE `quest` = '$id' AND `locale` = '" . $conf["locale_target"] . "'");	
		if($ter) {
			$txtRetorno .= "<a class=\"questEnd\" target=\"gameobject\" href=\"gameobject_template.php?id=" . $ter["entry"] . "\">" . $locale[TXT_MIS_TERMINA] . "</a>: " . $ter["Name"] . "<br>";		
		}
	}

	//Serie
	$arrQuests = array();
	$quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE RewardNextQuest='$id'");
	while($quest["RewardNextQuest"] != 0) {
		array_unshift($arrQuests, $quest["ID"]);
		$quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE RewardNextQuest='" . $quest["ID"] . "'");
	}
	
	$quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE ID='" . $id . "'");
	while($quest["ID"] != 0) {
		$arrQuests[] = $quest["ID"];
		$quest = $world->queryFirst("SELECT ID, RewardNextQuest FROM `quest_template` WHERE ID='" . $quest["RewardNextQuest"] . "'");
	}
	$arrTitulo = array();
	foreach ($arrQuests as $idQuest) {
		$icos = icoFaccionRazaClase($idQuest, "quest_template");
		if($idQuest == $id) {
			$arrTitulo[] = "<li>$icos" . htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE `id` = '" . $idQuest . "' AND `locale` = '" . $conf["locale_target"] . "'")) . "</li>";
		} else {
			$arrTitulo[] = "<li><a href=\"?id=$idQuest\">$icos" . htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE `id` = '" . $idQuest . "' AND `locale` = '" . $conf["locale_target"] . "'")) . "</a></li>";
		}
	}
	if(count($arrTitulo) > 1) {
		$txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_SERIE] . " (" . count($arrTitulo) . ")</div><ol>";
		$txtRetorno .= implode("", $arrTitulo);
		$txtRetorno .= "</ol>";
	}
	// Requiere
	$requiere = $world->queryFirst("SELECT * FROM `quest_template_addon` WHERE ID='$id'");
	if($requiere["PrevQuestID"] != 0) {
		$requiere["PrevQuestID"] = abs($requiere["PrevQuestID"]);
		$txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_REQUIERE] . "</div><ul>";
		$txtRequiere = htmlentities($world->queryCampo("SELECT Title FROM `quest_template_locale` WHERE ID = '" . $requiere["PrevQuestID"] . "' AND locale = '" . $conf["locale_target"] . "'"));
		if(!$txtRequiere) {
			$txtRequiere = $requiere["PrevQuestID"];
		}
		$icos = icoFaccionRazaClase($requiere["PrevQuestID"], "quest_template");
		$txtRetorno .= "<li><a href=\"?id=" . $requiere["PrevQuestID"] . "\">$icos" . $txtRequiere . "</a></li>";
		$txtRetorno .= "</ul>";
	}
	// Abre
	$arrTitulo = array();
	$rsAbre = $world->query("SELECT * FROM `quest_template_addon` JOIN `quest_template_locale` ON quest_template_locale.ID = quest_template_addon.ID AND Locale = '" . $conf["locale_target"] . "' WHERE PrevQuestId = '$id'");
	while($abre = $world->fetchArray($rsAbre)) {
		$icos = icoFaccionRazaClase($abre["ID"], "quest_template");
		if(!in_array($abre["ID"], $arrQuests)) {
			$arrTitulo[] = "<li><a href=\"?id=" . $abre["ID"] . "\">$icos" . htmlentities($abre["Title"]) . "</a></li>";
		}
	}
	if(count($arrTitulo) > 0) {
		$txtRetorno .= "<div style=\"text-align:center;\">" . $locale[TXT_MIS_ABRE] . " (" . count($arrTitulo) . ")</div><ul>";
		$txtRetorno .= implode("", $arrTitulo);
		$txtRetorno .= "</ul>";
	}
	
	return $txtRetorno;
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
