<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$identity = $input['identity'] ?? '';
$action   = $input['action'] ?? '';
$storeFile = __DIR__ . '/backups_searches.json';

if ($identity === '') {
    echo json_encode(['searches' => []]);
    exit;
}

if (!file_exists($storeFile)) {
    file_put_contents($storeFile, json_encode([], JSON_PRETTY_PRINT));
}

$all = json_decode(file_get_contents($storeFile), true);
if (!is_array($all)) {
    $all = [];
}

if ($action === 'list') {
    $searches = $all[$identity] ?? [];
    echo json_encode(['searches' => $searches]);
    exit;
}

if ($action === 'save') {
    $search = $input['search'] ?? null;
    if (!is_array($search) || !isset($search['policyId'])) {
        echo json_encode(['ok' => false]);
        exit;
    }

    if (!isset($all[$identity]) || !is_array($all[$identity])) {
        $all[$identity] = [];
    }

    $updated = false;
    foreach ($all[$identity] as &$s) {
        if (isset($s['policyId']) && $s['policyId'] === $search['policyId']) {
            $s = $search;
            $updated = true;
            break;
        }
    }
    unset($s);

    if (!$updated) {
        $all[$identity][] = $search;
    }

    file_put_contents($storeFile, json_encode($all, JSON_PRETTY_PRINT));
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['searches' => []]);
