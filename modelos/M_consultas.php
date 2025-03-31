<?php

class M_consultas extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'Consultas');
    }

     ////Crear consulta
    public function crearConsultaYRetornarId($data) {
        $this->copyFrom($data);
        $this->save();
        return $this->id_consulta; // Retorna el id de la consulta recién creada
    }

    public function crearReceta($data) {
        $receta = new \DB\SQL\Mapper($this->db, 'recetas');
        $receta->copyFrom($data);
        return $receta->save();
    }



    public function obtenerIdMedico($id_usuario) {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $result = $this->db->exec($sql, [$id_usuario]);
        return $result ? $result[0]['id_medico'] : null;
    }

   
    public function listarConsultas($id_medico, $fecha_ini, $fecha_fin) {
        $sql = "SELECT c.fecha_consulta, c.id_paciente ,u.nombre, u.apellido,u.cedula FROM Consultas c 
        JOIN Usuarios u ON u.id_usuario =c.id_paciente WHERE c.id_medico = ? AND c.fecha_consulta BETWEEN ? AND ?";
        return $this->db->exec($sql, [$id_medico, $fecha_ini, $fecha_fin]);
    }



     ///RECETA


// Método para listar recetas de un paciente específico en un año determinado
public function listarRecetas($id_paciente, $anio) {
    $sql = "SELECT r.fecha_receta, r.fecha_vencimiento, r.dosis, r.frecuencia, r.duracion, 
       r.instrucciones, r.observaciones, m.codigo, m.denominacion_comun_internacional, 
       m.forma_farmaceutica, c.motivo_consulta, u.nombre, u.apellido, u.cedula, u.fecha_nacimiento, u.Genero 
FROM recetas r 
JOIN Consultas c ON c.id_consulta = r.id_consulta 
JOIN Usuarios u ON u.id_usuario = c.id_paciente
LEFT JOIN medicamentos m ON m.id_medic = r.id_medic 
WHERE c.id_paciente = ? AND YEAR(r.fecha_receta) = ?;
";
            
    return $this->db->exec($sql, [$id_paciente, $anio]);
}
  

public function getAntecedentesPersonales($id_paciente) {
    $sql = "SELECT a.*, u.fecha_nacimiento, u.Genero, p.foto,u.nombre,u.apellido,u.cedula
            FROM Consultas a 
            JOIN Usuarios u ON u.id_usuario = a.id_paciente
            JOIN perfil p ON u.id_usuario = p.id_usuario 
            WHERE a.id_paciente = ?";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id_paciente]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result === false) {
        error_log("Error al obtener antecedentes personales: " . $this->db->errorInfo()[2]);
        return null;
    }

    foreach ($result as &$row) {
        if (isset($row['foto']) && $row['foto'] !== null) {
            $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
        }
    }

    return $result; 
}

    
}
?>