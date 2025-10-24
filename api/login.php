<?php
declare(strict_types=1);
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

require_once __DIR__ . '/../config.php';

session_start();

function json_fail(string $message, int $status = 400): void {
  http_response_code($status);
  echo json_encode(['ok' => false, 'error' => $message]);
  exit;
}

// Read JSON body or form data
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '{}', true);
if (!is_array($data) || !$data) {
  $data = $_POST;
}

$username = strtolower(trim((string)($data['username'] ?? '')));
$password = (string)($data['password'] ?? '');

if ($username === '' || $password === '') {
  json_fail('Missing username or password.', 422);
}

try {
  $pdo = get_pdo();

  // Accept username or email
  $stmt = $pdo->prepare('SELECT pk, name, username, password, account_type FROM login_tb WHERE LOWER(username) = :u OR LOWER(username) = :email LIMIT 1');
  $stmt->execute([':u' => $username, ':email' => $username]);
  $row = $stmt->fetch();

  if (!$row) {
    json_fail('Invalid credentials.', 401);
  }

  $stored = (string)$row['password'];

  $isHash = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2');
  $valid = $isHash ? password_verify($password, $stored) : hash_equals($stored, $password);

  if (!$valid) {
    json_fail('Invalid credentials.', 401);
  }

  // Regenerate session ID to prevent fixation
  session_regenerate_id(true);

  $_SESSION['user'] = [
    'pk' => (int)$row['pk'],
    'name' => (string)$row['name'],
    'username' => (string)$row['username'],
    'account_type' => (string)$row['account_type'],
    'logged_in' => true,
  ];

  echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}


