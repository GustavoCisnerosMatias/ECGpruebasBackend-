<?php

class M_alergias extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'Alergias');
    }

    public function obtenertipoalergias() {
        $sql = "SELECT *FROM TiposAlergia ";
        return $this->db->exec($sql);
    }

    public function obtenerAlergias($id_paciente) {
        // Prepara la consulta SQL
        $sql = "SELECT t.NombreTipo, t.Descripcion, a.*, m.codigo, m.denominacion_comun_internacional, p.foto, u.fecha_nacimiento, u.Genero 
                FROM Alergias a 
                JOIN TiposAlergia t ON t.id_tipo = a.id_tipo 
                JOIN Usuarios u ON u.id_usuario = a.id_paciente 
                LEFT JOIN medicamentos m ON m.id_medic = a.id_medic 
                JOIN perfil p ON u.id_usuario = p.id_usuario 
                WHERE a.id_paciente = ?";
    
        // Ejecuta la consulta y obtiene los resultados
        $result = $this->db->exec($sql, [$id_paciente]);
    
        // Convierte el BLOB a una cadena base64
        foreach ($result as &$row) {
            if ($row['foto']) {
                // Asegúrate de que el tipo de datos sea correcto
                $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
            }
        }
        
        // Retorna los resultados procesados
        return $result;
    }
    
    



public function guardarAlergias($datos) {
    

    // Obtener id_medico usando el id_usuario relacionado
    $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
    $medico = $this->db->exec($sql, [$datos['id_usuario']]);

    // Verifica si se encontró el médico
    if (empty($medico) || !isset($medico[0]['id_medico'])) { // Cambiar a $medico[0] para acceder correctamente al resultado
        error_log("No se encontró id_medico para id_usuario: " . $datos['id_usuario']);
        return 'medico_no_encontrado'; // Regresa un mensaje si no se encuentra el médico
    }

    // Verificar si id_medic está vacío y asignar NULL si es necesario
    $id_medic = isset($datos['id_medic']) && $datos['id_medic'] !== '' ? $datos['id_medic'] : null;

    

    // Guardar nuevo antecedente familiar
    $sqlInsert = "INSERT INTO Alergias (id_paciente, Descripcion, id_medic,id_medico,id_tipo,Nivel) VALUES (?, ?, ?, ?,?,?)";
    
    // Ejecutar la consulta de inserción
    $resultado = $this->db->exec($sqlInsert, [
        $datos['id_paciente'], 
        $datos['Descripcion'], 
        $id_medic,
        $medico[0]['id_medico'],
        $datos['id_tipo'], 
        $datos['Nivel']
    ]);

    // Verificar si la inserción fue exitosa
    if ($resultado) {
        return 'guardado'; // Devuelve un mensaje de éxito
    } else {
        error_log("Error al guardar la alerta: " . $datos['id_paciente']);
        return 'error_guardado'; // Devuelve un mensaje de error
    }
}
public function editarAlergias($datos) {
    // Guardar nuevo antecedente familiar
    $sqlUpdate = "UPDATE Alergias 
                  SET Descripcion = ?, 
                      Nivel = ?
                  WHERE id_alergia = ?"; // Asegúrate de que el nombre de la columna sea correcto
 
    // Ejecutar la consulta de actualización
    $resultado = $this->db->exec($sqlUpdate, [
        $datos['Descripcion'], 
        $datos['Nivel'],     // Debes pasar el nivel de la alergia
        $datos['id_alergia'] // ID de la alergia que se está actualizando
    ]);

    // Verificar si la actualización fue exitosa
    if ($resultado) {
        return 'actualizado'; // Devuelve un mensaje de éxito
    } else {
        error_log("Error al actualizar alergia con id_alergia: " . $datos['id_alergia']);
        return 'error_actualizado'; // Devuelve un mensaje de error
    }
}


///eliminar alergia
public function eliminarAlergia($id_alergia) {
    // Prepara la consulta SQL para eliminar la alergia
    $sqlDelete = "DELETE FROM Alergias WHERE id_alergia = ?";
    
    // Ejecuta la consulta de eliminación
    $resultado = $this->db->exec($sqlDelete, [$id_alergia]);

    // Verifica si la eliminación fue exitosa
    if ($resultado) {
        return 'eliminado'; // Devuelve un mensaje de éxito
    } else {
        error_log("Error al eliminar la alergia con id_alergia: " . $id_alergia);
        return 'error_eliminado'; // Devuelve un mensaje de error
    }
}


}

