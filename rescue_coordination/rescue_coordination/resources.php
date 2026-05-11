<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get database connection
$conn = getDbConnection();

// Initialize variables
$resources = [];
$filterType = $_GET['type'] ?? 'all';
$filterAgency = $_GET['agency'] ?? 'all';
$filterAvailability = $_GET['availability'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$totalResources = 0;
$agencies = [];

if ($conn) {
    // Get list of agencies for filter
    $agencyStmt = $conn->prepare("SELECT id, name FROM agencies WHERE verified = TRUE ORDER BY name");
    $agencyStmt->execute();
    $agencies = $agencyStmt->fetchAll();
    
    // Build query
    $query = "
        SELECT r.*, a.name as agency_name, a.agency_type
        FROM resources r
        JOIN agencies a ON r.agency_id = a.id
        WHERE 1=1
    ";
    $params = [];
    
    // Apply filters
    if ($filterType !== 'all') {
        $query .= " AND LOWER(r.resource_type) = :type";
        $params['type'] = strtolower($filterType);
    }
    
    if ($filterAgency !== 'all') {
        $query .= " AND r.agency_id = :agency_id";
        $params['agency_id'] = intval($filterAgency);
    }
    
    if ($filterAvailability !== 'all') {
        $availableBool = $filterAvailability === 'true' ? 1 : 0;
        $query .= " AND r.available = :available";
        $params['available'] = $availableBool;
    }
    
    if (!empty($searchQuery)) {
        $query .= " AND (r.name LIKE :search OR r.resource_type LIKE :search)";
        $params['search'] = "%$searchQuery%";
    }
    
    // Count total resources
    $countStmt = $conn->prepare($query);
    $countStmt->execute($params);
    $totalResources = $countStmt->rowCount();
    
    // Get paginated results
    $offset = ($page - 1) * $perPage;
    $query .= " ORDER BY r.updated_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(array_merge($params, ['limit' => $perPage, 'offset' => $offset]));
    $resources = $stmt->fetchAll();
    
    // Get resource type statistics
    $typeStmt = $conn->prepare("
        SELECT resource_type, COUNT(*) as count, SUM(quantity) as total_quantity
        FROM resources
        GROUP BY resource_type
        ORDER BY count DESC
        LIMIT 10
    ");
    $typeStmt->execute();
    $resourceTypes = $typeStmt->fetchAll();
}

// Calculate pagination
$totalPages = ceil($totalResources / $perPage);

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Resources Directory</h1>
        <p class="text-gray-600">Find available resources from rescue agencies for disaster response</p>
    </div>
    
    <!-- Resource Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="text-blue-600 text-3xl font-bold mb-2"><?= $totalResources ?></div>
            <div class="text-gray-700 font-medium">Total Resources</div>
            <p class="text-gray-500 text-sm mt-1">Registered in the system</p>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="text-green-600 text-3xl font-bold mb-2"><?= $conn ? $conn->query("SELECT COUNT(*) FROM resources WHERE available = TRUE")->fetchColumn() : 0 ?></div>
            <div class="text-gray-700 font-medium">Available Resources</div>
            <p class="text-gray-500 text-sm mt-1">Ready for deployment</p>
        </div>
        
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="text-purple-600 text-3xl font-bold mb-2"><?= count($agencies) ?></div>
            <div class="text-gray-700 font-medium">Contributing Agencies</div>
            <p class="text-gray-500 text-sm mt-1">Providing resources</p>
        </div>
    </div>
    
    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="Resource name, type..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <!-- Resource Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                    <select id="type" name="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
                        <option value="ambulance" <?= $filterType === 'ambulance' ? 'selected' : '' ?>>Ambulance</option>
                        <option value="fire truck" <?= $filterType === 'fire truck' ? 'selected' : '' ?>>Fire Truck</option>
                        <option value="medical equipment" <?= $filterType === 'medical equipment' ? 'selected' : '' ?>>Medical Equipment</option>
                        <option value="personnel" <?= $filterType === 'personnel' ? 'selected' : '' ?>>Personnel</option>
                        <option value="vehicles" <?= $filterType === 'vehicles' ? 'selected' : '' ?>>Vehicles</option>
                        <option value="supplies" <?= $filterType === 'supplies' ? 'selected' : '' ?>>Supplies</option>
                        <option value="equipment" <?= $filterType === 'equipment' ? 'selected' : '' ?>>Equipment</option>
                    </select>
                </div>
                
                <!-- Agency Filter -->
                <div>
                    <label for="agency" class="block text-sm font-medium text-gray-700 mb-2">Agency</label>
                    <select id="agency" name="agency"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filterAgency === 'all' ? 'selected' : '' ?>>All Agencies</option>
                        <?php foreach ($agencies as $ag): ?>
                            <option value="<?= $ag['id'] ?>" <?= $filterAgency == $ag['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ag['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Availability Filter -->
                <div>
                    <label for="availability" class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                    <select id="availability" name="availability"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filterAvailability === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="true" <?= $filterAvailability === 'true' ? 'selected' : '' ?>>Available</option>
                        <option value="false" <?= $filterAvailability === 'false' ? 'selected' : '' ?>>Not Available</option>
                    </select>
                </div>
                
                <!-- Search Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Results Info -->
    <div class="mb-6">
        <p class="text-gray-600">Showing <strong><?= $totalResources > 0 ? (($page - 1) * $perPage) + 1 : 0 ?></strong> to <strong><?= min($page * $perPage, $totalResources) ?></strong> of <strong><?= $totalResources ?></strong> resources</p>
    </div>
    
    <!-- Resources Table -->
    <?php if (!empty($resources)): ?>
        <div class="bg-white rounded-lg shadow-md overflow-x-auto mb-8">
            <table class="w-full">
                <thead class="bg-indigo-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Resource Name</th>
                        <th class="px-6 py-3 text-left font-semibold">Type</th>
                        <th class="px-6 py-3 text-left font-semibold">Agency</th>
                        <th class="px-6 py-3 text-center font-semibold">Quantity</th>
                        <th class="px-6 py-3 text-center font-semibold">Status</th>
                        <th class="px-6 py-3 text-center font-semibold">Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resources as $resource): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($resource['name']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    <?= htmlspecialchars($resource['resource_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: <?php
                                        $colors = ['medical' => '#ef4444', 'fire' => '#f97316', 'police' => '#3b82f6', 'military' => '#65a30d', 'ngo' => '#8b5cf6', 'other' => '#6b7280'];
                                        echo $colors[$resource['agency_type']] ?? '#6b7280';
                                    ?>"></span>
                                    <span class="text-gray-800"><?= htmlspecialchars($resource['agency_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-semibold text-gray-800">
                                    <?= $resource['quantity'] ?> <?= htmlspecialchars($resource['unit'] ?? 'units') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($resource['available']): ?>
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-check-circle mr-1"></i> Available
                                    </span>
                                <?php else: ?>
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-times-circle mr-1"></i> Not Available
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-600 text-sm"><?= timeElapsedString($resource['updated_at']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-8">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . urlencode($filterType) : '' ?><?= $filterAgency !== 'all' ? '&agency=' . $filterAgency : '' ?><?= $filterAvailability !== 'all' ? '&availability=' . $filterAvailability : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <i class="fas fa-chevron-left mr-1"></i> First
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . urlencode($filterType) : '' ?><?= $filterAgency !== 'all' ? '&agency=' . $filterAgency : '' ?><?= $filterAvailability !== 'all' ? '&availability=' . $filterAvailability : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . urlencode($filterType) : '' ?><?= $filterAgency !== 'all' ? '&agency=' . $filterAgency : '' ?><?= $filterAvailability !== 'all' ? '&availability=' . $filterAvailability : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                    <a href="?page=<?= $totalPages ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . urlencode($filterType) : '' ?><?= $filterAgency !== 'all' ? '&agency=' . $filterAgency : '' ?><?= $filterAvailability !== 'all' ? '&availability=' . $filterAvailability : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Last <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <i class="fas fa-exclamation-circle text-yellow-600 text-3xl mb-3"></i>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">No resources found</h3>
            <p class="text-yellow-700">No resources match your search criteria. Try adjusting your filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
