/* global L */

(function () {
    const DEFAULT_CENTER = [51.1657, 10.4515];
    const DEFAULT_ZOOM = 6;

    let mapInstance = null;
    let markerInstance = null;

    function syncMarkerToInputs() {
        if (!markerInstance) return;
        var ll = markerInstance.getLatLng();
        var latEl = getLatInput();
        var lngEl = getLngInput();
        if (latEl) latEl.value = ll.lat.toFixed(6);
        if (lngEl) lngEl.value = ll.lng.toFixed(6);
    }

    function clearMapSearchUi() {
        var msg = document.getElementById('map-search-msg');
        var list = document.getElementById('map-search-results');
        var q = document.getElementById('map-search-q');
        if (msg) {
            msg.hidden = true;
            msg.textContent = '';
            msg.className = 'map-search-msg';
        }
        if (list) {
            list.hidden = true;
            list.innerHTML = '';
        }
        if (q) q.removeAttribute('aria-busy');
    }

    function runMapSearch() {
        var qEl = document.getElementById('map-search-q');
        var btn = document.getElementById('map-search-btn');
        var msg = document.getElementById('map-search-msg');
        var list = document.getElementById('map-search-results');
        if (!qEl || !msg || !list) return;

        var query = (qEl.value || '').trim();
        if (query.length < 2) {
            msg.hidden = false;
            msg.className = 'map-search-msg map-search-msg-error';
            msg.textContent = 'Bitte mindestens 2 Zeichen eingeben.';
            list.hidden = true;
            list.innerHTML = '';
            return;
        }

        if (btn) btn.disabled = true;
        qEl.setAttribute('aria-busy', 'true');
        msg.hidden = false;
        msg.className = 'map-search-msg';
        msg.textContent = 'Suche läuft…';
        list.hidden = true;
        list.innerHTML = '';

        var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=5&dedupe=1';
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Accept-Language': 'de, en;q=0.9'
            }
        })
            .then(function (r) {
                if (r.status === 429) throw new Error('Zu viele Anfragen. Bitte kurz warten und erneut versuchen.');
                return r.json();
            })
            .then(function (items) {
                if (btn) btn.disabled = false;
                qEl.removeAttribute('aria-busy');
                if (items && typeof items === 'object' && !Array.isArray(items) && items.error) {
                    msg.className = 'map-search-msg map-search-msg-error';
                    msg.textContent = String(items.error);
                    return;
                }
                if (!Array.isArray(items) || items.length === 0) {
                    msg.className = 'map-search-msg map-search-msg-error';
                    msg.textContent = 'Keine Treffer. Bitte anderen Suchbegriff versuchen.';
                    return;
                }
                msg.hidden = true;
                list.hidden = false;
                list.innerHTML = '';
                items.forEach(function (item) {
                    var li = document.createElement('li');
                    li.setAttribute('role', 'option');
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = item.display_name || (item.lat + ', ' + item.lon);
                    b.addEventListener('click', function () {
                        applyNominatimSearchResult(item);
                    });
                    li.appendChild(b);
                    list.appendChild(li);
                });
            })
            .catch(function (err) {
                if (btn) btn.disabled = false;
                qEl.removeAttribute('aria-busy');
                msg.hidden = false;
                msg.className = 'map-search-msg map-search-msg-error';
                msg.textContent = err && err.message ? err.message : 'Suche fehlgeschlagen.';
            });
    }

    function applyNominatimSearchResult(item) {
        var lat = parseFloat(item.lat);
        var lon = parseFloat(item.lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lon) || !mapInstance || !markerInstance) return;

        markerInstance.setLatLng([lat, lon]);

        if (item.boundingbox && item.boundingbox.length === 4) {
            var south = parseFloat(item.boundingbox[0]);
            var north = parseFloat(item.boundingbox[1]);
            var west = parseFloat(item.boundingbox[2]);
            var east = parseFloat(item.boundingbox[3]);
            var bounds = L.latLngBounds(
                [south, west],
                [north, east]
            );
            mapInstance.fitBounds(bounds, { padding: [24, 24], maxZoom: 16 });
        } else {
            mapInstance.setView([lat, lon], 14);
        }

        syncMarkerToInputs();

        var adresse = document.getElementById('adresse');
        if (adresse && item.display_name) {
            adresse.value = item.display_name;
        }

        var list = document.getElementById('map-search-results');
        if (list) {
            list.hidden = true;
            list.innerHTML = '';
        }
        setTimeout(function () {
            if (mapInstance) mapInstance.invalidateSize();
        }, 50);
    }

    function getLatInput() {
        return document.getElementById('gps_lat');
    }

    function getLngInput() {
        return document.getElementById('gps_lng');
    }

    function parseExistingLatLng() {
        const latEl = getLatInput();
        const lngEl = getLngInput();
        if (!latEl || !lngEl) return null;
        const lat = parseFloat(String(latEl.value).replace(',', '.'));
        const lng = parseFloat(String(lngEl.value).replace(',', '.'));
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
        return [lat, lng];
    }

    function getGPS() {
        navigator.geolocation.getCurrentPosition(function (position) {
            const latEl = getLatInput();
            const lngEl = getLngInput();
            if (latEl) latEl.value = position.coords.latitude;
            if (lngEl) lngEl.value = position.coords.longitude;
        }, function () {
            alert('GPS konnte nicht ermittelt werden.');
        });
    }

    function showGPSSpinner() {
        const btn = document.getElementById('gps-btn');
        const btnText = document.getElementById('gps-btn-text');
        const spinner = document.getElementById('gps-spinner');

        if (btn) btn.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (spinner) spinner.style.display = 'inline-block';
    }

    function hideGPSSpinner() {
        const btn = document.getElementById('gps-btn');
        const btnText = document.getElementById('gps-btn-text');
        const spinner = document.getElementById('gps-spinner');

        if (btn) btn.disabled = false;
        if (btnText) btnText.style.display = 'inline';
        if (spinner) spinner.style.display = 'none';
    }

    function getAddress() {
        showGPSSpinner();

        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const latEl = getLatInput();
            const lngEl = getLngInput();
            if (latEl) latEl.value = lat;
            if (lngEl) lngEl.value = lng;

            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    const adresse = document.getElementById('adresse');
                    if (adresse && data.display_name) adresse.value = data.display_name;
                    hideGPSSpinner();
                })
                .catch(function () {
                    alert('Adresse konnte nicht ermittelt werden.');
                    hideGPSSpinner();
                });
        }, function () {
            alert('GPS konnte nicht ermittelt werden.');
            hideGPSSpinner();
        });
    }

    function showMapLoadError(message) {
        const container = document.getElementById('map-picker-container');
        if (!container) return;
        container.innerHTML = '';
        const p = document.createElement('p');
        p.className = 'map-load-error';
        p.textContent = message;
        container.appendChild(p);
    }

    function ensureMap() {
        const container = document.getElementById('map-picker-container');
        if (!container) return false;

        if (typeof L === 'undefined') {
            showMapLoadError('Kartenbibliothek konnte nicht geladen werden. Bitte Seite neu laden oder Netzwerk prüfen.');
            return false;
        }

        if (mapInstance) return true;

        container.innerHTML = '';

        const existing = parseExistingLatLng();
        const center = existing || DEFAULT_CENTER;
        const zoom = existing ? 14 : DEFAULT_ZOOM;

        mapInstance = L.map(container, { scrollWheelZoom: true }).setView(center, zoom);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapInstance);

        markerInstance = L.marker(center, { draggable: true }).addTo(mapInstance);

        markerInstance.on('dragend', syncMarkerToInputs);

        mapInstance.on('click', function (e) {
            markerInstance.setLatLng(e.latlng);
            syncMarkerToInputs();
        });

        syncMarkerToInputs();
        return true;
    }

    function refreshMapAfterOpen() {
        if (!mapInstance) return;
        mapInstance.invalidateSize();
        const ll = parseExistingLatLng();
        if (ll) {
            mapInstance.setView(ll, 14);
            if (markerInstance) markerInstance.setLatLng(ll);
        }
    }

    function openMapModal() {
        const modal = document.getElementById('map-picker-modal');
        if (!modal) return;

        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';

        // Leaflet needs a laid-out, visible container; init after paint (not while display:none).
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (!ensureMap()) return;
                refreshMapAfterOpen();
                setTimeout(function () {
                    if (mapInstance) {
                        mapInstance.invalidateSize();
                    }
                }, 150);
            });
        });
    }

    function closeMapModal() {
        const modal = document.getElementById('map-picker-modal');
        if (!modal) return;
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
        clearMapSearchUi();
    }

    function applyMapSelection() {
        const latEl = getLatInput();
        const lngEl = getLngInput();
        if (!latEl || !lngEl) {
            closeMapModal();
            return;
        }

        if (markerInstance) {
            const ll = markerInstance.getLatLng();
            latEl.value = ll.lat.toFixed(6);
            lngEl.value = ll.lng.toFixed(6);
        }

        const lat = parseFloat(latEl.value);
        const lng = parseFloat(lngEl.value);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            closeMapModal();
            return;
        }

        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const adresse = document.getElementById('adresse');
                if (adresse && data.display_name) adresse.value = data.display_name;
            })
            .catch(function () {});

        closeMapModal();
    }

    function formHasValidCoords() {
        const latEl = getLatInput();
        const lngEl = getLngInput();
        if (!latEl || !lngEl) return false;
        const lat = parseFloat(String(latEl.value).replace(',', '.'));
        const lng = parseFloat(String(lngEl.value).replace(',', '.'));
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return false;
        return lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    }

    function onFormSubmit(e) {
        const cfg = window.neuerEinsatzConfig || {};
        if (!cfg.dashboardApi) return;

        const cb = document.getElementById('create_flugstandort');
        if (!cb || !cb.checked) return;

        if (!formHasValidCoords()) {
            e.preventDefault();
            alert('Für „Als Flugstandort im Flug-Dienstbuch anlegen“ bitte zuerst Koordinaten setzen (GPS oder Karte).');
        }
    }

    function bindUi() {
        const openBtn = document.getElementById('map-picker-open');
        if (openBtn) openBtn.addEventListener('click', openMapModal);

        const backdrop = document.getElementById('map-modal-backdrop');
        const closeBtn = document.getElementById('map-modal-close');
        const cancelBtn = document.getElementById('map-modal-cancel');
        const applyBtn = document.getElementById('map-modal-apply');

        if (backdrop) backdrop.addEventListener('click', closeMapModal);
        if (closeBtn) closeBtn.addEventListener('click', closeMapModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeMapModal);
        if (applyBtn) applyBtn.addEventListener('click', applyMapSelection);

        var searchBtn = document.getElementById('map-search-btn');
        var searchQ = document.getElementById('map-search-q');
        if (searchBtn) searchBtn.addEventListener('click', runMapSearch);
        if (searchQ) {
            searchQ.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runMapSearch();
                }
            });
        }

        const form = document.querySelector('form');
        if (form) form.addEventListener('submit', onFormSubmit);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('map-picker-modal');
                if (modal && !modal.hasAttribute('hidden')) closeMapModal();
            }
        });
    }

    window.getGPS = getGPS;
    window.getAddress = getAddress;

    document.addEventListener('DOMContentLoaded', function () {
        bindUi();
        getGPS();
    });
})();
