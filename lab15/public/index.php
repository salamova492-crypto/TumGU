<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$htmlFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'Лаб_Парсер.htm';
$resultFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'result.txt';

$parsingSuccess = false;
$errorMessage = '';
$countWords = 0;
$processedBlocks = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!file_exists($htmlFilePath)) {
            throw new Exception("HTML файл не найден по пути: " . $htmlFilePath);
        }

        $htmlContent = file_get_contents($htmlFilePath);
        if ($htmlContent === false) {
            throw new Exception("Ошибка чтения HTML файла");
        }

        $detectedEncoding = null;
        if (function_exists('mb_detect_encoding')) {
            $detectedEncoding = mb_detect_encoding($htmlContent, ['UTF-8', 'CP1251', 'Windows-1251', 'ISO-8859-5', 'ASCII'], true);
        }
        if ($detectedEncoding === false || $detectedEncoding === null) {
            if (preg_match("/\<meta[^>]+charset=\s*\"?'?([^\"'\\s>]+)/i", $htmlContent, $mc)) {
                $detectedEncoding = $mc[1];
            }
        }
        if ($detectedEncoding === null || $detectedEncoding === false) {
            $detectedEncoding = 'Windows-1251';
        }

        $detectedEncodingNorm = strtoupper(str_replace(['CP', 'WINDOWS-'], ['', ''], $detectedEncoding));

        if (strtoupper($detectedEncoding) !== 'UTF-8' && strtoupper($detectedEncoding) !== 'UTF8') {
            $htmlContentUtf8 = mb_convert_encoding($htmlContent, 'UTF-8', $detectedEncoding);
        } else {
            $htmlContentUtf8 = $htmlContent;
        }

        $codeBlocks = [];

        // 1) <p ... class="...highlight..."><code>...</code></p>
        $pattern1 = '/<p\b[^>]*class=(?:"|\')?[^"\'>]*\bhighlight\b[^"\'>]*(?:"|\')?[^>]*>\s*<code[^>]*>(.*?)<\/code>\s*<\/p>/is';
        if (preg_match_all($pattern1, $htmlContentUtf8, $m1)) {
            foreach ($m1[1] as $part) {
                $codeBlocks[] = $part;
            }
        }

        // 2) <code class="...highlight...">...</code>
        $pattern2 = '/<code\b[^>]*class=(?:"|\')?[^"\'>]*\bhighlight\b[^"\'>]*(?:"|\')?[^>]*>(.*?)<\/code>/is';
        if (preg_match_all($pattern2, $htmlContentUtf8, $m2)) {
            foreach ($m2[1] as $part) {
                $codeBlocks[] = $part;
            }
        }

        // 3) <pre class="...highlight...">...</pre>
        $pattern3 = '/<pre\b[^>]*class=(?:"|\')?[^"\'>]*\bhighlight\b[^"\'>]*(?:"|\')?[^>]*>(.*?)<\/pre>/is';
        if (preg_match_all($pattern3, $htmlContentUtf8, $m3)) {
            foreach ($m3[1] as $part) {
                $codeBlocks[] = $part;
            }
        }

        $codeBlocks = array_values(array_filter(array_unique($codeBlocks), function($v){ return trim($v) !== ''; }));

        if (count($codeBlocks) === 0) {
            throw new Exception("Не найдено блоков кода с классом 'highlight'");
        }

        $resultFile = fopen($resultFilePath, 'w');
        if ($resultFile === false) {
            throw new Exception("Ошибка открытия файла для записи: " . $resultFilePath);
        }
        fwrite($resultFile, "\xEF\xBB\xBF");

        $totalPhpCount = 0;
        $processedBlocks = 0;

        foreach ($codeBlocks as $blockContent) {
            $processedBlocks++;

            $cleanCode = processCodeBlock($blockContent);

            $phpCount = countPhpWords($cleanCode);
            $totalPhpCount += $phpCount;

            fwrite($resultFile, "=== БЛОК КОДА #" . $processedBlocks . " ===\n");
            $lines = explode("\n", $cleanCode);
            foreach ($lines as $ln) {
                fwrite($resultFile, $ln . PHP_EOL);
            }
            fwrite($resultFile, PHP_EOL);
        }
        
        fwrite($resultFile, "=== ИТОГОВАЯ ИНФОРМАЦИЯ ===\n");
        fwrite($resultFile, "Обработано блоков кода: " . $processedBlocks . "\n");
        fwrite($resultFile, "Общее количество слов '?php': " . $totalPhpCount . "\n");
        
        fclose($resultFile);
        
        $countWords = $totalPhpCount;
        $parsingSuccess = true;
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        $parsingSuccess = false;
    }
}

function processCodeBlock(string $htmlContent): string {
    $content = preg_replace('/<br\s*\/?\s*>/i', "\n", $htmlContent);
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = preg_replace('/\\x{00A0}/u', ' ', $content);
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $lines = explode("\n", $content);
    while (count($lines) && trim($lines[0]) === '') {
        array_shift($lines);
    }
    while (count($lines) && trim(end($lines)) === '') {
        array_pop($lines);
    }
    $cleanLines = [];
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        $cleanLines[] = $line;
    }

    return implode("\n", $cleanLines);
}

function countPhpWords(string $code): int {
    if (preg_match_all('/\?php/i', $code, $matches)) {
        return count($matches[0]);
    }
    return 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парсер web-страниц - Лабораторная работа 15</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Лабораторная работа «Парсер web-страниц»</h1>
        
        <div class="info-block">
            <h2>Информация о задании:</h2>
            <p>Скрипт извлекает содержимое блоков кода из HTML файла и сохраняет их в текстовый файл.</p>
            <p><strong>Исходный файл:</strong> <?= htmlspecialchars($htmlFilePath) ?></p>
            <p><strong>Результат сохраняется в:</strong> <?= htmlspecialchars($resultFilePath) ?></p>
        </div>
        
        <form method="POST" class="parse-form">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Запустить парсинг</button>
            </div>
            
            <?php if ($parsingSuccess): ?>
                <div class="alert alert-success">
                    <h3>✅ Парсинг успешно выполнен!</h3>
                    <p><strong>Обработано блоков кода:</strong> <?= $processedBlocks ?></p>
                    <p><strong>Общее количество слов '?php':</strong> <?= $countWords ?></p>
                    <p><strong>Результат сохранен в файл:</strong> <?= htmlspecialchars($resultFilePath) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger">
                    <h3>❌ Ошибка при выполнении парсинга:</h3>
                    <p><?= htmlspecialchars($errorMessage) ?></p>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="instructions">
            <h2>Инструкция по использованию:</h2>
            <ol>
                <li>Нажмите кнопку "Запустить парсинг"</li>
                <li>Система прочитает HTML файл и извлечет все блоки кода</li>
                <li>Результат будет сохранен в текстовый файл</li>
                <li>На странице отобразится информация об успешном выполнении или об ошибке</li>
            </ol>
        </div>
    </div>
</body>
</html>