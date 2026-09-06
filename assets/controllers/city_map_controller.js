import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    configure(event) {
        // Pass the actual Leaflet CRS object before UX Map creates the map.
        event.detail.bridgeOptions = { crs: event.detail.L.CRS.Simple, minZoom: -3 };
    }

    stylePolygon(event) {
        event.detail.definition.bridgeOptions = {
            color: '#38bdf8',
            fillColor: '#0284c7',
            fillOpacity: 0.3,
        };
    }

    fitMap(event) {
        const { map, polygons, markers, L } = event.detail;
        const layers = [...polygons, ...markers];
        if (layers.length > 0) {
            // Include every city and heroes outside the city boundaries.
            map.fitBounds(L.featureGroup(layers).getBounds(), {
                padding: [30, 30],
                maxZoom: 5,
            });
        }
    }
}
