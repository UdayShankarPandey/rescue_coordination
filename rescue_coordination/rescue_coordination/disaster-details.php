<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get disaster ID from URL
$disasterId = intval($_GET['id'] ?? 0);

// Get database connection
$conn = getDbConnection();

// Initialize variables
$disaster = null;
$reportedBy = null;
$respondingAgencies = [];
$resourceRequests = [];

// Get disaster details
if ($conn && $disasterId > 0) {
    // Get disaster information
    $stmt = $conn->prepare("
        SELECT d.*, a.name as reported_by_name, a.agency_type as reported_by_type
        FROM disasters d
        LEFT JOIN agencies a ON d.reported_by = a.id
        WHERE d.id = :id
    ");
    $stmt->execute(['id' => $disasterId]);
    $disaster = $stmt->fetch();
    
    if ($disaster) {
        // Get responding agencies
        $stmt = $conn->prepare("
            SELECT a.*, al.status as response_status
            FROM agency_locations al
            JOIN agencies a ON al.agency_id = a.id
            WHERE al.disaster_id = :disaster_id
            ORDER BY al.timestamp DESC
        ");
        $stmt->execute(['disaster_id' => $disasterId]);
        $respondingAgencies = $stmt->fetchAll();
        
        // Get resource requests
        $stmt = $conn->prepare("
            SELECT rr.*, 
                   req.name as requesting_agency_name,
                   ful.name as fulfilling_agency_name
            FROM resource_requests rr
            JOIN agencies req ON rr.requesting_agency_id = req.id
            LEFT JOIN agencies ful ON rr.fulfilling_agency_id = ful.id
            WHERE rr.disaster_id = :disaster_id
            ORDER BY rr.requested_at DESC
        ");
        $stmt->execute(['disaster_id' => $disasterId]);
        $resourceRequests = $stmt->fetchAll();
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Main content -->
<?php if (!$disaster): ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-red-100 text-red-700 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                </div>
                <div class="ml-3">
                    <p>Disaster not found or has been removed.</p>
                    <p class="mt-2">
                        <a href="disasters.php" class="text-red-700 underline">Return to disasters list</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="container mx-auto px-4 py-8">
        <!-- Disaster header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($disaster['title']) ?></h1>
                <div class="flex flex-wrap items-center mt-2">
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-<?= getStatusColor($disaster['status']) ?>-100 text-<?= getStatusColor($disaster['status']) ?>-800 mr-2 mb-2">
                        <?= ucfirst($disaster['status']) ?>
                    </span>
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-<?= getSeverityColor($disaster['severity']) ?>-100 text-<?= getSeverityColor($disaster['severity']) ?>-800 mr-2 mb-2">
                        <?= ucfirst($disaster['severity']) ?>
                    </span>
                    <span class="inline-block px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-800 mb-2">
                        <?= ucfirst($disaster['disaster_type']) ?>
                    </span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                <?php if (isLoggedIn()): ?>
                    <a href="respond-disaster.php?id=<?= $disaster['id'] ?>" class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded-md">
                        <i class="fas fa-hands-helping mr-2"></i> Respond
                    </a>
                    
                    <a href="request-resources.php?disaster_id=<?= $disaster['id'] ?>" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md">
                        <i class="fas fa-boxes mr-2"></i> Request Resources
                    </a>
                <?php endif; ?>
                
                <a href="disasters.php" class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-4 py-2 rounded-md">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
        
        <!-- Main content grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left column: Disaster information -->
            <div class="lg:col-span-2">
                <!-- Basic information card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Disaster Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Description</h3>
                            <p class="text-gray-600"><?= nl2br(htmlspecialchars($disaster['description'])) ?></p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-800 mb-2">Details</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li><strong>Reported by:</strong> <?= htmlspecialchars($disaster['reported_by_name'] ?? 'Unknown') ?></li>
                                    <li><strong>Reported:</strong> <?= formatDate($disaster['created_at']) ?></li>
                                    <li><strong>Started:</strong> <?= $disaster['started_at'] ? formatDate($disaster['started_at']) : 'Unknown' ?></li>
                                    <li><strong>Status updated:</strong> <?= timeElapsedString($disaster['updated_at']) ?></li>
                                    <li><strong>Affected radius:</strong> <?= $disaster['radius_km'] ?> km</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-800 mb-2">Location</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li><strong>Coordinates:</strong> <?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?></li>
                                    <li>
                                        <a href="https://www.google.com/maps?q=<?= $disaster['latitude'] ?>,<?= $disaster['longitude'] ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                            <i class="fas fa-external-link-alt mr-1"></i> View on Google Maps
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Map -->
                        <div id="disaster-map" class="w-full h-80 rounded-md border border-gray-200"></div>
                    </div>
                </div>
            </div>
            
            <!-- Right column: Sidebar -->
            <div>
                <!-- Responding agencies card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Responding Agencies</h2>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                            <?= count($respondingAgencies) ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <?php if (count($respondingAgencies) > 0): ?>
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($respondingAgencies as $agency): ?>
                                    <li class="py-3 flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                            <i class="<?= getAgencyTypeIcon($agency['agency_type']) ?>"></i>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($agency['name']) ?></p>
                                            <p class="text-xs text-gray-500"><?= ucfirst($agency['agency_type']) ?> • <?= ucfirst($agency['response_status']) ?></p>
                                        </div>
                                        <a href="agency-details.php?id=<?= $agency['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                            Details
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-gray-500">No agencies responding yet.</p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="respond-disaster.php?id=<?= $disaster['id'] ?>" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-hands-helping mr-1"></i> Be the first to respond
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Resource requests card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Resource Requests</h2>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                            <?= count($resourceRequests) ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <?php if (count($resourceRequests) > 0): ?>
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($resourceRequests as $request): ?>
                                    <li class="py-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($request['resource_type']) ?></p>
                                                <p class="text-xs text-gray-500">
                                                    Requested by <?= htmlspecialchars($request['requesting_agency_name']) ?>
                                                    • <?= timeElapsedString($request['requested_at']) ?>
                                                </p>
                                            </div>
                                            <span class="inline-block px-2 py-1 text-xs rounded-full 
                                                <?php
                                                switch ($request['status']) {
                                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'approved': echo 'bg-blue-100 text-blue-800'; break;
                                                    case 'fulfilled': echo 'bg-green-100 text-green-800'; break;
                                                    case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                                    case 'cancelled': echo 'bg-gray-100 text-gray-800'; break;
                                                }
                                                ?>">
                                                <?= ucfirst($request['status']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-2 flex justify-between items-center">
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium"><?= $request['quantity'] ?></span> units
                                                • Priority: <span class="font-medium"><?= ucfirst($request['priority']) ?></span>
                                            </div>
                                            <?php if (isLoggedIn() && $request['status'] === 'pending'): ?>
                                                <a href="fulfill-request.php?id=<?= $request['id'] ?>" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                                    Fulfill
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-gray-500">No resource requests yet.</p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="request-resources.php?disaster_id=<?= $disaster['id'] ?>" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-boxes mr-1"></i> Request resources
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Map Initialization Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($disaster): ?>
            // Initialize map
            const map = L.map('disaster-map').setView([<?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?>], 12);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Add disaster marker
            const disasterIcon = L.divIcon({
                className: 'disaster-marker',
                html: '<i class="fas fa-exclamation-triangle"></i>',
                iconSize: [30, 30]
            });
            
            L.marker([<?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?>], {
                icon: disasterIcon
            }).addTo(map);
            
            // Add circle to represent affected area
            L.circle([<?= $disaster['latitude'] ?>, <?= $disaster['longitude'] ?>], {
                color: '#b91c1c',
                fillColor: '#b91c1c',
                fillOpacity: 0.2,
                radius: <?= $disaster['radius_km'] * 1000 ?>
            }).addTo(map);
            
            // Add responding agency markers
            <?php foreach ($respondingAgencies as $agency): ?>
                <?php if (!empty($agency['latitude']) && !empty($agency['longitude'])): ?>
                    // Create custom agency marker
                    const agencyIcon = L.divIcon({
                        className: 'agency-marker',
                        html: '<i class="fas fa-building"></i>',
                        iconSize: [20, 20]
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
                            <p class="text-xs mt-1">Status: <?= ucfirst($agency['response_status']) ?></p>
                            <a href="agency-details.php?id=<?= $agency['id'] ?>" class="text-blue-600 hover:underline text-sm block mt-2">View Details</a>
                        </div>
                    `);
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.php'; ?>
