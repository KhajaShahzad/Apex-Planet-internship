<?php
// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

// Read JSON input payload
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!$data || empty($data['fullname']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$fullname = trim($data['fullname']);
$username = trim($data['username']);
$email = trim($data['email']);
$password = trim($data['password']);

$dbFile = 'users.json';
$users = [];

// Load existing users if the file exists
if (file_exists($dbFile)) {
    $jsonContent = file_get_contents($dbFile);
    $users = json_decode($jsonContent, true);
    if (!is_array($users)) {
        $users = [];
    }
}

// Verify duplicates on the server side just in case
foreach ($users as $u) {
    if (strtolower($u['username']) === strtolower($username)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username already exists.']);
        exit();
    }
    if (strtolower($u['email']) === strtolower($email)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit();
    }
}

// Hash password securely (industry standard best-practice)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Construct new user record
$newUser = [
    'fullname' => $fullname,
    'username' => $username,
    'email' => $email,
    'password' => $hashedPassword,
    'registered_at' => date('Y-m-d H:i:s')
];

$users[] = $newUser;

// Save updated data to users.json
if (file_put_contents($dbFile, json_encode($users, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Registration successful!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write data to storage.']);
}
exit();
?>
