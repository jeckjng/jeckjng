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

class  kaTable extends AbstractModel
{
    protected $tableName = "fx_ka";

    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('id', 11)->setIsNotNull()->setIsPrimaryKey();
        $table->colInt('lhh',20);
        $table->colInt('token',36);
        return $table;
    }
}