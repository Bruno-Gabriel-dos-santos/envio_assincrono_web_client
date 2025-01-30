<!-- resources/views/upload.blade.php -->
<input type="file" id="fileInput">
<button onclick="startUpload()">Enviar Arquivo</button>
<div id="progress"></div>

<script>
async function startUpload() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (!file) return;

    const CHUNK_SIZE = 9 * 1024 * 1024; // 9MB
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const fileId = Date.now() + '-' + Math.random().toString(36).substr(2);

    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
        const chunk = file.slice(chunkIndex * CHUNK_SIZE, (chunkIndex + 1) * CHUNK_SIZE);
        
        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('fileId', fileId);
        formData.append('chunkIndex', chunkIndex);
        formData.append('totalChunks', totalChunks);
        formData.append('fileName', file.name);
        formData.append('fileSize', file.size);

        try {
            const response = await fetch('/api/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();
            
            if (!result.success) {
                throw new Error('Erro no upload');
            }

            const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            document.getElementById('progress').innerHTML = `Progresso: ${progress}%`;
            
        } catch (error) {
            console.error('Erro:', error);
            return;
        }
    }
    document.getElementById('progress').innerHTML = 'Upload completo!';
}
</script>