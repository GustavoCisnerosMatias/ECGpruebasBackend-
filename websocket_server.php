<?php
require 'vendor/autoload.php';

// Encabezados CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\Socket\Server as ReactServer;
use React\EventLoop\Factory as ReactLoop;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $db;

    public function __construct($db)
    {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;
        echo "WebSocket server initialized.\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (isset($data['id_usuario'])) {
            $id_usuario = $data['id_usuario'];
            try {
                $datos = $this->obtenerDatosEnTiempoReal($id_usuario);
                foreach ($this->clients as $client) {
                    if ($from !== $client) {
                        $client->send(json_encode($datos));
                    }
                }
            } catch (\Exception $e) {
                echo "Error fetching data: {$e->getMessage()}\n";
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection closed: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function obtenerDatosEnTiempoReal($id_usuario)
    {
        try {
            $sql = "SELECT d.codigo, r.valor FROM reldatos r JOIN tab_dispositivos d ON r.codigo = d.codigo WHERE d.id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            return [];
        }
    }
}

try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=_Proyectobiomecio', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $loop = ReactLoop::create();
    $webSock = new ReactServer('0.0.0.0:8001', $loop);

    $webServer = new IoServer(
        new HttpServer(
            new WsServer(
                new WebSocketServer($db)
            )
        ),
        $webSock,
        $loop
    );

    echo "Starting WebSocket server...\n";
    $loop->run();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
?>
