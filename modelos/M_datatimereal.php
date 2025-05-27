<?php

class M_datatimereal extends \DB\SQL\Mapper {
    public function __construct() {
          $this->db = \Base::instance()->get('DB');
    }

    public function obtenerTopicsActivos() {
        $result = \Base::instance()->get('DB')->exec('SELECT nombre FROM tab_dispositivos WHERE estado = ?', ['A']);
        return array_map(function($item) {
            return $item['nombre'];
        }, $result);
    }

    public function obtenerNormasGlobales($id_parametro) {
        return $this->db->exec(
            'SELECT valor_minimo, valor_maximo FROM parametros_globales WHERE id_parametro = ?',
            [$id_parametro]
        )[0] ?? null;
    }
    public function crearNormasGlobales($id_parametro, $min, $max) {
        $this->db->exec(
            'INSERT INTO parametros_globales (id_parametro, valor_minimo, valor_maximo) VALUES (?, ?, ?)',
            [$id_parametro, $min, $max]
        );
    }

    public function obtenerEstadisticasUsuario($id_usuario, $id_parametro) {
        return $this->db->exec(
            'SELECT id_usuario, id_parametro, media, desviacion_estandar, count, mean_welford, m2_welford
                FROM parametros_usuario
                WHERE id_usuario = ? AND id_parametro = ?',
            [$id_usuario, $id_parametro]
        )[0] ?? null;
    }
    public function obtenerParametrosEstadistica($id_usuario) {
        $parametrosGlobales = $this->db->exec(
            'SELECT id_parametro, valor_minimo, valor_maximo FROM parametros_globales'
        );

        $parametrosUsuario = $this->db->exec(
            'SELECT id_usuario, id_parametro, media, desviacion_estandar, count, mean_welford, m2_welford 
            FROM parametros_usuario 
            WHERE id_usuario = ?',
            [$id_usuario]
        );

        return [
            'globales' => $parametrosGlobales,
            'usuario' => $parametrosUsuario
        ];
    }

    
    public function filtrarValores($valores, $minG, $maxG, $minU, $maxU) {
        $validos = [];
        $falsos = [];
        foreach ($valores as $x) {
            if ($x < $minG || $x > $maxG || $x < $minU || $x > $maxU) {
                $falsos[] = $x;
            } else {
                $validos[] = $x;
            }
        }
        return [$validos, $falsos];
    }
    
    public function guardarFalsosPositivos($valores, $idU, $idP, $dur, $disp) {
        if (empty($valores)) return;

        $jsonValores = json_encode($valores);
        $this->db->exec(
            'INSERT INTO falsos_positivos (id_usuario, id_parametro, valores, duracion, id_dispo)
                VALUES (?, ?, ?, ?, ?)',
            [$idU, $idP, $jsonValores, $dur, $disp]
        );
    }

    public function guardarDatosAgrupados($id_usuario, $id_parametro, $valores, $duracion, $id_dispo) {
        $sql = "INSERT INTO datos_agrupados (id_usuario, id_parametro, valores, duracion_segundos, id_dispo)
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->exec($sql, [$id_usuario, $id_parametro, json_encode($valores), $duracion, $id_dispo]);
    }
    
    public function actualizarEstadisticas($valores, $userStats, $idU, $idP) {
        $batch_n = 0;
        $batch_mu = 0.0;
        $batch_M2 = 0.0;
    
        // Calcular la media y el M2 utilizando el algoritmo de Welford
        foreach ($valores as $x) {
            $batch_n++;
            $delta = $x - $batch_mu;
            $batch_mu += $delta / $batch_n;
            $delta2 = $x - $batch_mu;
            $batch_M2 += $delta * $delta2;
        }
    
        // Verificar si es la primera vez que insertamos estadísticas
        if ((int)$userStats['count'] === 0) {
            // Primera vez, insertar
            $k = 2.0;  // Umbral K por defecto
            $sd = $batch_n > 1 ? sqrt($batch_M2 / ($batch_n - 1)) : 0;  // Desviación estándar usando M2
    
            // Insertar los nuevos datos en la base de datos
            $this->db->exec(
                'INSERT INTO parametros_usuario (id_usuario, id_parametro, media, desviacion_estandar, count, mean_welford, m2_welford)
                    VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$idU, $idP, $batch_mu, $sd, $batch_n, $batch_mu, $batch_M2]
            );
        } else {
            // Si ya existen estadísticas, actualizar
            $n1 = (int)$userStats['count'];
            $mu1 = (float)$userStats['mean_welford'];
            $M2_1 = (float)$userStats['m2_welford'];
    
            $n2 = $batch_n;
            $mu2 = $batch_mu;
            $M2_2 = $batch_M2;
    
            // Calcular los nuevos valores combinando las estadísticas previas y las nuevas
            $n = $n1 + $n2;
            $delta = $mu2 - $mu1;
            $mu_new = ($n1 * $mu1 + $n2 * $mu2) / $n;
            $M2_new = $M2_1 + $M2_2 + ($delta * $delta) * $n1 * $n2 / $n;
            $sd_new = $n > 1 ? sqrt($M2_new / ($n - 1)) : 0;
    
            // Actualizar los datos en la base de datos
            $this->db->exec(
                'UPDATE parametros_usuario
                    SET media = ?, desviacion_estandar = ?, count = ?, mean_welford = ?, m2_welford = ?
                    WHERE id_usuario = ? AND id_parametro = ?',
                [$mu_new, $sd_new, $n, $mu_new, $M2_new, $idU, $idP]
            );
        }
    }



}



