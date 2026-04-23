<?php
$db = new PDO('sqlite:database/database.sqlite');
$tablesQuery = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
$output = "";

while($table = $tablesQuery->fetch(PDO::FETCH_ASSOC)) {
    $tableName = $table['name'];
    $output .= "-- Table: $tableName\n";
    $output .= $table['sql'] . ";\n\n";
    
    $rows = $db->query("SELECT * FROM \"$tableName\"");
    while($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        $cols = array_keys($row);
        $vals = array_values($row);
        
        // Escape values
        $escapedVals = array_map(function($val) use ($db) {
            if ($val === null) return 'NULL';
            return $db->quote($val);
        }, $vals);
        
        $output .= "INSERT INTO \"$tableName\" (\"" . implode('", "', $cols) . "\") VALUES (" . implode(', ', $escapedVals) . ");\n";
    }
    $output .= "\n\n";
}

file_put_contents('base.sql', $output);
echo "Database dumped to base.sql\n";
