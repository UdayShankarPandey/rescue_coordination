<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get database connection
$conn = getDbConnection();

// Get active disasters count
$disastersCount = 0;
$agenciesCount = 0;
$resourcesCount = 0;

if ($conn) {
    // Get active disasters
    $stmt = $conn->prepare("SELECT COUNT(*) FROM disasters WHERE status IN ('reported', 'active')");
    $stmt->execute();
    $disastersCount = $stmt->fetchColumn();
    
    // Get registered agencies
    $stmt = $conn->prepare("SELECT COUNT(*) FROM agencies WHERE verified = TRUE");
    $stmt->execute();
    $agenciesCount = $stmt->fetchColumn();
    
    // Get available resources
    $stmt = $conn->prepare("SELECT COUNT(*) FROM resources WHERE available = TRUE");
    $stmt->execute();
    $resourcesCount = $stmt->fetchColumn();
    
    // Get recent disasters
    $stmt = $conn->prepare("
        SELECT d.*, a.name as reported_by_name 
        FROM disasters d
        LEFT JOIN agencies a ON d.reported_by = a.id
        WHERE d.status IN ('reported', 'active')
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentDisasters = $stmt->fetchAll();
    
    // Get active agencies
    $stmt = $conn->prepare("
        SELECT id, name, agency_type, latitude, longitude, last_active
        FROM agencies
        WHERE verified = TRUE AND latitude IS NOT NULL AND longitude IS NOT NULL
        ORDER BY last_active DESC
        LIMIT 10
    ");
    $stmt->execute();
    $activeAgencies = $stmt->fetchAll();
}

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
 <h2>CI/CD Pipeline Working Successfully</h2>
<section class="bg-indigo-700 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Coordinating Rescue Efforts During Disasters</h1>
                <p class="text-xl mb-8">A comprehensive platform for rescue agencies to coordinate during natural and man-made calamities, enhancing response time and saving lives.</p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <?php if (!$isLoggedIn): ?>
                        <a href="register.php" class="bg-white text-indigo-700 hover:bg-indigo-100 px-6 py-3 rounded-md font-semibold text-center">
                            <i class="fas fa-user-plus mr-2"></i> Register Your Agency
                        </a>
                        <a href="login.php" class="border border-white text-white hover:bg-indigo-600 px-6 py-3 rounded-md font-semibold text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                    <?php else: ?>
                        <a href="disasters.php" class="bg-white text-indigo-700 hover:bg-indigo-100 px-6 py-3 rounded-md font-semibold text-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i> View Active Disasters
                        </a>
                        <a href="report-disaster.php" class="border border-white text-white hover:bg-indigo-600 px-6 py-3 rounded-md font-semibold text-center">
                            <i class="fas fa-plus-circle mr-2"></i> Report Disaster
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="https://images.unsplash.com/photo-1543269664-76bc3997d9ea?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Rescue Coordination" class="rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-indigo-50 p-6 rounded-lg text-center">
                <div class="text-indigo-600 text-4xl font-bold mb-2"><?= $disastersCount ?></div>
                <div class="text-gray-700 text-lg">Active Disasters</div>
                <p class="text-gray-500 mt-2">Ongoing emergency situations that require immediate attention and coordination.</p>
            </div>
            
            <div class="bg-indigo-50 p-6 rounded-lg text-center">
                <div class="text-indigo-600 text-4xl font-bold mb-2"><?= $agenciesCount ?></div>
                <div class="text-gray-700 text-lg">Registered Agencies</div>
                <p class="text-gray-500 mt-2">Rescue and relief organizations ready to respond to emergencies and provide assistance.</p>
            </div>
            
            <div class="bg-indigo-50 p-6 rounded-lg text-center">
                <div class="text-indigo-600 text-4xl font-bold mb-2"><?= $resourcesCount ?></div>
                <div class="text-gray-700 text-lg">Available Resources</div>
                <p class="text-gray-500 mt-2">Essential supplies, equipment, and personnel available for deployment during disasters.</p>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Live Disaster Map</h2>
            <p class="text-gray-600 max-w-3xl mx-auto">View active disasters and rescue agencies in real-time. The map shows the current location of registered agencies and ongoing emergency situations.</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div id="map" class="map-container"></div>
        </div>
        
        <div class="mt-6 flex flex-wrap justify-center gap-4">
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-red-500 mr-2"></div>
                <span class="text-sm text-gray-700">Medical</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-orange-500 mr-2"></div>
                <span class="text-sm text-gray-700">Fire</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                <span class="text-sm text-gray-700">Police</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-green-600 mr-2"></div>
                <span class="text-sm text-gray-700">Military</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                <span class="text-sm text-gray-700">NGO</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-gray-500 mr-2"></div>
                <span class="text-sm text-gray-700">Other</span>
            </div>
            <div class="border-l border-gray-300 h-6 mx-2"></div>
            <div class="flex items-center">
                <div class="w-5 h-5 rounded-full bg-red-600 border-2 border-white mr-2 flex items-center justify-center text-white text-xs">!</div>
                <span class="text-sm text-gray-700">Disaster</span>
            </div>
        </div>
    </div>
</section>

<!-- Recent Disasters Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Recent Disasters</h2>
            <p class="text-gray-600 max-w-3xl mx-auto">Stay informed about the latest emergency situations requiring coordination and assistance.</p>
        </div>
        
        <?php if (isset($recentDisasters) && count($recentDisasters) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($recentDisasters as $disaster): ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($disaster['title']) ?></h3>
                                <span class="inline-block px-3 py-1 text-sm rounded-full <?= getStatusClass($disaster['status']) ?>">
                                    <?= ucfirst($disaster['status']) ?>
                                </span>
                            </div>
                            
                            <div class="mb-4">
                                <span class="inline-block px-3 py-1 text-sm rounded-full <?= getSeverityClass($disaster['severity']) ?> mr-2">
                                    <?= ucfirst($disaster['severity']) ?>
                                </span>
                                <span class="inline-block px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800">
                                    <?= ucfirst($disaster['disaster_type']) ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($disaster['description'], 0, 120)) ?>...</p>
                            
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <div>
                                    <i class="fas fa-map-marker-alt mr-1"></i> 
                                    <?= round($disaster['latitude'], 3) ?>, <?= round($disaster['longitude'], 3) ?>
                                </div>
                                <div>
                                    <i class="fas fa-clock mr-1"></i> 
                                    <?= timeElapsedString($disaster['created_at']) ?>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-user mr-1"></i> 
                                    Reported by: <?= htmlspecialchars($disaster['reported_by_name'] ?? 'Unknown') ?>
                                </div>
                                <a href="disaster-details.php?id=<?= $disaster['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="disasters.php" class="inline-block bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-3 rounded-md font-semibold">
                    View All Disasters <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 text-blue-700 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm md:text-base">No active disasters at the moment. Stay prepared!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Key Features</h2>
            <p class="text-gray-600 max-w-3xl mx-auto">Our platform provides essential tools for effective disaster response coordination.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Real-time Location Tracking</h3>
                <p class="text-gray-600">Track the location of rescue agencies in real-time to coordinate response efforts effectively.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Inter-Agency Communication</h3>
                <p class="text-gray-600">Communicate with other agencies seamlessly to share information and coordinate activities.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Resource Management</h3>
                <p class="text-gray-600">Track and allocate resources efficiently to ensure they reach where they are needed most.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Real-time Alerts</h3>
                <p class="text-gray-600">Receive instant notifications about new disasters, resource requests, and important updates.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-brain"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">AI-Powered Predictions</h3>
                <p class="text-gray-600">Leverage artificial intelligence to predict disaster progression and optimize resource allocation.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-indigo-600 text-3xl mb-4">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Analytics Dashboard</h3>
                <p class="text-gray-600">Access comprehensive analytics to evaluate response effectiveness and improve future operations.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-indigo-700 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Join the Network of Rescue Agencies</h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto">Be part of a coordinated effort to save lives during disasters. Register your agency today and enhance your response capabilities.</p>
        <?php if (!$isLoggedIn): ?>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="register.php" class="bg-white text-indigo-700 hover:bg-indigo-100 px-8 py-4 rounded-md font-semibold text-lg">
                    <i class="fas fa-user-plus mr-2"></i> Register Your Agency
                </a>
                <a href="about.php" class="border border-white text-white hover:bg-indigo-600 px-8 py-4 rounded-md font-semibold text-lg">
                    <i class="fas fa-info-circle mr-2"></i> Learn More
                </a>
            </div>
        <?php else: ?>
            <a href="report-disaster.php" class="bg-white text-indigo-700 hover:bg-indigo-100 px-8 py-4 rounded-md font-semibold text-lg inline-block">
                <i class="fas fa-plus-circle mr-2"></i> Report a Disaster
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Map Initialization Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('map').setView([<?= DEFAULT_LAT ?>, <?= DEFAULT_LNG ?>], <?= DEFAULT_ZOOM ?>);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add disaster markers
        <?php if (isset($recentDisasters) && count($recentDisasters) > 0): ?>
            <?php foreach ($recentDisasters as $disaster): ?>
                // Create custom disaster marker
                const disasterIcon = L.divIcon({
                    className: 'disaster-marker <?= $disaster['disaster_type'] ?>',
                    html: '<i class="fas fa-exclamation"></i>',
                    iconSize: [20, 20]
                });
                
                // Add marker to map
                const disasterMarker = L.marker([<?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?>], {
                    icon: disasterIcon
                }).addTo(map);
                
                // Add popup
                disasterMarker.bindPopup(`
                    <div class="text-center">
                        <h3 class="font-bold"><?= htmlspecialchars($disaster['title']) ?></h3>
                        <p class="text-sm"><?= ucfirst($disaster['disaster_type']) ?> - <?= ucfirst($disaster['severity']) ?></p>
                        <p class="text-xs mt-1"><?= htmlspecialchars(substr($disaster['description'], 0, 100)) ?>...</p>
                        <a href="disaster-details.php?id=<?= $disaster['id'] ?>" class="text-blue-600 hover:underline text-sm block mt-2">View Details</a>
                    </div>
                `);
                
                // Add circle to represent affected area
                L.circle([<?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?>], {
                    color: '<?= $disaster['disaster_type'] === 'fire' ? '#c2410c' : ($disaster['disaster_type'] === 'flood' ? '#1d4ed8' : '#b91c1c') ?>',
                    fillColor: '<?= $disaster['disaster_type'] === 'fire' ? '#c2410c' : ($disaster['disaster_type'] === 'flood' ? '#1d4ed8' : '#b91c1c') ?>',
                    fillOpacity: 0.2,
                    radius: <?= $disaster['radius_km'] * 1000 ?>
                }).addTo(map);
            <?php endforeach; ?>
        <?php endif; ?>
        
        // Add agency markers
        <?php if (isset($activeAgencies) && count($activeAgencies) > 0): ?>
            <?php foreach ($activeAgencies as $agency): ?>
                <?php if ($agency['latitude'] && $agency['longitude']): ?>
                    // Create custom agency marker
                    const agencyIcon = L.divIcon({
                        className: 'agency-marker <?= $agency['agency_type'] ?>',
                        iconSize: [12, 12]
                    });
                    
                    // Add marker to map
                    const agencyMarker = L.marker([<?= $agency['latitude'] ?>, <?= $agency['longitude'] ?>], {
                        icon: agencyIcon
                    }).addTo(map);
                    
                    // Add popup
                    agencyMarker.bindPopup(`
                        <div class="text-center">
                            <h3 class="font-bold"><?= htmlspecialchars($agency['name']) ?></h3>
                            <p class="text-sm"><?= ucfirst($agency['agency_type']) ?></p>
                            <p class="text-xs mt-1">Last active: <?= timeElapsedString($agency['last_active']) ?></p>
                            <a href="agency-details.php?id=<?= $agency['id'] ?>" class="text-blue-600 hover:underline text-sm block mt-2">View Details</a>
                        </div>
                    `);
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
