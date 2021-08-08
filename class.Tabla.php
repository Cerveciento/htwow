<?php
/**
 * Esta clase crea y manipula tablas HTML
 * 
 * <h4>Ejemplo</h4>
 * <code>
 * $t = new Tabla();
 * $t->insFila();
 * $t->inCelda("Celda 1");
 * $t->insCelda("Celda 2");
 * $t->mostrar();
 * </code>
 * @version	1.0.6 (Marzo 2012)
 */
class Tabla {
    private $filas = array();
    
    private $id = null;
    private $clase = "";
    private $arrAtrib = array();

    public function __construct($id = NULL, $clase = NULL, $arrAtrib = array() ) {
        $this->id = $id;
        $this->clase = $clase;
        $this->arrAtrib = $arrAtrib;
    }
    public function __destruct() {
        unset($this->filas);
    }

    private function insAtributos( $a_Attrib ) {
        $str = '';
        foreach( $a_Attrib as $key=>$val ) {
            $str .= " $key=\"$val\"";
        }
        return $str;
    }
    public function delFila($fila) {
        array_splice($this->filas, $fila, 1);
    }
    public function insFila($clase = NULL, $a_Attrib = array() ) {
        if(!isset($a_Attrib["id"])) {
            $a_Attrib["id"] = "F_" . sizeof($this->filas);
        }
        $fila = new FilaTabla($clase, $a_Attrib);
        array_push($this->filas, $fila);
        return sizeof($this->filas) - 1;
    }
    /**
     * Inserta una celda en la fila actual
     * 
     * @param string $dato [Opcional = <b>""</b>]
     * <p>Dato que se inserta en la celda.</p>
     * @param string $clase [Opcional = <b>NULL</b>]
     * <p>Clase que se aplica al elemento <b>&lt;td&gt;</b> o <b>&lt;th&gt;</b> que crea la celda</p>
     * @param string $tipo [Opcional = <b>"td"</b>]
     * @param array $a_Attrib [Opcional = <b>array()</b>]
     */
    public function insCelda($dato = "", $clase = NULL, $tipo = "td", $a_Attrib = array() ) {
        if(count($this->filas) === 0) {
            $this->insFila();
        }
        $filaActual = $this->filas[ count( $this->filas ) - 1 ];
        if(is_array($tipo)) {
            $a_Attrib = $tipo;
            $tipo = "td";
        }
        if(!isset($a_Attrib["id"])) {
            $fil = sizeof($this->filas) - 1;
            $col = sizeof($filaActual->celdas);
            $a_Attrib["id"] = $this->id . "_C_" . $fil . "_" . $col;
        }
        $celda = new CeldaTabla( $dato, $tipo, $clase, $a_Attrib );
        // Añade la nueva celda a la fila de celdas
        array_push( $filaActual->celdas, $celda );
    }
    public function insCeldaTitulo($dato = "", $clase = NULL, $a_Attrib = array() ) {
        $this->insCelda($dato, $clase, "th", $a_Attrib);
    }

    public function getTabla() {
        $txtTabla = "<table" . ( !empty($this->id)? " id=\"$this->id\"": "" ) .
        ( !empty($this->clase)? " class=\"$this->clase\"": '' ) . $this->insAtributos( $this->arrAtrib ) . ">";
        
        foreach ($this->filas as $numFila => $fila) {
            $txtTabla .= $this->getFila($numFila);
        }
        $txtTabla .= "</table>";
        return $txtTabla;
    }
    public function mostrar() {
        echo $this->getTabla();
    }
    /**
     * 
     * @param int $numFila
     * @return string
     */
    public function getFila($numFila) {
        $fila = $this->filas[$numFila];
        $txtFila = "";
        $txtFila .= !empty($fila->clase) ? "<tr class=\"$fila->clase\"" : "<tr";
        $txtFila .= $this->insAtributos($fila->aAttr) . ">";
        $txtFila .= $this->getCeldasFila($numFila);
        $txtFila .= "</tr>";

        return $txtFila;
    }
    /**
     * Obtiene el código HTML de las celdas de una fila de la tabla
     * @param int $numFila
     * @return string
     */
    private function getCeldasFila($numFila) {
        $txt = "";
        foreach( $this->filas[$numFila]->celdas as $celda ) {
            $tag = ($celda->tipo == "td") ? "td": "th";
            $txt .= !empty($celda->clase) ? "<$tag class=\"$celda->clase\"" : "<$tag";
            $txt .= $this->insAtributos($celda->aAttr) . ">";
            $txt .= $celda->dato;
            $txt .= "</$tag>";
        }

        return $txt;
    }
}

/**
 * Clase que da soporte a una fila de la tabla
 * 
 * @version	1.0.6 (Marzo 2012)
 */
class FilaTabla {
    public $celdas = array();
    public $clase = NULL;
    public $aAttr = array();
    
    function __construct($clase = NULL, $aAttr = array()) {
        $this->celdas = array();
        $this->clase = $clase;
        $this->aAttr = $aAttr;
    }
    public function __destruct() {
        unset($this->celdas);
    }
}
/**
 * Clase que da soporte a una celda de la tabla
 * @version	1.0.6 (Marzo 2012)
 */
class CeldaTabla {
    /**
     * Contenido de la celda
     * @var string 
     */
    public $dato = "";
    /**
     * Tipo de celda
     * 
     * <p>Sólo puede ser <b>td</b> o <b>th</b>
     * @var string
     */
    public $tipo = "td";
    /**
     * Clase que se aplica al elemento <b>td</b> o <b>th</b>
     * @var string 
     */
    public $clase = NULL;
    /**
     * Array asociativo de atributos HTML para la celda
     * @var array
     */
    public $aAttr = array();

    function __construct($dato = "", $tipo = "td", $clase = NULL, $aAttr = array()) {
        if($tipo != "th") {
            $tipo = "td";
        }
        $this->dato = $dato;
        $this->tipo = $tipo;
        $this->clase = $clase;
        $this->aAttr = $aAttr;
    }
}
