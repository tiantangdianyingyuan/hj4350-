<?php
namespace CityService;

interface CityServiceInterface
{
    // 获取已支持的配送公司列表
    public function getAllImmeDelivery():ResponseInterface;

    // 预下单
    public function preAddOrder(array $data = []):ResponseInterface;

    // 下配送单
    public function addOrder(array $data = []):ResponseInterface;

    // 重新下单
    public function reOrder(array $data = []):ResponseInterface;

    // 增加小费
    public function addTip(array $data = []):ResponseInterface;

    // 预取消配送订单
    public function preCancelOrder(array $data = []):ResponseInterface;

    // 取消配送单
    public function cancelOrder(array $data = []):ResponseInterface;

    // 异常件退回商家确认收货
    public function abnormalConfirm(array $data = []):ResponseInterface;

    // 拉取配送单信息
    public function getOrder(array $data = []):ResponseInterface;

    // 模拟配送公司更新配送订单状态
    public function mockUpdateOrder(array $data = [], array $params = []):ResponseInterface;
}