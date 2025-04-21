<?php
require('lib/functions.php');

$host = 'localhost';
$dbname = 'volunteers';
$username = 'assigner';
$password = 'Assignments_789';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = '';

    // Handle POST and redirect with GET
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
        $query = trim($_POST['query']);
        header("Location: search.php?query=" . urlencode($query));
        exit;
    }

    // Handle GET display
    if (isset($_GET['query'])) {
        $query = trim($_GET['query']);
        $results = searchData($pdo, $query);
    } else {
        $results = null;
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <style>
        .result-section { margin-bottom: 2rem; }
        .result-section h3 { margin-top: 1rem; }
        ul { padding-left: 1rem; }
        li { margin-bottom: 0.5rem; }
    </style>
</head>
<body>

<h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

<?php if ($results): ?>

    <div class="result-section">
        <h3>Organizations</h3>
        <?php if (count($results['organizations']) > 0): ?>
            <ul>
                <?php foreach ($results['organizations'] as $org): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($org['name']); ?></strong><br>
                        <?php echo htmlspecialchars($org['email']); ?> | <?php echo htmlspecialchars($org['address']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No matching organizations found.</p>
        <?php endif; ?>
    </div>

    <div class="result-section">
        <h3>Events</h3>
        <?php if (count($results['events']) > 0): ?>
            <ul>
                <?php foreach ($results['events'] as $event): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong> by <?php echo htmlspecialchars($event['organization_name']); ?><br>
                        <?php echo htmlspecialchars($event['description']); ?><br>
                        Location: <?php echo htmlspecialchars($event['location']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No matching events found.</p>
        <?php endif; ?>
    </div>

<?php else: ?>
    <p>Use the search form above to find events and organizations.</p>
<?php endif; ?>

</body>
</html>
