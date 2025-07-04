<?php

class M_alertas extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'alertas');
    }
    public function crearAlerta($id_usuario, $vista, $id_datos, $contenido) {
        
        $fecha_alerta = date('Y-m-d H:i:s');
        $sql = "INSERT INTO alertas (id_usuario, vista, estado_alerta, fecha_alerta, id_datos, contenido)
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->exec($sql, [
            $id_usuario,
            $vista,
            1, // 1 = por ver
            $fecha_alerta,
            $id_datos,
            $contenido
        ]);
    }


    // Obtener alertas por usuario
    public function obtenerAlertasPorUsuario($id_usuario) {
        $sql = "SELECT * FROM alertas WHERE id_usuario = ? ORDER BY fecha_alerta DESC";
        return $this->db->exec($sql, [$id_usuario]);
    }

    // Marcar una alerta como vista (estado_alerta = 0)
    public function marcarComoVista($id_alerta) {
        $sql = "UPDATE alertas SET estado_alerta = 0 WHERE id_alertas = ?";
        return $this->db->exec($sql, [$id_alerta]);
    }


}
