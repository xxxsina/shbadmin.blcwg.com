<?php

namespace app\admin\model;

use think\Model;

class RedisNameConfigModel extends Model
{
    // 用户问题列表
    public function getCustomerMessagesListKey($userId)
    {
        return sprintf("shb:customer_messages:user:%s", $userId);
    }
    // 用户问题
    public function getCustomerMessagesKey($userId, $msgId)
    {
        return sprintf("shb:customer_messages:userId:%s:msgId:%s", $userId, $msgId);
    }

    public function getArticleById($id)
    {
        return sprintf("shb:article:id:%s", $id);
    }

    public function getArticleList()
    {
        return sprintf("shb:articles:list");
    }
}
