<?php
/**
 * One-time migration: Fix sub-events with NULL cawangan_id
 * to inherit cawangan_id from their parent Master Event.
 * DELETE THIS FILE AFTER RUNNING.
 */
require_once __DIR__ . '/bootstrap.php';

$db = \App\Core\Database::getInstance()->getConnection();

$sql = "UPDATE tbl_event e 
        JOIN tbl_event p ON e.parent_event_id = p.event_id 
        SET e.cawangan_id = p.cawangan_id 
        WHERE e.cawangan_id IS NULL 
          AND e.event_level = 'SUB' 
          AND p.cawangan_id IS NOT NULL";

if ($db->query($sql)) {
    echo "<p style='color:green;font-family:sans-serif;font-size:16px;'>";
    echo "✅ Success! Updated <strong>" . $db->affected_rows . "</strong> sub-event(s) with inherited cawangan_id from their parent Master Event.";
    echo "</p>";
    echo "<p style='color:red;font-family:sans-serif;font-size:12px;'>⚠️ Please delete this file immediately: <code>fix_cawangan_inherit.php</code></p>";
} else {
    echo "<p style='color:red;font-family:sans-serif;'>❌ Query failed: " . htmlspecialchars($db->error) . "</p>";
}
?>
