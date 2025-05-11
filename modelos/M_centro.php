<?php

class M_centro extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'centros_hospitalarios');
    }

    public function mostrarCentro() {
        $sql = "SELECT *FROM centros_hospitalarios p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }

     /* // Crear un nuevo usuario
     public function createpais($data)
     {
         $this->copyFrom($data);
         return $this->save();
     } */
}
