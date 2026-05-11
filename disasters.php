<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get database connection
$conn = getDbConnection();

// Initialize variables
$disasters = [];
$filterType = $_GET['type'] ?? 'all';
$filterStatus = $_GET['status'] ?? 'all';
$filterSeverity = $_GET['severity'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$totalDisasters = 0;

if ($conn) {
    // Build query
    $query = "
        SELECT d.*, a.name as reported_by_name 
        FROM disasters d
        LEFT JOIN agencies a ON d.reported_by = a.id
        WHERE 1=1
    ";
    $params = [];
    
    // Apply filters
    if ($filterType !== 'all') {
        $query .= " AND d.disaster_type = :type";
        $params['type'] = $filterType;
    }
    
    if ($filterStatus !== 'all') {
        $query .= " AND d.status = :status";
        $params['status'] = $filterStatus;
    }
    
    if ($filterSeverity !== 'all') {
        $query .= " AND d.severity = :severity";
        $params['severity'] = $filterSeverity;
    }
    
    if (!empty($searchQuery)) {
        $query .= " AND (d.title LIKE :search OR d.description LIKE :search)";
        $params['search'] = "%$searchQuery%";
    }
    
    // Count total disasters
    $countStmt = $conn->prepare($query);
    $countStmt->execute($params);
    $totalDisasters = $countStmt->rowCount();
    
    // Calculate pagination
    $totalPages = ceil($totalDisasters / $perPage);
    $offset = ($page - 1) * $perPage;
    
    // Get disasters with pagination
    $query .= " ORDER BY d.created_at DESC LIMIT :offset, :limit";
    $params['offset'] = $offset;
    $params['limit'] = $perPage;
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === 'offset' || $key === 'limit') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":$key", $value);
        }
    }
    
    $stmt->execute();
    $disasters = $stmt->fetchAll();
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Disasters</h1>
            <p class="text-gray-600 mt-2">View and manage disaster information</p>
        </div>
        
        <?php if ($isLoggedIn): ?>
            <a href="report-disaster.php" class="mt-4 md:mt-0 bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md font-medium">
                <i class="fas fa-plus-circle mr-2"></i> Report Disaster
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="type" class="block text-gray-700 font-medium mb-2">Disaster Type</label>
                <select id="type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="earthquake" <?= $filterType === 'earthquake' ? 'selected' : '' ?>>Earthquake</option>
                    <option value="flood" <?= $filterType === 'flood' ? 'selected' : '' ?>>Flood</option>
                    <option value="fire" <?= $filterType === 'fire' ? 'selected' : '' ?>>Fire</option>
                    <option value="hurricane" <?= $filterType === 'hurricane' ? 'selected' : '' ?>>Hurricane</option>
                    <option value="tsunami" <?= $filterType === 'tsunami' ? 'selected' : '' ?>>Tsunami</option>
                    <option value="landslide" <?= $filterType === 'landslide' ? 'selected' : '' ?>>Landslide</option>
                    <option value="pandemic" <?= $filterType === 'pandemic' ? 'selected' : '' ?>>Pandemic</option>
                    <option value="other" <?= $filterType === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="reported" <?= $filterStatus === 'reported' ? 'selected' : '' ?>>Reported</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="contained" <?= $filterStatus === 'contained' ? 'selected' : '' ?>>Contained</option>
                    <option value="resolved" <?= $filterStatus === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
            </div>
            
            <div>
                <label for="severity" class="block text-gray-700 font-medium mb-2">Severity</label>
                <select id="severity" name="severity" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" <?= $filterSeverity === 'all' ? 'selected' : '' ?>>All Severities</option>
                    <option value="low" <?= $filterSeverity === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $filterSeverity === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $filterSeverity === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= $filterSeverity === 'critical' ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>
            
            <div>
                <label for="search" class="block text-gray-700 font-medium mb-2">Search</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search by title or description"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md font-medium w-full">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
    
    <!-- Disasters List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($disasters) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Disaster
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Location
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Severity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reported
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($disasters as $disaster): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                            <i class="<?= getDisasterTypeIcon($disaster['disaster_type']) ?>"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($disaster['title']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: <?= $disaster['id'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <?= ucfirst($disaster['disaster_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= round($disaster['latitude'], 3) ?>, <?= round($disaster['longitude'], 3) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusClass($disaster['status']) ?>">
                                        <?= ucfirst($disaster['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getSeverityClass($disaster['severity']) ?>">
                                        <?= ucfirst($disaster['severity']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><?= timeElapsedString($disaster['created_at']) ?></div>
                                    <div class="text-xs">by <?= htmlspecialchars($disaster['reported_by_name'] ?? 'Unknown') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="disaster-details.php?id=<?= $disaster['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($isLoggedIn): ?>
                                        <a href="respond-disaster.php?id=<?= $disaster['id'] ?>" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-hands-helping"></i> Respond
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?= ($offset + 1) ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalDisasters) ?></span> of <span class="font-medium"><?= $totalDisasters ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= ($page - 1) ?>&type=<?= $filterType ?>&status=<?= $filterStatus ?>&severity=<?= $filterSeverity ?>&search=<?= urlencode($searchQuery) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $startPage + 4);
                                
                                if ($startPage > 1) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                
                                for ($i = $startPage; $i <= $endPage; $i++) {
                                    $isActive = $i === $page;
                                    $classes = $isActive 
                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
                                    
                                    echo '<a href="?page=' . $i . '&type=' . $filterType . '&status=' . $filterStatus . '&severity=' . $filterSeverity . '&search=' . urlencode($searchQuery) . '" class="relative inline-flex items-center px-4 py-2 border ' . $classes . ' text-sm font-medium">' . $i . '</a>';
                                }
                                
                                if ($endPage < $totalPages) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= ($page + 1) ?>&type=<?= $filterType ?>&status=<?= $filterStatus ?>&severity=<?= $filterSeverity ?>&search=<?= urlencode($searchQuery) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 mb-4">
                    <i class="fas fa-search text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No disasters found</h3>
                <p class="text-gray-500 mb-6">No disasters match your search criteria. Try adjusting your filters or search query.</p>
                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    <i class="fas fa-redo mr-2"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
