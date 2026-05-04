<?php
// Reload database with corrected image paths
require_once 'DBConn.php';

$sql_file = file_get_contents('myClothingStore.sql');
$statements = array_filter(array_map('trim', explode(';', $sql_file)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conn->multi_query($statement)) {
            while ($conn->next_result()) {;}
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "Error: " . $conn->error . "<br>";
        }
    }
}

echo "<h2>Database updated successfully!</h2>";
echo "<a href='clothes.php'>Go to Browse Clothes</a>";
?>
