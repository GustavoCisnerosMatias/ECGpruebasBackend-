<?php

class M_User extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'usuarios');
    }

    public function getTodosusuarios()
    {
        $sql = "SELECT p.nombre, p.fecha_creacion, p.estado FROM usuarios p JOIN rol r ON r.id_rol = p.id_rol WHERE p.id_rol = 1";
        return $this->db->exec($sql);
    }

    public function createasis($data)
    {
        $db = \Base::instance()->get('DB');
        
        $queryMedico = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $medico = $db->exec($queryMedico, [$data['id_medicoss']]);
        
        if (count($medico) > 0) {
            $id_medico = $medico[0]['id_medico'];

            $query = "INSERT INTO asistente (id_usuario, id_medico, estado) VALUES (?, ?, ?)";
            $result = $db->exec($query, [$data['id_usuario'], $id_medico, $data['estado']]);
            
            if ($result) {
                return $db->lastInsertId();
            } else {
                error_log('Error al crear asistente: ' . implode(', ', $db->errorInfo()));
                return false;
            }
        } else {
            error_log('No se encontró el médico para id_usuario: ' . $data['id_medicoss']);
            return false;
        }
    }

    public function getMenu($userId)
    {
        $db = \Base::instance()->get('DB');
        $query = "SELECT m.men_descripcion, m.men_icono, m.men_pagina, m.categoria
                  FROM usuarios u 
                  JOIN seg_accesos a ON u.id_rol = a.id_rol
                  JOIN menu m ON a.men_id = m.men_id 
                  WHERE u.id_usuario = ? AND m.men_estado = 'A'";
        return $db->exec($query, $userId);
    }

    public function getpeso($userId)
    {
        $db = \Base::instance()->get('DB');
        $query = "SELECT a.peso, a.estatura, d.codigo
                  FROM usuarios u
                  JOIN datos_fisicos a ON u.id_usuario = a.id_usuario
                  JOIN tab_dispositivos d ON u.id_usuario = d.id_usuario
                  WHERE u.id_usuario = ?";
        return $db->exec($query, $userId);
    }

    public function getdispo($userId)
    {
        $db = \Base::instance()->get('DB');
        $query = "SELECT t.id_dispo, t.nombre FROM tab_dispositivos t WHERE t.id_usuario = ? AND  t.estado='A'";
        return $db->exec($query, $userId);
    }

    public function getIdUsuarioByCedula($cedula)
    {
        $this->load(['cedula = ?', $cedula]);
        if ($this->dry()) {
            return false;
        }
        return $this->id_usuario;
    }

    public function checkCedulaExists($cedula)
    {
        return $this->count(['cedula = ?', $cedula]) > 0;
    }

    public function checkCorreoExists($correo)
    {
        return $this->count(['correo_electronico = ?', $correo]) > 0;
    }

    public function createUser($data)
    {
        $this->copyFrom($data);
        if ($this->save()) {
            return $this->id_usuario;
        } else {
            return false;
        }
    }

    public function editarUsuario($data)
    {
        $this->load(['id_usuario = ?', $data['id_usuario']]);

        $allowedFields = [
            'correo_electronico', 'telefono'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }

        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function getUsuarioById($id_usuario)
    {
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return null;
        }
        return [
            'id_usuario' => $this->id_usuario,
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono,
            'correo_electronico' => $this->correo_electronico,
            'id_rol' => $this->id_rol,
            'estado' => $this->estado,
        ];
    }

    public function ediucontra($id_usuario, $nueva_contrasena)
    {
        $this->load(['id_usuario = ?', $id_usuario]);

        if ($this->dry()) {
            return false;
        }

        $this->contrasena = $nueva_contrasena;

        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenercontrasenaPorId($id_usuario)
    {
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return null;
        }
        return [
            'contrasena' => $this->contrasena,
        ];
    }

    public function editarestado($data)
    {
        $this->load(['id_usuario = ?', $data['id_usuario']]);

        $allowedFields = [
            'cedula', 'nombre', 'apellido', 'telefono', 'correo_electronico', 'id_rol', 'estado'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }

        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function guardarToken($usuarioId, $token, $tokenExpiracion)
    {
        $db = \Base::instance()->get('DB');
        $fechaCreacion = date('Y-m-d H:i:s');

        $query = "INSERT INTO recuperacion_contraseñas (id_usuario, token_recuperacion, token_expiracion, fecha_creacion)
                  VALUES (?, ?, ?, ?)";
        return $db->exec($query, [$usuarioId, $token, $tokenExpiracion, $fechaCreacion]);
    }

    public function getIdUsuarioByCorreo($correo)
    {
        $this->load(['correo_electronico = ?', $correo]);
        if ($this->dry()) {
            return false;
        }
        return $this->id_usuario;
    }

    public function verificarToken($usuarioId, $token)
    {
        $db = \Base::instance()->get('DB');

        $horaActual = new DateTime("now", new DateTimeZone('America/Guayaquil'));
        $horaActualFormato = $horaActual->format('Y-m-d H:i:s');

        $query = "SELECT * FROM recuperacion_contraseñas 
                  WHERE id_usuario = ? 
                    AND token_recuperacion = ? 
                    AND token_expiracion > ? 
                    AND utilizado = 0";

        $result = $db->exec($query, [$usuarioId, $token, $horaActualFormato]);

        if (!empty($result)) {
            $this->marcarTokenComoUtilizado($usuarioId, $token);
            return true;
        }
        return false;
    }

    private function marcarTokenComoUtilizado($usuarioId, $token)
    {
        $db = \Base::instance()->get('DB');
        $query = "UPDATE recuperacion_contraseñas 
                  SET utilizado = 1 
                  WHERE id_usuario = ? AND token_recuperacion = ?";
        $db->exec($query, [$usuarioId, $token]);
    }

    public function actualizarContrasena($usuarioId, $nuevaContrasena)
    {
        $this->load(['id_usuario = ?', $usuarioId]);
        if (!$this->dry()) {
            $this->contrasena = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
            $this->estado = 'A';
            return $this->update();
        }
        return false;
    }

    public function bloquearus($username, $bloqueado)
    {
        $this->load(['cedula = ?', $username]);
        if (!$this->dry()) {
            $this->estado = $bloqueado;
            return $this->update();
        }
        return false;
    }
}
