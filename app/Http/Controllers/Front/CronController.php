<?php
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\BaseController;

class CronController extends BaseController
{
    public function index(Request $request)
    {
        $this->getBonBanh();

        $this->getMuaBan();

        $this->getOtoVietName();

        $this->getCarmundi();

        $this->getBanXeHoi();

        $this->getChoXe();

        $this->getXe360();

        $this->getXe5Giay();

        $this->getSanOtoVn();

        $this->getMuaBanOto();

//        $this->getOtoS(); // khong lay dc

        $this->getMuaBanNhanh();

        $this->getRongBay();

        $this->getEnBac();

        $this->getTheGioiXeOto();

        $this->getOtoThien();

        $this->getCafeAuto();
    }
}