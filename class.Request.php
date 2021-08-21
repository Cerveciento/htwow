<?php

/**
 * Esta clase maneja los datos recibidos por medio de POST o GET.
 * Almacena los elementos en sendos arrays y un tercer array llamado REQUEST.
 * 
 *<p>Una vez instanciada la clase, se puede acceder a los elementos por medio 
 * del método get, los arrays o directamente como propiedades del objeto.</p>
 * <h4>Ejemplo</h4>
 * <code>
 * $REQ = Request::getInstancia(Request::FILTRO_MAYUSCULAS);	// Pasa a mayúsculas
 * echo $REQ->get("nombre", "No establecido");
 * echo $REQ->apellidos . " " . $REQ->POST["direccion"];
 * </code>
 * ***** IMPORTANTE *****
 * Esta clase no realiza comprobaciones de seguridad ni proporciona protección ante 
 * ningún tipo de ataque, por lo que su uso está completamente DESACONSEJADO en 
 * cualquier proyecto con acceso público.
 */
class Request
{
    const FILTRO_NO = 0;
    const FILTRO_MAYUSCULAS = 1;
    const FILTRO_MINUSCULAS = 2;
    const FILTRO_NO_ESPACIOS = 4;
    const FILTRO_ENTERO = 8;
    const FILTRO_NUMERO = 16;
    const FILTRO_BOOL = 32;

    private static $instancia = null;
    private $filtro = 0;
    public $GET;
    public $POST;
    public $REQUEST;
    /**
     * 
     * @param int $filtro
     * @return Request
     */
    public static function getInstancia($filtro = Request::FILTRO_NO_ESPACIOS)
    {
        if (self::$instancia === null) {
            self::$instancia = new Request($filtro);
        } else {
            self::$instancia->filtro = $filtro;
        }
        return self::$instancia;
    }

