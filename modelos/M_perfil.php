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
    //Buscarpor cedula devuelve id
    public function getIdUsuarioByid($id_usuario)
    {
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return false; // No se encontró ningún registro con ese id_usuario
        }
        return $this->foto; // Devolver la foto asociada al id_usuario
    }

}
