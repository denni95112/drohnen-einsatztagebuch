function getGPS() {
    navigator.geolocation.getCurrentPosition(function (position) {
        document.getElementById('gps_lat').value = position.coords.latitude;
        document.getElementById('gps_lng').value = position.coords.longitude;
    }, function () {
        alert('GPS konnte nicht ermittelt werden.');
    });
}

function showGPSSpinner() {
    const btn = document.getElementById('gps-btn');
    const btnText = document.getElementById('gps-btn-text');
    const spinner = document.getElementById('gps-spinner');
    
    btn.disabled = true;
    if (btnText) btnText.style.display = 'none';
    if (spinner) spinner.style.display = 'inline-block';
}

function hideGPSSpinner() {
    const btn = document.getElementById('gps-btn');
    const btnText = document.getElementById('gps-btn-text');
    const spinner = document.getElementById('gps-spinner');
    
    btn.disabled = false;
    if (btnText) btnText.style.display = 'inline';
    if (spinner) spinner.style.display = 'none';
}

function getAddress() {
    showGPSSpinner();
    
    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        document.getElementById('gps_lat').value = lat;
        document.getElementById('gps_lng').value = lng;

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('adresse').value = data.display_name;
                hideGPSSpinner();
            })
            .catch(() => {
                alert('Adresse konnte nicht ermittelt werden.');
                hideGPSSpinner();
            });
    }, function () {
        alert('GPS konnte nicht ermittelt werden.');
        hideGPSSpinner();
    });
}

window.onload = getGPS;

