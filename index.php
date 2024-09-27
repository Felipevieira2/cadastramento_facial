<?php 

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#317EFB" />
  <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">

  <link rel="manifest" href="manifest.json">
  <style>
    /* Reset básico para remover margens e paddings padrão */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      /* Altera para flex-start para melhor posicionamento em telas pequenas */
      min-height: 100vh;
      background-color: #e0e7ff;
      /* Cor de fundo mais moderna */
      padding: 20px;
    }

    h1 {
      margin-bottom: 20px;
      text-align: center;
      font-size: 2em;
      color: #1e3a8a;
      /* Cor do título */
    }

    .main-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 20px;
      width: 100%;
      max-width: 1000px;
      background-color: #ffffff;
      /* Cor de fundo do contêiner */
      border: 2px solid #c7d2fe;
      /* Borda sutil */
      border-radius: 15px;
      /* Bordas arredondadas */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      /* Sombra para profundidade */
      padding: 30px;
      /* Espaçamento interno */
    }

    #video-container {
      position: relative;
      width: 100%;
      max-width: 320px;
      aspect-ratio: 4 / 5;
      /* Mantém a proporção 320x400 */
      margin-bottom: 20px;
    }

    video,
    #canvas {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 10px;
      z-index: 1;
    }

    #canvas {
      display: none;
      object-fit: contain;
      background-color: #000;
      /* Fundo preto para melhor visualização */
    }

    #overlay {
      position: absolute;
      top: 7.5%; /* 30px / 400px */
      left: 15.625%; /* 50px / 320px */
      width: 68.75%; /* 220px / 320px */
      height: 90%; /* 360px / 400px */
      border: 0.5em solid rgba(0, 128, 0, 0.5); /* Bordas relativas */
      border-radius: 50% / 40%;
      box-sizing: border-box;
      z-index: 2;
    }

    .button {
      padding: 12px 24px;
      background-color: #3b82f6;
      /* Azul moderno */
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin: 5px 0;
      width: 100%;
      max-width: 200px;
      text-align: center;
      position: relative;
      /* Para posicionar o spinner */
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1em;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .button:hover:not([disabled]) {
      background-color: #2563eb;
      /* Azul mais escuro no hover */
      transform: translateY(-2px);
    }

    .button:disabled {
      background-color: #93c5fd;
      /* Azul claro para estado desabilitado */
      cursor: not-allowed;
      opacity: 0.8;
    }

    .button.secondary {
      background-color: #ef4444;
      /* Vermelho moderno */
    }

    .button.secondary:hover:not([disabled]) {
      background-color: #dc2626;
      /* Vermelho mais escuro no hover */
      transform: translateY(-2px);
    }

    #controls {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      /* Alinha os botões à esquerda */
      gap: 15px;
      width: 100%;
      max-width: 220px;
      /* Define uma largura máxima para os botões */
      margin-top: 20px;
    }

    #flash {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: white;
      opacity: 0;
      z-index: 3;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }

    .hidden {
      display: none;
    }

    #inputs-text-container {
      display: flex;
      flex-direction: column;
      gap: 15px;
      align-items: flex-start;
      width: 100%;
      max-width: 350px;
    }

    label {
      font-weight: 600;
      margin-bottom: 5px;
      font-size: 1em;
      color: #1f2937;
      /* Cor do texto */
    }

    input[type="text"] {
      padding: 12px 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      width: 100%;
      font-size: 1em;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="text"]:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
      outline: none;
    }

    /* Estilos para o Spinner */
    .spinner {
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top: 4px solid white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
      margin-right: 10px;
      display: none;
      /* Inicialmente escondido */
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Responsividade */
    /* As propriedades de #overlay agora são relativas, então as media queries não precisam alterar top, left, width e height */
    @media (max-width: 576px) {
      #overlay {
        /* Ajustes adicionais podem ser feitos se necessário */
        border: 0.4em solid rgba(0, 128, 0, 0.5);
      }
    }

    /* Estilos para a Modal */
    .modal {
      display: none;
      /* Escondida por padrão */
      position: fixed;
      z-index: 1000;
      /* Fica acima de todos os outros elementos */
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      /* Habilita scroll se necessário */
      background-color: rgba(0, 0, 0, 0.5);
      /* Fundo semitransparente */
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-content {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 20px 30px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
      position: relative;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modal-success {
      border-left: 5px solid #22c55e;
      /* Verde */
    }

    .modal-error {
      border-left: 5px solid #ef4444;
      /* Vermelho */
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    .modal-header h2 {
      display: flex;
      align-items: center;
      font-size: 1.5em;
      color: #1f2937;
    }

    .modal-icon {
      display: inline-block;
      margin-right: 10px;
    }

    .modal-success .modal-icon::before {
      content: '✔️'; /* Marca de verificação */
      color: #22c55e; /* Verde */
      font-size: 1.5em;
    }

    .modal-error .modal-icon::before {
      content: '❌'; /* Cruz */
      color: #ef4444; /* Vermelho */
      font-size: 1.5em;
    }

    .modal-body {
      font-size: 1em;
      color: #374151;
      margin-bottom: 20px;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 1.5em;
      cursor: pointer;
      color: #6b7280;
      transition: color 0.3s ease;
    }

    .close-btn:hover {
      color: #111827;
    }

    .modal-footer {
      text-align: right;
    }

    .modal-footer .button {
      background-color: #3b82f6;
    }

    .modal-footer .button:hover:not([disabled]) {
      background-color: #2563eb;
    }
  </style>
  <title>Cadastro FaceID</title>
</head>

<body>
  <h1>Cadastro FaceID</h1>

  <div class="main-container">
    <!-- Primeira coluna para o vídeo -->
    <div id="video-container">
      <!-- Flash Effect -->
      <div id="flash"></div>

      <!-- Video preview para a câmera -->
      <video id="video" autoplay playsinline></video>

      <!-- Frame overlay para simular interface semelhante a um banco -->
      <div id="overlay"></div>

      <!-- Canvas para exibir a foto capturada -->
      <canvas id="canvas" width="320" height="320"></canvas>
    </div>

    <!-- Segunda coluna para inputs -->
    <div id="inputs-text-container">
      <label for="name">Nome:</label>
      <input type="text" id="name" name="name" placeholder="Digite seu nome">

      <label for="apartamento">Apartamento:</label>
      <input type="text" id="apartamento" name="apartamento" placeholder="Digite seu apartamento">

    </div>
  </div>

  <!-- Botões de controle -->
  <div id="controls">
    <button id="capture-btn" class="button">Capturar</button>
    <button id="attach-btn" class="button">Anexar Foto</button> <!-- Novo Botão -->
    <button id="recapture-btn" class="button secondary hidden">Tentar novamente</button>
    <button id="upload-btn" class="button hidden">
      <span class="spinner" id="upload-spinner"></span>
      <span class="button-text">Usar essa foto</span>
    </button>
  </div>

  <!-- Formulário para upload da imagem -->
  <form id="uploadForm" enctype="multipart/form-data">
    <input type="file" id="fileInput" name="fileInput" accept="image/*" style="display:none;">
  </form>

  <!-- Modal de Feedback -->
  <div id="feedback-modal" class="modal" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modal-title"><span class="modal-icon"></span>Título</h2>
        <button class="close-btn" id="close-modal">&times;</button>
      </div>
      <div class="modal-body" id="modal-message">
        Mensagem
      </div>
      <div class="modal-footer">
        <button class="button" id="modal-ok-btn">OK</button>
      </div>
    </div>
  </div>

  <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('capture-btn');
    const attachBtn = document.getElementById('attach-btn'); // Novo botão
    const recaptureBtn = document.getElementById('recapture-btn');
    const uploadBtn = document.getElementById('upload-btn');
    const fileInput = document.getElementById('fileInput');
    const flash = document.getElementById('flash');
    const overlay = document.getElementById('overlay');
    const uploadSpinner = document.getElementById('upload-spinner');
    const uploadBtnText = document.querySelector('#upload-btn .button-text');
    let stream;

    // Modal Elements
    const modal = document.getElementById('feedback-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const closeModalBtn = document.getElementById('close-modal');
    const modalOkBtn = document.getElementById('modal-ok-btn');

    // Inicializa a câmera
    async function startCamera() {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            aspectRatio: 0.8 // 320/400 = 0.8
          }
        });
        video.srcObject = stream;
      } catch (err) {
        console.error('Erro ao acessar a câmera: ', err);
        showModal('Erro', 'Não foi possível acessar a câmera. Por favor, verifique as permissões.', 'error');
      }
    }

    // Mostra a animação de flash para simular captura de foto
    function flashEffect() {
      flash.style.opacity = '1';
      setTimeout(() => {
        flash.style.opacity = '0';
      }, 100);
    }

    // Função para mostrar a modal
    function showModal(title, message, type) {
      // Atualiza o título com o texto fornecido
      // Para garantir que o ícone seja exibido corretamente, mantemos o span.modal-icon intacto
      modalTitle.innerHTML = `<span class="modal-icon"></span>${title}`;
      modalMessage.textContent = message;

      // Remover classes anteriores
      modal.classList.remove('modal-success', 'modal-error');

      if (type === 'success') {
        modal.classList.add('modal-success');
      } else if (type === 'error') {
        modal.classList.add('modal-error');
      }

      modal.style.display = 'flex';
    }

    // Função para esconder a modal e resetar os botões
    function hideModalAndResetButtons() {
      modal.style.display = 'none';

      // Resetar os botões para o estado inicial
      captureBtn.classList.remove('hidden');
      attachBtn.classList.remove('hidden'); // Mostra o botão de anexar novamente
      recaptureBtn.classList.add('hidden');
      uploadBtn.classList.add('hidden');

      // Mostrar o vídeo novamente caso tenha sido ocultado
      video.style.display = 'block';
      overlay.style.display = 'block';

      // Limpar o canvas
      canvas.style.display = 'none';
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Limpar o fileInput
      fileInput.value = '';
    }

    // Função para obter o CSRF Token da tag meta
    function getCsrfToken() {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    }

    // Captura a foto e desenha no canvas
    captureBtn.addEventListener('click', () => {
      const context = canvas.getContext('2d');

      // Atualiza as dimensões do canvas para corresponder ao vídeo
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;

      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      // Esconde o vídeo e o overlay, mostra a foto capturada no canvas
      video.style.display = 'none';
      overlay.style.display = 'none';
      canvas.style.display = 'block';

      flashEffect(); // Simula flash

      captureBtn.classList.add('hidden');
      attachBtn.classList.add('hidden'); // Esconde o botão de anexar
      recaptureBtn.classList.remove('hidden');
      uploadBtn.classList.remove('hidden');

      // Converte a imagem do canvas para blob e prepara para upload
      canvas.toBlob(blob => {
        if (blob) {
          console.log('Blob criado:', blob);
          const file = new File([blob], 'photo.png', {
            type: 'image/png'
          });
          console.log('File criado:', file);

          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          fileInput.files = dataTransfer.files;
          console.log('Arquivo atribuído ao fileInput:', fileInput.files[0]);
        } else {
          console.error('Falha ao converter o canvas para blob.');
        }
      }, 'image/png');
    });

    // Evento para o botão de anexar foto
    attachBtn.addEventListener('click', () => {
      fileInput.click(); // Simula o clique no input de arquivo oculto
    });

    // Evento para quando um arquivo for selecionado
    fileInput.addEventListener('change', () => {
      if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];

        // Verifica se o arquivo é uma imagem
        if (!file.type.startsWith('image/')) {
          showModal('Erro', 'Por favor, selecione um arquivo de imagem válido.', 'error');
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          const img = new Image();
          img.onload = function() {
            // Ajusta o tamanho do canvas conforme a imagem
            canvas.width = img.width;
            canvas.height = img.height;

            const context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, canvas.width, canvas.height);

            // Esconde o vídeo e o overlay, mostra a foto no canvas
            video.style.display = 'none';
            overlay.style.display = 'none';
            canvas.style.display = 'block';

            // Atualiza os botões
            captureBtn.classList.add('hidden');
            attachBtn.classList.add('hidden'); // Esconde o botão de anexar
            recaptureBtn.classList.remove('hidden');
            uploadBtn.classList.remove('hidden');
          }
          img.src = e.target.result;
        }
        reader.readAsDataURL(file);
      }
    });

    // Função para recapturar a foto: reinicia a visualização da câmera
    recaptureBtn.addEventListener('click', () => {
      canvas.style.display = 'none'; // Esconde a foto capturada
      video.style.display = 'block'; // Mostra o vídeo novamente
      overlay.style.display = 'block'; // Mostra o overlay
      recaptureBtn.classList.add('hidden');
      uploadBtn.classList.add('hidden');
      captureBtn.classList.remove('hidden');
      attachBtn.classList.remove('hidden'); // Mostra o botão de anexar novamente

      // Limpa o fileInput
      fileInput.value = '';
      console.log('fileInput limpo:', fileInput.files);
    });

    // Trata o envio do formulário (upload da imagem)
    uploadBtn.addEventListener('click', (e) => {
      e.preventDefault(); // Evita o comportamento padrão do botão

      // Verifica se há um arquivo para upload
      if (!fileInput.files[0]) {
        showModal('Erro', 'Nenhuma foto para fazer upload.', 'error');
        return;
      }

      // Esconde os botões capture e recapture
      captureBtn.classList.add('hidden');
      attachBtn.classList.add('hidden');
      recaptureBtn.classList.add('hidden');

      // Mostra o spinner e altera o texto do botão para "Enviando"
      uploadSpinner.style.display = 'inline-block';
      uploadBtnText.textContent = 'Enviando';
      uploadBtn.disabled = true;
      uploadBtn.style.cursor = 'not-allowed';
      uploadBtn.style.opacity = '0.8';

      const formData = new FormData();
      formData.append('fileInput', fileInput.files[0]);

      // Defina sua API Key aqui (não recomendado expor no front-end)
      const apiKey = 'fff'; // Substitua pela sua API Key real
      // Obtenha o CSRF Token da meta tag
      const csrfToken = getCsrfToken();
      console.log('CSRF Token:', csrfToken); // Log para depuração

      fetch('upload.php', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${apiKey}`,
            'X-CSRF-Token': `${csrfToken}`
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(result => {
          console.log('Resposta do servidor:', result);

          if (result.errors) {
            // Se houver erros, mostrar na modal
            showModal('Erro', result.errors[0].message, 'error');
          } else if (result.message && result.data) {
            // Sucesso
            showModal('Sucesso', result.message, 'success');
          } else {
            // Resposta inesperada
            showModal('Erro', 'Resposta do servidor inválida.', 'error');
          }

          // Resetar o botão de upload após o sucesso ou erro
          uploadSpinner.style.display = 'none';
          uploadBtn.disabled = false;
          uploadBtn.style.cursor = 'pointer';
          uploadBtn.style.opacity = '1';
          uploadBtnText.textContent = 'Usar essa foto';
        })
        .catch(error => {
          console.error('Erro:', error);
          showModal('Erro', 'Ocorreu um erro durante o upload.', 'error');

          // Esconde o spinner e reativa o botão de upload
          uploadSpinner.style.display = 'none';
          uploadBtn.disabled = false;
          uploadBtn.style.cursor = 'pointer';
          uploadBtn.style.opacity = '1';
          uploadBtnText.textContent = 'Usar essa foto';
        });
    });

    // Evento para fechar a modal quando o botão de fechar for clicado
    closeModalBtn.addEventListener('click', hideModalAndResetButtons);

    // Evento para fechar a modal quando o botão OK for clicado
    modalOkBtn.addEventListener('click', hideModalAndResetButtons);

    // Fecha a modal quando o usuário clica fora do conteúdo da modal
    window.addEventListener('click', (event) => {
      if (event.target == modal) {
        hideModalAndResetButtons();
      }
    });

    // Inicia a câmera quando a página carrega
    window.addEventListener('load', startCamera);
  </script>
</body>

</html>
