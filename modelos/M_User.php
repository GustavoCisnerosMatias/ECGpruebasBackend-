<?php


class M_User extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'Usuarios');
    }

    public function getTodosusuarios()
    {
        $sql = "SELECT p.nombre, p.fecha_creacion,p.estado FROM Usuarios p JOIN rol r on r.id_rol=p.id_rol where p.id_rol =1";
        return $this->db->exec($sql);
    }

    
    public function createasis($data)
    {
        $db = \Base::instance()->get('DB');
        
        // Consultar el id_medico en la tabla medicos
        $queryMedico = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $medico = $db->exec($queryMedico, [$data['id_medicoss']]);
        
        // Verificar si se encontró un médico
        if (count($medico) > 0) {
            $id_medico = $medico[0]['id_medico']; // Obtener el id_medico

            // Preparar la consulta SQL para insertar en asistente
            $query = "INSERT INTO asistente (id_usuario, id_medico, estado) VALUES (?, ?, ?)";
            
            // Ejecutar la consulta con los parámetros proporcionados
            $result = $db->exec($query, [$data['id_usuario'], $id_medico, $data['estado']]);
            
            // Verificar si la inserción fue exitosa
            if ($result) {
                return $db->lastInsertId(); // Devuelve el ID del nuevo asistente
            } else {
                // Manejo de errores
                error_log('Error al crear asistente: ' . implode(', ', $db->errorInfo()));
                return false; // Retorna false si la inserción falló
            }
        } else {
            // Manejo de errores si no se encontró el médico
            error_log('No se encontró el médico para id_usuario: ' . $data['id_medicoss']);
            return false; // Retorna false si no se encontró el médico
        }
    }
    

    // Método para obtener el menú basado en el ID del usuario
    public function getMenu($userId)
    {
        $db = \Base::instance()->get('DB');
        $query = "SELECT m.men_descripcion, m.men_icono, m.men_pagina, m.categoria
                  FROM Usuarios u 
                  JOIN seg_accesos a ON u.id_rol = a.id_rol
                  JOIN menu m ON a.men_id = m.men_id 
                  WHERE u.id_usuario = ? AND m.men_estado = 'A'";
        return $db->exec($query, $userId);
    }
      // Método para obtener el menú basado en el ID del usuario
      public function getpeso($userId)
      {
          $db = \Base::instance()->get('DB');
          $query = "SELECT a.peso, a.estatura, d.codigo
           FROM Usuarios u JOIN datos_fisicos a ON u.id_usuario = a.id_usuario JOIN tab_dispositivos d on u.id_usuario= d.id_usuario
           WHERE u.id_usuario = ?";
          return $db->exec($query, $userId);
      }

       // Método para obtener el menú basado en el ID del usuario
       public function getdispo($userId)
       {
           $db = \Base::instance()->get('DB');
        //    $query = "SELECT t.nombre FROM tab_dispositivos t WHERE t.id_usuario =  ?";
        $query = "SELECT t.id_dispo,  t.nombre FROM tab_dispositivos t WHERE t.id_usuario =  ?";

           return $db->exec($query, $userId);
       }

    // Buscar por cédula y devolver el ID de usuario
    public function getIdUsuarioByCedula($cedula)
    {
        $this->load(['cedula = ?', $cedula]);
        if ($this->dry()) {
            return false; // No se encontró ningún registro con esa cédula
        }
        return $this->id_usuario; // Devuelve el id_usuario encontrado
    }

    // Verificar si la cédula ya está registrada
    public function checkCedulaExists($cedula)
    {
        return $this->count(['cedula = ?', $cedula]) > 0;
    }


    // Verificar si el correo electrónico ya está registrado
    public function checkCorreoExists($correo)
    {
        return $this->count(['correo_electronico = ?', $correo]) > 0;
    }
    public function createUser($data)
    {
        $this->copyFrom($data);
        if ($this->save()) {
            return $this->id_usuario; // Devolver el ID generado
        } else {
            return false;
        }
    }
   
        

    public function editarUsuario($data) {
        // Cargar el usuario existente por su id_usuario
        $this->load(['id_usuario = ?', $data['id_usuario']]);
        
        // Campos permitidos para actualizar>
        $allowedFields = [
            'correo_electronico', 'telefono'
        ];

        // Actualizar los campos permitidos si están presentes en $data
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }

        // Guardar los cambios
        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false; // Error al actualizar
        }
    }
    // Obtener datos del usuario por id_usuario
    public function getUsuarioById($id_usuario)
    {
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return null; // No se encontró ningún registro con ese id_usuario
        }
        
        // Devolver los datos específicos solicitados
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
    




    public function ediucontra($id_usuario, $nueva_contrasena) {
        // Cargar el registro del usuario
        $this->load(['id_usuario = ?', $id_usuario]);
    
        // Verificar si se cargó correctamente el registro
        if ($this->dry()) {
            return false; // No se encontró ningún registro con ese id_usuario
        }
        
        // Actualizar la contraseña del usuario
        $this->contrasena = $nueva_contrasena; // La contraseña ya debería estar encriptada
        
        try {
            return $this->save(); // Guardar los cambios
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false; // Error al actualizar
        }
    }
    
    public function obtenercontrasenaPorId($id_usuario) {
        $this->load(['id_usuario = ?', $id_usuario]);
        if ($this->dry()) {
            return null; // No se encontró ningún registro con ese id_usuario
        }
        
        // Devolver los datos específicos solicitados
        return [
            'contrasena' => $this->contrasena,
        ];
    }





    public function editarestado($data) {
        // Cargar el usuario existente por su id_usuario
        $this->load(['id_usuario = ?', $data['id_usuario']]);
        
        // Campos permitidos para actualizar>
        $allowedFields = [
            'cedula','nombre','apellido','telefono','correo_electronico','id_rol','estado'
        ];

        // Actualizar los campos permitidos si están presentes en $data
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }

        // Guardar los cambios
        try {
            return $this->save();
        } catch (\Exception $e) {
            error_log('Error al actualizar usuario: ' . $e->getMessage());
            return false; // Error al actualizar
        }
    }
    



    //////Recuperar contraseña /////////////////////////////////////////
 // Método para generar y guardar un token de recuperación de contraseña

 public function guardarToken($usuarioId, $token, $tokenExpiracion)
{
    $db = \Base::instance()->get('DB');
    $fechaCreacion = date('Y-m-d H:i:s'); // Define la fecha de creación dentro del método

    $query = "INSERT INTO recuperacion_contraseñas (id_usuario, token_recuperacion, token_expiracion, fecha_creacion)
              VALUES (?, ?, ?, ?)";
    
    return $db->exec($query, [$usuarioId, $token, $tokenExpiracion, $fechaCreacion]);
}

 
 // Método para obtener el ID de usuario basado en el correo electrónico
 public function getIdUsuarioByCorreo($correo)
 {
     $this->load(['correo_electronico = ?', $correo]);
     if ($this->dry()) {
         return false; // No se encontró ningún registro con ese correo
     }
     return $this->id_usuario; // Devuelve el id_usuario encontrado
 } 


