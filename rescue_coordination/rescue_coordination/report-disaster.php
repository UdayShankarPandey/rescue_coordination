<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require authentication
requireAuth();

// Get database connection
$conn = getDbConnection();

// Initialize variables
$error = '';
$success = '';
$formData = [
    'title' => '',
    'description' => '',
    'disaster_type' => '',
    'severity' => '',
    'latitude' => '',
    'longitude' => '',
    'radius_km' => '5',
    'status' => 'reported'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        $error = 'Database connection error. Please try again later.';
    } else {
        // Get form data
        $formData = [
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'disaster_type' => sanitize($_POST['disaster_type'] ?? ''),
            'severity' => sanitize($_POST['severity'] ?? ''),
            'latitude' => floatval($_POST['latitude'] ?? 0),
            'longitude' => floatval($_POST['longitude'] ?? 0),
            'radius_km' => floatval($_POST['radius_km'] ?? 5),
            'status' => 'reported'
        ];
        
        // Validate form data
        if (empty($formData['title']) || empty($formData['description']) || 
            empty($formData['disaster_type']) || empty($formData['severity']) || 
            empty($formData['latitude']) || empty($formData['longitude'])) {
            $error = 'Please fill in all required fields.';
        } elseif ($formData['latitude'] < -90 || $formData['latitude'] > 90) {
            $error = 'Latitude must be between -90 and 90.';
        } elseif ($formData['longitude'] < -180 || $formData['longitude'] > 180) {
            $error = 'Longitude must be between -180 and 180.';
        } elseif ($formData['radius_km'] <= 0 || $formData['radius_km'] > 1000) {
            $error = 'Radius must be between 0 and 1000 km.';
        } else {
            try {
                // Insert disaster
                $stmt = $conn->prepare("
                    INSERT INTO disasters (
                        title, description, disaster_type, severity,
                        latitude, longitude, radius_km, status,
                        reported_by, started_at
                    ) VALUES (
                        :title, :description, :disaster_type, :severity,
                        :latitude, :longitude, :radius_km, :status,
                        :reported_by, NOW()
                    )
                ");
                
                $stmt->execute([
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'disaster_type' => $formData['disaster_type'],
                    'severity' => $formData['severity'],
                    'latitude' => $formData['latitude'],
                    'longitude' => $formData['longitude'],
                    'radius_km' => $formData['radius_km'],
                    'status' => $formData['status'],
                    'reported_by' => getCurrentUserId()
                ]);
                
                $disasterId = $conn->lastInsertId();
                
                // Log the action
                logAction($conn, 'disaster_reported', getCurrentUserId(), "Disaster ID: $disasterId");
                
                // Set success message
                $success = 'Disaster reported successfully.';
                
                // Clear form data
                $formData = [
                    'title' => '',
                    'description' => '',
                    'disaster_type' => '',
                    'severity' => '',
                    'latitude' => '',
                    'longitude' => '',
                    'radius_km' => '5',
                    'status' => 'reported'
                ];
                
                // Set flash message and redirect
                $_SESSION['flash_message'] = 'Disaster reported successfully.';
                $_SESSION['flash_type'] = 'success';
                
                // Redirect to disaster details
                redirect("disaster-details.php?id=$disasterId");
            } catch (PDOException $e) {
                error_log("Error reporting disaster: " . $e->getMessage());
                $error = 'An error occurred while reporting the disaster. Please try again.';
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Report Disaster</h1>
            <a href="disasters.php" class="text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Disasters
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-md mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p><?= $error ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-md mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p><?= $success ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="md:col-span-2">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="title" class="block text-gray-700 font-medium mb-2">Disaster Title <span class="text-red-600">*</span></label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($formData['title']) ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="E.g., Flood in Riverside Area">
                    </div>
                    
                    <div>
                        <label for="disaster_type" class="block text-gray-700 font-medium mb-2">Disaster Type <span class="text-red-600">*</span></label>
                        <select id="disaster_type" name="disaster_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="" disabled <?= empty($formData['disaster_type']) ? 'selected' : '' ?>>Select disaster type</option>
                            <option value="earthquake" <?= $formData['disaster_type'] === 'earthquake' ? 'selected' : '' ?>>Earthquake</option>
                            <option value="flood" <?= $formData['disaster_type'] === 'flood' ? 'selected' : '' ?>>Flood</option>
                            <option value="fire" <?= $formData['disaster_type'] === 'fire' ? 'selected' : '' ?>>Fire</option>
                            <option value="hurricane" <?= $formData['disaster_type'] === 'hurricane' ? 'selected' : '' ?>>Hurricane</option>
                            <option value="tsunami" <?= $formData['disaster_type'] === 'tsunami' ? 'selected' : '' ?>>Tsunami</option>
                            <option value="landslide" <?= $formData['disaster_type'] === 'landslide' ? 'selected' : '' ?>>Landslide</option>
                            <option value="pandemic" <?= $formData['disaster_type'] === 'pandemic' ? 'selected' : '' ?>>Pandemic</option>
                            <option value="other" <?= $formData['disaster_type'] === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="severity" class="block text-gray-700 font-medium mb-2">Severity <span class="text-red-600">*</span></label>
                        <select id="severity" name="severity" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="" disabled <?= empty($formData['severity']) ? 'selected' : '' ?>>Select severity</option>
                            <option value="low" <?= $formData['severity'] === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $formData['severity'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $formData['severity'] === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="critical" <?= $formData['severity'] === 'critical' ? 'selected' : '' ?>>Critical</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description <span class="text-red-600">*</span></label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Provide a detailed description of the disaster..."><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>
                    
                    <!-- Location Information -->
                    <div class="md:col-span-2 mt-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Location Information</h2>
                        <p class="text-gray-600 mb-4">Use the map below to select the disaster location or enter coordinates manually.</p>
                        
                        <div id="map" class="w-full h-80 border border-gray-300 rounded-md mb-4"></div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="latitude" class="block text-gray-700 font-medium mb-2">Latitude <span class="text-red-600">*</span></label>
                                <input type="number" id="latitude" name="latitude" value="<?= htmlspecialchars($formData['latitude']) ?>" required
                                       step="any" min="-90" max="90"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label for="longitude" class="block text-gray-700 font-medium mb-2">Longitude <span class="text-red-600">*</span></label>
                                <input type="number" id="longitude" name="longitude" value="<?= htmlspecialchars($formData['longitude']) ?>" required
                                       step="any" min="-180" max="180"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label for="radius_km" class="block text-gray-700 font-medium mb-2">Affected Radius (km) <span class="text-red-600">*</span></label>
                                <input type="number" id="radius_km" name="radius_km" value="<?= htmlspecialchars($formData['radius_km']) ?>" required
                                       step="0.1" min="0.1" max="1000"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="md:col-span-2 mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <i class="fas fa-paper-plane mr-2"></i> Report Disaster
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Map Initialization Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('map').setView([<?= !empty($formData['latitude']) ? $formData['latitude'] : DEFAULT_LAT ?>, 
                                         <?= !empty($formData['longitude']) ? $formData['longitude'] : DEFAULT_LNG ?>], 
                                         <?= DEFAULT_ZOOM ?>);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add marker for disaster location
        let marker = null;
        let circle = null;
        
        // Initialize marker if coordinates are set
        if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            const radius = parseFloat(document.getElementById('radius_km').value) * 1000; // Convert to meters
            
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            circle = L.circle([lat, lng], { radius: radius }).addTo(map);
            
            marker.on('dragend', updateMarkerPosition);
        }
        
        // Handle map click to set marker
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            const radius = parseFloat(document.getElementById('radius_km').value) * 1000; // Convert to meters
            
            // Update form fields
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            
            // Update or create marker
            if (marker) {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                circle.setRadius(radius);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                circle = L.circle([lat, lng], { radius: radius }).addTo(map);
                marker.on('dragend', updateMarkerPosition);
            }
        });
        
        // Update marker position when dragged
        function updateMarkerPosition() {
            const position = marker.getLatLng();
            const radius = parseFloat(document.getElementById('radius_km').value) * 1000; // Convert to meters
            
            // Update form fields
            document.getElementById('latitude').value = position.lat.toFixed(6);
            document.getElementById('longitude').value = position.lng.toFixed(6);
            
            // Update circle position
            circle.setLatLng(position);
            circle.setRadius(radius);
        }
        
        // Update circle radius when radius input changes
        document.getElementById('radius_km').addEventListener('input', function() {
            if (circle) {
                const radius = parseFloat(this.value) * 1000; // Convert to meters
                circle.setRadius(radius);
            }
        });
        
        // Update marker when coordinates are manually changed
        document.getElementById('latitude').addEventListener('change', updateMapFromCoordinates);
        document.getElementById('longitude').addEventListener('change', updateMapFromCoordinates);
        
        function updateMapFromCoordinates() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            const radius = parseFloat(document.getElementById('radius_km').value) * 1000; // Convert to meters
            
            if (!isNaN(lat) && !isNaN(lng)) {
                // Update or create marker
                if (marker) {
                    marker.setLatLng([lat, lng]);
                    circle.setLatLng([lat, lng]);
                    circle.setRadius(radius);
                } else {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    circle = L.circle([lat, lng], { radius: radius }).addTo(map);
                    marker.on('dragend', updateMarkerPosition);
                }
                
                // Center map on marker
                map.setView([lat, lng]);
            }
        }
        
        // Try to get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Set map view to user's location
                map.setView([lat, lng], 12);
                
                // Only set marker if form fields are empty
                if (!document.getElementById('latitude').value && !document.getElementById('longitude').value) {
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    
                    // Create marker
                    const radius = parseFloat(document.getElementById('radius_km').value) * 1000; // Convert to meters
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    circle = L.circle([lat, lng], { radius: radius }).addTo(map);
                    marker.on('dragend', updateMarkerPosition);
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
