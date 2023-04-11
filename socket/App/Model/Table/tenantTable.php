<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/3/8
 * Time: 16:14
 */

namespace App\Model\Table;

use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Utility\Schema\Table;

class  tenantTable extends AbstractModel
{
    protected $tableName = "cmf_tenant";
    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id', 20)->setIsNotNull()->setIsPrimaryKey();
        $table->colInt('status',1);
        return $table;
    }


}