<?php
session_start();

// Carrega dados do MySQL se o usuário estiver logado
$dados = [];
if (isset($_SESSION['usuario_id'])) {
    require_once 'conexao.php';
    $stmt = $conn->prepare("SELECT * FROM imc_registros WHERE usuario_id = ? ORDER BY data DESC");
    $stmt->execute([$_SESSION['usuario_id']]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function classificaIMC($imc) {
    if ($imc < 18.5) return 'Abaixo do peso';
    if ($imc < 25) return 'Peso normal';
    if ($imc < 30) return 'Sobrepeso';
    if ($imc < 35) return 'Obesidade Grau I';
    if ($imc < 40) return 'Obesidade Grau II';
    return 'Obesidade Grau III';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IMC - DiaCare</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <style>
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 9999; }
    .modal-content { background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; }
    .modal-content h3 { margin-top: 0; color: #007acc; }
    .modal-content input { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
    .modal-content button { background-color: #007acc; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; }
    .close-btn { float: right; color: red; font-size: 20px; cursor: pointer; }
    .fa-edit { color: #007acc; }
    .fa-trash { color: red; }
  </style>
</head>
<body>
<header class="header">
  <div class="logo-container">
    <h1>DiaCare</h1>
    <div class="heart"></div>
  </div>
</header>

<main>
  <div class="form">
    <h2>Calculadora de IMC</h2>
    <form action="imc_controller.php?action=salvar" method="POST">
      <?php if (isset($_SESSION['usuario_id'])): ?>
        <input type="hidden" name="usuario_id" value="<?= $_SESSION['usuario_id'] ?>">
      <?php endif; ?>
      <div class="input-group">
        <label for="data">Data</label>
        <input type="date" name="data" required>
      </div>
      <div class="input-group">
        <label for="altura">Altura (m)</label>
        <input type="number" step="0.01" name="altura" required placeholder="Ex: 1.70">
      </div>
      <div class="input-group">
        <label for="peso">Peso (kg)</label>
        <input type="number" step="0.1" name="peso" required placeholder="Ex: 65">
      </div>
      <button type="submit" class="form-button" <?= !isset($_SESSION['usuario_id']) ? 'disabled title="Faça login para salvar"' : '' ?>>Calcular IMC</button>
    </form>

    <h3 style="margin-top: 30px">Histórico</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Data</th>
          <th>Altura</th>
          <th>Peso</th>
          <th>IMC</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['data']) ?></td>
            <td><?= htmlspecialchars($item['altura']) ?></td>
            <td><?= htmlspecialchars($item['peso']) ?></td>
            <td><?= number_format($item['imc'], 1) ?> (<?= classificaIMC($item['imc']) ?>)</td>
            <td>
              <button onclick="abrirModal('<?= $item['id'] ?>')" title="Editar"><i class="fas fa-edit"></i></button>
              <a href="imc_controller.php?action=excluir&id=<?= $item['id'] ?>" onclick="return confirm('Deseja excluir?')" title="Excluir"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal -->
<div id="modal-editar" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="fecharModal()">&times;</span>
    <h3>Editar Registro</h3>
    <form id="form-editar">
      <input type="hidden" name="id" id="editar-id">
      <label>Data: <input type="date" name="data" id="editar-data" required></label>
      <label>Altura: <input type="number" step="0.01" name="altura" id="editar-altura" required></label>
      <label>Peso: <input type="number" step="0.1" name="peso" id="editar-peso" required></label>
      <button type="submit">Atualizar</button>
    </form>
  </div>
</div>

<a href="main.html" class="botao-voltar">Voltar</a>
  
<footer class="footer">
  <p>&copy; 2025 DiaCare. Todos os direitos reservados.</p>
</footer>

<script>
function abrirModal(id) {
  fetch('imc_controller.php?action=editar&id=' + id)
    .then(res => res.json())
    .then(dado => {
      if (dado.erro) {
        alert(dado.erro);
      } else {
        document.getElementById('editar-id').value = dado.id;
        document.getElementById('editar-data').value = dado.data;
        document.getElementById('editar-altura').value = dado.altura;
        document.getElementById('editar-peso').value = dado.peso;
        document.getElementById('modal-editar').style.display = 'flex';
      }
    });
}

function fecharModal() {
  document.getElementById('modal-editar').style.display = 'none';
}

document.getElementById('form-editar').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('imc_controller.php?action=atualizar', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.sucesso) {
      location.reload();
    } else {
      alert(data.erro || 'Erro ao atualizar');
    }
  });
});
</script>
</body>
</html>
