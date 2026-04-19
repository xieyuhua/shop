<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\model\Coupon;

class CouponController extends BaseController
{
    public function list()
    {
        $this->adminAuth();
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $keyword = $this->request->get('keyword', '');
        $status = $this->request->get('status', '');

        $query = Coupon::with(['shop']);

        if ($keyword) {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($status !== '') {
            $query = $query->where('is_show', $status);
        }

        $total = $query->count();
        $list = $query->order('create_time', 'desc')->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function detail()
    {
        $this->adminAuth();
        $id = $this->request->get('id');

        $coupon = Coupon::with(['shop'])->find($id);
        if (!$coupon) {
            return $this->error('优惠券不存在');
        }

        return $this->success($coupon);
    }

    public function create()
    {
        $this->adminAuth();
        $data = $this->request->post();

        if (empty($data['name'])) {
            return $this->error('请输入优惠券名称');
        }
        if (empty($data['type'])) {
            return $this->error('请选择优惠券类型');
        }
        if (empty($data['money']) && empty($data['discount'])) {
            return $this->error('请输入优惠金额或折扣');
        }

        $coupon = new Coupon();
        $coupon->shop_id = $data['shop_id'] ?? 0;
        $coupon->name = $data['name'];
        $coupon->type = $data['type'];
        $coupon->money = $data['money'] ?? 0;
        $coupon->discount = $data['discount'] ?? 0;
        $coupon->min_money = $data['min_money'] ?? 0;
        $coupon->max_money = $data['max_money'] ?? 0;
        $coupon->send_type = $data['send_type'] ?? Coupon::SEND_TYPE_PUBLISH;
        $coupon->total_num = $data['total_num'] ?? 0;
        $coupon->send_num = 0;
        $coupon->receive_num = 0;
        $coupon->use_num = 0;
        $coupon->each_limit = $data['each_limit'] ?? 1;
        $coupon->start_time = $data['start_time'] ? strtotime($data['start_time']) : 0;
        $coupon->end_time = $data['end_time'] ? strtotime($data['end_time']) : 0;
        $coupon->valid_days = $data['valid_days'] ?? 0;
        $coupon->category_ids = $data['category_ids'] ?? [];
        $coupon->product_ids = $data['product_ids'] ?? [];
        $coupon->is_show = $data['is_show'] ?? 1;
        $coupon->status = 1;
        $coupon->save();

        return $this->success($coupon);
    }

    public function update()
    {
        $this->adminAuth();
        $data = $this->request->post();

        $coupon = Coupon::find($data['id']);
        if (!$coupon) {
            return $this->error('优惠券不存在');
        }

        $coupon->name = $data['name'] ?? $coupon->name;
        $coupon->money = $data['money'] ?? $coupon->money;
        $coupon->discount = $data['discount'] ?? $coupon->discount;
        $coupon->min_money = $data['min_money'] ?? $coupon->min_money;
        $coupon->max_money = $data['max_money'] ?? $coupon->max_money;
        $coupon->total_num = $data['total_num'] ?? $coupon->total_num;
        $coupon->each_limit = $data['each_limit'] ?? $coupon->each_limit;
        $coupon->start_time = isset($data['start_time']) ? strtotime($data['start_time']) : $coupon->start_time;
        $coupon->end_time = isset($data['end_time']) ? strtotime($data['end_time']) : $coupon->end_time;
        $coupon->valid_days = $data['valid_days'] ?? $coupon->valid_days;
        $coupon->is_show = $data['is_show'] ?? $coupon->is_show;
        $coupon->save();

        return $this->success($coupon);
    }

    public function delete()
    {
        $this->adminAuth();
        $id = $this->request->post('id');

        $coupon = Coupon::find($id);
        if (!$coupon) {
            return $this->error('优惠券不存在');
        }

        if ($coupon->receive_num > 0) {
            return $this->error('该优惠券已有人领取，无法删除');
        }

        $coupon->delete();

        return $this->success();
    }
}
