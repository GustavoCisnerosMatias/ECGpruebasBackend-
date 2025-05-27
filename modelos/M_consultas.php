<?php

class M_consultas extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'consultas');
    }

    public function crearconsultayretornarid($data) {
        $this->copyFrom($data);
        $this->save();
        return $this->id_consulta;
    }

    public function crearreceta($data) {
        $receta = new \DB\SQL\Mapper($this->db, 'recetas');
        $receta->copyFrom($data);
        return $receta->save();
    }

    public function obteneridmedico($id_usuario) {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $result = $this->db->exec($sql, [$id_usuario]);
        return $result ? $result[0]['id_medico'] : null;
    }

    public function listarconsultas($id_medico, $fecha_ini, $fecha_fin) {
        $sql = "SELECT c.fecha_consulta, c.id_paciente, u.nombre, u.apellido, u.cedula 
                FROM consultas c 
                JOIN usuarios u ON u.id_usuario = c.id_paciente 
                WHERE c.id_medico = ? AND c.fecha_consulta BETWEEN ? AND ?";
        return $this->db->exec($sql, [$id_medico, $fecha_ini, $fecha_fin]);
    }

    public function listarrecetas($id_paciente, $anio) {
        $sql = "SELECT r.fecha_receta, r.fecha_vencimiento, r.dosis, r.frecuencia, r.duracion, 
                       r.instrucciones, r.observaciones, m.codigo, m.denominacion_comun_internacional, 
                       m.forma_farmaceutica, c.motivo_consulta, u.nombre, u.apellido, u.cedula, u.fecha_nacimiento, u.genero 
                FROM recetas r 
                JOIN consultas c ON c.id_consulta = r.id_consulta 
                JOIN usuarios u ON u.id_usuario = c.id_paciente
                LEFT JOIN medicamentos m ON m.id_medic = r.id_medic 
                WHERE c.id_paciente = ? AND YEAR(r.fecha_receta) = ?";
        return $this->db->exec($sql, [$id_paciente, $anio]);
    }

    public function getantecedentespersonales($id_paciente) {
        $sql = "SELECT a.*, u.fecha_nacimiento, u.genero, p.foto, u.nombre, u.apellido, u.cedula
                FROM consultas a 
                JOIN usuarios u ON u.id_usuario = a.id_paciente
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
