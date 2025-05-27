<?php

class M_parametros extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'parametros');
    }

   
    public function createmedico($data){
        $this->copyFrom($data);
        return $this->save();
    }

    public function crearParametro($data){
        $this->copyFrom($data);
        return $this->save();
    }

    public function eliminarParametro($id_parametro){
        $this->load(['id_parametro = ?', $id_parametro]);
        if ($this->dry()) {
            return false; 
        }
        return $this->erase();  
    }

}
