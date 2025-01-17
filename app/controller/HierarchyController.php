<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;
use think\facade\View;
use think\Request;
use think\facade\Log;

class HierarchyController
{
    public function index(Request $request)
    {
        // 获取查询参数
        $level = $request->get('level', 'city');
        $parentId = $request->get('parent_id', null);
        $parentName = $request->get('parent_name', null);
        $accountingId = $request->get('accounting_id', null);
        
        // 获取可用日期列表
        $availableDates = $this->getAvailableDates();
        
        // 获取当前日期，如果未指定则使用最新日期
        $currentDate = $request->get('date', $availableDates[0] ?? null);
        
        // 定义层级名称映射
        $levelNames = [
            'city' => '市级层级',
            'branch' => '支行层级',
            'accounting' => '核算层级',
            'employee' => '员工层级',
            'company' => '公司层级'
        ];

        // 根据层级获取数据
        switch ($level) {
            case 'city':
                $data = $this->getCityData($currentDate);
                break;
            case 'branch':
                $data = $this->getBranchData($parentId, $currentDate);
                break;
            case 'accounting':
                $data = $this->getAccountingData($parentId, $currentDate);
                break;
            case 'employee':
                $data = $this->getEmployeeData($parentId, $currentDate);
                break;
            case 'company':
                $data = $this->getCompanyData($parentName, $accountingId, $currentDate);
                break;
            default:
                $data = [];
        }

        // 确保 $data 是数组
        if (!is_array($data)) {
            $data = [];
        }

        // 转换金额为万元
        $data = $this->convertToTenThousand($data);

        return View::fetch('hierarchy/hierarchy', [
            'data' => $data,
            'level' => $level,
            'next_level' => $this->getNextLevel($level),
            'level_names' => $levelNames,
            'current_date' => $currentDate,
            'available_dates' => $availableDates
        ]);
    }

    // 获取可用日期列表
    private function getAvailableDates()
    {
        $dates = Db::query("SELECT DISTINCT 日期 FROM daily_balance ORDER BY 日期 DESC");
        // 转换结果为一维数组
        return array_column($dates, '日期');
    }

    // 转换金额为万元
    private function convertToTenThousand($data)
    {
        $fields = ['balance', 'total_balance', 'compare_yesterday', 'compare_month', 
                   'compare_year', 'yearly_avg', 'yearly_avg_yesterday', 
                   'yearly_avg_month', 'yearly_avg_year'];
                   
        foreach ($data as &$item) {
            foreach ($fields as $field) {
                if (isset($item[$field])) {
                    $item[$field] = round($item[$field] / 10000, 2);
                }
            }
        }
        return $data;
    }

    // 修改市级数据查询
    private function getCityData($date)
    {
        $sql = "SELECT 
                    jigou.市行机构号 as id, 
                    jigou.市行名称 as name,
                    SUM(db.账户余额) as balance,
                    SUM(db.时点存款比昨日) as compare_yesterday,
                    SUM(db.时点存款比月初) as compare_month,
                    SUM(db.时点存款比年初) as compare_year,
                    SUM(db.年日均存款余额) as yearly_avg,
                    SUM(db.年日均存款比昨日) as yearly_avg_yesterday,
                    SUM(db.年日均存款比月初) as yearly_avg_month,
                    SUM(db.年日均存款比年初) as yearly_avg_year
                FROM daily_balance db
                JOIN customer_info ci ON db.customer_id = ci.ID
                JOIN jigou ON ci.核算机构编号 = jigou.核算机构编号
                WHERE db.日期 = :date
                GROUP BY jigou.市行机构号, jigou.市行名称";

        return Db::query($sql, ['date' => $date]);
    }

    private function getBranchData($cityId, $date)
    {
        $sql = "SELECT 
                    jigou.支行机构号 as id, 
                    jigou.支行名称 as name,
                    SUM(db.账户余额) as balance,
                    SUM(db.时点存款比昨日) as compare_yesterday,
                    SUM(db.时点存款比月初) as compare_month,
                    SUM(db.时点存款比年初) as compare_year,
                    SUM(db.年日均存款余额) as yearly_avg,
                    SUM(db.年日均存款比昨日) as yearly_avg_yesterday,
                    SUM(db.年日均存款比月初) as yearly_avg_month,
                    SUM(db.年日均存款比年初) as yearly_avg_year
                FROM jigou
                INNER JOIN customer_info ci ON jigou.核算机构编号 = ci.核算机构编号
                INNER JOIN daily_balance db ON ci.ID = db.customer_id
                WHERE jigou.市行机构号 = :cityId
                AND db.日期 = :date
                GROUP BY jigou.支行机构号, jigou.支行名称";

        return Db::query($sql, ['cityId' => $cityId, 'date' => $date]);
    }

