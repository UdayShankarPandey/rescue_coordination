/**
 * Rescue Coordination System - Map Initialization
 * Loads and displays disasters and agencies on a Leaflet map
 */

let map;
let disasterMarkers = [];
let agencyMarkers = [];

// Initialize the map
function initializeMap() {
    // Create map centered on India
    map = L.map('map').setView([20.5937, 78.9629], 5);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Load map data
    loadDisasters();
    loadAgencies();
}

// Load disasters from the server
function loadDisasters() {
    fetch('api/get-disasters.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.disasters) {
                displayDisasters(data.disasters);
            }
        })
        .catch(error => console.error('Error loading disasters:', error));
}

// Load agencies from the server
function loadAgencies() {
    fetch('api/get-agencies.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.agencies) {
                displayAgencies(data.agencies);
            }
        })
        .catch(error => console.error('Error loading agencies:', error));
}

// Display disasters on the map
function displayDisasters(disasters) {
    // Clear existing markers
    disasterMarkers.forEach(marker => map.removeLayer(marker));
    disasterMarkers = [];
    
    disasters.forEach(disaster => {
        if (disaster.latitude && disaster.longitude) {
            const colors = {
                'earthquake': '#b91c1c',
                'flood': '#1d4ed8',
                'fire': '#c2410c',
                'hurricane': '#4338ca',
                'tsunami': '#0369a1',
                'landslide': '#92400e',
                'pandemic': '#4d7c0f',
                'other': '#4b5563'
            };
            
            const color = colors[disaster.disaster_type] || '#4b5563';
            
            // Create custom icon
            const icon = L.divIcon({
                className: 'disaster-marker',
                html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(0, 0, 0, 0.7); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">⚠</div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
            
            const marker = L.marker([disaster.latitude, disaster.longitude], {icon: icon})
                .addTo(map)
                .bindPopup(`
                    <div class="popup-content">
                        <h3 class="font-bold text-lg">${escapeHtml(disaster.title)}</h3>
                        <p class="text-sm text-gray-600">${escapeHtml(disaster.description)}</p>
                        <div class="mt-2 text-xs">
                            <p><strong>Type:</strong> ${capitalizeFirst(disaster.disaster_type)}</p>
                            <p><strong>Severity:</strong> <span style="color: ${getSeverityColor(disaster.severity)}">${capitalizeFirst(disaster.severity)}</span></p>
                            <p><strong>Status:</strong> ${capitalizeFirst(disaster.status)}</p>
                            <p><strong>Radius:</strong> ${disaster.radius_km}km</p>
                        </div>
                        <a href="disaster-details.php?id=${disaster.id}" class="mt-3 inline-block bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium hover:bg-blue-700">View Details</a>
                    </div>
                `);
            
            disasterMarkers.push(marker);
        }
    });
}

// Display agencies on the map
function displayAgencies(agencies) {
    // Clear existing markers
    agencyMarkers.forEach(marker => map.removeLayer(marker));
    agencyMarkers = [];
    
    agencies.forEach(agency => {
        if (agency.latitude && agency.longitude) {
            const colors = {
                'medical': '#ef4444',
                'fire': '#f97316',
                'police': '#3b82f6',
                'military': '#65a30d',
                'ngo': '#8b5cf6',
                'other': '#6b7280'
            };
            
            const color = colors[agency.agency_type] || '#6b7280';
            const typeEmoji = {
                'medical': '🏥',
                'fire': '🚒',
                'police': '🚔',
                'military': '🪖',
                'ngo': '🤝',
                'other': '📍'
            };
            
            const icon = L.divIcon({
                className: 'agency-marker',
                html: `<div style="background-color: ${color}; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            
            const marker = L.marker([agency.latitude, agency.longitude], {icon: icon})
                .addTo(map)
                .bindPopup(`
                    <div class="popup-content">
                        <h3 class="font-bold text-lg">${escapeHtml(agency.name)}</h3>
                        <p class="text-sm text-gray-600">${typeEmoji[agency.agency_type] || '📍'} ${capitalizeFirst(agency.agency_type)}</p>
                        <div class="mt-2 text-xs">
                            <p><strong>City:</strong> ${escapeHtml(agency.city)}, ${escapeHtml(agency.state)}</p>
                            <p><strong>Email:</strong> <a href="mailto:${escapeHtml(agency.email)}" class="text-blue-600">${escapeHtml(agency.email)}</a></p>
                            <p><strong>Phone:</strong> ${escapeHtml(agency.phone)}</p>
                        </div>
                    </div>
                `);
            
            agencyMarkers.push(marker);
        }
    });
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeFirst(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

function getSeverityColor(severity) {
    const colors = {
        'low': '#10b981',
        'medium': '#f59e0b',
        'high': '#ef4444',
        'critical': '#7c2d12'
    };
    return colors[severity] || '#6b7280';
}

// Initialize map when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMap);
} else {
    initializeMap();
}

// Refresh map data every 30 seconds
setInterval(function() {
    loadDisasters();
    loadAgencies();
}, 30000);
