<?php
// parser.php
// Этот скрипт принимает строку вида:
//   "user_145, user_139, user_1"
// или массив таких строк,
// и возвращает:
//   { "ids": ["145","139","1"], "ids_string": "145,139,1" }

/**
 * Извлечь все числовые ID из подстрок формата user_XXX.
 *
 * @param string $text
 * @return array
 */
function extractIdsFromString(string $text): array
{
    // Ищем user_ЧИСЛО (регистр значения не имеет)
    if (preg_match_all('/user_(\d+)/ui', $text, $matches)) {
        return $matches[1]; // только цифры без 'user_'
    }
    return [];
}

// Читаем входные данные
// Можно передать:
// 1) POST/GET-параметр: text="user_145, user_139, user_1"
// 2) JSON: { "text": "..." } или { "items": ["...", "..."] }

$input = null;

// 1) Пробуем взять из обычных параметров
if (isset($_REQUEST['text'])) {
    $input = $_REQUEST['text'];
}

// 2) Пробуем прочитать из JSON-тела
if ($input === null) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($json['text'])) {
                // Одна строка
                $input = $json['text'];
            } elseif (isset($json['items']) && is_array($json['items'])) {
                // Массив строк
                $input = $json['items'];
            }
        }
    }
}

$ids = [];

if (is_array($input)) {
    // Если пришёл массив строк
    foreach ($input as $item) {
        if (!is_string($item)) {
            continue;
        }
        $ids = array_merge($ids, extractIdsFromString($item));
    }
} elseif (is_string($input)) {
    // Если пришла одна строка
    $ids = extractIdsFromString($input);
}

// Убираем дубликаты и переиндексируем
$ids = array_values(array_unique($ids));

// Формируем результат
$result = [
    'ids'        => $ids,                 // ["145","139","1"]
    'ids_string' => implode(',', $ids),   // "145,139,1"
];

// Отдаём JSON-ответ
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
