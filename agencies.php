<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get database connection
$conn = getDbConnection();

// Initialize variables
$agencies = [];
$filterType = $_GET['type'] ?? 'all';
$filterVerified = $_GET['verified'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$totalAgencies = 0;

if ($conn) {
    // Build query
    $query = "
        SELECT id, name, email, phone, agency_type, city, state, country, 
               latitude, longitude, verified, last_active, created_at
        FROM agencies
        WHERE 1=1
    ";
    $params = [];
    
    // Apply filters
    if ($filterType !== 'all') {
        $query .= " AND agency_type = :type";
        $params['type'] = $filterType;
    }
    
    if ($filterVerified !== 'all') {
        $filterVerifiedBool = $filterVerified === 'true' ? 1 : 0;
        $query .= " AND verified = :verified";
        $params['verified'] = $filterVerifiedBool;
    }
    
    if (!empty($searchQuery)) {
        $query .= " AND (name LIKE :search OR city LIKE :search OR state LIKE :search)";
        $params['search'] = "%$searchQuery%";
    }
    
    // Count total agencies
    $countStmt = $conn->prepare($query);
    $countStmt->execute($params);
    $totalAgencies = $countStmt->rowCount();
    
    // Get paginated results
    $offset = ($page - 1) * $perPage;
    $query .= " ORDER BY last_active DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(array_merge($params, ['limit' => $perPage, 'offset' => $offset]));
    $agencies = $stmt->fetchAll();
    
    // Get agency type counts for filters
    $typeStmt = $conn->prepare("SELECT agency_type, COUNT(*) as count FROM agencies WHERE verified = TRUE GROUP BY agency_type");
    $typeStmt->execute();
    $agencyTypeCounts = [];
    while ($row = $typeStmt->fetch()) {
        $agencyTypeCounts[$row['agency_type']] = $row['count'];
    }
}

// Calculate pagination
$totalPages = ceil($totalAgencies / $perPage);

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Rescue Agencies</h1>
        <p class="text-gray-600">Browse and connect with registered rescue and relief organizations</p>
    </div>
    
    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="Agency name, city, state..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <!-- Agency Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Agency Type</label>
                    <select id="type" name="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
                        <option value="medical" <?= $filterType === 'medical' ? 'selected' : '' ?>>Medical</option>
                        <option value="fire" <?= $filterType === 'fire' ? 'selected' : '' ?>>Fire Department</option>
                        <option value="police" <?= $filterType === 'police' ? 'selected' : '' ?>>Police</option>
                        <option value="military" <?= $filterType === 'military' ? 'selected' : '' ?>>Military</option>
                        <option value="ngo" <?= $filterType === 'ngo' ? 'selected' : '' ?>>NGO</option>
                        <option value="other" <?= $filterType === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <!-- Verification Status Filter -->
                <div>
                    <label for="verified" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="verified" name="verified"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filterVerified === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="true" <?= $filterVerified === 'true' ? 'selected' : '' ?>>Verified</option>
                        <option value="false" <?= $filterVerified === 'false' ? 'selected' : '' ?>>Unverified</option>
                    </select>
                </div>
                
                <!-- Search Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <a href="agencies.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 font-medium text-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Results Info -->
    <div class="mb-6">
        <p class="text-gray-600">Showing <strong><?= $totalAgencies > 0 ? (($page - 1) * $perPage) + 1 : 0 ?></strong> to <strong><?= min($page * $perPage, $totalAgencies) ?></strong> of <strong><?= $totalAgencies ?></strong> agencies</p>
    </div>
    
    <!-- Agencies Grid -->
    <?php if (!empty($agencies)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php foreach ($agencies as $agency): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <!-- Agency Header -->
                    <div class="bg-indigo-600 text-white p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold"><?= htmlspecialchars($agency['name']) ?></h3>
                            <?php if ($agency['verified']): ?>
                                <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">Verified</span>
                            <?php else: ?>
                                <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-indigo-100 text-sm"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($agency['agency_type']))) ?></p>
                    </div>
                    
                    <!-- Agency Details -->
                    <div class="p-4 space-y-3">
                        <div>
                            <p class="text-gray-500 text-sm">Location</p>
                            <p class="text-gray-800 font-medium"><?= htmlspecialchars($agency['city']) ?>, <?= htmlspecialchars($agency['state']) ?></p>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars($agency['country']) ?></p>
                        </div>
                        
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-gray-500 text-sm">Contact</p>
                            <p class="text-gray-800 font-medium"><i class="fas fa-envelope mr-2 text-indigo-600"></i><?= htmlspecialchars($agency['email']) ?></p>
                            <p class="text-gray-800 font-medium"><i class="fas fa-phone mr-2 text-indigo-600"></i><?= htmlspecialchars($agency['phone']) ?></p>
                        </div>
                        
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-gray-500 text-sm">Last Active</p>
                            <p class="text-gray-800 text-sm">
                                <?php 
                                if ($agency['last_active']) {
                                    echo timeElapsedString($agency['last_active']);
                                } else {
                                    echo 'Never';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="p-4 bg-gray-50 border-t border-gray-200">
                        <a href="disaster-details.php?id=1" class="block w-full text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium text-sm">
                            <i class="fas fa-info-circle mr-2"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-8">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . $filterType : '' ?><?= $filterVerified !== 'all' ? '&verified=' . $filterVerified : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <i class="fas fa-chevron-left mr-1"></i> First
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . $filterType : '' ?><?= $filterVerified !== 'all' ? '&verified=' . $filterVerified : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . $filterType : '' ?><?= $filterVerified !== 'all' ? '&verified=' . $filterVerified : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                    <a href="?page=<?= $totalPages ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= $filterType !== 'all' ? '&type=' . $filterType : '' ?><?= $filterVerified !== 'all' ? '&verified=' . $filterVerified : '' ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Last <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <i class="fas fa-exclamation-circle text-yellow-600 text-3xl mb-3"></i>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">No agencies found</h3>
            <p class="text-yellow-700">Try adjusting your search or filter criteria to find agencies.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
