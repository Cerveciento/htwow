<html>
<head>
<LINK REL=StyleSheet HREF="htwow.css" TYPE="text/css">
<script src="js.js"></script>
</head>
<body>
<?php
include "config.php";
include "constantes.php";
include "class.BaseDatos.php";
include "class.Campo.php";
include "class.Pagina.php";
include "class.Request.php";
include "funciones.php";
include "iniciar.php";

echo "<h2>" . $locale[TXT_VERSION_WOW] . "</h2>";

$REQ = Request::getInstancia();

echo "<div class=\"parteSuperior\" style=\"flex-flow: wrap;\">";
// -------------- MISIONES -------------------
$mision = new Pagina(PAG_MISION);
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"php/quest.php\">" . $mision->getTituloPlural() . "</a></div>";
echo $mision->estadisticas();
echo "</div>";

echo "</div>";
exit;

// -------------- CRIATURA -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"creature_template.php\">Criatura</a></div>";
include("creature_template.php");
echo "</div>";
// -------------- SALUDO --------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"quest_greeting.php\">Saludos</a></div>";
include("quest_greeting.php");
echo "</div>";
// -------------- LOGROS --------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"achievement_reward.php\">Logros</a></div>";
include("achievement_reward.php");
echo "</div>";
// -------------- BROADCAST -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"broadcast_text.php\">Broadcast</a></div>";
include("broadcast_text.php");
echo "</div>";
// -------------- POI -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"points_of_interest.php\">Puntos de interés</a></div>";
include("points_of_interest.php");
echo "</div>";
// -------------- OBJETOS -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"gameobject_template.php\">Objetos</a></div>";
include("gameobject_template.php");
echo "</div>";
// -------------- ITEMS -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"item_template.php\">Items</a></div>";
include("item_template.php");
echo "</div>";
// -------------- NPC_TEXT -------------------
echo "<div class=\"caja\">";
echo "<div style=\"text-align:center;\"><a href=\"npc_text.php\">Textos NPC</a></div>";
include("npc_text.php");
echo "</div>";

echo "</div>";