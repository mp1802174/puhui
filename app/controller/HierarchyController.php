<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;
use think\facade\View;
use think\Request;

class HierarchyController
{
    public function index(Request $request)
    {
        $level = $request->get('level', 'city');
        $parentId = $request->get('parent_id', null);
        $parentName = $request->get('parent_name', null);
        $accountingId = $request->get('accounting_id', null);

        // 调试输出
        dump('Level: ' . $level);
        
        switch ($level) {
            case 'city':
                $data = $this->getCityData();
                break;
            case 'branch':
                $data = $this->getBranchData($parentId);
                break;
            case 'accounting':
                $data = $this->getAccountingData($parentId);
                break;
            case 'employee':
                $data = $this->getEmployeeData($parentId);
                break;
            case 'company':
                $data = $this->getCompanyData($parentName, $accountingId);
                break;
            default:
                $data = [];
        }

        // 确保 $data 是数组
        if (!is_array($data)) {
            $data = [];
        }

        // 调试输出
        dump($data);

        return View::fetch('hierarchy/hierarchy', [
            'data' => $data,
            'level' => $level,
            'next_level' => $this->getNextLevel($level)
        ]);
    }

    private function getCityData($date = null)
    {
        // 如果没有指定日期，获取最新日期
        if (!$date) {
            $date = Db::query("SELECT 日期 FROM daily_balance ORDER BY 日期 DESC LIMIT 1")[0]['日期'];
        }

        $sql = "SELECT 
                    jigou.市行机构号 as id, 
                    jigou.市行名称 as name, 
                    SUM(db.账户余额) as total_balance
                FROM daily_balance db
                JOIN customer_info ci ON db.customer_id = ci.ID
                JOIN jigou ON ci.核算机构编号 = jigou.核算机构编号
                WHERE db.日期 = :date
                GROUP BY jigou.市行机构号, jigou.市行名称";

        $data = Db::query($sql, ['date' => $date]);

        return $data;
    }

    private function getBranchData($cityId)
    {
        // 调试输出
        dump('City ID: ' . $cityId);

        $sql = "SELECT 支行机构号 as id, 支行名称 as name, SUM(daily_balance.账户余额) as total_balance
                FROM jigou
                INNER JOIN customer_info ON jigou.核算机构编号 = customer_info.核算机构编号
                INNER JOIN daily_balance ON customer_info.ID = daily_balance.customer_id
                WHERE jigou.市行机构号 = :cityId
                GROUP BY 支行机构号, 支行名称";

        $data = Db::query($sql, ['cityId' => $cityId]);

        return $data;
    }

    private function getAccountingData($branchId)
    {
        $sql = "SELECT jigou.核算机构编号 as id, jigou.核算机构 as name, SUM(daily_balance.账户余额) as total_balance
                FROM jigou
                INNER JOIN customer_info ON jigou.核算机构编号 = customer_info.核算机构编号
                INNER JOIN daily_balance ON customer_info.ID = daily_balance.customer_id
                WHERE jigou.支行机构号 = :branchId
                GROUP BY jigou.核算机构编号, jigou.核算机构";

        $data = Db::query($sql, ['branchId' => $branchId]);

        return $data;
    }

    private function getEmployeeData($accountingId)
    {
        $fields = [];
        $fieldNames = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '一十', '一十一', '一十二'];

        foreach ($fieldNames as $index => $chineseNumber) {
            $fieldName = "营销人名称{$chineseNumber}";
            $fields[] = "SUBSTRING_INDEX(SUBSTRING_INDEX({$fieldName}, ':', 1), '-', -1) as name{$index}";
            $fields[] = "SUM(daily_balance.账户余额 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as balance{$index}";
        }

        $groupByFields = [];
        foreach (array_keys($fieldNames) as $index) {
            $groupByFields[] = "name{$index}";
        }