    /**
     * Constructor de la clase <b>Request</b>.
     * 
     * <p>Recupera los datos de <i>$_GET</i> y <i>$_POST</i>, limpia sus valores 
     * y los pasa a los arrays <i>$this->GET</i>, <i>$this->POST</i> y 
     * <i>$this->REQUEST</i>. También crea propiedades en el objeto con estos 
     * valores.</p>
     * <h4>Ejemplo</h4>
     * <code>
     * $REQ = Request::getInstancia(Request::FILTRO_MAYUSCULAS);	// Pasa a mayúsculas
     * echo $REQ->get("nombre", "No establecido");
     * echo $REQ->apellidos . " " . $REQ->POST["direccion"];
     * </code>
     * @param integer $filtro
     * (Opcional = Request::FILTRO_NO) Filtro aplicado
     */
    private function __construct($filtro = Request::FILTRO_NO)
    {
        $this->filtro = $filtro;

        $GET = $_GET;
        $POST = $_POST;
        $REQUEST = $_REQUEST;

        $this->GET = array();
        $this->POST = array();
        $this->REQUEST = array();

        if (is_array($GET)) {
            foreach ($GET as $clave => $valor) {
                $this->{$clave} = $this->_limpiar($valor, "[]{}\"\n*<>");

                $this->GET[$clave] = $this->{$clave};
                $this->REQUEST[$clave] = $this->{$clave};
            }
        }
        if (is_array($POST)) {
            foreach ($POST as $clave => $valor) {
                $this->{$clave} = $this->_limpiar($valor, "[]{}\"\n*<>");
                $this->POST[$clave] = $this->{$clave};
                $this->REQUEST[$clave] = $this->{$clave};
            }
        }
        if (is_array($REQUEST)) {
            foreach ($REQUEST as $clave => $valor) {
                $this->{$clave} = $this->_limpiar($valor, "[]{}\"\n*<>");
                $this->REQUEST[$clave] = $this->{$clave};
            }
        }
    }
    /**
     * Evita que el objeto pueda ser clonado.
     */
    public function __clone()
    {
    }
    /**
     * Evita la deserialización
     * 
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Esto es un Singleton. No se puede deserializar.");
    }

    /**
     * Aplica el filtro indicado a un valor.
     * 
     * @param mixed $valor
     * <p>Valor a filtrar</p>
     * @param string $filtro
     * <p>Filtro que se aplica</p>
     * @return string
     * <p>El valor filtrado</p>
     */
    private function _filtrar($valor, $filtro)
    {
        if ($valor !== null) {
            if (is_array($valor)) {
                foreach ($valor as $clave => $val) {
                    $valor[$clave] = $this->_filtrar($val, $filtro);
                }
            } else {
                if ($filtro & Request::FILTRO_MAYUSCULAS) {
                    $valor = mb_strtoupper($valor, "UTF-8");
                }
                if ($filtro & Request::FILTRO_MINUSCULAS) {
                    $valor = mb_strtolower($valor, "UTF-8");
                }
                if ($filtro & Request::FILTRO_NO_ESPACIOS) {
                    $valor = trim($valor);
                }
                if ($filtro & Request::FILTRO_ENTERO) {
                    $valor = Request::filtrarEntero($valor);
                }
                if ($filtro & Request::FILTRO_NUMERO) {
                    $valor = Request::filtrarNumero($valor);
                }
                if ($filtro & Request::FILTRO_BOOL) {
                    $valor = Request::filtrarBool($valor);
                }
            }
        }
        return $valor;
    }
    public function &__get($nombre)
    {
        if (!isset($this->{$nombre})) {
            $this->{$nombre} = null;
        }
        return $this->{$nombre};
    }
    /**
     * Devuelve el valor contenido en <i>$nombre</i> o el valor indicado en 
     * <i>$valor</i> si <i>$nombre</i> no existe.
     * 
     * <h4>Ejemplo</h4>
     * <code>
     * $municipio = $REQ->get("municipio", "MADRID", Request::FILTRO_MAYUSCULAS);
     * </code>
     * @param string $nombre
     * <p>Nombre del elemento a devolver</p>
     * @param mixed $valor (Opcional = <b>""</b>)
     * <p>Valor asignado al elemento en caso de no existir y devuelto por el 
     * método</p>
     * @param integer $filtro (Opcional = <b>null</b>)
     * <p>Filtro aplicado al valor. Si se omite, se aplica el filtro general del 
     * objeto</p>
     * @return string
     * <p>El valor del elemento</p>
     */
    public function get($nombre, $valor = "", $filtro = null)
    {
        if ($filtro === null) {
            $filtro = $this->filtro;
        }
        $valor = $this->_filtrar($valor, $filtro);
        if (strpos($nombre, "[") !== false) {
            list($izq, $der) = explode("[", $nombre);
            $der = substr($der, 0, -1);
            if (!isset($this->{$izq}[$der])) {
                $this->{$izq}[$der] = $valor;
                $this->REQUEST[$izq][$der] = $valor;
            }
            $retorno = $this->{$izq}[$der];
        } else {
            if (!isset($this->{$nombre})) {
                $this->{$nombre} = $valor;
                $this->REQUEST[$nombre] = $valor;
            }
            $retorno = $this->{$nombre};
        }
        return $this->_filtrar($retorno, $filtro);
    }
    public function getArray($prefijo = "")
    {
        $resp = array();
        $claves = array_keys($this->REQUEST);
        $tamaPrefijo = strlen($prefijo);
        foreach ($claves as $clave) {
            if (substr($clave, 0, $tamaPrefijo) == $prefijo) {
                $resp[$clave] = $this->get($clave);
            }
        }
        return $resp;
    }
    /**
     * Asigna un valor a 
     * @param string $nombre
     * @param mixed $valor
     */
    public function __set($nombre, $valor)
    {
        $filtro = $this->filtro;
        $valor = $this->_filtrar($valor, $filtro);

        if (strpos($nombre, "[") !== false) {
            list($izq, $der) = explode("[", $nombre);
            $der = substr($der, 0, -1);
            $this->{$izq}[$der] = $valor;
            $this->REQUEST[$izq][$der] = $valor;
            if (isset($_GET[$izq][$der])) {
                $_GET[$izq][$der] = $valor;
                $this->GET[$izq][$der] = $valor;
            }
            if (isset($_POST[$izq][$der])) {
                $_POST[$izq][$der] = $valor;
                $this->POST[$izq][$der] = $valor;
            }
            $_REQUEST[$izq][$der] = $valor;
        } else {
            $this->{$nombre} = $valor;
            $this->REQUEST[$nombre] = $valor;
            if (isset($_GET[$nombre])) {
                $_GET[$nombre] = $valor;
                $this->GET[$nombre] = $valor;
            }
            if (isset($_POST[$nombre])) {
                $_POST[$nombre] = $valor;
                $this->POST[$nombre] = $valor;
            }
            $_REQUEST[$nombre] = $valor;
        }
    }
    /**
     * 
     * @param string $nombre
     * @return boolean
     */
    public function existe($nombre)
    {
        if (strpos($nombre, "[") !== false) {
            list($izq, $der) = explode("[", $nombre);
            $der = substr($der, 0, -1);
            $existe = isset($this->REQUEST[$izq][$der]);
        } else {
            $existe = isset($this->REQUEST[$nombre]);
        }
        return $existe;
    }
    public function existeConDato($nombre)
    {
        $existe = $this->existe($nombre);
        if ($existe && trim($this->get($nombre, "")) === "") {
            $existe = false;
        }
        return $existe;
    }
    /**
     * Borra un dato de <i>$_GET</i>, <i>$_POST</i>, <i>$_REQUEST</i> y de las 
     * propiedades del objeto.
     * 
     * <h4>Ejemplo</h4>
     * <code>
     * $REQ->borrar("provincia");
     * </code>
     * @param string $nombre
     * <p>Elemento a borrar.</p>
     */
    public function borrar($nombre)
    {
        if (strpos($nombre, "[") !== false) {
            list($izq, $der) = explode("[", $nombre);
            $der = substr($der, 0, -1);
            if (isset($this->{$izq}[$der])) {
                unset($this->{$izq}[$der]);
            }
            if (isset($this->REQUEST[$izq][$der])) {
                unset($this->REQUEST[$izq][$der]);
                unset($_REQUEST[$izq][$der]);
            }
            if (isset($this->GET[$izq][$der])) {
                unset($this->GET[$izq][$der]);
                unset($_GET[$izq][$der]);
            }
            if (isset($this->POST[$izq][$der])) {
                unset($this->POST[$izq][$der]);
                unset($_POST[$izq][$der]);
            }
        } else {
            if (isset($this->{$nombre})) {
                unset($this->{$nombre});
            }
            if (isset($this->REQUEST[$nombre])) {
                unset($this->REQUEST[$nombre]);
                unset($_REQUEST[$nombre]);
            }
            if (isset($this->GET[$nombre])) {
                unset($this->GET[$nombre]);
                unset($_GET[$nombre]);
            }
            if (isset($this->POST[$nombre])) {
                unset($this->POST[$nombre]);
                unset($_POST[$nombre]);
            }
        }
    }
    public function borrarPatron($patron)
    {
        $claves = array_keys($this->REQUEST);
        foreach ($claves as $clave) {
            if (strpos($clave, $patron) !== false) {
                $this->borrar($clave);
            }
        }
    }
    /**
     * Borra todos los datos de <i>$_GET</i>, <i>$_POST</i>, <i>$_REQUEST</i> y de los 
     * miembros del objeto.
     * 
     * <h4>Ejemplo</h4>
     * <code>
     * $REQ->borrarPOST();
     * </code>
     */
    public function borrarPOST()
    {
        foreach ($this->POST as $clave => $valor) {
            unset($this->{$clave});
            unset($this->POST[$clave]);
            unset($_POST[$clave]);
            if (isset($this->REQUEST[$clave])) {
                unset($this->REQUEST[$clave]);
                unset($_REQUEST[$clave]);
            }
        }
    }
    public function borrarGET()
    {
        foreach ($_GET as $clave => $valor) {
            unset($this->{$clave});
            unset($this->GET[$clave]);
            unset($_GET[$clave]);
            if (isset($this->REQUEST[$clave])) {
                unset($this->REQUEST[$clave]);
                unset($_REQUEST[$clave]);
            }
        }
    }
    public function borrarREQUEST()
    {
        $claves = array_keys($_REQUEST);
        foreach ($claves as $clave) {
            $this->borrar($clave);
        }
    }

