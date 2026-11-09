<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radius Access Settings - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .campus-radius-card {
            max-width: 1040px;
            padding: 1.5rem;
        }
        .campus-radius-layout {
            display: grid;
            grid-template-columns: minmax(280px, 0.8fr) minmax(0, 1.2fr);
            gap: 1.5rem;
            align-items: start;
        }
        .radius-preview {
            padding: 1.25rem;
            border: 1px solid rgba(148, 184, 216, 0.25);
            border-radius: 16px;
            background: linear-gradient(145deg, rgba(18, 55, 85, 0.92), rgba(8, 32, 59, 0.96));
            color: #f4f8fc;
        }
        .radius-preview h2 {
            margin: 0;
            color: #fff;
            font-size: 1.1rem;
        }
        .radius-preview > p {
            margin: 0.35rem 0 1rem;
            color: #b8c9d9;
            font-size: 0.85rem;
        }
        #campusMap {
            width: 100%;
            height: 380px;
            margin-bottom: 1.1rem;
            border: 1px solid rgba(148, 184, 216, 0.3);
            border-radius: 12px;
            background: #dce6ee;
            z-index: 0;
        }
        .leaflet-container {
            font-family: inherit;
        }
        .radius-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }
        .radius-metric {
            min-width: 0;
            padding: 0.75rem;
            border: 1px solid rgba(148, 184, 216, 0.18);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.045);
        }
        .radius-metric span {
            display: block;
            color: #b8c9d9;
            font-size: 0.75rem;
        }
        .radius-metric strong {
            display: block;
            margin-top: 0.2rem;
            color: #fff;
            font-size: 1rem;
            overflow-wrap: anywhere;
        }
        @media (max-width: 760px) {
            .campus-radius-layout {
                grid-template-columns: 1fr;
            }
            .radius-preview {
                order: -1;
            }
            #campusMap {
                height: 300px;
            }
        }
    </style>
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1 style="color: #FFFFFF;">Radius Access Settings</h1>
                    <p>Configure the access radius and center coordinates.</p>
                </div>
            </header>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="dashboard-card campus-radius-card">
                <div class="campus-radius-layout">
                    <form action="<?= base_url('admin/security/update') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="campus_latitude">Campus Latitude</label>
                            <input type="text" id="campus_latitude" name="campus_latitude" class="form-control" value="<?= esc(old('campus_latitude', $campus_latitude !== '' ? $campus_latitude : '9.998023509291377')) ?>" placeholder="e.g. 14.6500">
                            <small class="text-muted">Enter the latitude of the campus center.</small>
                        </div>
                        <div class="form-group">
                            <label for="campus_longitude">Campus Longitude</label>
                            <input type="text" id="campus_longitude" name="campus_longitude" class="form-control" value="<?= esc(old('campus_longitude', $campus_longitude !== '' ? $campus_longitude : '122.81471120800092')) ?>" placeholder="e.g. 121.0700">
                            <small class="text-muted">Enter the longitude of the campus center.</small>
                        </div>
                        <div class="form-group">
                            <label for="campus_access_radius">Allowed Radius (meters)</label>
                            <input type="number" id="campus_access_radius" name="campus_access_radius" class="form-control" value="<?= esc($campus_access_radius) ?>" min="0" step="1" placeholder="e.g. 500">
                            <small class="text-muted">Users can only access the system when their device is inside this radius from campus center.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Campus Access Settings</button>
                    </form>

                    <section class="radius-preview" aria-labelledby="radius-preview-title" aria-live="polite">
                        <h2 id="radius-preview-title">Campus access boundary</h2>
                        <p>Satellite map follows the entered coordinates. Drag the marker or click the map to set the campus center.</p>
                        <div id="campusMap" role="application" aria-label="Map showing the campus center and configured access radius"></div>
                        <div class="radius-metrics">
                            <div class="radius-metric">
                                <span>Radius</span>
                                <strong id="radius-value">—</strong>
                            </div>
                            <div class="radius-metric">
                                <span>Diameter</span>
                                <strong id="diameter-value">—</strong>
                            </div>
                            <div class="radius-metric">
                                <span>Circumference</span>
                                <strong id="circumference-value">—</strong>
                            </div>
                            <div class="radius-metric">
                                <span>Area</span>
                                <strong id="area-value">—</strong>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <script>
        (function () {
            const radiusInput = document.getElementById('campus_access_radius');
            const latitudeInput = document.getElementById('campus_latitude');
            const longitudeInput = document.getElementById('campus_longitude');
            const mapElement = document.getElementById('campusMap');
            const radiusValue = document.getElementById('radius-value');
            const diameterValue = document.getElementById('diameter-value');
            const circumferenceValue = document.getElementById('circumference-value');
            const areaValue = document.getElementById('area-value');
            const numberFormat = new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 });
            let map = null;
            let campusMarker = null;
            let radiusCircle = null;
            let hasCenteredOnRadius = false;

            function updateRadiusPreview() {
                const radius = Number.parseFloat(radiusInput.value);
                if (!Number.isFinite(radius) || radius < 0) {
                    radiusValue.textContent = '—';
                    diameterValue.textContent = '—';
                    circumferenceValue.textContent = '—';
                    areaValue.textContent = '—';
                    return;
                }

                radiusValue.textContent = `${numberFormat.format(radius)} m`;
                diameterValue.textContent = `${numberFormat.format(radius * 2)} m`;
                circumferenceValue.textContent = `${numberFormat.format(2 * Math.PI * radius)} m`;
                areaValue.textContent = `${numberFormat.format(Math.PI * radius * radius)} m²`;
                if (radiusCircle) {
                    radiusCircle.setRadius(radius);
                    fitRadiusBounds();
                }
            }

            function getCoordinates() {
                const lat = Number.parseFloat(latitudeInput.value);
                const lng = Number.parseFloat(longitudeInput.value);
                if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                    return null;
                }
                return [lat, lng];
            }

            function getRadius() {
                const radius = Number.parseFloat(radiusInput.value);
                return Number.isFinite(radius) && radius > 0 ? radius : 0;
            }

            function fitRadiusBounds() {
                if (!map || !radiusCircle) return;
                if (getRadius() <= 0) {
                    map.setView(radiusCircle.getLatLng(), 16);
                    return;
                }
                const bounds = radiusCircle.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [28, 28], maxZoom: 17 });
                }
            }

            function syncMap() {
                const center = getCoordinates();
                if (!map) {
                    const initialCenter = center || [0, 0];
                    map = L.map(mapElement, { scrollWheelZoom: false }).setView(initialCenter, center ? 16 : 2);
                    const satelliteLayer = L.tileLayer(
                        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                        {
                            maxZoom: 19,
                            attribution: 'Imagery &copy; <a href="https://www.esri.com/" target="_blank" rel="noopener noreferrer">Esri</a>, Maxar, Earthstar Geographics, and the GIS User Community'
                        }
                    ).addTo(map);
                    satelliteLayer.on('tileerror', function () {
                        mapElement.setAttribute('aria-label', 'Satellite map tiles could not be loaded. Check your internet connection.');
                    });
                    map.on('click', function (event) {
                        latitudeInput.value = event.latlng.lat.toFixed(6);
                        longitudeInput.value = event.latlng.lng.toFixed(6);
                        syncMap();
                    });
                }

                if (!center) {
                    if (campusMarker) {
                        map.removeLayer(campusMarker);
                        campusMarker = null;
                    }
                    if (radiusCircle) {
                        map.removeLayer(radiusCircle);
                        radiusCircle = null;
                    }
                    hasCenteredOnRadius = false;
                    map.invalidateSize();
                    return;
                }

                const centerLatLng = L.latLng(center[0], center[1]);
                if (!campusMarker) {
                    campusMarker = L.marker(centerLatLng, { draggable: true })
                        .bindPopup('Campus center')
                        .addTo(map);
                    campusMarker.on('dragend', function () {
                        const position = campusMarker.getLatLng();
                        latitudeInput.value = position.lat.toFixed(6);
                        longitudeInput.value = position.lng.toFixed(6);
                        syncMap();
                    });
                } else {
                    campusMarker.setLatLng(centerLatLng);
                }

                if (!radiusCircle) {
                    radiusCircle = L.circle(centerLatLng, {
                        radius: getRadius(),
                        color: '#2563eb',
                        weight: 2,
                        fillColor: '#3b82f6',
                        fillOpacity: 0.2
                    }).addTo(map);
                } else {
                    radiusCircle.setLatLng(centerLatLng);
                    radiusCircle.setRadius(getRadius());
                }

                map.invalidateSize();
                if (!hasCenteredOnRadius) {
                    hasCenteredOnRadius = true;
                    fitRadiusBounds();
                } else {
                    map.panTo(centerLatLng, { animate: false });
                }
            }

            radiusInput.addEventListener('input', updateRadiusPreview);
            latitudeInput.addEventListener('input', syncMap);
            longitudeInput.addEventListener('input', syncMap);
            updateRadiusPreview();
            if (typeof L !== 'undefined') {
                syncMap();
            } else {
                window.addEventListener('load', function () {
                    if (typeof L !== 'undefined') {
                        syncMap();
                    } else {
                        mapElement.textContent = 'The map could not be loaded. Check your internet connection and try again.';
                        mapElement.style.display = 'grid';
                        mapElement.style.placeItems = 'center';
                        mapElement.style.color = '#0b2748';
                    }
                });
            }
        })();
    </script>
</body>
</html>
