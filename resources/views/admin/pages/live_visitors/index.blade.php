@extends('admin.layouts.app')
@section('title', 'Canlı Ziyaretçiler')
@section('content')
    <div class="p-4 pt-0 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Canlı Ziyaretçi Takibi</h1>
                <p class="text-sm text-gray-500 mt-1">Anlık olarak siteyi ziyaret eden kullanıcılar (Son 60 saniye)</p>
            </div>
            <div class="bg-blue-50 px-4 py-2 rounded-xl flex items-center gap-3">
                <div class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                </div>
                <span class="text-sm font-bold text-blue-700"><span id="live-count">0</span> Canlı Ziyaretçi</span>
            </div>
        </div>

        <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
            <div class="w-full h-[600px] rounded-xl overflow-hidden relative">
                <div id="map-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 z-10">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                    <div class="mt-4 text-gray-600">Veriler alınıyor...</div>
                </div>
                <div id="live-map" class="w-full h-full"></div>
            </div>
        </div>
    </div>
@endsection

@push('head')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>
        #live-map {
            background-color: #111827;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>
        $(document).ready(function() {
            let map = null;
            let markers = {};
            let updateInterval = null;

            function initMap() {
                map = new maplibregl.Map({
                    container: 'live-map',
                    style: {
                        version: 8,
                        sources: {
                            'osm': {
                                type: 'raster',
                                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                                tileSize: 256,
                                attribution: ''
                            }
                        },
                        layers: [{
                            id: 'osm-layer',
                            type: 'raster',
                            source: 'osm',
                            paint: {
                                'raster-opacity': 0.2,
                                'raster-brightness-min': 0.5,
                                'raster-brightness-max': 0.5,
                                'raster-contrast': 0.3,
                                'raster-saturation': -1
                            }
                        }],
                        glyphs: 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf'
                    },
                    center: [32.8597, 39.9334],
                    zoom: 6
                });

                map.on('load', function() {
                    map.addLayer({
                        id: 'background',
                        type: 'background',
                        paint: {
                            'background-color': '#111827'
                        }
                    }, 'osm-layer');

                    $('#map-loading').fadeOut(300);
                    $('#live-map').css('opacity', '1');
                });

                map.addControl(new maplibregl.NavigationControl(), 'top-right');
            }

            function createPulseMarker(lat, lng, visitor) {
                const el = document.createElement('div');
                el.className = 'pulse-marker';
                el.innerHTML = `
                    <div class="relative flex items-center justify-center">
                        <div class="absolute w-4 h-4 bg-blue-500 rounded-full animate-ping opacity-75"></div>
                        <div class="relative w-4 h-4 bg-blue-600 rounded-full border-2 border-white shadow-lg"></div>
                    </div>
                `;

                const popup = new maplibregl.Popup({
                        offset: 25
                    })
                    .setHTML(`
                        <div class="p-2">
                            <div class="font-semibold text-gray-800">${visitor.city}</div>
                            <div class="text-xs text-gray-600 mt-1">OS: ${visitor.platform}</div>
                            <div class="text-xs text-gray-500 mt-1">${visitor.visited_at}</div>
                        </div>
                    `);

                const marker = new maplibregl.Marker(el)
                    .setLngLat([lng, lat])
                    .setPopup(popup)
                    .addTo(map);

                return marker;
            }

            function updateVisitors() {
                $.ajax({
                    url: '{{ route('admin.live-visitors.fetch') }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#live-count').text(response.count || 0);

                        Object.keys(markers).forEach(ip => {
                            if (markers[ip]) {
                                markers[ip].remove();
                            }
                        });
                        markers = {};

                        if (response.visitors && response.visitors.length > 0) {
                            const validVisitors = response.visitors.filter(v => v.lat && v.lng);

                            if (validVisitors.length > 0) {
                                validVisitors.forEach(visitor => {
                                    if (visitor.lat && visitor.lng) {
                                        const marker = createPulseMarker(visitor.lat, visitor
                                            .lng, visitor);
                                        markers[visitor.ip] = marker;
                                    }
                                });

                                if (validVisitors.length === 1) {
                                    map.flyTo({
                                        center: [validVisitors[0].lng, validVisitors[0].lat],
                                        zoom: 10
                                    });
                                } else {
                                    const bounds = new maplibregl.LngLatBounds();
                                    validVisitors.forEach(v => {
                                        bounds.extend([v.lng, v.lat]);
                                    });
                                    map.fitBounds(bounds, {
                                        padding: 50
                                    });
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }

            initMap();
            updateVisitors();

            updateInterval = setInterval(updateVisitors, 5000);

            $(window).on('beforeunload', function() {
                if (updateInterval) {
                    clearInterval(updateInterval);
                }
            });
        });
    </script>
@endpush
