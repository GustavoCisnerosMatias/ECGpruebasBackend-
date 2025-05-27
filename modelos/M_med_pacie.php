<?php

class M_med_pacie extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'medico_paciente');
    }

    public function guardarRelacion($id_medico, $id_usuario) {
        $this->reset();
        $this->id_medico = $id_medico;
        $this->id_paciente = $id_usuario;
        $this->estado = 'A';

        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al guardar relación medico_paciente: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerDatosMedicoPaciente($id_usuario) {
        $sql = "SELECT medico_paciente.id_medpaci, medico_paciente.id_medico, usuarios.nombre, usuarios.telefono, usuarios.apellido, medico_paciente.estado, c.nombre_centro, e.nombre_esp, TO_BASE64(p.foto) 
        AS foto_base64 FROM medico_paciente 
        JOIN medicos ON medico_paciente.id_medico = medicos.id_medico 
        JOIN usuarios ON medicos.id_usuario = usuarios.id_usuario 
        JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro 
        JOIN especialidades e ON e.id_especialidad = medicos.id_especialidad 
        LEFT JOIN perfil p ON p.id_usuario = usuarios.id_usuario 
        WHERE medico_paciente.id_paciente  = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function obtenerDatosPaciente($id_usuario) {
        $sql = "SELECT p.fecha_registro,u.id_usuario,u.nombre,u.apellido, u.telefono, u.correo_electronico,u.cedula,TO_BASE64(f.foto) AS foto_base64
            FROM usuarios u 
            JOIN medico_paciente p ON p.id_paciente = u.id_usuario 
            JOIN medicos ON medicos.id_medico = p.id_medico 
            LEFT JOIN perfil f ON f.id_usuario = u.id_usuario
             WHERE medicos.id_usuario  = ? and p.estado = 'A'";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function eliminarRelacion($id_medpaci) {
        try {
            $sql = "DELETE FROM medico_paciente WHERE id_medpaci = ?";
            $result = $this->db->exec($sql, [$id_medpaci]);
            return $result > 0;
        } catch (\Exception $e) {
            error_log('Error al eliminar relación medico_paciente: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id_medpaci, $estado) {
        if (!in_array($estado, ['A', 'I'])) {
            throw new Exception('Estado inválido');
        }

        $this->load(['id_medpaci = ?', $id_medpaci]);
        if ($this->dry()) {
            throw new Exception('Relación no encontrada');
        }

        $this->estado = $estado;
        $this->save();
    }
}
