<?php

class M_parametros extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'parametros');
    }

   
     // Crear un nuevo usuario
     public function createmedico($data)
     {
         $this->copyFrom($data);
         return $this->save();
     }

     // Crear un nuevo parámetro
    public function crearParametro($data)
    {
        // Copiar los datos del nuevo parámetro
        $this->copyFrom($data);
        return $this->save();
    }

    public function eliminarParametro($id_parametro)
{
    // Buscar el parámetro por su ID
    $this->load(['id_parametro = ?', $id_parametro]);
    if ($this->dry()) {
        return false; // No se encontró el parámetro
    }
    return $this->erase(); // Elimina el parámetro
}

}