    private function getAccountingData($branchId, $date)
    {
        $sql = "SELECT 
                    jigou.核算机构编号 as id, 
                    jigou.核算机构 as name,
                    SUM(db.账户余额) as balance,
                    SUM(db.时点存款比昨日) as compare_yesterday,
                    SUM(db.时点存款比月初) as compare_month,
                    SUM(db.时点存款比年初) as compare_year,
                    SUM(db.年日均存款余额) as yearly_avg,
                    SUM(db.年日均存款比昨日) as yearly_avg_yesterday,
                    SUM(db.年日均存款比月初) as yearly_avg_month,
                    SUM(db.年日均存款比年初) as yearly_avg_year
                FROM jigou
                INNER JOIN customer_info ci ON jigou.核算机构编号 = ci.核算机构编号
                INNER JOIN daily_balance db ON ci.ID = db.customer_id
                WHERE jigou.支行机构号 = :branchId
                AND db.日期 = :date
                GROUP BY jigou.核算机构编号, jigou.核算机构";

        return Db::query($sql, ['branchId' => $branchId, 'date' => $date]);
    }

    private function getEmployeeData($accountingId, $date)
    {
        $fields = [];
        $fieldNames = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '一十', '一十一', '一十二'];
        
        foreach ($fieldNames as $index => $chineseNumber) {
            $fieldName = "营销人名称{$chineseNumber}";
            $fields[] = "SUBSTRING_INDEX(SUBSTRING_INDEX({$fieldName}, ':', 1), '-', -1) as name{$index}";
            $fields[] = "SUM(db.账户余额 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as balance{$index}";
            $fields[] = "SUM(db.时点存款比昨日 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as compare_yesterday{$index}";
            $fields[] = "SUM(db.时点存款比月初 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as compare_month{$index}";
            $fields[] = "SUM(db.时点存款比年初 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as compare_year{$index}";
            $fields[] = "SUM(db.年日均存款余额 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as yearly_avg{$index}";
            $fields[] = "SUM(db.年日均存款比昨日 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as yearly_avg_yesterday{$index}";
            $fields[] = "SUM(db.年日均存款比月初 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as yearly_avg_month{$index}";
            $fields[] = "SUM(db.年日均存款比年初 * (SUBSTRING_INDEX({$fieldName}, ':', -1) / 100)) as yearly_avg_year{$index}";
        }

        $sql = "SELECT " . implode(', ', $fields) . ",
                ci.核算机构编号 as accounting_id
                FROM customer_info ci
                INNER JOIN daily_balance db ON ci.ID = db.customer_id
                WHERE ci.核算机构编号 = :accountingId
                AND db.日期 = :date
                GROUP BY " . implode(', ', array_map(function($i) { return "name{$i}"; }, array_keys($fieldNames))) . 
                ", ci.核算机构编号";

        $rawData = Db::query($sql, ['accountingId' => $accountingId, 'date' => $date]);

        // 合并员工数据时保存核算机构编号
        $mergedData = [];
        foreach ($rawData as $row) {
            for ($i = 0; $i < count($fieldNames); $i++) {
                if (!empty($row["name{$i}"])) {
                    $name = $row["name{$i}"];
                    if (!isset($mergedData[$name])) {
                        $mergedData[$name] = [
                            'name' => $name,
                            'accounting_id' => $row['accounting_id'],
                            'balance' => 0,
                            'compare_yesterday' => 0,
                            'compare_month' => 0,
                            'compare_year' => 0,
                            'yearly_avg' => 0,
                            'yearly_avg_yesterday' => 0,
                            'yearly_avg_month' => 0,
                            'yearly_avg_year' => 0
                        ];
                    }
                    $mergedData[$name]['balance'] += $row["balance{$i}"];
                    $mergedData[$name]['compare_yesterday'] += $row["compare_yesterday{$i}"];
                    $mergedData[$name]['compare_month'] += $row["compare_month{$i}"];
                    $mergedData[$name]['compare_year'] += $row["compare_year{$i}"];
                    $mergedData[$name]['yearly_avg'] += $row["yearly_avg{$i}"];
                    $mergedData[$name]['yearly_avg_yesterday'] += $row["yearly_avg_yesterday{$i}"];
                    $mergedData[$name]['yearly_avg_month'] += $row["yearly_avg_month{$i}"];
                    $mergedData[$name]['yearly_avg_year'] += $row["yearly_avg_year{$i}"];
                }
            }
        }

        return array_values($mergedData);
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

    private function getCompanyData($employeeName, $accountingId, $date)
    {
        $sql = "SELECT 
                    c.客户名称 as name,
                    SUM(b.账户余额) as balance,
                    SUM(b.时点存款比昨日) as compare_yesterday,
                    SUM(b.时点存款比月初) as compare_month,
                    SUM(b.时点存款比年初) as compare_year,
                    SUM(b.年日均存款余额) as yearly_avg,
                    SUM(b.年日均存款比昨日) as yearly_avg_yesterday,
                    SUM(b.年日均存款比月初) as yearly_avg_month,
                    SUM(b.年日均存款比年初) as yearly_avg_year
                FROM customer_info c
                JOIN daily_balance b ON c.ID = b.customer_id
                WHERE c.核算机构编号 = :accountingId 
                AND b.日期 = :date
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
            'date' => $date
        ];
        
        // 添加12个营销人名称的模糊查询参数
        for ($i = 1; $i <= 12; $i++) {
            $params['employeeName' . $i] = '%' . $employeeName . '%';
        }

        try {
            return Db::query($sql, $params);
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('getCompanyData查询失败: ' . $e->getMessage());
            return [];
        }
    }
} 