<?php

class M_Provincias extends \DB\SQL\Mapper {
    public function __construct(){
        parent::__construct(\Base::instance()->get('DB'), 'provincias');
    }

    public function obtenerprovincias($id_pais){
        $sql = "SELECT *FROM provincias p WHERE p.estado = 'A'and p.id_pais=?";
        return $this->db->exec($sql,$id_pais);
        
    }
 
}
