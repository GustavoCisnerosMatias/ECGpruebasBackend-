<?php
//headers
header("Access-Control-Allow-Origin: http://localhost:8100");
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type, Authorization,X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT,PATCH, DELETE");

error_log("--- DEBUG INDEX: Punto 1 - Headers configurados. ---");

$f3=require('lib/base.php');

date_default_timezone_set('America/Guayaquil');
$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<8.0)
    trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');
 $f3->config('routes.ini');

$db = new DB\SQL(
    'mysql:host=' . $f3->get('database.host') . ';port=' . $f3->get('database.portBD') . ';dbname=' . $f3->get('database.dbname'),
    $f3->get('database.user'),
    $f3->get('database.pass'),
    array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT => TRUE,
        \PDO::MYSQL_ATTR_COMPRESS => TRUE
    )
);

//  Zona horaria para MySQL
$db->exec("SET time_zone = 'America/Guayaquil'");

//  Guardar en F3
$f3->set('DB', $db);


// Tus rutas GET / y /userref
$f3->route('GET /',
    function($f3) {
        error_log("--- DEBUG INDEX: Ruta principal '/' ejecutada. ---");
        $classes=array( /* ... */ );
        $f3->set('classes',$classes);
        $f3->set('content','welcome.htm');
        echo View::instance()->render('layout.htm');
    }
);

$f3->route('GET /userref',
    function($f3) {
        error_log("--- DEBUG INDEX: Ruta '/userref' ejecutada. ---");
        $f3->set('content','userref.htm');
        echo View::instance()->render('layout.htm');
    }
);
$f3->route('GET /descargar-apk', function($f3) {
    $filePath = 'public/descargas/app-debug.apk';

    if (file_exists($filePath)) {
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="app-debug.apk"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Archivo no encontrado']);
    }
});

$f3->run();
