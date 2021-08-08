<?php  
/**
 * Clase para manipular bases de datos MySQL
 * 
 */
class BaseDatos {
    /**
     * Número de filas afectadas por la última sentencia SQL
     * @var integer 
     */
    public $filasAfectadas = 0;
    /**
     * Nombre del servidor de Base de Datos.
     * @var string
     */
    private $servidor = "";
    /**
     * Usuario para la conexión.
     * @var string
     */
    private $usuario = "";
    /**
     * Contraseña del usuario.
     * @var string 
     */
    private $contrasena = "";
    /**
     * Nombre de la base de datos a utilizar.
     * @var string 
     */
    private $baseDatos = "";
    /**
     * Charset que se utiliza en la conexión.
     * @var string 
     */
    private $charset  = "UTF-8";

    private $errTxt = "";

    /**
     * 
     * @var PDO
     */
    private $bd = null;
    /**
     * 
     * @var PDOStatement
     */
    private $sentencia = null;
    /**
     * Sentencia SQL preparada
     * @var string
     */
    private $sql = "";
    /**
     * Datos que se utilizarán en la ejecución de la sentencia SQL preparada.
     * @var array
     */
    private $datos = array();
    /**
     * Texto de la sentencia SQL con los datos sustituidos
     * @var string
     */
    private $ultimoSQL = "";

    /**
     * Modo DEBUG de las consultas
     * @property bool
     */
    private $modoDebug = true;
    
    private static $recuentoErrores = 0;

    /**
     * Constructor de la clase BaseDatos.
     * 
     * <p>Establece los valores de las propiedades privadas según la variable 
     * global <i>$conf</i></p>
     * <p>Realiza la conexión.</p>
     * @param array $conf
     * @return BaseDatos
     */
    public function __construct(array $conf) {
        if(!is_array($conf)) {
            die('SIN CONFIGURACIÓN DE ACCESO A LA BASE DE DATOS');
        }
        
        $this->servidor = $conf["server"];
        $this->usuario = $conf["user"];
        $this->contrasena = $conf["password"];
        $this->baseDatos = $conf["database"];
        $this->charset = $conf["charset"];
        
        $this->connect();
        return $this;
    }

