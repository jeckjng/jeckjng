<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Task
{
    public function getTaskClassification($uid,$client,$type)
    {
        $model = new Model_Task();
        $rs = $model->getTaskClassification($uid,$client,$type);
        return $rs;
    }

    public function getTask($uid,$client,$task_classification_id)
    {
        $model = new Model_Task();
        $rs = $model->getTask($uid,$client,$task_classification_id);
        return $rs;
    }

    public function addUserTask($uid,$task_id, $client)
    {
        $model = new Model_Task();
        $rs = $model->addUserTask($uid,$task_id, $client);
        return $rs;
    }

    public function getUserTask($uid,$type,$p)
    {
        $model = new Model_Task();
        $rs = $model->getUserTask($uid,$type,$p);
        return $rs;
    }

    public function getUserTaskInfo($uid,$user_task_id)
    {
        $model = new Model_Task();
        $rs = $model->getUserTaskInfo($uid,$user_task_id);
        return $rs;
    }

    public function finishUserTask($uid,$user_task_id)
    {
        $model = new Model_Task();
        $rs = $model->finishUserTask($uid,$user_task_id);
        return $rs;
    }

    public function getTaskRewardLog($uid,$type,$p)
    {
        $model = new Model_Task();
        $rs = $model->getTaskRewardLog($uid,$type,$p);
        return $rs;
    }

    public function getTaskRewardLogInfo($uid,$reward_log_id)
    {
        $model = new Model_Task();
        $rs = $model->getTaskRewardLogInfo($uid,$reward_log_id);
        return $rs;
    }

}