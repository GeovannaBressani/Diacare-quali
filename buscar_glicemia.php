<?php
require 'conexao.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit();
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    $sql = "SELECT data, valor_glicemia, periodo FROM glicemia 
            WHERE usuario_id = :usuario_id 
            ORDER BY data DESC LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
