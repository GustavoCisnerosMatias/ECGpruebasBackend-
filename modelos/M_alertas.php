<?php

class M_alertas extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'Alertas');
    }
    
    
    
    public function obtenerAlertasfecha($id_usuario, $fecha_ini, $fecha_fin) {
    $sql = "SELECT a.fecha_alerta, a.vista, t.nombre_alerta, t.descripcion, r.valor, p.nombre, p.unidad_medida, a.id_alertas
            FROM Alertas a 
            JOIN TipoAlertas t ON t.id_tipoalerta = a.id_tipoalerta 
            JOIN Usuarios u ON u.id_usuario = a.id_usuario
            JOIN d_realtime r ON r.id_realtime = a.id_realtime
            JOIN parametros p ON p.id_parametro = r.id_parametro 
            WHERE u.id_usuario = ? 
            AND a.fecha_alerta BETWEEN ? AND ?";
    return $this->db->exec($sql, [$id_usuario, $fecha_ini, $fecha_fin]);
}

    public function obtenerAlertas($id_usuario) {
        $sql = "SELECT a.fecha_alerta, a.vista, t.nombre_alerta, t.descripcion, r.valor, p.nombre, p.unidad_medida, a.id_alertas
            FROM Alertas a
            JOIN TipoAlertas t ON t.id_tipoalerta = a.id_tipoalerta
            JOIN Usuarios u ON u.id_usuario = a.id_usuario
            JOIN d_realtime r ON r.id_realtime = a.id_realtime
            JOIN parametros p ON p.id_parametro = r.id_parametro
            WHERE u.id_usuario = ?
              AND a.fecha_alerta >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function obtenertodasAlertasparamedico($id_usuario) {
                    $sql = "SELECT 
                DATE_FORMAT(a.fecha_alerta, '%Y-%m-%d %H:00:00') AS hora_alerta,  
                t.nombre_alerta, u.nombre,u.apellido,
                u.cedula,  
                MAX(a.vista) AS vista,  
                MAX(r.valor) AS valor,  
                MAX(p.nombre) AS nombre_parametro,  
                MAX(p.unidad_medida) AS unidad_medida  
            FROM 
                Alertas a
            JOIN 
                TipoAlertas t ON t.id_tipoalerta = a.id_tipoalerta
            JOIN 
                Usuarios u ON u.id_usuario = a.id_usuario 
            JOIN 
                d_realtime r ON r.id_realtime = a.id_realtime
            JOIN 
                parametros p ON p.id_parametro = r.id_parametro 
            JOIN 
                medico_paciente j ON j.id_paciente = u.id_usuario  
            JOIN 
                medicos m ON m.id_medico = j.id_medico 
            WHERE 
                m.id_usuario = ? 
                AND DATE(a.fecha_alerta) = CURDATE()  
            GROUP BY 
                hora_alerta,  
                t.nombre_alerta,  
                u.cedula";
        return $this->db->exec($sql, [$id_usuario]);
    }
    
    public function actualizarVista($id_alertas, $vista) {
        $sql = "UPDATE Alertas SET vista = ? WHERE id_alertas = ?";
        $this->db->exec($sql, [$vista, $id_alertas]);
    }
    public function obtenertipoAlertas() {
        $sql = "SELECT * FROM TipoAlertas";
        return $this->db->exec($sql);
    }



    public function editarTipoAlerta($data) {
        $sql = "UPDATE TipoAlertas SET nombre_alerta = ?, descripcion = ?, estado = ?, rango_min = ?, rango_max = ?, 
                edad_min = ?, edad_max = ?, genero = ?, fecha = ?, id_parametro = ? WHERE id_tipoalerta = ?";
        $this->db->exec($sql, [
            $data['nombre_alerta'], $data['descripcion'], $data['estado'], $data['rango_min'], $data['rango_max'],
            $data['edad_min'], $data['edad_max'], $data['genero'], $data['fecha'], $data['id_parametro'], $data['id_tipoalerta']
        ]);
    }

    public function eliminarTipoAlerta($id_tipoalerta) {
        $sql = "DELETE FROM TipoAlertas WHERE id_tipoalerta = ?";
        $this->db->exec($sql, [$id_tipoalerta]);
    }

    public function crearTipoAlerta($data) {
        $sql = "INSERT INTO TipoAlertas (nombre_alerta, descripcion, estado, rango_min, rango_max, edad_min, edad_max, genero,  id_parametro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->exec($sql, [
            $data['nombre_alerta'], $data['descripcion'], $data['estado'], $data['rango_min'], $data['rango_max'],
            $data['edad_min'], $data['edad_max'], $data['genero'], $data['id_parametro']
        ]);
    }

}

