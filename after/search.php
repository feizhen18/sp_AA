<?php
// search.php - SECURE VERSION
require_once 'db_config.php'; // Ensure your database connection uses PDO

if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];

    try {
        // FIX: Use Prepared Statements to structurally isolate data from commands
        $stmt = $pdo->prepare("SELECT id, name, illness_history FROM patient_records WHERE name LIKE ?");
        
        // Safely bind the wildcard search term
        $searchTerm = "%" . $keyword . "%";
        $stmt->execute([$searchTerm]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($results) > 0) {
            foreach ($results as $row) {
                // FIX: Use context-aware output encoding to prevent DOM-based execution (XSS)
                $safe_keyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
                $safe_name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                $safe_history = htmlspecialchars($row['illness_history'], ENT_QUOTES, 'UTF-8');
                
                echo "<div>Result found for keyword: " . $safe_keyword . "<br>";
                echo "Patient: " . $safe_name . " | History: " . $safe_history . "</div><hr>";
            }
        } else {
            // FIX: Encode the keyword in the error message as well
            $safe_keyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
            echo "No records found for: " . $safe_keyword;
        }
    } catch (PDOException $e) {
        // Fail safely without exposing database stack traces
        echo "A database error occurred. Please try again later.";
    }
}
?>