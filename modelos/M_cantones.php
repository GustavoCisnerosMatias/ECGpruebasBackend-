<?php

class M_cantones extends \DB\SQL\Mapper {
    public function __construct(){
        parent::__construct(\Base::instance()->get('DB'), 'cantones');
    }

    public function obtenerCantones($id_provincia){
        $sql = "SELECT *FROM cantones p WHERE p.estado = 'A'and p.id_provincia=?";
        return $this->db->exec($sql,$id_provincia);
        
    }
}
