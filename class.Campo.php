<?php
class Campo {
    private $titulo = "";
    
    private $tablaOrigen = "";
    private $idOrigen = "";
    private $campoOrigen = "";

    private $tablaDestino = "";
    private $idDestino = "";
    private $campoDestino = "";

    public function __construct($campo) {
        $this->titulo = $campo["titulo"];
        $this->tablaOrigen = $campo["tablaOrigen"];
        $this->idOrigen = $campo["idOrigen"];
        $this->campoOrigen = $campo["campoOrigen"];
        $this->tablaDestino = $campo["tablaDestino"];
        $this->idDestino = $campo["idDestino"];
        $this->campoDestino = $campo["campoDestino"];

    }
    /**
     * Get the value of titulo
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Get the value of tablaOrigen
     */
    public function getTablaOrigen()
    {
        return $this->tablaOrigen;
    }

    /**
     * Get the value of idOrigen
     */
    public function getIdOrigen()
    {
        return $this->idOrigen;
    }

    /**
     * Get the value of campoOrigen
     */
    public function getCampoOrigen()
    {
        return $this->campoOrigen;
    }


    /**
     * Get the value of tablaDestino
     */
    public function getTablaDestino()
    {
        return $this->tablaDestino;
    }

    /**
     * Get the value of idDestino
     */
    public function getIdDestino()
    {
        return $this->idDestino;
    }

    /**
     * Get the value of campoDestino
     */
    public function getCampoDestino()
    {
        return $this->campoDestino;
    }
}