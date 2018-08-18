<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:29
 */

namespace App\Http\Controllers;


use App\Logic\RoomSourceLogic;
use App\Model\AreaModel;
use App\Model\BespeakModel;
use App\Model\HouseTypeModel;
use App\Model\RoomCategoryModel;
use App\Model\RoomSourceMarkModel;
use App\Model\RoomSourceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    //列表
    public function index(Request $request) {
        $this->pageData['title'] = "房源列表";
        $this->pageData['keyword'] = $request->get("keyword", '');
        return view('room/list', $this->pageData);
    }

    /**
     * Ajax 获取房源列表
     * @param Request $request
     * @return string
     */
    public function getRoomList(Request $request) {
//        DB::connection()->enableQueryLog(); // 开启查询日志

        $type = $request->get('type', 1);
        $keyword = $request->get('keyword');
        $isRecommend = $request->get('recommend');
        $minPrice = intval($request->get('minPrice'));
        $maxPrice = intval($request->get('maxPrice'));
        $minAcreage = intval($request->get('minAcreage'));
        $maxAcreage = intval($request->get('maxAcreage'));
        $areaId = $request->get('areaId');
        $houseTypeId = $request->get('houseTypeId');
        $categoryId = $request->get('categoryId');
        $page = $request->get('page', 1);

        $where = ['type' => $type, 'isDel' => 0];
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%' . trim($keyword) . '%'];
        }
        //是否推荐
        if (!empty($isRecommend)) {
            $where['isRecommend'] = 1;
        }
        //类别
        if (!empty($categoryId)) {
            $where['roomCategoryId'] = $categoryId;
        }
        //户型
        if (!empty($houseTypeId)) {
            $where['houseTypeId'] = $houseTypeId;
        }
        //地域
        if (!empty($areaId)) {
            $where['areaId'] = $areaId;
        }
        //均价范围
        if (!empty($minPrice) && !empty($maxPrice)) {
            $where[] = ["avgPrice", ">=", $minPrice];
            $where[] = ['avgPrice', '<=', $maxPrice];
        } else if (!empty($minPrice)) {
            $where[] = ["avgPrice", "<=", $minPrice];
        } else if(!empty($maxPrice)) {
            $where[] = ['avgPrice', '>=', $maxPrice];
        }
        //面积范围
        if (!empty($minAcreage) && !empty($maxAcreage)) {
            $where[] = ["acreage", ">=", $minAcreage];
            $where[] = ['acreage', '<=', $maxAcreage];
        } else if (!empty($minAcreage)) {
            $where[] = ["acreage", "<=", $minAcreage];
        } else if(!empty($maxAcreage)) {
            $where[] = ['acreage', '>=', $maxAcreage];
        }

        $pageSize =10;
        $offset = ($page - 1) * $pageSize;
        //取得所有推荐的房源
        $roomSourceModel = new RoomSourceModel();
        $roomList = $roomSourceModel->select(['id', "type", "roomCategoryId", "name", "areaId", "avgPrice", "totalPrice", "imgJson"])
            ->where($where)->offset($offset)->limit($pageSize)->get();

//        dd(DB::getQueryLog());

        $roomList = (new RoomSourceLogic())->formatRoomList($roomList);

        return ResultClientJson(0, '数据获取成功', ['list' => $roomList, 'isEnd' => empty($roomList)]);
    }

    /**
     * 房源详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Request $request) {
        $id = $request->get("id");

        $row = (new RoomSourceModel())->getOne(['*'], ['id' => $id]);
        list($row) = (new RoomSourceLogic())->formatRoomList([$row]);


        //查询是否已经收藏
        $model = new RoomSourceMarkModel();
        $markRow = $model->getOne(['status'], ['userId' => $this->user['id'], 'roomId' => $id]);

        $this->pageData['isMark'] = !empty($markRow->status);
        $this->pageData['row'] = $row;
        $this->pageData['title'] = '详情 - ' . $row->name;
        return view('room/detail', $this->pageData);
    }

    /**
     * 房源收藏
     */
    public function mark(Request $request) {
        $roomId = $request->post("roomId");
        $markStatus = intval($request->post("markStatus"));

        $model = new RoomSourceMarkModel();
        $row = $model->getOne(['id'], ['userId' => $this->user['id'], 'roomId' => $roomId]);
        if (empty($row)) {
            $model->insert([
                'userId' => $this->user['id'],
                'roomId' => $roomId,
                'createTime' => time(),
                'status' => 1
            ]);
        } else {
            $model->updateData(['status' => $markStatus], ['id' => $row->id]);
        }

        return ResultClientJson(0, '操作成功');
    }

    /**
     * 预约看房
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bespeak(Request $request) {
        $roomId = $request->get("roomId");
        $this->pageData['room'] = (new RoomSourceModel())->getOne(['id', 'name'], ['id' => $roomId]);
        $this->pageData['title'] = "预约看房";
        return view("room/bespeak", $this->pageData);
    }

    /**
     * 预约ING
     */
    public function bespeaking(Request $request) {
        $data = $request->all();
        $rule = [
            'roomId' => 'required',
            'name' => 'required',
            'tel' => 'required',
            'num' => 'required',
            'address' => 'required',
            'time' => 'required',
        ];
        $message = [
            'roomId.required' => '房源ID必填',
            'name.required' => '姓名必填',
            'tel.required' => '电话必填',
            'num.required' => '预约人数必填',
            'address.required' => '接送地址必填',
            'time.required' => '接送时间必填',
        ];
        $validate = Validator::make($data, $rule, $message);
        if (!$validate->passes()) {
            return back()->withErrors($validate);
        }

        $model = new BespeakModel();

        //查询此房源当前用户是否预约过
        $count = $model->where("roomId", $data['roomId'])->where('userId', $this->user['id'])->where("tel", $data['tel'])->count();
        if ($count) {
            return ResultClientJson(100, '当前房源您有还未完成的预约,请更换账号或者联系的手机号');
        }

        $newId = $model->insert([
            'userId' => $this->user['id'],
            'adminId' => $this->user['admin_id'],
            'roomId' => $data['roomId'],
            'name' => $data['name'],
            'tel' => $data['tel'],
            'num' => $data['num'],
            'address' => $data['address'],
            'time' => $data['time'],
            'createTime' => time()
        ]);

        if ($newId) {
            //TODO 发送短信消息给对应的手机号

            return ResultClientJson(0, '预约成功');
        }
        return ResultClientJson(100, '预约失败');
    }

    /**
     * 地域列表
     * @return array
     */
    public function getAreaList() {
        return (new AreaModel())->getList(['*'], ['isDel' => 0]);
    }

    /**
     * 户型列表
     * @return array
     */
    public function getHouseTypeList() {
        return (new HouseTypeModel())->getList(['*'], ['isDel' => 0]);
    }

    /**
     * 房源类别列表
     * @return array
     */
    public function getCategoryList() {
        return (new RoomCategoryModel())->getList(['*'], ['isDel' => 0]);
    }

}