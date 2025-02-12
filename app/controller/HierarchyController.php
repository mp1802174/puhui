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
                $employeeName = $request->get('employee_name', '');
                $data = $this->getEmployeeData($parentId, $currentDate, $employeeName);
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

        // 添加验证数据
        $validation = null;
        if (($level === 'branch' && $parentId) || 
            ($level === 'accounting' && $parentId) ||
            ($level === 'employee' && $parentId)) {
            
            $prefix = $level === 'branch' ? 'city' : 
                     ($level === 'accounting' ? 'branch' : 'accounting');
            
            // 从URL获取上级数据
            $parentData = [
                'balance' => $request->get($prefix . '_balance', 0),
                'compare_yesterday' => $request->get($prefix . '_compare_yesterday', 0),
                'compare_month' => $request->get($prefix . '_compare_month', 0),
                'compare_year' => $request->get($prefix . '_compare_year', 0),
                'yearly_avg' => $request->get($prefix . '_yearly_avg', 0),
                'yearly_avg_yesterday' => $request->get($prefix . '_yearly_avg_yesterday', 0),
                'yearly_avg_month' => $request->get($prefix . '_yearly_avg_month', 0),
                'yearly_avg_year' => $request->get($prefix . '_yearly_avg_year', 0)
            ];
            
            // 计算当前层级合计
            $currentTotal = [
                'balance' => 0,
                'compare_yesterday' => 0,
                'compare_month' => 0,
                'compare_year' => 0,
                'yearly_avg' => 0,
                'yearly_avg_yesterday' => 0,
                'yearly_avg_month' => 0,
                'yearly_avg_year' => 0
            ];
            
            foreach ($data as $item) {
                foreach ($currentTotal as $key => $value) {
                    $currentTotal[$key] += $item[$key];
                }
            }
            
            // 计算所有字段的差额和匹配度
            $validation = [
                'fields' => [
                    '时点余额' => [
                        $prefix => floatval($parentData['balance']),
                        'current' => floatval($currentTotal['balance']),
                        'difference' => floatval($parentData['balance']) - floatval($currentTotal['balance']),
                        'match_percentage' => floatval($parentData['balance']) ? round((floatval($currentTotal['balance']) / floatval($parentData['balance'])) * 100, 2) : 0
                    ],
                    '时点比昨日' => [
                        $prefix => floatval($parentData['compare_yesterday']),
                        'current' => floatval($currentTotal['compare_yesterday']),
                        'difference' => floatval($parentData['compare_yesterday']) - floatval($currentTotal['compare_yesterday']),
                        'match_percentage' => floatval($parentData['compare_yesterday']) ? round((floatval($currentTotal['compare_yesterday']) / floatval($parentData['compare_yesterday'])) * 100, 2) : 0
                    ],
                    '时点比月初' => [
                        $prefix => floatval($parentData['compare_month']),
                        'current' => floatval($currentTotal['compare_month']),
                        'difference' => floatval($parentData['compare_month']) - floatval($currentTotal['compare_month']),
                        'match_percentage' => floatval($parentData['compare_month']) ? round((floatval($currentTotal['compare_month']) / floatval($parentData['compare_month'])) * 100, 2) : 0
                    ],
                    '时点比年初' => [
                        $prefix => floatval($parentData['compare_year']),
                        'current' => floatval($currentTotal['compare_year']),
                        'difference' => floatval($parentData['compare_year']) - floatval($currentTotal['compare_year']),
                        'match_percentage' => floatval($parentData['compare_year']) ? round((floatval($currentTotal['compare_year']) / floatval($parentData['compare_year'])) * 100, 2) : 0
                    ],
                    '日均余额' => [
                        $prefix => floatval($parentData['yearly_avg']),
                        'current' => floatval($currentTotal['yearly_avg']),
                        'difference' => floatval($parentData['yearly_avg']) - floatval($currentTotal['yearly_avg']),
                        'match_percentage' => floatval($parentData['yearly_avg']) ? round((floatval($currentTotal['yearly_avg']) / floatval($parentData['yearly_avg'])) * 100, 2) : 0
                    ],
                    '日均比昨日' => [
                        $prefix => floatval($parentData['yearly_avg_yesterday']),
                        'current' => floatval($currentTotal['yearly_avg_yesterday']),
                        'difference' => floatval($parentData['yearly_avg_yesterday']) - floatval($currentTotal['yearly_avg_yesterday']),
                        'match_percentage' => floatval($parentData['yearly_avg_yesterday']) ? round((floatval($currentTotal['yearly_avg_yesterday']) / floatval($parentData['yearly_avg_yesterday'])) * 100, 2) : 0
                    ],
                    '日均比月初' => [
                        $prefix => floatval($parentData['yearly_avg_month']),
                        'current' => floatval($currentTotal['yearly_avg_month']),
                        'difference' => floatval($parentData['yearly_avg_month']) - floatval($currentTotal['yearly_avg_month']),
                        'match_percentage' => floatval($parentData['yearly_avg_month']) ? round((floatval($currentTotal['yearly_avg_month']) / floatval($parentData['yearly_avg_month'])) * 100, 2) : 0
                    ],
                    '日均比年初' => [
                        $prefix => floatval($parentData['yearly_avg_year']),
                        'current' => floatval($currentTotal['yearly_avg_year']),
                        'difference' => floatval($parentData['yearly_avg_year']) - floatval($currentTotal['yearly_avg_year']),
                        'match_percentage' => floatval($parentData['yearly_avg_year']) ? round((floatval($currentTotal['yearly_avg_year']) / floatval($parentData['yearly_avg_year'])) * 100, 2) : 0
                    ]
                ]
            ];
        }

        

        return View::fetch('hierarchy/hierarchy', [
            'data' => $data,
            'level' => $level,
            'next_level' => $this->getNextLevel($level),
            'level_names' => $levelNames,
            'current_date' => $currentDate,
            'available_dates' => $availableDates,
            'validation' => $validation
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
                GROUP BY jigou.市行机构号, jigou.市行名称
                ORDER BY SUM(db.年日均存款余额) DESC";

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
                GROUP BY jigou.支行机构号, jigou.支行名称
                ORDER BY SUM(db.年日均存款余额) DESC";

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
        AND TRIM(db.日期) = :date
        GROUP BY jigou.核算机构编号, jigou.核算机构
        ORDER BY SUM(db.年日均存款余额) DESC";

        return Db::query($sql, [
            'branchId' => $branchId,
            'date' => trim($date)
        ]);
    }

    public function getEmployeeData($parent_id, $date)
    {
        $sql = <<<SQL
WITH ls AS (
    SELECT 
        b.customer_id,
        i.客户名称,
        b.账户余额,
        b.时点存款比昨日,
        b.时点存款比月初,
        b.时点存款比年初,
        b.月日均存款余额,
        b.年日均存款余额,
        b.年日均存款比昨日,
        b.年日均存款比月初,
        b.年日均存款比年初
    FROM daily_balance b
    JOIN customer_info i ON b.customer_id = i.ID
    WHERE i.核算机构编号 = ? 
      AND b.日期 = ?
),
customer_ratio_check AS (
    SELECT 
        customer_id,
        COALESCE(SUM(marketer_ratio), 0) AS total_ratio,
        COALESCE(MAX(CASE WHEN remark LIKE '%比例异常%' THEN 1 ELSE 0 END), 0) AS has_remark
    FROM customer_marketer
    GROUP BY customer_id
),
employee_data AS (
    SELECT 
        COALESCE(m.marketer_name, '未分配') AS 员工姓名,
        CASE 
            WHEN cr.has_remark = 1 
               OR cr.total_ratio <> 1.00 
               OR m.id IS NULL 
            THEN ls.账户余额
            ELSE ls.账户余额 * m.marketer_ratio 
        END AS 账户余额,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.时点存款比昨日 ELSE ls.时点存款比昨日 * m.marketer_ratio END AS 时点存款比昨日,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.时点存款比月初 ELSE ls.时点存款比月初 * m.marketer_ratio END AS 时点存款比月初,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.时点存款比年初 ELSE ls.时点存款比年初 * m.marketer_ratio END AS 时点存款比年初,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.月日均存款余额 ELSE ls.月日均存款余额 * m.marketer_ratio END AS 月日均存款余额,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.年日均存款余额 ELSE ls.年日均存款余额 * m.marketer_ratio END AS 年日均存款余额,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.年日均存款比昨日 ELSE ls.年日均存款比昨日 * m.marketer_ratio END AS 年日均存款比昨日,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.年日均存款比月初 ELSE ls.年日均存款比月初 * m.marketer_ratio END AS 年日均存款比月初,
        CASE WHEN cr.has_remark = 1 OR cr.total_ratio <> 1.00 OR m.id IS NULL THEN ls.年日均存款比年初 ELSE ls.年日均存款比年初 * m.marketer_ratio END AS 年日均存款比年初
    FROM ls
    LEFT JOIN customer_ratio_check cr 
        ON ls.customer_id = cr.customer_id
    LEFT JOIN customer_marketer m 
        ON ls.customer_id = m.customer_id
        AND cr.total_ratio = 1.00
        AND cr.has_remark = 0
)
SELECT 
    员工姓名 AS employee_name,
    SUM(账户余额) AS balance,
    SUM(时点存款比昨日) AS compare_yesterday,
    SUM(时点存款比月初) AS compare_month,
    SUM(时点存款比年初) AS compare_year,
    SUM(月日均存款余额) AS monthly_avg,
    SUM(年日均存款余额) AS yearly_avg,
    SUM(年日均存款比昨日) AS yearly_avg_yesterday,
    SUM(年日均存款比月初) AS yearly_avg_month,
    SUM(年日均存款比年初) AS yearly_avg_year
FROM employee_data
GROUP BY 员工姓名
ORDER BY SUM(年日均存款余额) DESC;
SQL;

        $result = Db::query($sql, [$parent_id, $date]);

        // 处理无数据情况
        if (empty($result)) {
            return [];
        }

        // 字段类型转换（保持万元单位）
        return array_map(function($item) {
            return [
                'name' => $item['employee_name'],
                'balance' => (float)$item['balance'],
                'compare_yesterday' => (float)$item['compare_yesterday'],
                'compare_month' => (float)$item['compare_month'],
                'compare_year' => (float)$item['compare_year'],
                'monthly_avg' => (float)$item['monthly_avg'],
                'yearly_avg' => (float)$item['yearly_avg'],
                'yearly_avg_yesterday' => (float)$item['yearly_avg_yesterday'],
                'yearly_avg_month' => (float)$item['yearly_avg_month'],
                'yearly_avg_year' => (float)$item['yearly_avg_year']
            ];
        }, $result);
    }

    /**
     * 获取数字映射（包括十一、十二的特殊处理）
     */
    private function getNumberMap($number)
    {
        if ($number <= 10) {
            $numberMap = [
                1 => '一', 2 => '二', 3 => '三', 4 => '四', 5 => '五', 
                6 => '六', 7 => '七', 8 => '八', 9 => '九', 10 => '一十'
            ];
            return $numberMap[$number];
        } else if ($number == 11) {
            return '一十一';
        } else if ($number == 12) {
            return '一十二';
        }
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
        // 构建12个营销人字段的条件
        $conditions = [];
        $params = [
            'accountingId' => $accountingId,
            'date' => $date
        ];

        for ($i = 1; $i <= 12; $i++) {
            $fieldNum = $i == 10 ? '一十' : ($i == 11 ? '一十一' : ($i == 12 ? '一十二' : $this->numberToChinese($i)));
            $field = "营销人名称{$fieldNum}";
            // 修改：检查员工名称匹配且提取相应比例
            $conditions[] = "CASE 
                WHEN {$field} LIKE :pattern{$i} 
                THEN CAST(REPLACE(SUBSTRING_INDEX({$field}, ':', -1), '%', '') AS DECIMAL(10,2))/100 
                ELSE 0 
            END";
            $params["pattern{$i}"] = "%{$employeeName}%";
        }

        // 合并所有营销人的比例条件
        $ratioSum = "(" . implode(" + ", $conditions) . ")";

        $sql = "SELECT 
                    c.客户名称 as name,
                    SUM(b.账户余额 * {$ratioSum}) as balance,
                    SUM(b.时点存款比昨日 * {$ratioSum}) as compare_yesterday,
                    SUM(b.时点存款比月初 * {$ratioSum}) as compare_month,
                    SUM(b.时点存款比年初 * {$ratioSum}) as compare_year,
                    SUM(b.年日均存款余额 * {$ratioSum}) as yearly_avg,
                    SUM(b.年日均存款比昨日 * {$ratioSum}) as yearly_avg_yesterday,
                    SUM(b.年日均存款比月初 * {$ratioSum}) as yearly_avg_month,
                    SUM(b.年日均存款比年初 * {$ratioSum}) as yearly_avg_year
                FROM customer_info c
                JOIN daily_balance b ON c.ID = b.customer_id
                WHERE c.核算机构编号 = :accountingId 
                AND b.日期 = :date
                AND (
                    c.营销人名称一 LIKE :pattern1 OR
                    c.营销人名称二 LIKE :pattern2 OR
                    c.营销人名称三 LIKE :pattern3 OR
                    c.营销人名称四 LIKE :pattern4 OR
                    c.营销人名称五 LIKE :pattern5 OR
                    c.营销人名称六 LIKE :pattern6 OR
                    c.营销人名称七 LIKE :pattern7 OR
                    c.营销人名称八 LIKE :pattern8 OR
                    c.营销人名称九 LIKE :pattern9 OR
                    c.营销人名称一十 LIKE :pattern10 OR
                    c.营销人名称一十一 LIKE :pattern11 OR
                    c.营销人名称一十二 LIKE :pattern12
                )
                GROUP BY c.客户名称
                ORDER BY SUM(b.年日均存款余额 * {$ratioSum}) DESC";

        try {
            return Db::query($sql, $params);
        } catch (\Exception $e) {
            Log::error('getCompanyData查询失败: ' . $e->getMessage());
            return [];
        }
    }

    // 辅助函数：将数字转换为中文数字
    private function numberToChinese($num)
    {
        $chineseNumbers = ['一', '二', '三', '四', '五', '六', '七', '八', '九'];
        return $chineseNumbers[$num - 1];
    }

    
} 