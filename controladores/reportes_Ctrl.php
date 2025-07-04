<?php
require_once('lib/middleware/JwtMiddleware.php');
class reportes_Ctrl {

    // Método para obtener todos los reportes según un rango de fechas
    public function getReportesPorFecha($f3) {
        $data = json_decode($f3->get('BODY'), true);

        // Validar JSON y fechas
        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !isset($data['fecha_inicio'], $data['fecha_fin'])
        ) {
            echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido o fechas incompletas']);
            return;
        }

        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];

        // Cargar modelo
        $modelo = new M_reportes();

        // Obtener datos
        $datos_biomedicos = $modelo->totalDatosBiomedicos($fecha_inicio, $fecha_fin);
        $falsos_positivos = $modelo->totalFalsosPositivos($fecha_inicio, $fecha_fin);
        $datos_limpios = $modelo->totalDatosLimpios($fecha_inicio, $fecha_fin);
        $top_usuarios = $modelo->topUsuarios($fecha_inicio, $fecha_fin);
        $dispositivo_mas_falsos = $modelo->dispositivoConMasFalsos($fecha_inicio, $fecha_fin);
        $ultimos_falsos = $modelo->ultimosFalsosPositivos($fecha_inicio, $fecha_fin);

        // Enviar respuesta
        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_datos_biomedicos' => $datos_biomedicos,
                'total_falsos_positivos' => $falsos_positivos,
                'total_datos_limpios' => $datos_limpios,
                'top_usuarios' => $top_usuarios,
                'dispositivo_con_mas_falsos' => $dispositivo_mas_falsos,
                'ultimos_falsos_positivos' => $ultimos_falsos
            ]
        ]);
    }
}