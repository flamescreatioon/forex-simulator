<?php
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);
require_once 'includes/session.php';
header('Content-Type: application/json');

// Read JSON body or form data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}
$pairs = [];
if (isset($data['pairs'])) {
    if (is_array($data['pairs'])) {
        $pairs = $data['pairs'];
    } else if (is_string($data['pairs'])) {
        $pairs = preg_split('/[\r\n,]+/', $data['pairs']);
    }
}

$clean = [];
foreach ($pairs as $s) {
    $s = strtoupper(trim(str_replace(' ', '', $s)));
    if ($s === '') continue;
    if (strpos($s, '/') === false && strlen($s) === 6) {
        $s = substr($s, 0, 3) . '/' . substr($s, 3);
    }
    $clean[] = $s;
}
$clean = array_values(array_unique($clean));

if (empty($clean)) {
    echo json_encode(['success' => false, 'error' => 'No pairs provided']);
    exit;
}

$_SESSION['pairs'] = $clean;
@setcookie('pairs', implode(',', $clean), time() + (86400 * 30), '/');

echo json_encode(['success' => true, 'pairs' => $clean]);
