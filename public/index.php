<?php
// Inicia a sessão para acessar os dados
session_start();

// Verifica se o usuário está logado
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'];
    $tokenApiSession = $_SESSION['session_token'];
} else {
    // Redireciona para a página de login caso a sessão não exista
    header("Location: login.php");
    exit;
}

// Configurações da API GLPI
$glpiApiUrl = "http://localhost/glpi/apirest.php";
$glpiApiToken = "b584dnObPObUCIOtr9d08UymhDopNy0Wb8R4Myat"; // App_Token do cliente: GLPI ADMIN

// Função para abrir o chamado
function abrirChamado($apiUrl, $sessionToken, $apiToken, $titulo, $descricao, $tipo, $prioridade, $urgencia, $impacto) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl . "/Ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Session-Token: $sessionToken",
        "App_Token: $apiToken",
        "Content-Type: application/json"
    ]);

    // Montar o corpo da requisição
    $body = [
        "input" => [
            "name" => $titulo,
            "content" => $descricao,
            "type" => $tipo,
            "priority" => $prioridade,
            "urgency" => $urgencia,
            "impact" => $impacto
        ]
    ];

    // Converter o corpo em JSON
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

    // Executar a requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 201) { // Chamado criado com sucesso
        return json_decode($response, true);
    } else {
        return [
            "error" => "Erro ao abrir chamado",
            "status_code" => $httpCode,
            "response" => $response
        ];
    }
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $prioridade = $_POST['prioridade'];
    $urgencia = $_POST['urgencia'];
    $impacto = $_POST['impacto'];

    $tipoChamado = isset($_POST['options']) ? $_POST['options'] : null;

    if (!$tipoChamado) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    popupAlert('Por favor, selecione o tipo do chamado.');
                });
              </script>";
        exit;
    }

    // Chama a função para abrir o chamado
    $resultado = abrirChamado($glpiApiUrl, $tokenApiSession, $glpiApiToken, $titulo, $descricao, $tipoChamado, $prioridade, $urgencia, $impacto);

    if (isset($resultado['error'])) {
        $mensagem = "Erro ao abrir chamado: " . $resultado['error'];
    } else {
        $mensagem = "Chamado aberto! ID do chamado: " . $resultado['id'];
    }

    // Exibir mensagem ao usuário
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                ticketAlert('$mensagem');
            });
        </script>";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

    <head>      
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Página Inicial</title>
        <!-------------------------------------------------------- Styles -------------------------------------------------------->
        <link rel="icon" href="../assets/images/favicon-transparent.svg" sizes="48x48" type="image">
        <link rel="stylesheet" href="../assets/css/indexStyle.css">
        <link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.3/dist/jBox.all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <!-----------------------------------------------------------  ----------------------------------------------------------->
    </head>

    <body>
        <section id="img-section">
            <h1>Abertura de Chamados</h1>
            <img src="../assets/images/attendant-image.svg" alt="">
        </section>

        <section id="form-section">
            <img src="../assets/images/logo-gray.svg" alt="logo-gray.svg" onclick="toErrorPage()">

            <form method="POST" action="index.php" enctype="multipart/form-data">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" required>

                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" placeholder="Descreva o incidente ou requisição" required></textarea>
                
                <label for="radio-container">Tipo do chamado</label>
                <div id="radio-container">
                    <input type="radio" id="tipo-incidente" name="options" value="1" checked>
                    <label for="tipo-incidente">Incidente</label>
                    <input type="radio" id="tipo-requisicao" name="options" value="2">
                    <label for="tipo-requisicao">Requisição</label>
                </div>

                <label for="prioridade">Prioridade</label>
                <select id="prioridade" name="prioridade" required>
                    <option value="1">Muito Baixa</option>
                    <option value="2">Baixa</option>
                    <option value="3" selected>Média</option>
                    <option value="4">Alta</option>
                    <option value="5">Muito Alta</option>
                    <option value="6">Crítica</option>
                </select>

                <div class="range-container">
                    <label for="urgencia" id="range-label">Urgência</label>
                    <div>
                        <input type="range" id="urgencia" name="urgencia" min="1" max="5" step="1">
                        <output id="urgencia-output" for="urgencia">Média</output>
                    </div>
                </div>

                <div class="range-container">
                    <label for="impacto" id="range-label">Impacto</label>
                    <div>
                        <input type="range" id="impacto" name="impacto" min="1" max="5" step="1">
                        <output id="impacto-output">Média</output>
                    </div>
                </div>

                <button type="submit">Abrir chamado</button>
            </form>
        </section>

        <button id="logout-button" class="btn btn-outline-danger" onclick="toLoginPage();">Sair<i class="bi bi-box-arrow-right" ></i></button>
        <!------------------------------------------------------- Scripts ------------------------------------------------------->
        <script src="../assets/js/script.js"></script>
        <script src="../assets/js/sweetalert2.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.3/dist/jBox.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <!-----------------------------------------------------------  ----------------------------------------------------------->
    </body>

</html>
