<?php
// Habilitar exibição de erros para desenvolvimento (desativar em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Inclui o autoload do Composer
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

// Gerar CSRF Token se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Função para validar o CSRF Token
function validateCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Definir cabeçalhos de resposta
header('Content-Type: application/json; charset=UTF-8');

// Função para enviar respostas JSON
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Função para validar API Key
function isValidApiKey($apiKey) {
    // Aqui você deve implementar a lógica para validar a chave API.
    // Isso pode incluir verificar no banco de dados se a chave existe e está ativa.
    $validApiKeys = ['fff']; // Substitua por uma lista segura ou consulta ao banco
    return in_array($apiKey, $validApiKeys);
}

// Autenticação: Verificar API Key no cabeçalho Authorization
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    sendResponse(['error' => 'Chave de autenticação ausente.'], 401);
}

$authHeader = $headers['Authorization'];
list($type, $apiKey) = explode(" ", $authHeader, 2);

// Verificar se o tipo de autorização é Bearer
if (strcasecmp($type, 'Bearer') != 0 || !isValidApiKey($apiKey)) {
    sendResponse(['errors' => [['code' => 'AUTH-002', 'message' => 'Chave de autenticação inválida.']]], 401);
}

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    sendResponse(['errors' => [['code' => 'METHOD-001', 'message' => 'Método de requisição não permitido.']]], 405); // 405 Method Not Allowed
}

// Verificar o CSRF Token
if (!isset($headers['X-CSRF-Token']) || !validateCsrfToken($headers['X-CSRF-Token'])) {
    sendResponse(['errors' => [['code' => 'CSRF-001', 'message' => 'Token CSRF inválido ou ausente.']]], 403);
}

// Definir limites e configurações
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 MB
$allowedFileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Verificar se o arquivo foi enviado sem erros
if (!isset($_FILES['fileInput']) || $_FILES['fileInput']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(['errors' => [['code' => 'UPLOAD-001', 'message' => 'Nenhum arquivo enviado ou erro no upload.']]], 400);
}

// Extrair informações do arquivo
$fileTmpPath = $_FILES['fileInput']['tmp_name'];
$fileName = $_FILES['fileInput']['name'];
$fileSize = $_FILES['fileInput']['size'];
$fileType = mime_content_type($fileTmpPath); // Verifica o MIME type real do arquivo
$fileNameCmps = explode(".", $fileName);
$fileExtension = strtolower(end($fileNameCmps));


// Validar extensão do arquivo
if (!in_array($fileExtension, $allowedFileExtensions)) {
    sendResponse(['errors' => [['code' => 'UPLOAD-002', 'message' => 'Extensão de arquivo não permitida.']]], 400);
}

// Validar MIME type do arquivo
if (!in_array($fileType, $allowedMimeTypes)) {
    sendResponse(['errors' => [['code' => 'UPLOAD-003', 'message' => 'Tipo de arquivo inválido.']]], 400);
}

// Validar tamanho do arquivo
if ($fileSize > MAX_FILE_SIZE) {
    sendResponse(['errors' => [['code' => 'UPLOAD-004', 'message' => 'O arquivo excede o tamanho máximo permitido de 2MB.']]], 400);
}

// (Opcional) Escanear arquivo para malware
// Você pode integrar um serviço de escaneamento de malware aqui, como ClamAV ou serviços externos.

// Gerar nome único para o arquivo
$newFileName = md5(time() . $fileName) . '.' . $fileExtension;

// Definir caminho seguro para salvar o arquivo (fora do diretório web)
$uploadFileDir = __DIR__ . '/uploads/';
if (!is_dir($uploadFileDir)) {
    mkdir($uploadFileDir, 0755, true); // Criar diretório se não existir
}
$destPath = $uploadFileDir . $newFileName;

// (Opcional) Mover o arquivo para o diretório seguro
/*
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    sendResponse(['error' => 'Erro ao mover o arquivo para o diretório de destino.'], 500);
}
*/

// Simulação de resposta (sucesso ou erro)
try {
    // Inicializa o cliente Guzzle
    $client = new Client();

    // Simula um retorno aleatório (sucesso ou erro)
    $responses = ['success', 'error']; // Corrigido para incluir 'success'
    $randomResponse = $responses[array_rand($responses)];

    if ($randomResponse === 'success') {
        // Simula um ID de usuário e face
        $userId = rand(1000, 9999);
        $faceId = rand(10000, 99999);

        // Formata a resposta de sucesso conforme a documentação
        $response = [
            'user' => [
                'id' => $userId,
                'faces' => [
                    [
                        'id' => $faceId,
                        'status' => 'active',
                        'quality' => rand(70, 100), // Qualidade aleatória
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]
            ]
        ];

        sendResponse(['message' => 'Cadastro realizado com sucesso.', 'data' => $response], 200);
    } else {
        // Formata a resposta de erro conforme a documentação
        $errors = [
            [
                'code' => 'FACE-001',
                'message' => 'Imagem facial não detectada.'
            ],
            [
                'code' => 'FACE-002',
                'message' => 'Imagem com qualidade insuficiente.'
            ]
        ];

        $randomError = $errors[array_rand($errors)];

        $response = [
            'errors' => [$randomError]
        ];

        sendResponse($response, 400); // 400 Bad Request
    }

    // Caso queira realmente enviar a requisição para a API, descomente o código abaixo e ajuste conforme necessário

    /*
    $apiResponse = $client->request('POST', 'https://api.seuendpoint.com/faces', [
        'headers' => [
            'Authorization' => 'Bearer SEU_TOKEN_AQUI',
            'Content-Type' => 'multipart/form-data'
        ],
        'multipart' => [
            [
                'name'     => 'face',
                'contents' => fopen($fileTmpPath, 'r'),
                'filename' => $fileName
            ],
            // Outros campos necessários pela API podem ser adicionados aqui
        ]
    ]);

    // Processa a resposta da API
    $apiResponseBody = json_decode($apiResponse->getBody()->getContents(), true);
    sendResponse($apiResponseBody, $apiResponse->getStatusCode());
    */

} catch (Exception $e) {
    // Log do erro no servidor (não expor detalhes ao cliente)
    error_log('Erro ao processar upload: ' . $e->getMessage());

    sendResponse(['error' => 'Erro interno no servidor. Por favor, tente novamente mais tarde.'], 500);
}
?>
