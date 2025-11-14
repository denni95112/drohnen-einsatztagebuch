document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('database_path_dropdown');
    const customInput = document.getElementById('database_path_custom');
    
    function toggleCustomInput() {
        if (dropdown.value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }
    
    dropdown.addEventListener('change', toggleCustomInput);
    toggleCustomInput(); // Initialize on page load

    // Handle library download form
    const libDownloadForm = document.getElementById('libDownloadForm');
    if (libDownloadForm) {
        libDownloadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('downloadLibsBtn');
            const status = document.getElementById('downloadStatus');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Lädt herunter...';
            status.style.display = 'block';
            status.innerHTML = '<p>Bibliotheken werden heruntergeladen, bitte warten...</p>';
            
            const formData = new FormData(libDownloadForm);
            formData.append('ajax', '1');
            formData.append('download_libs', '1'); // Ensure this is set
            
            fetch('setup.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Check if response is actually JSON
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    return response.text().then(text => {
                        // Show the actual response for debugging
                        const errorMsg = 'Server returned non-JSON response. Response type: ' + contentType + '. ';
                        const preview = text.substring(0, 500);
                        throw new Error(errorMsg + 'Response preview: ' + preview);
                    });
                }
                return response.json();
            })
            .then(data => {
                let html = '';
                let allSuccess = true;
                
                // Check for general error
                if (data.error) {
                    html += '<p style="color: #dc3545; font-weight: 500;">✗ ' + data.error + '</p>';
                    allSuccess = false;
                }
                
                // Show debug output if present (helps identify issues)
                if (data.debug_output) {
                    html += '<p style="color: #856404; font-size: 12px; font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px;">Debug: ' + data.debug_output.substring(0, 200) + '</p>';
                }
                
                if (data.results) {
                    for (const [lib, result] of Object.entries(data.results)) {
                        if (result.success) {
                            html += '<p style="color: #28a745; font-weight: 500;">✓ ' + lib + ' erfolgreich heruntergeladen</p>';
                        } else {
                            html += '<p style="color: #dc3545; font-weight: 500;">✗ ' + lib + ': ' + (result.error || 'Fehler') + '</p>';
                            allSuccess = false;
                        }
                    }
                }
                
                if (data.remaining && Object.keys(data.remaining).length > 0) {
                    html += '<p style="color: #856404;">Einige Bibliotheken fehlen noch. Bitte Seite neu laden.</p>';
                } else if (allSuccess && data.results && Object.keys(data.results).length > 0) {
                    html += '<p style="color: #28a745; font-weight: 600;"><strong>Alle Bibliotheken erfolgreich installiert! Seite wird neu geladen...</strong></p>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
                
                status.innerHTML = html;
                btn.disabled = false;
                btn.textContent = originalText;
            })
            .catch(error => {
                status.innerHTML = '<p style="color: #dc3545; font-weight: 500;">Fehler: ' + error.message + '</p>';
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }
});

