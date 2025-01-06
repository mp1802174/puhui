<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Log;

class Import extends BaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function import()
    {
        // 首先测试数据库连接
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

        // 读取标题行
        $headerLine = fgets($handle);
        $csvHeader = $this->parseCsvLine($headerLine);

        Log::info("CSV头部: " . implode(', ', $csvHeader));
        Log::info("CSV头部列数: " . count($csvHeader));

        // 获取数据库表的字段
        $tableFields = $this->getTableFields();
        Log::info("数据库表字段: " . implode(', ', $tableFields));

        // 比较CSV头部和数据库字段
        $validFields = array_intersect($csvHeader, $tableFields);
        $invalidFields = array_diff($csvHeader, $tableFields);

        Log::info("有效字段: " . implode(', ', $validFields));
        Log::info("无效字段: " . implode(', ', $invalidFields));

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            $pdo = Db::getPdo();
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($validFields), '?'));
            $sql = "INSERT INTO hqckmx (" . implode(',', $validFields) . ") VALUES (" . $placeholders . ")";
            $stmt = $pdo->prepare($sql);

            $lineNumber = 1; // 从1开始，因为0是标题行
            while (($data = $this->parseCsvLine(fgets($handle))) !== false) {
                $lineNumber++;
                Log::info("处理第 {$lineNumber} 行，列数: " . count($data));

                if (count($data) !== count($csvHeader)) {
                    $errorCount++;
                    $errorMessage = "行 {$lineNumber}: 列数不匹配。期望 " . count($csvHeader) . " 列，实际 " . count($data) . " 列";
                    $errors[] = $errorMessage;
                    Log::error($errorMessage);
                    continue;
                }

                $rowData = array_combine($csvHeader, $data);
                $validData = array_intersect_key($rowData, array_flip($validFields));

                try {
                    $stmt->execute(array_values($validData));
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMessage = "行 {$lineNumber}: " . $e->getMessage();
                    $errors[] = $errorMessage;
                    Log::error($errorMessage . ', 数据: ' . json_encode($validData));

                    if ($errorCount === 1) {
                        Log::error('第一个错误的详细信息: ' . $e->getTraceAsString());
                    }

                    if ($errorCount > 100) {
                        throw new \Exception("错误数量过多，导入终止");
                    }
                }

                if ($lineNumber % 1000 == 0) {
                    $pdo->commit();
                    $pdo->beginTransaction();
                    Log::info("已处理 {$lineNumber} 行");
                }
            }
            $pdo->commit();
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Log::error('CSV导入错误: ' . $e->getMessage());
            Log::error('错误详情: ' . $e->getTraceAsString());
            fclose($handle);
            return json([
                'success' => false, 
                'message' => '导入失败: ' . $e->getMessage(), 
                'errors' => array_slice($errors, 0, 100),
                'invalidFields' => $invalidFields
            ]);
        }

        fclose($handle);

        Log::info("CSV导入完成。成功: {$successCount}, 失败: {$errorCount}");

        return json([
            'success' => true,
            'message' => "导入完成。成功: {$successCount}, 失败: {$errorCount}。",
            'errors' => array_slice($errors, 0, 10),
            'invalidFields' => $invalidFields
        ]);
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
        return Db::getTableFields('hqckmx');
    }

    public function testDbConnection()
    {
        try {
            // 尝试执行一个简单的查询来测试连接
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

    public function testSingleRow()
    {
        // 首先测试数据库连接
        $dbConnectionResult = $this->testDbConnection();
        if (!$dbConnectionResult['success']) {
            return json($dbConnectionResult);
        }

        $testRow = [
            'id' => '7688900000',
            '开户日期' => '2004/10/30',
            '客户名称' => '市经济开发投资公司',
            '客户编号' => '768890000003844553',
            '核算机构' => '建行淄博分行本级',
            '客户责任部门' => '公司业务部',
            '账户性质' => '委贷基金账户',
            '账户余额' => '11480000',
            '时点存款比昨日' => '0',
            '时点存款比月初' => '0',
            '时点存款比年初' => '0',
            '月日均存款余额' => '11480000',
            '年日均最新日期' => '2024/07/21',
            '年日均存款余额' => '11480000',
            '年日均存款比昨日' => '0',
            '年日均存款比月初' => '0',
            '年日均存款比年初' => '0',
            '经办人员工编号' => 'CN0003700163084105000001715610',
            '认定状态' => '营销人已认定',
            '认定日期' => '2024/04/16',
            '对公客户账号' => '370016308410****0017',
            '业务标识号' => 'Q04wMDAzNzAwMTYzMDg0MTA1MDAwMDAxNzE1NjEw',
            '核算机构编号' => '370630841',
            '营销人一' => '370630009-12977500:100%',
            '营销人二' => '',
            '营销人三' => '',
            '营销人四' => '',
            '营销人五' => '',
            '营销人六' => '',
            '营销人七' => '',
            '营销人八' => '',
            '营销人九' => '',
            '营销人一十' => '',
            '营销人一十一' => '',
            '营销人一十二' => '',
            '营销人名称一' => '',
            '营销人名称二' => '',
            '营销人名称三' => '',
            '营销人名称四' => '建行淄博分行公司业务部-尚爱民:100%',
            '营销人名称五' => '',
            '营销人名称六' => '',
            '营销人名称七' => '',
            '营销人名称八' => '',
            '营销人名称九' => '',
            '营销人名称一十' => '',
            '营销人名称一十一' => '',
            '营销人名称一十二' => ''
        ];

        try {
            $tableFields = $this->getTableFields();
            $validData = array_intersect_key($testRow, array_flip($tableFields));

            $result = Db::name('hqckmx')->insert($validData);
            $message = '测试行插入成功';
            Log::info($message);
            return json(['success' => true, 'message' => $message, 'result' => $result]);
        } catch (\Exception $e) {
            $message = '测试行插入失败: ' . $e->getMessage();
            Log::error($message);
            Log::error('错误详情: ' . $e->getTraceAsString());
            return json(['success' => false, 'message' => $message]);
        }
    }
}
