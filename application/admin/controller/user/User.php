<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use think\Validate;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User;
    }

    protected $avatarField = [
        'avatar_1',
        'avatar_2',
        'avatar_3',
        'avatar_4',
        'avatar_5',
        'avatar_6',
        'avatar_7',
        'avatar_8',
    ];

    protected $imageUrl = "http://shb.blcwg.com/data/avatars/";

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                if (in_array($v->avatar, $this->avatarField)) {
                    $v->avatar = $this->imageUrl . $v->avatar . '.png';
                } else if (!empty($v->avatar)) {
                    $v->avatar = $this->imageUrl . $v->avatar;
                } else {
                    $v->avatar = letter_avatar($v->nickname);
                }
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

    public function checkin()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $username = trim($data['row']['username']);
            $daterange = trim($data['row']['daterange']);

            if (empty($username)) {
                $this->error(__('用户名不能为空'));
            }

            $row = $this->model->where(['username' => $username])->find();
            if (empty($row->id)) {
                $this->error(__('用户名不存在'));
            }

            $dataArr = explode(' - ', $daterange);
            if (!is_array($dataArr)) {
                $this->error(__('请正常选择日期区间'));
            }

            $validate = Validate::make([
                'date' => [
                    'require',
                    'dateFormat' => 'Y-m-d',
                ]
            ], [
                'date.require' => '请填写日期',
                'date.dateFormat' => '日期格式不正确，应为 2025-12-03 格式',
            ]);
            foreach ($dataArr as $dateStr) {
                $result = $validate->check([
                    'date' => $dateStr
                ]);
                if ($result === false) {
                    return $this->error($validate->getError());
                }
            }
            $data = $this->generateDateRange($dataArr[0], $dataArr[1]);

            foreach ($data as $date) {
                // 启动后台脚本
                $command = "php74 /www/wwwroot/shb.blcwg.com/scripts/checkin.form.admin.php {$username} {$date} >> /www/wwwroot/shb.blcwg.com/scripts/xcheckin.log 2>&1 &";
                $output = '';
                $return_var = 0;

                exec($command, $output, $return_var);
                if($return_var === 0) {
                    continue;
                } else {
                    $this->error(__('执行失败'));
                }
            }
            $this->success(__('成功发给系统执行'));
        }

        return $this->view->fetch();
    }

    /**
     * 生成日期区间内的所有日期
     * @param string $startDate 开始日期 Y-m-d
     * @param string $endDate 结束日期 Y-m-d
     * @return array 日期数组
     */
    public function generateDateRange($startDate, $endDate)
    {
        $dates = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    // 老方法-废弃
    public function checkinNO()
    {
//        if ($this->request->isPost()) {
//            $data = $this->request->post();
//            $username = trim($data['row']['username']);
//            $checkintime = trim($data['row']['checkintime']);
//
//            if (empty($username) || empty($checkintime)) {
//                $this->error(__('用户名或补签日期不能为空'));
//            }
//
//            $row = $this->model->where(['username' => $username])->find();
//            if (empty($row->id)) {
//                $this->error(__('用户名不存在'));
//            }
//
//            // 转义参数，防止命令注入
//            $date = escapeshellarg($checkintime);
//
//            // 启动后台脚本
//            $command = "php74 /www/wwwroot/shb.blcwg.com/scripts/checkin.form.admin.php {$username} {$date} >> /www/wwwroot/shb.blcwg.com/scripts/xcheckin.log 2>&1 &";
//            $output = '';
//            $return_var = 0;
//
//            exec($command, $output, $return_var);
//
//            if($return_var === 0) {
//                $this->success(__('执行完成'));
//            } else {
//                $this->error(__('执行失败'));
//            }
//        }
//
//        return $this->view->fetch();
    }
}