// Método para verificar el token
public function verificarToken($usuarioId, $token)
{
    $db = \Base::instance()->get('DB');
    
    // Obtener la hora actual en la misma zona horaria que los datos almacenados
    $horaActual = new DateTime("now", new DateTimeZone('America/Guayaquil')); // Ajusta a tu zona horaria
    $horaActualFormato = $horaActual->format('Y-m-d H:i:s');
    
    // Consulta para verificar el token
    $query = "SELECT * FROM recuperacion_contraseñas 
              WHERE id_usuario = ? 
                AND token_recuperacion = ? 
                AND token_expiracion > ? 
                AND utilizado = 0";
    
    // Ejecutar la consulta con la hora actual como parámetro
    $result = $db->exec($query, [$usuarioId, $token, $horaActualFormato]);
    
    if (!empty($result)) {
        // Si el token es válido, marcarlo como utilizado
        $this->marcarTokenComoUtilizado($usuarioId, $token);
        return true; // Devuelve true si el token es válido
    }
    return false; // Devuelve false si el token no es válido
}


// Método para marcar el token como utilizado
private function marcarTokenComoUtilizado($usuarioId, $token)
{
    $db = \Base::instance()->get('DB');
    $query = "UPDATE recuperacion_contraseñas 
              SET utilizado = 1 
              WHERE id_usuario = ? AND token_recuperacion = ?";
    $db->exec($query, [$usuarioId, $token]);
}




// Método para actualizar la contraseña
public function actualizarContrasena($usuarioId, $nuevaContrasena)
 {
     $this->load(['id_usuario = ?', $usuarioId]);
     if (!$this->dry()) {
         $this->contrasena = password_hash($nuevaContrasena, PASSWORD_BCRYPT); // Hashea la nueva contraseña
         $this->estado = 'A';
         return $this->update(); // Actualiza el registro en la base de datos
     }
     return false; // Usuario no encontrado
 }

// bloquear usuario
public function bloquearus($username, $bloqueado)
{     
     $this->load(['cedula = ?', $username]);
    if (!$this->dry()) {
        $this->estado = $bloqueado; // Establece el nuevo estado
        return $this->update(); // Actualiza el registro en la base de datos
    }
    return false; // Usuario no encontrado   
}

 
}



