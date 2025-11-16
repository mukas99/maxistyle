<?php
// parser.php
// This script accepts a string like:
//   "test test [145], мухаммед ануарбек [139], кто-то ещё [777]"
// and returns:
//   { "ids": ["145","139","777"], "ids_string": "145,139,777" }

/**
 * Extract all numeric IDs inside square brackets from a given text.
 *
 * @param string $text
 * @return array
 */
function extractIdsFromString(string $text): array
{
    // Find all numbers inside [ ... ]
    if (preg_match_all('/\[(\d+)\]/u', $text, $matches)) {
        return $matches[1];
    }
    return [];
}

// Read input
// You can pass data either as form field "text" or as JSON {"text": "..."}.

$input = null;

// 1) Try form field
if (isset($_REQUEST['text'])) {
    $input = $_REQUEST['text'];
}

// 2) Try JSON body
if ($input === null) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($json['text'])) {
                $input = $json['text'];
            } elseif (isset($json['items']) && is_array($json['items'])) {
                // If an array of strings is passed
                $input = $json['items'];
            }
        }
    }
}

$ids = [];

if (is_array($input)) {
    foreach ($input as $item) {
        if (!is_string($item)) {
            continue;
        }
        $ids = array_merge($ids, extractIdsFromString($item));
    }
} elseif (is_string($input)) {
    $ids = extractIdsFromString($input);
}

// Remove duplicates and reindex
$ids = array_values(array_unique($ids));

$result = [
    'ids'        => $ids,
    'ids_string' => implode(',', $ids),
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
