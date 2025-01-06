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
            
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            
            // 设置默认输入编码
            if (function_exists('mb_internal_encoding')) {
                mb_internal_encoding('UTF-8');
            }
            
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            $totalRows = $highestRow - 1;

            // 获取Excel的列映射
            $excelColumnMap = [];

            // 读取第一行内容
            for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $cell = $worksheet->getCell($col . '1');
                $value = $cell->getValue();

                Log::info("读取Excel列", [
                    '列号' => $col,
                    '原始值' => var_export($value, true),
                    '数据类型' => gettype($value),
                    '单元格类型' => $cell->getDataType(),
                    '格式化值' => var_export($cell->getFormattedValue(), true),
                    '最大列' => $highestColumn
                ]);

                if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $value = $value->getPlainText();
                }

                $value = $this->standardizeString($value);

                Log::info("处理后的值", [
                    '列号' => $col,
                    '标准化后' => var_export($value, true)
                ]);

                if (!empty($value)) {
                    $excelColumnMap[$col] = $value;
                    Log::info("添加到字段映射", [
                        '列号' => $col,
                        '字段名' => $value
                    ]);
                }
            }

            // 检查必要字段
            $requiredFields = ['客户编号', '客户名称', '对公客户账号'];
            $missingFields = [];
            $fieldMatches = [];
            
            foreach ($requiredFields as $required) {
                $found = false;
                foreach ($excelColumnMap as $col => $field) {
                    if ($this->isFieldMatch($required, $field)) {
                        $found = true;
                        Log::info("字段匹配成功", [
                            '需要字段' => $required,
                            'Excel字段' => $field,
                            '列号' => $col
                        ]);
                        break;
                    }
                }
                if (!$found) {
                    $missingFields[] = $required;
                    Log::info("字段未找到", ['字段名' => $required]);
                }
            }
            
            // 记录字段匹配结果
            Log::info("字段匹配结果", [
                '匹配详情' => json_encode($fieldMatches, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                '缺失字段' => $missingFields
            ]);

            if (!empty($missingFields)) {
                throw new \Exception("Excel中缺少必要字段：" . implode(', ', $missingFields));
            }

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

            $this->successCount = 0;
            $this->errorCount = 0;
            $batch = [];
            $currentRow = 0;
            
            $this->errorSummary = [];
            
            // 从第2行开始读取数据
            for ($row = 2; $row <= $highestRow; $row++) {
                if (file_exists($this->stopFile)) {
                    unlink($this->stopFile);
                    cache($cacheKey, null);
                    throw new \Exception("导入已手动停止");
                }

                try {
                    $rowData = [];
                    $hasValidData = false;

                    // 使用Excel的实际字段名进行映射
                    foreach ($excelColumnMap as $col => $field) {
                        $cell = $worksheet->getCell($col . $row);
                        $value = $cell->getValue();
                        
                        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                            $value = $value->getPlainText();
                        }
                        
                        // 确保字符串值使用UTF-8编码
                        if (is_string($value)) {
                            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
                        }
                        
                        if (!empty($value) && $value !== null) {
                            $hasValidData = true;
                        }
                        
                        // 记录每行的关键字段值
                        if (in_array($field, $requiredFields)) {
                            Log::info("关键字段数据", [
                                '行号' => $row,
                                '字段名' => $field,
                                '值' => $value,
                                '数据类型' => gettype($value)
                            ]);
                        }
                        
                        // 根据字段类型处理数据
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
                        
                        $rowData[$field] = $value;
                    }

                    if (!$hasValidData) {
                        Log::info("跳过空行", ['行号' => $row]);
                        continue;
                    }

                    // 验证必填字段
                    foreach ($requiredFields as $field) {
                        if (empty($rowData[$field])) {
                            throw new \Exception("{$field}不能为空");
                        }
                    }

                    if (!empty($rowData['客户编号'])) {
                        $batch[] = $rowData;
                        $currentRow = $row - 1;
                        
                        if (count($batch) >= $this->batchSize) {
                            try {
                                Log::info("开始批量插入", ['批次大小' => count($batch)]);
                                $this->batchInsert($batch);
                                $this->successCount += count($batch);
                                Log::info("批量插入成功", ['成功数量' => count($batch)]);
                            } catch (\Exception $e) {
                                $this->errorCount += count($batch);
                                $this->errorMessages[] = "批量导入失败: " . $e->getMessage();
                                Log::error("批量插入失败", ['错误信息' => $e->getMessage()]);
                            }
                            $batch = [];

                            $progress = [
                                'total' => $totalRows,
                                'current' => $currentRow,
                                'success' => $this->successCount,
                                'error' => $this->errorCount,
                                'percent' => round(($currentRow / $totalRows) * 100, 2),
                                'status' => 'processing',
                                'errorMessages' => $this->errorMessages
                            ];
                            
                            cache($cacheKey, $progress, 3600);
                        }
                    }
                    
                } catch (\Exception $e) {
                    $this->errorCount++;
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
            }

            // 处理最后一批数据
            if (!empty($batch)) {
                try {
                    Log::info("处理最后一批数据", ['批次大小' => count($batch)]);
                    $this->batchInsert($batch);
                    $this->successCount += count($batch);
                    Log::info("最后一批数据处理成功", ['成功数量' => count($batch)]);
                } catch (\Exception $e) {
                    $this->errorCount += count($batch);
                    $this->errorMessages[] = "最后批次导入失败: " . $e->getMessage();
                    Log::error("最后一批数据处理失败", ['错误信息' => $e->getMessage()]);
                }
            }

            $errorMessages = [];
            foreach ($this->errorSummary as $message => $details) {
                $firstRow = $details['firstRow'];
                $lastRow = $details['lastRow'];
                
                if ($firstRow === $lastRow) {
                    $rowRange = "第{$firstRow}行";
                } else {
                    $rowRange = "第{$firstRow}-{$lastRow}行";
                }
                
                $errorMessages[] = sprintf(
                    "%s (发生 %d 次, %s)", 
                    $message, 
                    $details['count'],
                    $rowRange
                );
            }

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

            // 在导入成功后记录导入历史
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

    private function batchInsert(array $rows): void
    {
        if (empty($rows)) {
            Log::info("批次为空，跳过插入");
            return;
        }

        try {
            // 记录要插入的字段和数据
            Log::info("准备插入数据", [
                '批次大小' => count($rows),
                '字段列表' => array_keys($rows[0]),
                '第一行数据' => $rows[0]
            ]);

            $fields = array_keys($rows[0]);
            $placeholders = [];
            $values = [];

            foreach ($rows as $row) {
                $rowPlaceholders = array_fill(0, count($fields), '?');
                $placeholders[] = '(' . implode(',', $rowPlaceholders) . ')';
                $values = array_merge($values, array_values($row));
            }

            $sql = "REPLACE INTO daily_record (`" . implode('`, `', $fields) . "`) VALUES " . 
                   implode(', ', $placeholders);

            // 记录完整的SQL信息
            Log::info("执行SQL", [
                'SQL' => $sql,
                '参数数量' => count($values),
                '字段数量' => count($fields),
                '数据行数' => count($rows)
            ]);

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($values);

            // 记录执行结果
            Log::info("SQL执行结果", [
                '执行状态' => $result ? '成功' : '失败',
                '影响行数' => $stmt->rowCount(),
                'lastInsertId' => $this->pdo->lastInsertId()
            ]);

            if (!$result) {
                throw new \Exception("SQL执行失败");
            }

        } catch (\PDOException $e) {
            Log::error("数据插入失败", [
                '错误代码' => $e->getCode(),
                '错误信息' => $e->getMessage(),
                'SQL状态' => $e->errorInfo[0] ?? null,
                'Driver错误码' => $e->errorInfo[1] ?? null,
                'Driver错误信息' => $e->errorInfo[2] ?? null,
                '第一行数据' => array_combine($fields, array_slice($values, 0, count($fields)))
            ]);
            throw $e;
        }
    }

    /**
     * 标准化字符串，处理全角半角转换
     */
    private function standardizeString($str) {
        if (!is_string($str)) {
            return strval($str);
        }
        
        // 移除BOM
        $str = str_replace("\xEF\xBB\xBF", '', $str);
        
        // 移除多余空格
        $str = trim($str);
        
        // 转换中文标点为英文标点
        $str = str_replace(
            ['，', '。', '：', '；', '！', '？', '"', '"', "\u{2018}", "\u{2019}", '【', '】', '（', '）', '、'],
            [',', '.', ':', ';', '!', '?', '"', '"', "'", "'", '[', ']', '(', ')', ','],
            $str
        );
        
        // 转换全角空格为半角空格
        $str = str_replace('　', ' ', $str);
        
        // 移除多余空格
        return trim($str);
    }

    private function isFieldMatch($required, $actual) {
        // 标准化两个字符串
        $required = $this->standardizeString($required);
        $actual = $this->standardizeString($actual);
        
        // 完全匹配
        if ($required === $actual) {
            return true;
        }
        
        // 计算相似度
        similar_text($required, $actual, $percent);
        if ($percent > 95) {
            return true;
        }
        
        // 移除所有空格后比较
        $required = str_replace(' ', '', $required);
        $actual = str_replace(' ', '', $actual);
        if ($required === $actual) {
            return true;
        }
        
        return false;
    }

    public function stopImport(): bool
    {
        file_put_contents($this->stopFile, '1');
        return true;
    }

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

    private function logImport(string $level, string $message, array $context = []): void
    {
        $context['time'] = date('Y-m-d H:i:s');
        
        switch (strtoupper($level)) {
            case 'ERROR':
                Log::error($message, $context);
                break;
            case 'WARNING':
                Log::warning($message, $context);
                break;
            default:
                Log::info($message, $context);
        }
    }

    private function isFileImported(string $fileMd5): bool
    {
        try {
            Log::info("检查文件是否已导入", ['md5' => $fileMd5]);
            $stmt = $this->pdo->prepare("SELECT import_time FROM import_history WHERE file_md5 = ? LIMIT 1");
            $stmt->execute([$fileMd5]);
            $result = $stmt->fetch();
            
            Log::info("查询结果", ['result' => json_encode($result)]);
            
            if ($result) {
                $importTime = strtotime($result['import_time']);
                if (time() - $importTime < 30 * 24 * 3600) {
                    Log::info("文件在30天内已导入", ['import_time' => $result['import_time']]);
                    return true;
                }
            }
            return false;
        } catch (\PDOException $e) {
            Log::error("检查文件导入历史失败", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function recordImportedFile(string $fileMd5, string $filePath, string $originalName): void
    {
        try {
            Log::info("记录文件导入历史", [
                'md5' => $fileMd5,
                'file' => $originalName,
                'success_count' => $this->successCount,
                'error_count' => $this->errorCount
            ]);
            
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
            
            Log::info("导入历史记录成功");
        } catch (\PDOException $e) {
            Log::error("记录导入历史失败", ['error' => $e->getMessage()]);
        }
    }
}