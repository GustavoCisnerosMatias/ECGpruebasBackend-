<?php

class M_med_pacie extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'medico_paciente');
    }

    // Método para guardar la relación medico_paciente
    public function guardarRelacion($id_medico, $id_usuario) {
        $this->reset();
        $this->id_medico = $id_medico;
        $this->id_paciente = $id_usuario;
        $this->estado = 'A';

        try {
            return $this->save(); // Guardar la relación en la tabla medico_paciente
        } catch (\Exception $e) {
            error_log('Error al guardar relación medico_paciente: ' . $e->getMessage());
            return false; // Error al guardar
        }
    }
    public function obtenerDatosMedicoPaciente($id_usuario) {
        $sql = "SELECT medico_paciente.id_medpaci, medico_paciente.id_medico, Usuarios.nombre, Usuarios.telefono, Usuarios.apellido, medico_paciente.estado, c.nombre_centro, e.nombre_esp, TO_BASE64(p.foto) 
        AS foto_base64 FROM medico_paciente 
        JOIN medicos ON medico_paciente.id_medico = medicos.id_medico 
        JOIN Usuarios ON medicos.id_usuario = Usuarios.id_usuario 
        JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro 
        JOIN especialidades e ON e.id_especialidad = medicos.id_especialidad 
        LEFT JOIN perfil p ON p.id_usuario = Usuarios.id_usuario 
        WHERE medico_paciente.id_paciente  = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }
    
    
    


    // Método para obtener los datos de los pacientes del medico  basado en el ID del usuario
    public function obtenerDatosPaciente($id_usuario) {
        $sql = "SELECT p.fecha_registro,u.id_usuario,u.nombre,u.apellido, u.telefono, u.correo_electronico,u.cedula,TO_BASE64(f.foto) AS foto_base64
            FROM Usuarios u 
            JOIN medico_paciente p ON p.id_paciente = u.id_usuario 
            JOIN medicos ON medicos.id_medico = p.id_medico 
            LEFT JOIN perfil f ON f.id_usuario = u.id_usuario
             WHERE medicos.id_usuario  = ? and p.estado = 'A'";
        return $this->db->exec($sql, [$id_usuario]);  // Utilizar el parámetro correctamente
    }


    public function eliminarRelacion($id_medpaci) {
    try {
        // Preparar la consulta SQL para eliminar el registro
        $sql = "DELETE FROM medico_paciente WHERE id_medpaci = ?";
        
        // Ejecutar la consulta
        $result = $this->db->exec($sql, [$id_medpaci]);

        // Verificar si se eliminó algún registro
        return $result > 0; // Retorna verdadero si se eliminó al menos un registro
    } catch (\Exception $e) {
        error_log('Error al eliminar relación medico_paciente: ' . $e->getMessage());
        return false; // Error en la eliminación
    }
}
    

 // Método para actualizar el estado de una relación médico-paciente
 public function actualizarEstado($id_medpaci, $estado) {
    // Verificar que el estado sea válido
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

  // Método para obtener los datos de los pacientes del medico  basado en el ID del usuario
/*   public function total_paceintes ($id_usuario) {
    $sql = "SELECT 
    mp.id_medico,
    mp.id_paciente,
    mp.fecha_registro
FROM 
    medico_paciente mp
JOIN 
    medicos m ON mp.id_medico = m.id_medico
WHERE 
    mp.id_medico = (
        SELECT id_medico 
        FROM medicos 
        WHERE id_usuario = ?
        LIMIT 1
    );";
    return $this->db->exec($sql, [$id_usuario]);  // Utilizar el parámetro correctamente
} */
    
}

   