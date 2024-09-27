<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se o arquivo foi enviado
    if (isset($_FILES['fileInput']) && $_FILES['fileInput']['error'] === UPLOAD_ERR_OK) {
        // Configuração de destino no servidor
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
        }

        // Extrai informações do arquivo
        $fileTmpPath = $_FILES['fileInput']['tmp_name'];
        $fileName = $_FILES['fileInput']['name'];
        $fileSize = $_FILES['fileInput']['size'];
        $fileType = $_FILES['fileInput']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Define um nome único para o arquivo
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Extensões permitidas
        $allowedFileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedFileExtensions)) {
            // Caminho de destino completo
            $destPath = $uploadDir . $newFileName;

            // Move o arquivo para o diretório de destino
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                echo "Arquivo carregado com sucesso: " . $newFileName;
            } else {
                echo "Erro ao mover o arquivo para o diretório de destino.";
            }
        } else {
            echo "Extensão de arquivo não permitida.";
        }
    } else {
        echo "Nenhum arquivo enviado ou erro no upload.";
    }
}
?>

