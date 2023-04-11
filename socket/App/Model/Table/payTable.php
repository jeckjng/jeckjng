<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/3/18
 * Time: 22:13
 */

namespace App\Model\Table;

use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Utility\Schema\Table;

class payTable extends AbstractModel
{
    protected $tableName = "fx_pay";

    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colTinyInt('status', 1);
        return $table;
    }
}