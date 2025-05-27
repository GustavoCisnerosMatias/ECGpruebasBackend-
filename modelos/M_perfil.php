<?php

class M_perfil extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'perfil');
    }

    public function agregarImagen($id_usuario, $foto) {
        $this->load(['id_usuario = ?', $id_usuario]);
        
        if ($this->dry()) {
            $this->id_usuario = $id_usuario;
            $this->foto = $foto;
            $this->save();
        } else {
            $this->foto = $foto;
            $this->update();
        }
        return true;
    }
    
    public function getIdUsuarioByid($id_usuario){
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return false; 
        }
        return $this->foto;
    }

}
