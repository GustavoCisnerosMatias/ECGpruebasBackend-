<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar el archivo config.ini
$config = parse_ini_file('../config.ini', true);
if ($config === false) {
    die("Error al cargar el archivo config.ini");
}

// Obtener configuración MQTT desde config.ini
$mqttConfig = $config['mqtt'];
$server = $mqttConfig['server'];
$port = $mqttConfig['port'];
$username = $mqttConfig['username'];
$password = $mqttConfig['password'];
$client_id = $mqttConfig['client_id'];

// Rutas de certificados
$client_cert = __DIR__ . '/client.crt'; // Ruta absoluta al certificado del cliente
$client_key = __DIR__ . '/cliente.key'; // Ruta absoluta a la clave privada del cliente
$ca_cert = __DIR__ . '/mosquitto.org.crt'; // Ruta del certificado CA


echo "Ruta del certificado del cliente: $client_cert\n";
echo "Ruta de la clave del cliente: $client_key\n";
echo "Ruta del certificado CA: $ca_cert\n";

if (!file_exists($client_cert) || !file_exists($client_key) || !file_exists($ca_cert)) {
    die("Uno o más archivos de certificado no se encontraron. Verifica las rutas.\n");
}


require('phpMQTT.php');
$mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

while (true) {
    // Establecer la conexión con opciones TLS
    if (!$mqtt->connect(true, NULL, $username, $password, [
        'tls' => true,
        'cafile' => $ca_cert,   // Archivo CA
        'cert' => $client_cert,  // Certificado del cliente
        'key' => $client_key     // Clave del cliente
    ])) {
        echo "Conexión fallida. Intentando nuevamente...\n";
        sleep(5);
        continue;
    } else {
        echo "Conexión exitosa al broker MQTT.\n"; // Mensaje adicional
    }
    
    // Obtener los tópicos activos desde la base de datos
    $topics = obtenerTopicosDesdeBaseDeDatos();
    if (!empty($topics)) {
        foreach ($topics as $topic) {
            echo "Suscribiéndome al tópico: $topic\n"; // Mensaje adicional
            // Suscribirse con QoS 2
            $mqtt->subscribe([$topic => ['qos' => 2, 'function' => 'procMsg']], 0);
        }
    } else {
        echo "No se pudo obtener ningún tópico de la base de datos.\n";
        exit(1);
    }
    
    // Mantener la conexión MQTT y procesar mensajes
    while ($mqtt->proc()) {
        // Procesar mensajes en tiempo real
    }

    $mqtt->close();
    sleep(5); // Espera antes de reconectar
}

// Función para procesar los mensajes MQTT
function procMsg($topic, $msg) {
    $logMessage = 'Msg Recibido: ' . date('r') . "\n" . "Tópico: {$topic}\n\n" . "\t$msg\n\n";
    file_put_contents('mqtt_log.txt', $logMessage, FILE_APPEND);
    echo $logMessage;

    $datos = json_decode($msg, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error al decodificar JSON: " . json_last_error_msg() . "\n";
        return; // Salir si el JSON es inválido
    }

    if (is_array($datos)) {
        foreach ($datos as $dato) {
            echo "Datos recibidos: " . print_r($dato, true) . "\n"; // Mensaje de depuración
            if (isset($dato['id_parametro'], $dato['codigo'], $dato['valor'])) {
                guardarEnBaseDeDatos($dato['id_parametro'], $dato['valor'], $dato['codigo']);
            } else {
                echo "Faltan datos requeridos en el mensaje.\n";
            }
        }
    } else {
        echo "Formato de mensaje inválido.\n";
    }
}

function guardarEnBaseDeDatos($id_parametro, $valor, $codigo) {
    try {
        $db = Database::getInstance()->getConnection();

        // Validar entradas
        if (!is_numeric($id_parametro) || !is_numeric($valor) || !preg_match('/^[a-zA-Z0-9]+$/', $codigo)) {
            throw new InvalidArgumentException("Datos inválidos.");
        }

        // Usar prepared statements
        $stmt = $db->prepare('INSERT INTO d_realtime (id_parametro, valor, codigo) VALUES (:id_parametro, :valor, :codigo)');
        $stmt->bindParam(':id_parametro', $id_parametro, PDO::PARAM_INT);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':codigo', $codigo);

        $stmt->execute();
        echo "Dato guardado en la base de datos: id_parametro={$id_parametro}, valor={$valor}, codigo={$codigo}.\n";
    } catch (InvalidArgumentException $e) {
        echo "Error en la validación de datos: " . $e->getMessage() . "\n";
    } catch (PDOException $e) {
        echo "Error al guardar en la base de datos: " . $e->getMessage() . "\n";
    }
}

// Función para obtener los tópicos activos desde la base de datos
function obtenerTopicosDesdeBaseDeDatos() {
    try {
        // Obtener la conexión a la base de datos usando el singleton
        $db = Database::getInstance()->getConnection();
        echo "Conexión a la base de datos establecida.\n"; // Mensaje adicional
        
        // Consultar los tópicos activos (estado "A")
        $stmt = $db->query('SELECT nombre FROM tab_dispositivos WHERE estado = "A"');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "No se encontraron tópicos activos en la base de datos.\n"; // Mensaje adicional
        }
        
        return array_column($results, 'nombre'); // Devuelve los nombres de los tópicos
    } catch (PDOException $e) {
        echo "Error al conectar a la base de datos: " . $e->getMessage();
        return [];
    }
}

// Clase Singleton para la conexión a la base de datos
class Database {
    private static $instance = null;
    private $connection;

    private $host;
    private $dbname;
    private $username;
    private $password;

    // Constructor privado para evitar instanciación directa
    private function __construct() {
        // Cargar la configuración de la base de datos desde config.ini
        global $config;
        $dbConfig = $config['database'];
        $this->host = $dbConfig['host'];
        $this->dbname = $dbConfig['dbname'];
        $this->username = $dbConfig['user'];
        $this->password = $dbConfig['pass'];

        try {
            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Conexión a la base de datos establecida.\n"; // Mensaje adicional
        } catch (PDOException $e) {
            echo "Error en la conexión de la base de datos: " . $e->getMessage();
        }
    }

    // Obtener la instancia única de la clase
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Obtener la conexión PDO
    public function getConnection() {
        return $this->connection;
    }
}
