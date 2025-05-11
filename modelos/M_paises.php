<?php

class M_paises extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'paises');
    }

    public function obtenerpais() {
        $sql = "SELECT *FROM paises p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }

     /* // Crear un nuevo usuario
     public function createpais($data)
     {
         $this->copyFrom($data);
         return $this->save();
     } */
}
