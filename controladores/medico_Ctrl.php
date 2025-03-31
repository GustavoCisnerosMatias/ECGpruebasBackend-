<?php

class medico_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_medico();
    }
    public function VerificarMedicos($f3)
{
    // Obtener la cédula desde el request (suponiendo que viene en un POST)
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    if (!isset($data['cedula'])) {
        echo json_encode(['mensaje' => 'Falta parámetro: cédula']);
        return;
    }

    $cedula = $data['cedula'];

    // Endpoint del servicio
    $url = "https://saccs.acess.gob.ec/publico/talentohumano/consultareg/titulosreg/" . $cedula;

    // Usamos cURL para obtener la respuesta
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar verificación SSL
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones

    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$respuesta) {
        echo json_encode(['mensaje' => 'No se pudo obtener datos del servidor externo']);
        return;
    }

    // Decodificar JSON si la respuesta es JSON, de lo contrario, usarla como HTML
    $datos = json_decode($respuesta, true);
    
    if ($datos && isset($datos['respuesta'])) {
        $html = $datos['respuesta']; // Si el JSON tiene 'respuesta', es HTML dentro del JSON
    } else {
        $html = $respuesta; // Si no hay JSON, asumimos que la respuesta es HTML puro
    }

    // Verificar si se obtuvo HTML válido
    if (empty($html)) {
        echo json_encode(['mensaje' => 'El servidor no devolvió información válida']);
        return;
    }

    // Procesar el HTML con DOMDocument
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Extraer información con XPath
    $xpath = new DOMXPath($dom);

    // Extraer el nombre completo de la persona
    $nombreNode = $xpath->query('//p[label[b="Nombres:"]]/label[2]');
    $nombreCompleto = $nombreNode->length > 0 ? trim($nombreNode->item(0)->textContent) : 'No disponible';

    // Separar los apellidos y nombres
    $nombreArray = explode(' ', $nombreCompleto);

    // Suponemos que los dos primeros elementos son los apellidos y el resto son los nombres
    $apellidos = isset($nombreArray[0]) ? $nombreArray[0] . ' ' . (isset($nombreArray[1]) ? $nombreArray[1] : '') : 'No disponible';
    $nombres = isset($nombreArray[2]) ? implode(' ', array_slice($nombreArray, 2)) : 'No disponible';

    // Extraer información de los títulos
    $rows = $xpath->query('//table[@id="datatable"]/tbody/tr');

    if ($rows->length == 0) {
        echo json_encode(['titulo' => 'No se encontraron títulos registrados']);
        return;
    }

    // Extraer datos del primer título registrado
    $primeraFila = $rows[0]->getElementsByTagName('td');

    if ($primeraFila->length < 5) {
        echo json_encode(['mensaje' => 'Datos insuficientes en la respuesta']);
        return;
    }

    $titulo = trim($primeraFila->item(0)->textContent);
    $universidad = trim($primeraFila->item(2)->textContent);
    $fechaGraduacion = trim($primeraFila->item(4)->textContent);
    $añoGraduacion = explode('-', $fechaGraduacion)[0];

    // Respuesta final con apellidos, nombres, título, universidad y año de graduación
    echo json_encode([
        'apellidos' => $apellidos,
        'nombres' => $nombres,
        'titulo' => $titulo,
        'universidad' => $universidad,
        'año_graduacion' => $añoGraduacion, 
        'titulo'=>'Si'
    ]);
}

//     public function VerificarMedicos($f3)
// {
//     // Obtener la cédula desde el request (suponiendo que viene en un POST)
//     $json = $f3->get('BODY');
//     $data = json_decode($json, true);

//     if (json_last_error() !== JSON_ERROR_NONE) {
//         echo json_encode(['mensaje' => 'JSON inválido']);
//         return;
//     }

//     if (!isset($data['cedula'])) {
//         echo json_encode(['mensaje' => 'Falta parámetro: cédula']);
//         return;
//     }

//     $cedula = $data['cedula'];

//     // Endpoint del servicio
//     $url = "https://saccs.acess.gob.ec/publico/talentohumano/consultareg/titulosreg/" . $cedula;

