<?php
require_once 'bootstrap.php';
use App\Helpers\EventsHelper;
use App\Core\Database;

echo "Starting verification test...\n";

$data = [
    'event_title' => 'Test Event Verification',
    'event_date' => '2024-05-15',
    'event_end_date' => '',
    'venue' => 'Test Venue',
    'kawasan' => 'Test Kawasan',
    'objective' => 'This is a long objective text that used to cause issues with double type hint.',
    'budget_est' => '1500.50',
    'assigned_cawangan_id' => '1'
];

$userId = 36; // Using existing user ID
$isPusatCreator = true;

try {
    // We'll use the actual method. If it crashes, the script will die.
    // We can then check the DB to see if it was inserted.
    $id = EventsHelper::addEvent($data, $userId, $isPusatCreator);
    if ($id) {
        echo "SUCCESS: Event created with ID: $id\n";
        // Clean up
        $db = Database::getInstance()->getConnection();
        $db->query("DELETE FROM tbl_event WHERE event_id = $id");
        echo "Cleanup done.\n";
    } else {
        echo "FAILED: Event was not created.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
