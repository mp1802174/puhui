<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;
use think\facade\Db;

class CheckCustomer extends Command
{
    protected function configure()
    {
        $this->setName('check:customer')
            ->setDescription('检查客户基本信息表数据');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            // 直接使用默认连接
            $output->writeln("=== 开始命令行检查客户基本信息表 ===");
            
            // 1. 基本统计
            $count = Db::table('customer_info')->count();
            $output->writeln("总记录数: {$count}");
            
            // 2. ID范围检查
            $idInfo = Db::table('customer_info')
                ->field(['MIN(ID) as min_id', 'MAX(ID) as max_id'])
                ->find();
            $output->writeln("ID范围: {$idInfo['min_id']} - {$idInfo['max_id']}");
            
            // 3. 分段统计
            $segments = [
                '1-10000',
                '10001-20000',
                '20001-30000',
                '30001-40000',
                '40001-50000',
                '50001-60000',
                '60001-70000'
            ];
            
            $output->writeln("\n分段统计:");
            foreach ($segments as $segment) {
                list($start, $end) = explode('-', $segment);
                $segmentCount = Db::table('customer_info')
                    ->whereBetween('ID', [$start, $end])
                    ->count();
                $output->writeln("ID {$segment}: {$segmentCount}条");
            }
            
            // 4. 抽样检查
            $output->writeln("\n随机抽样检查:");
            $samples = Db::table('customer_info')
                ->whereIn('ID', [1, 15000, 30000, 45000, 62636])
                ->order('ID')
                ->select();
            
            foreach ($samples as $sample) {
                $output->writeln("ID {$sample['ID']}:");
                $output->writeln("- 客户编号: {$sample['客户编号']}");
                $output->writeln("- 账号: {$sample['对公客户账号']}");
                $output->writeln("- 性质: {$sample['账户性质']}");
                $output->writeln("");
            }
            
            $output->writeln("=== 检查完成 ===");
            
        } catch (\Exception $e) {
            $output->writeln("<error>检查失败: {$e->getMessage()}</error>");
            return 1;
        }
        
        return 0;
    }
} 