<?php

class M_datos_fisicos extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('db'), 'datos_fisicos');
    }
    
    public function listardatosfisicos($id_usuario)
    {
        if (!$id_usuario) {
            return null;
        }

        $resultado = $this->find(['id_usuario = ?', $id_usuario]);

        return $resultado;
    }

    public function datosfisicosexisten($id_usuario)
    {
        return $this->count(['id_usuario = ?', $id_usuario]) > 0;
    }

    public function createorupdatedatosfisicos($id_usuario, $data)
    {
        $this->load(['id_usuario = ?', $id_usuario]);

        if (!$this->dry()) {
            $this->copyFrom($data);
        } else {
            $this->reset();
            $this->id_usuario = $id_usuario;
            $this->copyFrom($data);
        }

        return $this->save();
    }
}
