<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class DailyRecord extends Model
{
    // 设置表名
    protected $name = 'daily_record';
    
    // 设置主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = false;

    // 字段映射关系
    protected $map = [
        'id' => 'id',
        'open_date' => '开户日期',
        'customer_name' => '客户名称',
        'customer_number' => '客户编号',
        'accounting_org' => '核算机构',
        'customer_dept' => '客户责任部门',
        'account_nature' => '账户性质',
        'account_balance' => '账户余额',
        'deposit_vs_yesterday' => '时点存款比昨日',
        'deposit_vs_month_start' => '时点存款比月初',
        'deposit_vs_year_start' => '时点存款比年初',
        'monthly_avg_deposit' => '月日均存款余额',
        'yearly_avg_latest_date' => '年日均最新日期',
        'yearly_avg_deposit' => '年日均存款余额',
        'yearly_avg_vs_yesterday' => '年日均存款比昨日',
        'yearly_avg_vs_month_start' => '年日均存款比月初',
        'yearly_avg_vs_year_start' => '年日均存款比年初',
        'operator_number' => '经办人员工编号',
        'recognition_status' => '认定状态',
        'recognition_date' => '认定日期',
        'public_account' => '对公客户账号',
        'business_id' => '业务标识号',
        'accounting_org_number' => '核算机构编号'
    ];

    // 字段类型转换
    protected $type = [
        'id' => 'string',
        'open_date' => 'string',
        'customer_name' => 'string',
        'customer_number' => 'string',
        'accounting_org' => 'string',
        'customer_dept' => 'string',
        'account_nature' => 'string',
        'account_balance' => 'float',
        'deposit_vs_yesterday' => 'float',
        'deposit_vs_month_start' => 'float',
        'deposit_vs_year_start' => 'float',
        'monthly_avg_deposit' => 'float',
        'yearly_avg_latest_date' => 'string',
        'yearly_avg_deposit' => 'float',
        'yearly_avg_vs_yesterday' => 'float',
        'yearly_avg_vs_month_start' => 'float',
        'yearly_avg_vs_year_start' => 'float',
        'operator_number' => 'string',
        'recognition_status' => 'string',
        'recognition_date' => 'string',
        'public_account' => 'string',
        'business_id' => 'string',
        'accounting_org_number' => 'string'
    ];

    /**
     * 重写保存前的数据处理
     */
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        
        // 将中文字段名转换为英文字段名
        $data = $this->getData();
        $newData = [];
        foreach ($data as $key => $value) {
            $newKey = array_search($key, $this->map);
            if ($newKey !== false) {
                $newData[$newKey] = $value;
            } else {
                $newData[$key] = $value;
            }
        }
        
        // 更新模型数据
        $this->data = $newData;
    }

    /**
     * 重写保存方法
     */
    public function save(array $data = [], string $sequence = null): bool
    {
        try {
            // 移除所有字段的反引号
            $cleanData = [];
            foreach ($this->getData() as $key => $value) {
                $cleanKey = trim($key, '`');
                $cleanData[$cleanKey] = $value;
            }
            
            // 更新模型数据
            $this->data = $cleanData;
            
            return parent::save($data, $sequence);
        } catch (\Exception $e) {
            \think\facade\Log::error('保存失败', [
                'error' => $e->getMessage(),
                'data' => $this->getData()
            ]);
            throw $e;
        }
    }
}