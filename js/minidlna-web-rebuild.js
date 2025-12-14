// Variable to store interval ID
let statusInterval;

// Load status on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatus();
    
    // Start auto-refresh every 6 seconds
    statusInterval = setInterval(loadStatus, 6000);
    
    // Attach click event to rebuild button
    document.querySelector('.btn-primary').addEventListener('click', rebuildContent);
});

/**
 * Load MiniDLNA status
 */
function loadStatus() {
    fetch('actions.php?action=getStatus')
        .then(response => {
            // Log della risposta grezza
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            return response.text(); // Cambia temporaneamente in text per vedere cosa arriva
        })
        .then(text => {
            console.log('Raw response:', text); // Vedi cosa arriva davvero
            
            // Prova a parsare il JSON
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    document.getElementById('audioFiles').textContent = data.data.audio;
                    document.getElementById('videoFiles').textContent = data.data.video;
                    document.getElementById('imageFiles').textContent = data.data.images;
                } else {
                    console.error('Error loading status:', data.error);
                    showResult('Error: ' + data.error, false);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                showResult('Invalid response from server', false);
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            showResult('Network error: ' + error.message, false);
        });
}

/**
 * Rebuild MiniDLNA content
 */
function rebuildContent() {
    const button = document.querySelector('.btn-primary');
    const icon = button.querySelector('i');
    
    // Disable button and add spinning animation
    button.disabled = true;
    icon.classList.add('rotating');
    
    showResult('Rebuilding content...', true);
    
    fetch('actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=rebuild'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showResult(data.data.message, true);
            // Force immediate status reload after rebuild
            setTimeout(loadStatus, 2000);
        } else {
            showResult('Error: ' + data.error, false);
        }
    })
    .catch(error => {
        showResult('Network error: ' + error.message, false);
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        icon.classList.remove('rotating');
    });
}

/**
 * Show result in textarea
 */
function showResult(message, isSuccess) {
    const textarea = document.getElementById('resultArea');
    const timestamp = new Date().toLocaleTimeString();
    textarea.value = `[${timestamp}] ${message}`;
    
    // Optional: change textarea border color based on success/error
    if (isSuccess) {
        textarea.classList.remove('border-danger');
        textarea.classList.add('border-success');
    } else {
        textarea.classList.remove('border-success');
        textarea.classList.add('border-danger');
    }
}

// Optional: Stop interval when page is hidden (save resources)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        clearInterval(statusInterval);
    } else {
        statusInterval = setInterval(loadStatus, 6000);
        loadStatus(); // Load immediately when page becomes visible
    }
});