//     // Usamos cURL para obtener la respuesta
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar verificación SSL
//     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones

//     $respuesta = curl_exec($ch);
//     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);

//     if ($httpCode !== 200 || !$respuesta) {
//         echo json_encode(['mensaje' => 'No se pudo obtener datos del servidor externo']);
//         return;
//     }

//     // Decodificar JSON si la respuesta es JSON, de lo contrario, usarla como HTML
//     $datos = json_decode($respuesta, true);
    
//     if ($datos && isset($datos['respuesta'])) {
//         $html = $datos['respuesta']; // Si el JSON tiene 'respuesta', es HTML dentro del JSON
//     } else {
//         $html = $respuesta; // Si no hay JSON, asumimos que la respuesta es HTML puro
//     }

//     // Verificar si se obtuvo HTML válido
//     if (empty($html)) {
//         echo json_encode(['mensaje' => 'El servidor no devolvió información válida']);
//         return;
//     }

//     // Procesar el HTML con DOMDocument
//     libxml_use_internal_errors(true);
//     $dom = new DOMDocument();
//     $dom->loadHTML($html);
//     libxml_clear_errors();

//     // Extraer información con XPath
//     $xpath = new DOMXPath($dom);

//     // Extraer el nombre de la persona
//     $nombreNode = $xpath->query('//p[label[b="Nombres:"]]/label[2]');
//     $nombre = $nombreNode->length > 0 ? trim($nombreNode->item(0)->textContent) : 'No disponible';

//     // Extraer información de los títulos
//     $rows = $xpath->query('//table[@id="datatable"]/tbody/tr');

//     if ($rows->length == 0) {
//         echo json_encode(['mensaje' => 'No se encontraron títulos registrados']);
//         return;
//     }

//     // Extraer datos del primer título registrado
//     $primeraFila = $rows[0]->getElementsByTagName('td');

//     if ($primeraFila->length < 5) {
//         echo json_encode(['mensaje' => 'Datos insuficientes en la respuesta']);
//         return;
//     }

//     $titulo = trim($primeraFila->item(0)->textContent);
//     $universidad = trim($primeraFila->item(2)->textContent);
//     $fechaGraduacion = trim($primeraFila->item(4)->textContent);
//     $añoGraduacion = explode('-', $fechaGraduacion)[0];

//     // Respuesta final con nombre, título, universidad y año de graduación
//     echo json_encode([
//         'nombre' => $nombre,
//         'titulo' => $titulo,
//         'universidad' => $universidad,
//         'año_graduacion' => $añoGraduacion
//     ]);
// }

    

    public function listartodosmedicos($f3)
    {
        try {
            $medicos = $this->modelo->getTopics();

            if ($medicos) {
                echo json_encode(['medicos' => $medicos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron medicos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener medicos: ' . $e->getMessage()]);
        }
    }

    // Método para mostrar todos los médicos con id_medico, nombre, apellido y centro_hospitalario
    public function mostrarMedicos($f3)
    {
        try {
            $medicos = $this->modelo->obtenerMedicos();

            if ($medicos) {
                echo json_encode(['medicos' => $medicos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron médicos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener médicos: ' . $e->getMessage()]);
        }
    }

    // Método para crear usuario
    public function createMedico($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes
        $requiredFields = [
            'id_usuario', 'id_centro', 'id_especialidad','estado'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }


        // Crear el nuevo usuario
        $result = $this->modelo->createmedico($data);

        if ($result) {
            echo json_encode(['mensaje' => 'Usuario creado exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al crear el usuario']);
        }
    }




    // Método para obtener los datos de un médico por id_usuario
    public function obtenerMedicoPorUsuario($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: id_usuario']);
            return;
        }

        try {
            $medico = $this->modelo->obtenerMedicoPorUsuario($data['id_usuario']);

            if ($medico) {
                echo json_encode(['medico' => $medico]);
            } else {
                echo json_encode(['mensaje' => 'No se encontró el médico']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el médico: ' . $e->getMessage()]);
        }
    }

}
?>
