document.getElementById('pdfForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const statusDiv = document.getElementById('status');
    const protectBtn = document.getElementById('protectBtn');
    
    // Reset status
    statusDiv.className = 'status-message';
    statusDiv.textContent = '';
    statusDiv.style.display = 'none';
    
    // Show processing message
    statusDiv.textContent = 'Processing your PDF...';
    statusDiv.className = 'status-message';
    statusDiv.style.display = 'block';
    protectBtn.disabled = true;
    
    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            statusDiv.textContent = data.message;
            statusDiv.className = 'status-message success';
            
            // Create download link
            const downloadLink = document.createElement('a');
            downloadLink.href = data.downloadLink;
            downloadLink.textContent = 'Download Protected PDF';
            downloadLink.className = 'download-link';
            downloadLink.download = 'protected_' + document.getElementById('pdfFile').files[0].name;
            
            statusDiv.appendChild(document.createElement('br'));
            statusDiv.appendChild(downloadLink);
        } else {
            statusDiv.textContent = data.message || 'Error processing PDF';
            statusDiv.className = 'status-message error';
            
            // Show debug info if available
            if (data.debug) {
                console.error('PDF Protection Error:', data.debug);
            }
        }
    })
    .catch(error => {
        statusDiv.textContent = 'Network error occurred. Please try again.';
        statusDiv.className = 'status-message error';
        console.error('Fetch Error:', error);
    })
    .finally(() => {
        protectBtn.disabled = false;
    });
});