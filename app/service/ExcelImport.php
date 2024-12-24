<?php
declare (strict_types = 1);

namespace app\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Db;
use think\facade\Log;
use PDO;

class ExcelImport
{
    private $pdo;
    private $batchSize = 1000;
    private $stopFile;
    private $errorMessages = [];
    private $errorSummary = [];
    private $successCount = 0;
    private $errorCount = 0;

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=phkq;charset=utf8mb4',
            'root',
            '760516',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ]
        );
        
        $this->stopFile = runtime_path() . 'import_stop';
    }

    public function importDailyRecord(string $filePath, string $originalName): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception('文件不存在');
            }

            // 计算文件MD5并检查是否重复导入
            $fileMd5 = md5_file($filePath);
            if ($this->isFileImported($fileMd5)) {
                throw new \Exception("文件 {$originalName} 已经导入过，请勿重复导入");
            }

            set_time_limit(0);
            $startTime = microtime(true);

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $totalRows = $highestRow - 1;

            // 读取表头并建立映射
            $excelColumnMap = $this->getHeaderMap($worksheet, $highestColumnIndex);

            // 检查必要字段
            $requiredFields = ['客户编号', '客户名称', '对公客户账号'];
            $missingFields = $this->checkMissingFields($excelColumnMap, $requiredFields);
            if (!empty($missingFields)) {
                throw new \Exception("Excel中缺少必要字段：" . implode(', ', $missingFields));
            }

            // 初始化缓存和进度信息
            $cacheKey = 'excel_import_progress_' . md5($filePath);
            cache($cacheKey, null);
            $progress = [
                'total' => $totalRows,
                'current' => 0,
                'success' => 0,
                'error' => 0,
                'percent' => 0,
                'status' => 'processing'
            ];
            cache($cacheKey, $progress, 3600);

            // 逐行读取数据并批量插入
            $this->successCount = 0;
            $this->errorCount = 0;
            $batch = [];
            $currentRow = 0;
            $this->errorSummary = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                if (file_exists($this->stopFile)) {
                    unlink($this->stopFile);
                    cache($cacheKey, null);
                    throw new \Exception("导入已手动停止");
                }

                try {
                    $rowData = $this->getRowData($worksheet, $row, $excelColumnMap, $requiredFields);
                    if (empty($rowData)) {
                        continue;
                    }

                    $batch[] = $rowData;
                    $currentRow = $row - 1;

                    if (count($batch) >= $this->batchSize) {
                        $this->batchInsert($batch);
                        $this->successCount += count($batch);
                        $batch = [];

                        $progress['current'] = $currentRow;
                        $progress['success'] = $this->successCount;
                        $progress['error'] = $this->errorCount;
                        $progress['percent'] = round(($currentRow / $totalRows) * 100, 2);
                        cache($cacheKey, $progress, 3600);
                    }
                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->logError($e, $row);
                }
            }

            // 处理剩余批次
            if (!empty($batch)) {
                $this->batchInsert($batch);
                $this->successCount += count($batch);
            }

            // 格式化错误信息
            $errorMessages = $this->formatErrorMessages();

            // 更新最终进度
            $finalProgress = [
                'total' => $totalRows,
                'current' => $totalRows,
                'success' => $this->successCount,
                'error' => $this->errorCount,
                'percent' => 100,
                'status' => 'completed',
                'message' => "导入完成！成功导入：{$this->successCount} 条记录，失败：{$this->errorCount} 条",
                'errorMessages' => $errorMessages,
                'elapsed' => round(microtime(true) - $startTime, 2)
            ];
            cache($cacheKey, $finalProgress, 3600);

            // 记录导入历史
            if ($this->successCount > 0) {
                $this->recordImportedFile($fileMd5, $filePath, $originalName);
            }

            return [
                'success' => true,
                'message' => "导入完成，成功：{$this->successCount}条，失败：{$this->errorCount}条",
                'count' => $this->successCount,
                'total' => $totalRows,
                'errors' => $this->errorCount,
                'errorMessages' => $errorMessages,
                'status' => 'completed'
            ];
        } catch (\Exception $e) {
            if (isset($cacheKey)) {
                $errorMessage = $e->getMessage();
                cache($cacheKey, [
                    'total' => $totalRows ?? 0,
                    'current' => $currentRow ?? 0,
                    'success' => $this->successCount ?? 0,
                    'error' => ($this->errorCount ?? 0) + 1,
                    'percent' => 100,
                    'status' => 'error',
                    'errorMessages' => [$errorMessage],
                    'message' => "导入失败：" . $errorMessage
                ], 3600);
            }
            throw $e;
        }
    }

    // 获取表头映射
    private function getHeaderMap($worksheet, $highestColumnIndex) {
        $excelColumnMap = [];
        for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $cell = $worksheet->getCell($col . '1');
            $value = $cell->getValue();
    
            if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                $value = $value->getPlainText();
            }
    
            $value = $this->standardizeString($value);
    
            if (!empty($value)) {
                $excelColumnMap[$col] = $value;
                Log::info("读取Excel列", [
                    '列号' => $col,
                    '原始值' => var_export($cell->getValue(), true),
                    '数据类型' => gettype($cell->getValue()),
                    '单元格类型' => $cell->getDataType(),
                    '格式化值' => var_export($cell->getFormattedValue(), true),
                    '最大列' => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($highestColumnIndex)
                ]);
                Log::info("处理后的值", [
                    '列号' => $col,
                    '标准化后' => var_export($value, true)
                ]);
                Log::info("添加到字段映射", [
                    '列号' => $col,
                    '字段名' => $value
                ]);
            }
        }
        return $excelColumnMap;
    }

    // 检查缺失字段
    private function checkMissingFields($excelColumnMap, $requiredFields) {
        $missingFields = [];
        foreach ($requiredFields as $required) {
            $found = false;
            foreach ($excelColumnMap as $field) {
                if ($this->isFieldMatch($required, $field)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingFields[] = $required;
            }
        }
        return $missingFields;
    }

    // 获取行数据
    private function getRowData($worksheet, $row, $excelColumnMap, $requiredFields) {
        $rowData = [];
        $hasValidData = false;
        foreach ($excelColumnMap as $col => $field) {
            $cell = $worksheet->getCell($col . $row);
            $value = $cell->getValue();
    
            if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                $value = $value->getPlainText();
            }
    
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'auto');
            }
    
            if (!empty($value)) {
                $hasValidData = true;
            }
    
            $value = $this->processFieldValue($field, $value, $cell);
            $rowData[$field] = $value;
        }
    
        if (!$hasValidData) {
            return [];
        }
    
        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                throw new \Exception("{$field}不能为空");
            }
        }
    
        return $rowData;
    }

    // 处理字段值
    private function processFieldValue($field, $value, $cell) {
        switch (true) {
            case in_array($field, [
                '账户余额', '时点存款比昨日', '时点存款比月初', 
                '时点存款比年初', '月日均存款余额', '年日均存款余额',
                '年日均存款比昨日', '年日均存款比月初', '年日均存款比年初'
            ]):
                $value = empty($value) || !is_numeric($value) ? 0 : (float)$value;
                break;
            case in_array($field, ['开户日期', '认定日期', '年日均最新日期']):
                if (!empty($value)) {
                    if ($cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC) {
                        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $value = $dateValue->format('Y-m-d');
                    } else {
                        $timestamp = strtotime($value);
                        if ($timestamp !== false) {
                            $value = date('Y-m-d', $timestamp);
                        }
                    }
                }
                break;
            default:
                $value = strval($value);
        }
        return $value;
    }

    // 批量插入数据
    private function batchInsert(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $fields = array_keys($rows[0]);
        $placeholders = array_fill(0, count($fields), '?');
        $placeholders = '(' . implode(',', $placeholders) . ')';
        $placeholders = implode(',', array_fill(0, count($rows), $placeholders));

        $values = [];
        foreach ($rows as $row) {
            $values = array_merge($values, array_values($row));
        }

        $sql = "REPLACE INTO daily_record (`" . implode('`, `', $fields) . "`) VALUES " . $placeholders;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
    }

    // 标准化字符串
    private function standardizeString($str) {
        if (!is_string($str)) {
            return strval($str);
        }
        $str = str_replace("\xEF\xBB\xBF", '', $str);
        $str = trim($str);
        $str = str_replace(
            ['，', '。', '：', '；', '！', '？', '"', '"', "\u{2018}", "\u{2019}", '【', '】', '（', '）', '、'],
            [',', '.', ':', ';', '!', '?', '"', '"', "'", "'", '[', ']', '(', ')', ','],
            $str
        );
        $str = str_replace('　', ' ', $str);
        return trim($str);
    }

    // 字段匹配
    private function isFieldMatch($required, $actual) {
        $required = $this->standardizeString($required);
        $actual = $this->standardizeString($actual);
        if ($required === $actual) {
            return true;
        }
        $required = str_replace(' ', '', $required);
        $actual = str_replace(' ', '', $actual);
        if ($required === $actual) {
            return true;
        }
        similar_text($required, $actual, $percent);
        if ($percent > 90) {
            return true;
        }
        if (strpos($actual, $required) !== false || strpos($required, $actual) !== false) {
            return true;
        }
        return false;
    }

    // 停止导入
    public function stopImport(): bool
    {
        file_put_contents($this->stopFile, '1');
        return true;
    }

    // 获取导入进度
    public function getImportProgress(string $filePath): array
    {
        $cacheKey = 'excel_import_progress_' . md5($filePath);
        $progress = cache($cacheKey);
        return $progress ?: [
            'total' => 0,
            'current' => 0,
            'success' => 0,
            'error' => 0,
            'percent' => 0,
            'status' => 'error'
        ];
    }

    // 记录导入日志
    private function logImport(string $level, string $message, array $context = []): void
    {
        $context['time'] = date('Y-m-d H:i:s');
        $logMethod = strtolower($level);
        if (in_array($logMethod, ['error', 'warning', 'info'])) {
            Log::$logMethod($message, $context);
        } else {
            Log::info($message, $context);
        }
    }

    // 检查文件是否已导入
    private function isFileImported(string $fileMd5): bool
    {
        $stmt = $this->pdo->prepare("SELECT import_time FROM import_history WHERE file_md5 = ? LIMIT 1");
        $stmt->execute([$fileMd5]);
        $result = $stmt->fetch();

        if ($result) {
            $importTime = strtotime($result['import_time']);
            if (time() - $importTime < 30 * 24 * 3600) {
                return true;
            }
        }
        return false;
    }

    // 记录导入的文件
    private function recordImportedFile(string $fileMd5, string $filePath, string $originalName): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO import_history (file_md5, file_name, file_size, import_time, success_count, error_count) 
             VALUES (?, ?, ?, NOW(), ?, ?)"
        );
        $stmt->execute([
            $fileMd5,
            $originalName,
            filesize($filePath),
            $this->successCount ?? 0,
            $this->errorCount ?? 0
        ]);
    }

    // 记录错误
    private function logError(\Exception $e, $row) {
        $errorMessage = $e->getMessage();
        if (!isset($this->errorSummary[$errorMessage])) {
            $this->errorSummary[$errorMessage] = [
                'count' => 1,
                'firstRow' => $row,
                'lastRow' => $row
            ];
        } else {
            $this->errorSummary[$errorMessage]['count']++;
            $this->errorSummary[$errorMessage]['lastRow'] = $row;
        }
        $this->logImport('ERROR', "第 {$row} 行: " . $errorMessage, ['exception' => $e]);
    }

    // 格式化错误消息
    private function formatErrorMessages() {
        $errorMessages = [];
        foreach ($this->errorSummary as $message => $details) {
            $firstRow = $details['firstRow'];
            $lastRow = $details['lastRow'];
            $rowRange = ($firstRow === $lastRow) ? "第{$firstRow}行" : "第{$firstRow}-{$lastRow}行";
            $errorMessages[] = sprintf("%s (发生 %d 次, %s)", $message, $details['count'], $rowRange);
        }
        return $errorMessages;
    }
}