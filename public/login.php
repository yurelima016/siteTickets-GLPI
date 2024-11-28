<!DOCTYPE html>
<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <!-------------------------------------------------------- Styles -------------------------------------------------------->
        <link rel="icon" href="../assets/images/favicon-transparent.svg" sizes="48x48" type="image">
        <link rel="stylesheet" href="../assets/css/loginStyle.css">
        <link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.3/dist/jBox.all.min.css" rel="stylesheet">
        <!-----------------------------------------------------------  ----------------------------------------------------------->
    </head>

    <body>
        <section id="form-section">
            <header>
                <img src="../assets/images/logo-gray.svg" alt="Única Logo" onclick="toErrorPage()">
            </header>
            <main>
                <form id="login-form" method="POST" action="login.php">
                    <h1>Faça login na sua conta</h1>

                    <div class="input-container">
                        <label for="username">Usuário:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="input-container">
                        <label for="password" class="input-label">Senha:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="btn-container">
                        <button type="submit" id="login-button">Entrar</button>
                    </div>
                </form>
            </main>
            <footer>
                <h5>Única Confecção © 1994-2024</h5>
            </footer>
        </section>
        <section id="img-section"></section>
        <!------------------------------------------------------- Scripts ------------------------------------------------------->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.3/dist/jBox.all.min.js"></script>
        <script src="../assets/js/script.js"></script>
        <!----------------------------------------------------------  ---------------------------------------------------------->
    </body>

</html>

<?php

//Iniciar sessão PHP
session_start();

//Configuração do Banco de Dados
$host = 'localhost';
$dbname = 'glpidb';
$username = 'root';
$password = '';

//Conectar ao banco
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

//Configurações da API do GLPI
$glpiApiUrl = "http://localhost/glpi/apirest.php";
$glpiApiToken = "b584dnObPObUCIOtr9d08UymhDopNy0Wb8R4Myat"; //App_Token do cliente: GLPI ADMIN

//Função para iniciar sessão da API
function iniciarSessaoGLPI($apiUrl, $apiToken, $userToken) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl . "/initSession");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "App_Token: $apiToken",
        "Authorization: user_token $userToken"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return $data['session_token'];
    } 
    else {
        return null;
    }
}

//Validação do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    //Verificar se o usuário existe no Banco de Dados do GLPI
    $stmt = $pdo->prepare("SELECT password, id,firstname, api_token FROM glpi_users WHERE name = :user");
    $stmt->bindParam(':user', $user);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $hashedPassword = $result['password'];
        $userToken = $result['api_token'];

        if (password_verify($pass, $hashedPassword)) {
            //Iniciar a sessão da API
            $sessionToken = iniciarSessaoGLPI($glpiApiUrl, $glpiApiToken, $userToken);

            if ($sessionToken) {
                $_SESSION['user_id'] = $result['id']; // Salva o id do usuário
                $_SESSION['user_name'] = $result['firstname']; // Salva o nome do usuário
                $_SESSION['session_token'] = $sessionToken; // Salva o token da sessão API
                echo "<script>toMainPage();</script>";
            } 
            else {
                echo "<script>popupAlert('Erro ao iniciar a sessão na API do GLPI. Verifique o token do usuário.');</script>";
            }
        } 
        else {
            echo "<script>popupAlert('Senha incorreta!');</script>";
        }
    } 
    else {
        echo "<script>popupAlert('Usuário não encontrado!');</script>";
    }
}
?>
