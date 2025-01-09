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
            default:
                $data = [];
        }

        return View::fetch('hierarchy/hierarchy', ['data' => $data, 'level' => $level]);
    }

    private function getCityData()
    {
        return Db::table('jigou')
            ->field('市行机构号, 市行名称, SUM(账户余额) as total_balance')
            ->join('daily_balance', 'jigou.核算机构编号 = daily_balance.customer_id')
            ->group('市行机构号, 市行名称')
            ->select()
            ->fetchAll();
    }

    private function getBranchData($cityId)
    {
        return Db::table('jigou')
            ->field('支行机构号, 支行名称, SUM(账户余额) as total_balance')
            ->join('daily_balance', 'jigou.核算机构编号 = daily_balance.customer_id')
            ->where('市行机构号', $cityId)
            ->group('支行机构号, 支行名称')
            ->select();
    }

    private function getAccountingData($branchId)
    {
        return Db::table('jigou')
            ->field('核算机构编号, 核算机构, SUM(账户余额) as total_balance')
            ->join('daily_balance', 'jigou.核算机构编号 = daily_balance.customer_id')
            ->where('支行机构号', $branchId)
            ->group('核算机构编号, 核算机构')
            ->select();
    }

    private function getEmployeeData($accountingId)
    {
        return Db::table('customer_info')
            ->field('SUBSTRING_INDEX(SUBSTRING_INDEX(营销人名称一, ":", 1), "-", -1) as employee_name, SUM(账户余额) as total_balance')
            ->join('daily_balance', 'customer_info.ID = daily_balance.customer_id')
            ->where('核算机构编号', $accountingId)
            ->group('employee_name')
            ->select();
    }
} 