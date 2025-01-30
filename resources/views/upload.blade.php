<!DOCTYPE html>
<html>
<head>
    <title>Upload de Arquivo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <input type="file" id="fileInput">
    <button onclick="startUpload()">Enviar Arquivo</button>
    <div id="progress" style="width: 300px; height: 20px; background: #eee;">
        <div id="progressBar" style="width: 0%; height: 100%; background: #4CAF50;"></div>
    </div>

    <script>
        const CHUNK_SIZE = 7 * 1024 * 1024; // 7MB

        async function startUpload() {
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Selecione um arquivo primeiro!');
                return;
            }

            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            const uniqueIdentifier = Date.now() + '-' + Math.random().toString(36).substr(2);

            for (let chunkNumber = 0; chunkNumber < totalChunks; chunkNumber++) {
                const chunk = file.slice(
                    chunkNumber * CHUNK_SIZE,
                    (chunkNumber + 1) * CHUNK_SIZE
                );

                const formData = new FormData();
                formData.append('file', chunk);
                formData.append('chunkNumber', chunkNumber);
                formData.append('totalChunks', totalChunks);
                formData.append('originalName', file.name);
                formData.append('uniqueIdentifier', uniqueIdentifier);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                try {
                    await fetch('/upload-chunk', {
                        method: 'POST',
                        body: formData
                    });

                    const progress = ((chunkNumber + 1) / totalChunks) * 100;
                    document.getElementById('progressBar').style.width = progress + '%';
                } catch (error) {
                    console.error('Erro no upload:', error);
                    alert('Erro ao enviar arquivo!');
                    return;
                }
            }

            alert('Upload concluído com sucesso!');
        }
    </script>
</body>
</html>