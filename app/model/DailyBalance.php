<?php
namespace app\model;

use think\Model;

class DailyBalance extends Model
{
    protected $name = 'daily_balance';
    protected $pk = 'id';
    public $autoWriteTimestamp = false;
}