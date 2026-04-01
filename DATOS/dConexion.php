<?php

class dConexion {
    
    private $servidor;
    private $basedatos;
    private $puerto;
    private $usuario;
    private $clave;
    
    function __construct(){
        $this->servidor="localhost";
        $this->basedatos="bdprueba5";
        $this->puerto="3306";
        $this->usuario="sebastian";
        $this->clave="destrolla";
    }
    
    function Conectar(){
        $con = mysqli_connect($this->servidor, $this->usuario, $this->clave, $this->basedatos, $this->puerto);
        if (!$con) {
            die('ERROR en la conexión de base de datos: ' . mysqli_connect_error());
        }
        return $con;
    }
}
