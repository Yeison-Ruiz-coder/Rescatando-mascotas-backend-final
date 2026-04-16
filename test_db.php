<?php
try {
    $pdo = new PDO(
        'mysql:host=mainline.proxy.rlwy.net;port=55143;dbname=railway',
        'root',
        'vyRAsPHWROIebzZBjJXdMHJiTOEwOKvs',
        [
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::MYSQL_ATTR_SSL_CA => '',
        ]
    );
    echo "✅ ¡Conexión exitosa con Laravel!\n";

    $result = $pdo->query("SELECT VERSION() as version");
    $row = $result->fetch();
    echo "📦 Versión MySQL: " . $row['version'] . "\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
