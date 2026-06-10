<?php
// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Dummy database of existing users and emails
$takenUsernames = ['admin', 'devsphere', 'khaja', 'shahzad', 'intern', 'john_doe'];
$takenEmails = ['admin@devsphere.com', 'test@test.com', 'user@example.com', 'john@example.com'];

// Read dynamically from local JSON database if it exists
$dbFile = 'users.json';
if (file_exists($dbFile)) {
    $jsonContent = file_get_contents($dbFile);
    $users = json_decode($jsonContent, true);
    if (is_array($users)) {
        foreach ($users as $u) {
            if (isset($u['username'])) {
                $takenUsernames[] = strtolower(trim($u['username']));
            }
            if (isset($u['email'])) {
                $takenEmails[] = strtolower(trim($u['email']));
            }
        }
    }
}

// Read input parameters (check query string or JSON payload)
$username = isset($_GET['username']) ? trim($_GET['username']) : null;
$email = isset($_GET['email']) ? trim($_GET['email']) : null;

// Fallback to JSON payload if query parameters are empty (for POST requests)
if (!$username && !$email) {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);
    if ($data) {
        $username = isset($data['username']) ? trim($data['username']) : null;
        $email = isset($data['email']) ? trim($data['email']) : null;
    }
}

$response = [
    'exists' => false,
    'message' => 'Available!'
];

// Check if username is taken
if ($username !== null) {
    $usernameLower = strtolower($username);
    if (in_array($usernameLower, $takenUsernames)) {
        $response['exists'] = true;
        $response['message'] = 'This username is already taken.';
    }
} 
// Check if email is taken
elseif ($email !== null) {
    $emailLower = strtolower($email);
    if (in_array($emailLower, $takenEmails)) {
        $response['exists'] = true;
        $response['message'] = 'This email is already registered.';
    }
} else {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Missing username or email parameter.'
    ]);
    exit();
}

// Respond with JSON status
echo json_encode($response);
exit();
?>
