<?php

class perfil_Ctrl
{
    protected $M_Modelo;

    public function __construct()
    {
        $this->M_Modelo = new M_perfil();
    }

    // Método para buscar id_usuario 
public function getImagen($f3)
{
    $id_usuario = $f3->get('GET.id_usuario');

    // Validar que se haya proporcionado el id_usuario
    if (!$id_usuario) {
        echo json_encode(['mensaje' => 'Falta el parámetro: id_usuario']);
        return;
    }

    // Llamar al método en el modelo para buscar el id_usuario 
    $foto = $this->M_Modelo->getIdUsuarioByid($id_usuario);

    if ($foto !== null) {
        // Codificar la imagen a Base64
        $fotoBase64 = base64_encode($foto);

        // Devolver la foto codificada como respuesta en formato JSON
        echo json_encode(['foto' => $fotoBase64]);
    } else {
        echo json_encode(['mensaje' => 'No se encontró ninguna foto con ese id']);
    }
}
public function agregarImagen($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes
        $requiredFields = ['id_usuario', 'foto'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        // Decodificar la imagen de base64
        $foto = base64_decode($data['foto']);
        if ($foto === false) {
            echo json_encode(['mensaje' => 'Error al decodificar la imagen']);
            return;
        }

        // Crear o actualizar la imagen del perfil
        $result = $this->M_Modelo->agregarImagen($data['id_usuario'], $foto);

        if ($result) {
            echo json_encode(['mensaje' => 'Foto subida exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al subir la foto']);
        }
    }


}
?>

