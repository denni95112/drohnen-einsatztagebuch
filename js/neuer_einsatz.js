function getGPS() {
    navigator.geolocation.getCurrentPosition(function (position) {
        document.getElementById('gps_lat').value = position.coords.latitude;
        document.getElementById('gps_lng').value = position.coords.longitude;
    }, function () {
        alert('GPS konnte nicht ermittelt werden.');
    });
}

function getAddress() {
    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        document.getElementById('gps_lat').value = lat;
        document.getElementById('gps_lng').value = lng;

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('adresse').value = data.display_name;
            })
            .catch(() => alert('Adresse konnte nicht ermittelt werden.'));
    });
}

window.onload = getGPS;

