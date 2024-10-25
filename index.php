<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

if (PHP_SAPI === 'cli') {
    exec('cd ' . __DIR__ . '/../idrinth-thalui && git pull');
    function scan(array &$out, string $root, string $path): void {
        foreach (scandir(rtrim($root . '/' . $path, '/')) as $file) {
            if ($file[0] === '.' || $file[0] === '_') {
                //ignore hidden files
            } elseif (is_dir($root . '/' . $path . '/' . $file) && $file !== '.' && $file !== '..') {
                $out[$file] = [];
                scan($out[$file], rtrim($root . '/' . $path, '/'), $file);
                if (count($out[$file]) === 0) {
                    unset($out[$file]);
                }
            } elseif (is_file(rtrim($root . '/' . $path, '/'). '/' . $file) && str_ends_with($file, '.md')) {
                $out[$file] = [
                    'content' => file_get_contents(rtrim($root . '/' . $path, '/') . '/' . $file),
                    'last' => filemtime(rtrim($root . '/' . $path, '/') . '/' . $file),
                ];
            }
        }
    }
    $out = [];
    scan($out, __DIR__ . '/../idrinth-thalui', '');
    file_put_contents(__DIR__ . '/../data.json', json_encode($out));
    exit;
}
$data = is_file(__DIR__ . '/../data.json') ? json_decode(file_get_contents(__DIR__ . '/../data.json') ?: '[]', true): [];
if (isset($_GET['last'])) {
    foreach ($data as $item) {
        if ($item['last'] > $_GET['last']) {
            die('true');
        }
    }
    die('false');
}
function simplify(array $data): array
{
    $return = [];
    foreach ($data as $pos => $item) {
        if (is_array($item) && !isset($item['content'])) {
            $return[$pos] = simplify($item);
        } else {
            $return[$pos] = $item['content'];
        }
    }
    return $return;
}
echo json_encode(simplify($data));