    private function _limpiar($texto, $permitidoAdicional = "")
    {
        return $texto;
        if (!is_array($texto)) {
            json_decode($texto, true);
            if (json_last_error() == 0) {
                $permitidoAdicional .= '"{}[]:';
            }
        }
        if (is_array($texto)) {
            foreach ($texto as $clave => $valor) {
                $texto[$clave] = $this->_limpiar($valor, $permitidoAdicional);
            }
        } else {
            $texto = $this->_limpiarTexto($texto, $permitidoAdicional);
        }
        return $texto;
    }
    /**
     * <p>Limpia un texto de caracteres no permitidos
     * permite letras, numeros, espacios, acentos, eñes, guiones y signos de puntuación.</p>
     *
     * @param string $texto <p>Texto a limpiar</p>
     * @param string $permitido_adicional (Opcional = <b>""</b>) <p>Caracteres adicionales permitidos</p>
     * @return string <p>El texto limpio</p>
     */
    private function _limpiarTexto($texto, $permitido_adicional = "")
    {
        //compruebo que los caracteres sean los permitidos
        $permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_.:,; áéíúóÁÉÍÓÚñÑºª/@()'" . $permitido_adicional;
        return $this->_limpiarEstricto($texto, $permitidos);
    }
    /**
     * Asegura un valor booleano
     * @param mixed $valor
     */
    static private function filtrarBool($valor)
    {
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }
    /**
     * <p>Limpia un entero de caracteres no permitidos.
     * Permite numeros.</p>
     *
     * @param string $num <p>entero a limpiar</p>
     * @return float <p>entero limpio</p>
     */
    static private function filtrarEntero($num)
    {
        //compruebo que los caracteres sean los permitidos
        $permitidos = "0123456789";
        return Request::_limpiarEstricto($num, $permitidos);
    }
    /**
     * <p>Limpia un número de caracteres no permitidos.
     * Permite numeros y punto.</p>
     *
     * @param string $num <p>número a limpiar</p>
     * @return integer <p>número limpio</p>
     */
    static private function filtrarNumero($num)
    {
        //compruebo que los caracteres sean los permitidos
        $permitidos = "0123456789.";
        return Request::_limpiarEstricto($num, $permitidos);
    }
    static private function _limpiarEstricto($texto, $permitidos)
    {
        $texto = str_replace('\t', "", $texto);
        for ($i = 0; $i < mb_strlen($texto, "UTF8"); $i++) {
            $letra = mb_substr($texto, $i, 1, "UTF8");
            if (mb_strpos($permitidos, mb_substr($texto, $i, 1, "UTF8"), 0, "UTF8") === false) {

                $texto = mb_substr($texto, 0, $i, "UTF8") . mb_substr($texto, $i + 1, null, "UTF8");
                $i--;
            }
        }
        return $texto;
    }
}
