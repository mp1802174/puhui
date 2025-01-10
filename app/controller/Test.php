<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;
use think\facade\Log;

class Test
{
    public function index()
    {
        try {
            Log::info('Starting query execution');
            
            $data = Db::name('jigou')
                ->field('市行机构号, 市行名称, SUM(账户余额) as total_balance')
                ->join('daily_balance', 'jigou.核算机构编号 = daily_balance.customer_id')
                ->group('市行机构号, 市行名称')
                ->select();

            Log::info('Query executed, processing results');

            // 输出结果
            foreach ($data as $row) {
                echo "市行机构号: " . $row['市行机构号'] . " - 市行名称: " . $row['市行名称'] . " - 余额: " . $row['total_balance'] . "<br>";
            }

            Log::info('Results processed successfully');

        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    }
}
