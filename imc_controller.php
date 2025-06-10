<?php
session_start();
header('Content-Type: application/json');
require_once 'conexao.php'; // Importa a conexão com o banco

$action = $_GET['action'] ?? '';

// Bloqueia ações se não estiver logado
if (in_array($action, ['salvar', 'editar', 'atualizar', 'excluir']) && !isset($_SESSION['usuario_id'])) {
    die(json_encode(['erro' => 'Faça login para realizar esta ação.']));
}

// SALVAR
if ($action === 'salvar') {
    $altura = floatval($_POST['altura'] ?? 0);
    $peso = floatval($_POST['peso'] ?? 0);
    $data = $_POST['data'] ?? date('Y-m-d');
    $usuario_id = $_POST['usuario_id'];

    if ($altura > 0 && $peso > 0) {
        $imc = round($peso / ($altura * $altura), 1);
        $id = uniqid();

        // Insere no banco de dados
        $stmt = $conn->prepare("INSERT INTO imc_registros (id, data, altura, peso, imc, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $data, $altura, $peso, $imc, $usuario_id]);

        header('Location: imc.php');
    } else {
        echo json_encode(['erro' => 'Altura ou peso inválido']);
    }
    exit;
}

// EDITAR
if ($action === 'editar') {
    $id = $_GET['id'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM imc_registros WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    $dado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dado) {
        echo json_encode($dado);
    } else {
        echo json_encode(['erro' => 'Registro não encontrado']);
    }
    exit;
}

// ATUALIZAR
if ($action === 'atualizar') {
    $id = $_POST['id'] ?? '';
    $data = $_POST['data'] ?? '';
    $altura = floatval($_POST['altura'] ?? 0);
    $peso = floatval($_POST['peso'] ?? 0);

    if ($altura > 0 && $peso > 0) {
        $imc = round($peso / ($altura * $altura), 1);
        $stmt = $conn->prepare("UPDATE imc_registros SET data = ?, altura = ?, peso = ?, imc = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$data, $altura, $peso, $imc, $id, $_SESSION['usuario_id']]);

        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['erro' => 'Altura ou peso inválido']);
    }
    exit;
}

// EXCLUIR
if ($action === 'excluir') {
    $id = $_GET['id'] ?? '';
    $stmt = $conn->prepare("DELETE FROM imc_registros WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);

    header('Location: imc.php');
    exit;
}
?>