        $sql = "SELECT " . implode(', ', $fields) . "
                FROM customer_info
                INNER JOIN daily_balance ON customer_info.ID = daily_balance.customer_id
                WHERE customer_info.核算机构编号 = :accountingId
                GROUP BY " . implode(', ', $groupByFields);

        $rawData = Db::query($sql, ['accountingId' => $accountingId]);

        // 合并员工数据
        $mergedData = [];
        foreach ($rawData as $row) {
            for ($i = 0; $i < count($fieldNames); $i++) {
                if (!empty($row["name{$i}"])) {
                    $name = $row["name{$i}"];
                    if (!isset($mergedData[$name])) {
                        $mergedData[$name] = [
                            'name' => $name,
                            'balance' => 0
                        ];
                    }
                    $mergedData[$name]['balance'] += $row["balance{$i}"];
                }
            }
        }
        $data = array_values($mergedData);

        // 调试输出
        var_dump($data);

        return $data;
    }

    private function getNextLevel($currentLevel)
    {
        $levels = [
            'city' => 'branch',
            'branch' => 'accounting',
            'accounting' => 'employee',
            'employee' => 'company'
        ];
        return $levels[$currentLevel] ?? '';
    }

    private function getCompanyData($employeeName, $accountingId)
    {
        // 调试输出
        dump('Debug Info:');
        dump('Employee Name: ' . $employeeName);
        dump('Accounting ID: ' . $accountingId);
        dump('Accounting ID Type: ' . gettype($accountingId));

        // 如果没有指定日期，获取最新日期
        $latestDate = Db::query("SELECT 日期 FROM daily_balance ORDER BY 日期 DESC LIMIT 1")[0]['日期'];
        dump('Latest Date: ' . $latestDate);

        // 构建查询
        $sql = "SELECT 
                    c.客户名称 as name,
                    SUM(b.账户余额) as balance
                FROM customer_info c
                JOIN daily_balance b ON c.ID = b.customer_id
                WHERE c.核算机构编号 = :accountingId 
                AND b.日期 = :latestDate
                AND (
                    c.营销人名称一 LIKE :employeeName1 OR
                    c.营销人名称二 LIKE :employeeName2 OR
                    c.营销人名称三 LIKE :employeeName3 OR
                    c.营销人名称四 LIKE :employeeName4 OR
                    c.营销人名称五 LIKE :employeeName5 OR
                    c.营销人名称六 LIKE :employeeName6 OR
                    c.营销人名称七 LIKE :employeeName7 OR
                    c.营销人名称八 LIKE :employeeName8 OR
                    c.营销人名称九 LIKE :employeeName9 OR
                    c.营销人名称一十 LIKE :employeeName10 OR
                    c.营销人名称一十一 LIKE :employeeName11 OR
                    c.营销人名称一十二 LIKE :employeeName12
                )
                GROUP BY c.客户名称";

        // 准备参数
        $params = [
            'accountingId' => $accountingId,
            'latestDate' => $latestDate,
            'employeeName1' => '%' . $employeeName . '%',
            'employeeName2' => '%' . $employeeName . '%',
            'employeeName3' => '%' . $employeeName . '%',
            'employeeName4' => '%' . $employeeName . '%',
            'employeeName5' => '%' . $employeeName . '%',
            'employeeName6' => '%' . $employeeName . '%',
            'employeeName7' => '%' . $employeeName . '%',
            'employeeName8' => '%' . $employeeName . '%',
            'employeeName9' => '%' . $employeeName . '%',
            'employeeName10' => '%' . $employeeName . '%',
            'employeeName11' => '%' . $employeeName . '%',
            'employeeName12' => '%' . $employeeName . '%'
        ];

        // 调试输出SQL
        dump('SQL Query:');
        dump($sql);
        dump('Params:');
        dump($params);

        // 执行查询
        try {
            $result = Db::query($sql, $params);
            dump('Query Result:');
            dump($result);
            return $result;
        } catch (\Exception $e) {
            dump('Error:');
            dump($e->getMessage());
            dump($e->getTraceAsString());
            throw $e;
        }
    }
} 