    /**
     * Conecta con la base de datos
     */
    public function connect() {
        // Si no hay conexión previa
        if(!$this->bd) {
            try {
                $this->bd = new PDO("mysql:host=" . $this->servidor . 
                        ";dbname=" . $this->baseDatos . 
                        ";charset=$this->charset", 
                        $this->usuario, $this->contrasena, 
                        array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            } catch (PDOException $e) {
                echo "Error de conexión a <b>$this->servidor</b> -> " . 
                        $e->getMessage();
                die;
            }
        }
    }
    /**
     * Ejecuta la sentencia SQL sobre la conexión a la BD
     * 
     * @param string $sql
     * @param array $datos (Opcional = null)
     * @return PDOStatement
     */
    public function query(string $sql, array $datos = null) : PDOStatement {
        $this->sql = $sql;
        $this->datos = $datos;
        $this->sentencia = $this->bd->prepare($sql);
        $correcto = $this->sentencia->execute($datos);
        $this->ultimoSQL = $this->getSQL();
        if (!$correcto) {
            $this->oops("<b>Fallo en la consulta MySQL:</b> $sql");
        }
        $this->filasAfectadas = $this->sentencia->rowCount();
        return $this->sentencia;
    }
    /**
     * Obtiene la siguiente fila de la consulta indicada o de la última 
     * consulta ejecutada.
     * 
     * @param PDOStatement $consulta (opcional = null)
     * @return array|false
     */
    public function fetchArray(PDOStatement $consulta = null) {
        if ($consulta != null) {
            $this->sentencia = $consulta;
        }

        return $this->sentencia->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ejecuta la consulta y devuelve la primera fila.
     *
     * @param string $sql
     * @param array $datos
     * @return array
     */
    public function queryFirst(string $sql, array $datos = null) {
        $this->query($sql, $datos);
        return $this->fetchArray();
    }

    /**
     * <p>Ejecuta la consulta y devuelve el primer campo del primer registro obtenido.</p>
     * @param string $sql	Consulta SQL a ejecutar.
     * @return string $dato			Dato consultado o <b>NULL</b>
     */
    public function queryCampo($sql, $datos = null) {
        $this->query($sql, $datos);
        $fila = $this->fetchArray();
        if(is_array($fila)) {
            $claves = array_keys($fila);
            $dato = $fila[$claves[0]];
        } else {
            $dato = null;
        }
        return $dato;
    }

    /**
     * Ejecuta una sentenia UPDATE sobre la tabla indicada usando el array de 
     * datos como valores de los campos y aplicando la cláusula WHERE indicada.
     * Devuelve la cantidad de filas afectadas.
     * 
     * @param string $tabla
     * @param array $datos
     * @param string $where
     * @return int
     */
    public function queryUpdate(string $tabla, array $datos, string $where = '1') : int {
        $q = "UPDATE `" . $this->prefijo . $tabla . "` SET ";
        $campos = array_keys($datos);
        foreach ($campos as $campo) {
            $q .= "`$campo` = :$campo , ";
        }
        $q = rtrim($q, ", ") . " WHERE " . $where;

        $this->query($q, $datos);
        return $this->filasAfectadas;
    }

    /**
     * Ejecuta una sentencia DELETE sobre la tabla indicada aplicando la 
     * cláusula WHERE.
     * Devuelve la cantidad de filas afectadas.
     * 
     * @param string $tabla
     * @param string $where
     * @return int 
     */
    public function queryDelete(string $tabla, string $where='1') : int {
        $q = "DELETE FROM `" . $this->prefijo . $tabla . "` WHERE " . $where;
        $this->query($q);
        return $this->filasAfectadas;
    }

    /**
     * Ejecuta una sentencia INSERT sobre la tabla indicada usando el array de 
     * datos como valores para los campos.
     * Devuelve el id del registro insertado o <b>false</b> en caso de error.
     * 
     * @param string $tabla
     * @param array $datos
     * @return int|false id del registro insertado o false en caso de error.
     */
    public function queryInsert(string $tabla, array $datos) : int {
        $campos = array_keys($datos);
        $q = "INSERT INTO `" . $this->prefijo . $tabla . "` ";
        $q .= "(" . implode(", ", $campos);
        $q .= ") VALUES (";
        $q .= ":" . implode(", :", $campos) . ")";

        $id = false;
        if($this->query($q, $datos)) {
            $id = $this->bd->lastInsertId();
        }
        return $id;
    }

    /**
     * Ejecuta una sentencia INSERT ON DUPLICATE KEY UPDATE sobre la tabla 
     * indicada usando el array de datos como valores para los campos.
     * Devuelve el id del registro insertado o <b>false</b> en caso de error.
     * 
     * @param type $tabla
     * @param type $datos
     * @return int|false id del registro insertado o false en caso de error.
     */
    public function queryInsertUpdate(string $tabla, array $datos) : int {
        $campos = array_keys($datos);
        $q = "INSERT INTO `" . $this->prefijo . $tabla . "` ";
        $q .= "(" . implode(", ", $campos);
        $q .= ") VALUES (";
        $q .= ":" . implode(", :", $campos) . ")";
        $q .= " ON DUPLICATE KEY UPDATE ";
        foreach ($campos as $campo) {
            $q .= "`$campo` = :$campo , ";
        }
        $q = rtrim($q, ', ');

        $id = false;
        if($this->query($q, $datos)) {
            $id = $this->bd->lastInsertId();
        }
        return $id;
    }

    /**
     * Lanza el mensaje de error si está activado el modo debug.
     * 
     * @param string $msg (opcional = "")
     * Mensaje de error a mostrar.
     */
    private function oops(string $msg = "") {
        if($this->bd->errorCode() != "00000") {
            $this->errTxt = $this->bd->errorInfo();
        } else {
            $this->errTxt = $this->sentencia->errorInfo();
        }
        if($this->modoDebug) {
            echo '<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">';
            echo '<tr><th colspan=2>Error BaseDatos</th></tr>';
            echo '<tr><td align="right" valign="top" style="width:130px;">Mensaje:</td><td>' . $msg . '</td></tr>';
            echo "<tr><td align=\"right\" valign=\"top\">SQL:</td><td>" . $this->getSQL() . "</td></tr>";
            if(count($this->errTxt) > 0) {
                echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>' . $this->errTxt[2] . '</td></tr>';
                echo '<tr><td align="right" valign="top" nowrap>MySQL Error Nº:</td><td>' . $this->errTxt[1] . '</td></tr>';
            }
            echo '<tr><td align="right">Fecha:</td><td>' . date("l, F j, Y \a\\t g:i:s A") . '</td></tr>';
            echo '<tr><td align="right">Script:</td><td><a href="' . @$_SERVER['REQUEST_URI'] . '">' . @$_SERVER['REQUEST_URI'] . '</a></td></tr>';
            if(strlen(@$_SERVER['HTTP_REFERER']) > 0) {
                echo '<tr><td align="right">Referer:</td><td><a href="' . @$_SERVER['HTTP_REFERER'].'">' . @$_SERVER['HTTP_REFERER'] . '</a></tr></td>';
            }
            echo "<tr><td colspan=\"2\"><pre>";
            debug_print_backtrace();
            echo "</pre></td></tr>";
            echo '</table>';
        }
        self::$recuentoErrores++;
        if(self::$recuentoErrores > 4) {
            die("========== DEMASIADOS ERRORES ==========");
        }
    }
    /**
     * Devuelve la verdadera sentencia SQL que ha fallado. 
     * 
     * @return string
     */
    private function getSQL() : string {
        $campos = array();
        $valores = array();
         
        // Una expresión regular por cada parámetro
        if(is_array($this->datos)) {
            foreach ($this->datos as $clave => $valor) {
                if (is_string($clave)) {
                    $campos[] = "/:" . $clave . "/";
                } else {
                    $campos[] = "/[?]/";
                }
                $valores[] = "'" . $valor . "'";
            }
        }		 
        $sql = preg_replace($campos, $valores, $this->sql, 1);
        return $sql;	
    }
    /**
     * <p>Establece el modo debug. Afectando a la visualización de los mensajes 
     * de error.</p>
     * @param bool $modo	(Opcional) Modo debug establecido. Por defecto 
     * <b>true</b>.
     */
    public function setDebug(bool $modo = true) {
        $this->modoDebug = $modo;
    }
    /**
     * Asigna el prefijo usado con los nombre de tablas en las sentencias 
     * INSERT, UPDATE y DELETE
     * 
     * @param string $prefijo
     */
    public function setPrefijo(string $prefijo) {
        $this->prefijo = $prefijo;
    }
    /**
     * Devuelve una cadena de texto con la última sentencia SQL ejecutada.
     * 
     * @return string
     */
    public function getUltimoSQL() : string {
        return $this->ultimoSQL;
    }
}