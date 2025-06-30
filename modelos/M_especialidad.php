<?php

class M_especialidad extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'especialidades');
    }

    public function mostrarEspecialidad(){
        $sql = "SELECT *FROM especialidades p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }

    // Crear nueva especialidad
    public function crearEspecialidad($data) {
        $this->reset();
        $this->nombre_esp = $data['nombre_esp'];
        $this->estado = $data['estado'];
        $this->save();
        return $this->id_especialidad;
    }

    // Actualizar especialidad
    public function actualizarEspecialidad($data) {
        $this->load(['id_especialidad=?', $data['id_especialidad']]);
        if (!$this->dry()) {
            $this->nombre_esp = $data['nombre_esp'];
            $this->estado = $data['estado'];
            $this->save();
        }
    }

    // Eliminar especialidad (cambio de estado a 'I')
    public function eliminarEspecialidad($id) {
        $this->load(['id_especialidad=?', $id]);
        if (!$this->dry()) {
            $this->estado = 'I';
            $this->save();
        }
    }

    // Buscar especialidad por ID
    public function buscarEspecialidadPorId($id) {
        $this->load(['id_especialidad=?', $id]);
        return $this->cast();
    }
}
