<?php

class M_reportes {

    private $db;

    public function __construct() {
        $this->db = \Base::instance()->get('DB');
    }

    // Total de datos biomédicos agrupados
    public function totalDatosBiomedicos($inicio, $fin) {
        $result = $this->db->exec("
            SELECT SUM(cantidad) AS total_datos 
            FROM datos_agrupados 
            WHERE fecha_registro BETWEEN ? AND ?
        ", [$inicio, $fin]);

        return $result[0]['total_datos'] ?? 0;
    }

    // Total de falsos positivos
    public function totalFalsosPositivos($inicio, $fin) {
        $result = $this->db->exec("
            SELECT SUM(cantidad) AS total_datos 
            FROM falsos_positivos 
            WHERE fecha_creacion BETWEEN ? AND ?
        ", [$inicio, $fin]);

        return $result[0]['total_datos'] ?? 0;
    }

    // Total de datos limpios
    public function totalDatosLimpios($inicio, $fin) {
        $result = $this->db->exec("
            SELECT SUM(cantidad) AS total_datos 
            FROM datos_limpios 
            WHERE fecha_creacion BETWEEN ? AND ?
        ", [$inicio, $fin]);

        return $result[0]['total_datos'] ?? 0;
    }

    // Top 5 usuarios con más datos agrupados
    public function topUsuarios($inicio, $fin) {
        return $this->db->exec("
        SELECT 
            u.*,
            COUNT(d.id) AS total_registros
        FROM 
            usuarios u
        JOIN 
            datos_agrupados d ON u.id_usuario = d.id_usuario
        WHERE 
            d.fecha_registro BETWEEN ? AND ?
        GROUP BY 
            u.id_usuario
        ORDER BY 
            total_registros DESC
        LIMIT 5;
        ", [$inicio, $fin]);
    }

    // Dispositivo con más falsos positivos
    public function dispositivoConMasFalsos($inicio, $fin) {
        return $this->db->exec("
            SELECT d.id_dispo, d.nombre AS nombre_dispositivo, COUNT(fp.id_dispo) AS cantidad_falsos
            FROM tab_dispositivos d
            LEFT JOIN falsos_positivos fp ON d.id_dispo = fp.id_dispo
            WHERE fp.fecha_creacion BETWEEN ? AND ?
            GROUP BY d.id_dispo, d.nombre
            ORDER BY cantidad_falsos DESC
            LIMIT 1
        ", [$inicio, $fin]);
    }

    // Últimos 10 falsos positivos con detalles
    public function ultimosFalsosPositivos($inicio, $fin) {
        return $this->db->exec("
            SELECT fp.*, p.nombre AS parametro_nombre, d.nombre AS dispositivo_nombre
            FROM falsos_positivos fp
            JOIN parametros p ON fp.id_parametro = p.id_parametro
            JOIN tab_dispositivos d ON fp.id_dispo = d.id_dispo
            WHERE fp.fecha_creacion BETWEEN ? AND ?
            ORDER BY fp.fecha_creacion DESC
            LIMIT 10
        ", [$inicio, $fin]);
    }
}