<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Log;

class Importday extends BaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function import()
    {
        try {
            $dbConnectionResult = $this->testDbConnection();
            if (!$dbConnectionResult['success']) {
                return json($dbConnectionResult);
            }

            $file = Request::file('csv_file');
            if (!$file) {
                return json(['success' => false, 'message' => '请选择要上传的CSV文件']);
            }

            $filePath = $file->getPathname();
            Log::info("开始导入CSV文件: " . $filePath);

            $handle = fopen($filePath, 'r');
            $headerLine = fgets($handle);
            $csvHeader = $this->parseCsvLine($headerLine);

            $tableFields = $this->getTableFields();
            $validFields = array_intersect($csvHeader, $tableFields);

            $pdo = Db::getPdo();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($validFields), '?'));
            $sql = "INSERT INTO daily_record (" . implode(',', $validFields) . ") VALUES (" . $placeholders . ")";
            $stmt = $pdo->prepare($sql);

            $lineNumber = 1;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $batchSize = 1000;

            while (($data = $this->parseCsvLine(fgets($handle))) !== false) {
                $lineNumber++;
                if (count($data) !== count($csvHeader)) {
                    $errorCount++;
                    continue;
                }

                $rowData = array_combine($csvHeader, $data);
                $validData = array_intersect_key($rowData, array_flip($validFields));

                try {
                    $stmt->execute(array_values($validData));
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "行 {$lineNumber}: " . $e->getMessage();
                }

                if ($lineNumber % $batchSize == 0) {
                    $pdo->commit();
                    $pdo->beginTransaction();
                    return json([
                        'success' => true,
                        'message' => "已处理 {$lineNumber} 行",
                        'progress' => $lineNumber
                    ]);
                }
            }
            $pdo->commit();
            fclose($handle);

            return json([
                'success' => true,
                'message' => "导入完成。成功: {$successCount}, 失败: {$errorCount}。",
                'errors' => array_slice($errors, 0, 10)
            ]);
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Log::error('CSV导入错误: ' . $e->getMessage());
            return json([
                'success' => false,
                'message' => '导入失败: ' . $e->getMessage(),
                'errors' => array_slice($errors, 0, 10)
            ]);
        }
    }

    private function parseCsvLine($line)
    {
        if ($line === false) {
            return false;
        }
        $line = trim($line);
        $fields = [];
        $inQuotes = false;
        $field = '';

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            if ($char == '"' && !$inQuotes) {
                $inQuotes = true;
            } elseif ($char == '"' && $inQuotes) {
                if ($i + 1 < strlen($line) && $line[$i + 1] == '"') {
                    $field .= '"';
                    $i++;
                } else {
                    $inQuotes = false;
                }
            } elseif ($char == ',' && !$inQuotes) {
                $fields[] = $field;
                $field = '';
            } else {
                $field .= $char;
            }
        }
        $fields[] = $field;

        return $fields;
    }

    private function getTableFields()
    {
        return Db::getTableFields('daily_record');
    }

    public function testDbConnection()
    {
        try {
            Db::query("SELECT 1");
            $message = '数据库连接成功';
            Log::info($message);
            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            $message = '数据库连接异常: ' . $e->getMessage();
            Log::error($message);
            Log::error('错误详情: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => $message];
        }
    }
}
