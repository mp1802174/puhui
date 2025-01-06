<?php
namespace app\model;

use think\Model;

class CustomerInfo extends Model
{
    protected $name = 'customer_info';
    protected $pk = 'id';
    public $autoWriteTimestamp = false;
}