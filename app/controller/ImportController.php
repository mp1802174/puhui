<?php
namespace app\controller;

use app\BaseController;
use app\model\CustomerInfo;
use app\model\DailyBalance;
use think\facade\Db;
use think\facade\Log;

class ImportController extends BaseController
{
    const BATCH_SIZE = 10000;
    
    protected function initialize()
    {
        parent::initialize();
        Log::info('--- 开始初始化 ImportController ---');
        
        // 修改数据库配置
        $config = config('database.connections.mysql');
        $config['params'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        $config['params'][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        
        // 更新配置
        config(['database.connections.mysql' => $config]);
        
        // 检查MySQL服务状态
        try {
            $mysqli = new \mysqli('127.0.0.1', 'root', '760516', 'phkq', 3306);
            Log::info('直接MySQL连接测试：' . ($mysqli->connect_errno ? '失败' : '成功'));
            if ($mysqli->connect_errno) {
                Log::error('MySQL连接错误: ' . $mysqli->connect_error);
            }
            $mysqli->close();
        } catch (\Exception $e) {
            Log::error('MySQL连接测试异常：' . $e->getMessage());
        }
        
        // 检查ThinkPHP数据库配置
        try {
            $dbConfig = config('database.connections.mysql');
            Log::info('数据库配置：' . json_encode($dbConfig));
            
            // 检查数据库连接池状态
            Log::info('Db类是否可用：' . (class_exists('think\facade\Db') ? '是' : '否'));
            
            // 尝试简单查询测试连接
            try {
                $result = Db::query('SELECT 1');
                Log::info('ThinkPHP Db查询测试：成功');
            } catch (\Exception $e) {
                Log::error('ThinkPHP Db查询测试失败：' . $e->getMessage());
            }
            
            // 尝试获取PDO连接
            Log::info('尝试获取PDO连接');
            try {
                $pdo = Db::getPdo();
                Log::info('PDO连接对象：' . ($pdo ? '获取成功' : '获取失败'));
                if ($pdo) {
                    $result = $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    Log::info('PDO缓冲查询设置结果: ' . ($result ? '成功' : '失败'));
                } else {
                    Log::error('PDO对象获取失败');
                    // 检查ThinkPHP数据库连接池状态
                    Log::error('数据库连接池状态：' . json_encode(Db::getConfig()));
                }
            } catch (\PDOException $e) {
                Log::error('PDO异常：' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('数据库连接失败：' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function importData()
    {
        Log::info('=== 开始导入数据 ===');
        $startTime = microtime(true);

        try {
            // 获取PDO连接
            $pdo = Db::getPdo();
            if (!$pdo) {
                throw new \Exception('无法获取数据库连接');
            }
            
            // 获取总记录数
            $stmt = $pdo->query("SELECT COUNT(*) FROM daily_record");
            $total = $stmt->fetchColumn();
            Log::info("原始表daily_record总记录数: {$total}");
            $stmt->closeCursor();  // 确保关闭结果集
            
            $pages = ceil($total / self::BATCH_SIZE);
            Log::info("总记录数: {$total}, 总批次: {$pages}");
            
            // 记录初始状态
            $initialCustomerCount = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            Log::info("导入前customer_info表记录数: {$initialCustomerCount}");
            
            for ($page = 0; $page < $pages; $page++) {
                Log::info("=== 开始处理第 " . ($page + 1) . " 批次数据 ===");
                
                $pdo->beginTransaction();
                Log::info('事务开始');
                
                try {
                    // 获取批次数据
                    $offset = $page * self::BATCH_SIZE;
                    $stmt = $pdo->prepare("SELECT * FROM daily_record LIMIT :offset, :limit");
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                    $stmt->bindValue(':limit', self::BATCH_SIZE, \PDO::PARAM_INT);
                    $stmt->execute();
                    $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    Log::info("批次{$page} - 获取记录数: " . count($records));
                    $stmt->closeCursor();

                    // 记录每批次的处理详情
                    $batchUpdateCount = 0;
                    $batchInsertCount = 0;
                    
                    foreach ($records as $index => $record) {
                        try {
                            // 检查客户是否存在
                            $stmt = $pdo->prepare("SELECT id FROM customer_info WHERE 客户编号 = ? AND 对公客户账号 = ? AND 账户性质 = ?");
                            $stmt->execute([$record['客户编号'], $record['对公客户账号'], $record['账户性质']]);
                            $existingId = $stmt->fetchColumn();
                            $stmt->closeCursor();  // 确保关闭结果集
                            
                          
                            
                            // 构建客户数据
                            $customerData = $this->buildCustomerInfoData($record);
                            $fields = array_keys($customerData);
                            
                            if ($existingId) {
                                // 如果存在，使用UPDATE
                                $updates = array_map(function($field) {
                                    return "`{$field}` = ?";
                                }, $fields);
                                $sql = "UPDATE customer_info SET " . implode(',', $updates) . 
                                      " WHERE 客户编号 = ? AND 对公客户账号 = ?";
                                $stmt = $pdo->prepare($sql);
                                $values = array_values($customerData);
                                $values[] = $record['客户编号'];
                                $values[] = $record['对公客户账号'];
                                $stmt->execute($values);
                                $batchUpdateCount++;
                            } else {
                                // 如果不存在，使用INSERT
                                $placeholders = array_fill(0, count($fields), '?');
                                $sql = "INSERT INTO customer_info (" . implode(',', $fields) . 
                                      ") VALUES (" . implode(',', $placeholders) . ")";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute(array_values($customerData));
                                $batchInsertCount++;
                            }
                            $stmt->closeCursor();
                            
                            // 获取客户ID（无论是更新还是插入）
                            $stmt = $pdo->prepare("SELECT id FROM customer_info WHERE 客户编号 = ? AND 对公客户账号 = ?");
                            $stmt->execute([$record['客户编号'], $record['对公客户账号']]);
                            $customerId = $stmt->fetchColumn();
                            $stmt->closeCursor();
                            
                            if ($customerId) {
                                // 构建并插入余额信息
                                $balanceData = $this->buildDailyBalanceData($record, $customerId);
                                $fields = array_keys($balanceData);
                                $placeholders = array_fill(0, count($fields), '?');
                                
                                $sql = "INSERT INTO daily_balance (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute(array_values($balanceData));
                                $stmt->closeCursor();
                            }
                        } catch (\Exception $e) {
                            Log::error("处理记录失败: " . $e->getMessage());
                            throw $e;
                        }
                    }
                    
                    // 记录每批次的处理结果
                    Log::info("批次{$page}处理详情:\n" .
                        "- 批次大小:" . count($records) . "\n" .
                        "- 更新记录:{$batchUpdateCount}\n" .
                        "- 插入记录:{$batchInsertCount}\n" .
                        "- 实际customer_info表总数:" . $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn());
                    
                    $pdo->commit();
                    Log::info("批次{$page} - 事务提交成功");
                    
                    // 在每批次结束时添加统计
                    $customerCount = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
                    $balanceCount = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
                    Log::info("批次{$page}完成 - customer_info:{$customerCount}, daily_balance:{$balanceCount}");
                    
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    Log::error("批次{$page} - 事务回滚, 原因: " . $e->getMessage());
                    throw $e;
                }
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $message = "数据导入完成:\n- 总耗时: {$duration} 秒\n- 总批次: {$pages} 批";
            Log::info($message);
            
            // 最终统计
            $finalCustomerCount = $pdo->query("SELECT COUNT(*) FROM customer_info")->fetchColumn();
            $finalBalanceCount = $pdo->query("SELECT COUNT(*) FROM daily_balance")->fetchColumn();
            Log::info("导入完成最终统计:\n- 原始记录:{$total}\n- customer_info:{$finalCustomerCount}\n- daily_balance:{$finalBalanceCount}");
            
            // 记录最终的详细统计
            $distinctRecords = $pdo->query("SELECT COUNT(DISTINCT CONCAT(客户编号, 对公客户账号, 账户性质)) as count FROM customer_info")->fetchColumn();
            
            Log::info("导入完成详细统计:\n" .
                "- 原始记录总数:{$total}\n" .
                "- customer_info总数:{$finalCustomerCount}\n" .
                "- 不同组合记录数:{$distinctRecords}\n" .
                "- daily_balance总数:{$finalBalanceCount}");
            
            return $message;

        } catch (\Exception $e) {
            Log::error("导入失败: " . $e->getMessage());
            return '数据导入失败: ' . $e->getMessage();
        }
    }

    private function buildCustomerInfoData($record)
    {
        return [
            '开户日期' => $record['开户日期'] ?? '',
            '客户编号' => $record['客户编号'] ?? '',
            '对公客户账号' => $record['对公客户账号'] ?? '',
            '客户名称' => $record['客户名称'] ?? '',
            '核算机构' => $record['核算机构'] ?? '',
            '客户责任部门' => $record['客户责任部门'] ?? '',
            '账户性质' => $record['账户性质'] ?? '',
            '核算机构编号' => $record['核算机构编号'] ?? '',
            '经办人员工编号' => $record['经办人员工编号'] ?? '',
            '业务标识号' => $record['业务标识号'] ?? '',
            '营销人一' => $record['营销人一'] ?? '',
            '营销人二' => $record['营销人二'] ?? '',
            '营销人三' => $record['营销人三'] ?? '',
            '营销人四' => $record['营销人四'] ?? '',
            '营销人五' => $record['营销人五'] ?? '',
            '营销人六' => $record['营销人六'] ?? '',
            '营销人七' => $record['营销人七'] ?? '',
            '营销人八' => $record['营销人八'] ?? '',
            '营销人九' => $record['营销人九'] ?? '',
            '营销人一十' => $record['营销人一十'] ?? '',
            '营销人一十一' => $record['营销人一十一'] ?? '',
            '营销人一十二' => $record['营销人一十二'] ?? '',
            '营销人名称一' => $record['营销人名称一'] ?? '',
            '营销人名称二' => $record['营销人名称二'] ?? '',
            '营销人名称三' => $record['营销人名称三'] ?? '',
            '营销人名称四' => $record['营销人名称四'] ?? '',
            '营销人名称五' => $record['营销人名称五'] ?? '',
            '营销人名称六' => $record['营销人名称六'] ?? '',
            '营销人名称七' => $record['营销人名称七'] ?? '',
            '营销人名称八' => $record['营销人名称八'] ?? '',
            '营销人名称九' => $record['营销人名称九'] ?? '',
            '营销人名称一十' => $record['营销人名称一十'] ?? '',
            '营销人名称一十一' => $record['营销人名称一十一'] ?? '',
            '营销人名称一十二' => $record['营销人名称一十二'] ?? ''
        ];
    }

    private function buildDailyBalanceData($record, $customerId)
    {
        return [
            'customer_id' => $customerId,
            '日期' => isset($record['日期']) ? $record['日期'] : date('Y-m-d'),
            '账户余额' => $record['账户余额'] ?? 0,
            '时点存款比昨日' => $record['时点存款比昨日'] ?? 0,
            '时点存款比月初' => $record['时点存款比月初'] ?? 0,
            '时点存款比年初' => $record['时点存款比年初'] ?? 0,
            '月日均存款余额' => $record['月日均存款余额'] ?? 0,
            '年日均最新日期' => $record['年日均最新日期'] ?? '',
            '年日均存款余额' => $record['年日均存款余额'] ?? 0,
            '年日均存款比昨日' => $record['年日均存款比昨日'] ?? 0,
            '年日均存款比月初' => $record['年日均存款比月初'] ?? 0,
            '年日均存款比年初' => $record['年日均存款比年初'] ?? 0,
            '认定状态' => $record['认定状态'] ?? '',
            '认定日期' => $record['认定日期'] ?? ''
        ];
    }

    private function setPdoAttributes()
    {
        try {
            $pdo = Db::getPdo();
            if ($pdo instanceof \PDO) {
                $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            } else {
                Log::error('获取PDO对象失败，无法设置属性');
                // 记录更多的调试信息
                Log::error('数据库连接配置: ' . json_encode(config('database.connections.mysql')));
            }
        } catch (\Exception $e) {
            Log::error('数据库连接失败：' . $e->getMessage());
        }
    }
}