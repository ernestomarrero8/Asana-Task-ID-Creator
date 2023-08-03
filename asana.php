<?php
define('MYSQL_HOST', 'Your_IP_OR_HOST');
define('MYSQL_USER', 'YOUR_USER');
define('MYSQL_PASSWORD', 'YOUR_SQL_PASSWORD');
define('MYSQL_DATABASE', 'YOUR_DATABASE');

$token = "1/999999999999:9999999999abcd999999";

try {
    // Conexión a la base de datos usando PDO
    $conexion = new PDO("mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificamos si la petición es de tipo POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtenemos el contenido de la petición POST y decodificamos
        $contenido = file_get_contents('php://input');
        $contenido_decodificado = urldecode($contenido);

        // Eliminamos espacios y caracteres no numéricos al inicio y final del contenido
        $contenido_decodificado = trim($contenido_decodificado, " \t\n\r\0\x0B=");

        // Insertamos el valor en la tabla tasks
        $query = "INSERT INTO tasks (asana_id) VALUES (:asana_id)";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':asana_id', $contenido_decodificado, PDO::PARAM_INT);
        $stmt->execute();

        // Consultamos el último valor insertado en la tabla tasks
        $query_last_inserted = "SELECT internal_id FROM tasks ORDER BY internal_id DESC LIMIT 1";
        $stmt_last_inserted = $conexion->prepare($query_last_inserted);
        $stmt_last_inserted->execute();
        $ultimo_registro = $stmt_last_inserted->fetchColumn();

        // Actualizamos el custom field en Asana
        $url = "https://app.asana.com/api/1.0/tasks/".$contenido_decodificado;
        $raw_data = '{
            "data": {
                "custom_fields": {
                    "your_custom_fields_GUID": "'.$ultimo_registro.'"
                }
            }
        }';
        $header = "Authorization: Bearer $token";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $raw_data,
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($header, "Content-Type: application/json"));
        $response = curl_exec($curl);
        curl_close($curl);

        // Imprimimos el contenido en HTML
        echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Contenido recibido desde Zapier</title>
            </head>
            <body>
                <h1>Contenido recibido:</h1>
                <pre>$contenido_decodificado</pre>
                <h2>Ultimo valor insertado en la tabla tasks:</h2>
                <p>$ultimo_registro</p>
                <h2>Respuesta de Asana:</h2>
                <pre>$response</pre>
            </body>
            </html>";
    } else {
        // Si no es una petición POST, mostramos un mensaje de error.
        echo "Error: Esta pagina solo acepta peticiones POST.";
    }
} catch (PDOException $e) {
    // En caso de error en la conexión o consulta, mostramos el mensaje de error
    echo "Error: " . $e->getMessage();
}
?>
