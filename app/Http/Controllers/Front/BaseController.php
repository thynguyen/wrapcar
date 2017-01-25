<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Contents;

class BaseController extends Controller
{
    public function __construct() {
        
    }

    protected function getBonBanh($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'bon_banh')->orderBy('id', 'DESC')->first();

        echo '======BEGIN bonbanh=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.bonbanh');

        $html = HtmlDomParser::file_get_html($url);
        $textTotal = $html->find('div.pagging div.cpage', 0)->innertext();
        unset($html);

        preg_match_all('/<b>([^{]*)<\/b>/s', $textTotal, $matches);

        $totalPage = 1;
        if (isset($matches[1][0])) {
            $number = str_replace(',', '', $matches[1][0]);
            preg_match_all('/\d+/', $number, $matches);
            $total = isset($matches[0][0]) ? $matches[0][0] : 0;
            $totalPage = $this->getTotalPage($total, $limit);
            $totalPage = 80;
        }
        if ($dataOld === NULL) {
            //$page = $totalPage;
        }
        $this->getContentBonBanh($page, $totalPage, $dataOld);

        echo "======END bonbanh=========<br/>";
        flush();
        ob_flush();
    }

    protected function getMuaBan($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'mua_ban')->orderBy('id', 'DESC')->first();

        echo '======BEGIN muaban=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        //$total = 4000;
        $total = 500;
        $totalPage = $this->getTotalPage($total, $limit);
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBan($page, $totalPage, $dataOld);

        echo "======END muaban=========<br/>";
        flush();
        ob_flush();
    }

    protected function getOtoVietName($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'cho_tot')->orderBy('id', 'DESC')->first();

        echo '======BEGIN OTO VIET NAM=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);
        DEFINE('MAX_FILE_SIZE', 6000000);

        $totalPage = 0; // Only one page
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentOtoVietNam($page, $totalPage, $dataOld, 'ul.bdImage_Widget_Grid', 0);
        $this->getContentOtoVietNam($page, $totalPage, $dataOld, 'ul.bdImage_Widget_Grid', 1);

        echo "======END OTO VIET NAM=========<br/>";
        flush();
        ob_flush();
    }

    protected function getCarmundi($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'carmudi')->orderBy('id', 'DESC')->first();

        echo '======BEGIN CARMUDI=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);
        DEFINE('MAX_FILE_SIZE', 6000000);

        $url = \config('wrap.url_site.carmudi') . '/all/?sort=suggested&page=1';

        $html = HtmlDomParser::file_get_html($url);
        $attrs = $html->find('ul.pagination', 0)->attr;
        unset($html);

        //$totalPage = isset($attrs['data-total-pages']) ? $attrs['data-total-pages'] : 0;
        $totalPage = 20;
        if (empty($totalPage)) {
            return;
        }

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentCarmudi($page, $totalPage, $dataOld);

        echo "======END CARMUDI=========<br/>";
        flush();
        ob_flush();
    }

    protected function getBanXeHoi($page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'ban_xe_hoi')->orderBy('id', 'DESC')->first();

        echo '======BEGIN BANXEHOI=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.banxehoi');

        $html = HtmlDomParser::file_get_html($url . 'ban-xe');
        $textTotal = $html->find('.listcar div.textcount span', 0)->plaintext;
        unset($html);

        $totalPage = 1;
        if ($textTotal) {
            $total = str_replace(',', '', $textTotal);
            $totalPage = $this->getTotalPage($total, $limit);
            $totalPage = 30;
        }
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentBanXeHoi($page, $totalPage, $dataOld);

        echo "======END BANXEHOI=========<br/>";
        flush();
        ob_flush();
    }

    protected function getChoXe($page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'cho_xe')->orderBy('id', 'DESC')->first();

        echo '======BEGIN CHOXE=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.choxe');

        $html = HtmlDomParser::file_get_html($url . 'oto/?page=1');
        $totalPage = $html->find('.pagination li', 6)->plaintext;
        $totalPage = 40;
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentChoXe($page, $totalPage, $dataOld);

        echo "======END BANXEHOI=========<br/>";
        flush();
        ob_flush();
    }

    protected function getXe360($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'xe_360')->orderBy('id', 'DESC')->first();

        echo '======BEGIN XE 360=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 65; // default
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentXe360($page, $totalPage, $dataOld);

        echo "======END XE 360=========<br/>";
        flush();
        ob_flush();
    }

    protected function getXe5giay($page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'xe_5giay')->orderBy('id', 'DESC')->first();

        echo '======BEGIN XE5GIAY=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.xe5giay');

        $html = HtmlDomParser::file_get_html($url . 'xe-oto/');
        $attrs = $html->find('.PageNav', 0)->attr;
        $totalPage = isset($attrs['data-last']) ? $attrs['data-last'] : 0;
        $totalPage = 60;
        unset($html);
        if (empty($totalPage)) {
            return;
        }

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentXe5Giay($page, $totalPage, $dataOld);

        echo "======END XE5GIAY=========<br/>";
        flush();
        ob_flush();
    }

    protected function getSanOtoVn($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'san_oto')->orderBy('id', 'DESC')->first();

        echo '======BEGIN SANOTO=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.sanotovn');

        $html = HtmlDomParser::file_get_html($url . 'oto/?page=1');
        $href = @$html->find('.pagination li', 6)->find('a', 0)->href;
        $totalPage = str_replace('/oto?page=', '', $href);
        $totalPage = 100;
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentSanOtoVn($page, $totalPage, $dataOld);

        echo "======END SANOTO=========<br/>";
        flush();
        ob_flush();
    }

    protected function getMuaBanOto($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'muaban_oto')->orderBy('id', 'DESC')->first();

        echo '======BEGIN MUABAN OTO=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 16;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBanOto($page, $totalPage, $dataOld);

        echo "======END MUABAN OTO=========<br/>";
        flush();
        ob_flush();
    }

    protected function getMuaBanNhanh($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'muaban_nhanh')->orderBy('id', 'DESC')->first();

        echo '======BEGIN MUABAN NHANH=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.muabannhanh');

        $html = HtmlDomParser::file_get_html($url . 'mua-ban-o-to?page=1');
        $href = @$html->find('.page-navi ul li', 6)->find('a', 0)->href;
        $totalPage = str_replace('https://muabannhanh.com/mua-ban-o-to?page=', '', $href);
        $totalPage = 100;
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBanNhanh($page, $totalPage, $dataOld);

        echo "======END MUABAN NHANH=========<br/>";
        flush();
        ob_flush();
    }

    protected function getRongBay($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'rong_bay')->orderBy('id', 'DESC')->first();

        echo '======BEGIN RONG BAY=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 319;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentRongBay($page, $totalPage, $dataOld);

        echo "======END RONG BAY=========<br/>";
        flush();
        ob_flush();
    }

    protected function getEnBac($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'en_bac')->orderBy('id', 'DESC')->first();

        echo '======BEGIN ENBAC=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 200;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentEnBac($page, $totalPage, $dataOld);

        echo "======END ENBAC=========<br/>";
        flush();
        ob_flush();
    }

    protected function getTheGioiXeOto($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'thegioixe_oto')->orderBy('id', 'DESC')->first();

        echo '======BEGIN THEGIOIXEOTO=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.thegioixeoto');

        $html = HtmlDomParser::file_get_html($url . 'ban-xe?page=1');
        $textTotal = @$html->find('.vs-page div', 0)->plaintext;
        $textTotal = str_replace('Tổng cộng:', '', $textTotal);

        $totalPage = 1;
        if (!empty($textTotal)) {
            $totalPage = $this->getTotalPage($textTotal, $limit);
            $totalPage = 30;
        }
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentTheGioiXeOto($page, $totalPage, $dataOld);

        echo "======END THEGIOIXEOTO=========<br/>";
        flush();
        ob_flush();
    }

    protected function getOtoThien($page = 1, $limit = 12)
    {
        $dataOld = Contents::where('type', 'oto_thien')->orderBy('id', 'DESC')->first();

        echo '======BEGIN OTOTHIEN=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.otothien');

        $html = HtmlDomParser::file_get_html($url . 'mua-o-to/');
        $totalPage = $html->find('.page-numbers li', 4)->find('.page-numbers', 0)->plaintext;
        unset($html);
        $totalPage = 40;

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentOtoThien($page, $totalPage, $dataOld);

        echo "======END OTOTHIEN=========<br/>";
        flush();
        ob_flush();
    }

    protected function getCafeAuto($page = 1, $limit = 15)
    {
        $dataOld = Contents::where('type', 'cafe_auto')->orderBy('id', 'DESC')->first();

        echo '======BEGIN CAFE AUTO=========<br/>';
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.cafeauto');

        $html = HtmlDomParser::file_get_html($url . 'mua-ban-xe/oto-xe-may-phu-tung/p-1.html');
        $obj = @$html->find('#market .form-group', 0);
        if (is_object($obj)) {
            $totalPage = @$obj->find('div', 1)->find('strong', 1)->plaintext;
        }
        unset($html);
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentCafeAuto($page, $totalPage, $dataOld);

        echo "======END CAFE AUTO=========<br/>";
        flush();
        ob_flush();
    }

    protected function getTotalPage($total, $limit)
    {
        $page = 1;
        if ($total > $limit) {
            if ($total % $limit === 0) {
                $page = floor($total / $limit);
            } else {
                $page = floor($total / $limit) + 1;
            }
        }
        return (int)$page;
    }

    protected function getContentBonBanh($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        if ($page == 0) {
            return;
        }

        if ($page > 1) {
            $url = \config('wrap.url_site.bonbanh') . 'oto/page,'.$page.'/';
        } else {
            $url = \config('wrap.url_site.bonbanh');
        }

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('.g-box-content .car-item');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            foreach ($items as $indexL => $item) {
                $productYear = trim($item->find('div', 0)->innertext());
                $title = trim($item->find('div', 1)->plaintext); // brand car
                $price = trim($item->find('div', 2)->innertext());
                $city = trim($item->find('div', 3)->plaintext);
                $link = trim($item->find('div', 4)->find('a',0)->href);
                $carCode = trim($item->find('div', 4)->find('span.car_code', 0)->plaintext);
                $shortContent = trim($item->find('div', 5)->innertext());
                $contactAndPhone = trim($item->find('div.cb7', 0)->innertext());

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }

                $productYear = $pdo->quote($productYear);
                $title = $pdo->quote($title);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $domain = $pdo->quote(\config('wrap.url_site.bonbanh'));
                $link = $domain . $pdo->quote($link);
                $carCode = $pdo->quote($carCode);
                $shortContent = $pdo->quote($shortContent);
                $contactAndPhone = $pdo->quote($contactAndPhone);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $carCode, $productYear, $title, $price, $contactAndPhone, $city, $shortContent, 'bon_banh', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'code_car_site', 'product_year', 'brand_car', 'price', 'contact', 'city', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'code_car_site=VALUES(code_car_site)', 'product_year=VALUES(product_year)', 'brand_car=VALUES(brand_car)', 
                    'price=VALUES(price)', 'contact=VALUES(contact)', 'city=VALUES(city)', 'short_content=VALUES(short_content)', 
                    'type=VALUES(type)', 'date_post=VALUES(date_post)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);
                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentBonBanh($page, $totalPage, $dataOld);
        }
    }

    protected function getContentMuaBan($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        if ($page == 0) {
            return;
        }

        if ($page > 1) {
            $url = \config('wrap.url_site.muaban') . 'ban-o-to-toan-quoc-l0-c41?cp='.$page;
        } else {
            $url = \config('wrap.url_site.muaban') . 'ban-o-to-toan-quoc-l0-c41';
        }

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('.mbn-box-list .mbn-box-list-content');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            foreach ($items as $indexL => $item) {
                $title = trim($item->find('a', 1)->find('.mbn-title', 0)->plaintext); // brand car
                $link = trim($item->find('a', 1)->href);
                $price = trim($item->find('a', 1)->find('.mbn-price', 0)->plaintext);
                $city = trim($item->find('a', 1)->find('.mbn-address', 0)->plaintext);
                $datePost = trim($item->find('a', 1)->find('.mbn-date', 0)->plaintext);
                $summary = $item->find('a', 1)->find('.mbn-item-summary', 0);
                $shortContent = null;
                if (is_object($summary)) {
                    $shortContent = trim($summary->innertext());
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $domain = $pdo->quote(\config('wrap.url_site.muaban'));

                if (!empty($datePost)) {
                    $date = \DateTime::createFromFormat('d/m/Y', $datePost);
                    $datePost = $date->format('Y-m-d');
                    $datePost = $pdo->quote($datePost);
                } else {
                    $datePost = NULL;
                }
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $city, $shortContent, 'mua_ban', $datePost, \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'city', 'short_content', 'type', 'date_post', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'city=VALUES(city)', 'short_content=VALUES(short_content)', 
                    'type=VALUES(type)', 'date_post=VALUES(date_post)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentMuaBan($page, $totalPage, $dataOld);
        }
    }

    protected function getContentOtoVietNam($page, $totalPage, $dataOld, $class, $position)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.otovietnam');

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find($class, $position)->find('li');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.otovietnam');

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('a', 1)->plaintext); // brand car
                $link = trim(@$item->find('a', 1)->href);
                $price = trim(@$item->find('.threadPrice', 0)->plaintext);
                $atrDatePost = @$item->find('.threadDate abbr', 0)->attr;
                $kmRun = trim(@$item->find('.threadOdo', 0)->plaintext);
                $datePost = isset($atrDatePost['data-time']) ? date('Y-m-d', $atrDatePost['data-time']) : null;

                $urlDetail = $url . $link;
                $details = $this->getDetailOtoVietNam($urlDetail);
                $city = isset($details['city']) ? $details['city'] : null;
                $color = isset($details['color']) ? $details['color'] : null;
                $productYear = isset($details['productYear']) ? $details['productYear'] : null;
                $shortContent = isset($details['shortContent']) ? $details['shortContent'] : null;

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $price = $pdo->quote($price);
                $datePost = $pdo->quote($datePost);
                $kmRun = $pdo->quote($kmRun);
                $city = $pdo->quote($city);
                $color = $pdo->quote($color);
                $productYear = $pdo->quote($productYear);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $datePost, $kmRun, $city, $color, $productYear, $shortContent, 'otovietnam', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'date_post', 'km_run', 'city', 'color', 'product_year', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'date_post=VALUES(date_post)', 
                    'km_run=VALUES(km_run)', 'city=VALUES(city)', 'color=VALUES(color)', 'product_year=VALUES(product_year)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentCarmudi($page, $totalPage, $dataOld);
        }
    }

    protected function getContentCarmudi($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.carmudi') . 'all/?sort=suggested&page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('.catalog-listing-item');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.carmudi');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = trim($item->find('.catalog-listing-description-data a', 0)->plaintext); // brand car
                $link = trim($item->find('.catalog-listing-description-data a', 0)->href);
                $price = trim($item->find('.catalog-listing-description-data .item-price a', 0)->plaintext);
                $city = trim($item->find('.catalog-listing-description-dealer-info .catalog-listing-item-location span', 0)->plaintext);
                $contact = trim($item->find('.catalog-listing-description-dealer-info .catalog-listing-item-agent', 0)->plaintext);
                $shortContent = trim($item->find('.catalog-listing-item-description .description', 0)->innertext());

                // Get Phone number detail
                $attrContact = $item->find('.catalog-listing-description-buttons-right a.contact-link', 0)->attr;
                $sku = isset($attrContact['data-sku']) ? $attrContact['data-sku'] : null;
                $phone = null;
                if (!empty($sku)) {
                    $phone = $this->getDetailCarmudi($domain, $sku);
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $contact = $pdo->quote($contact);
                $shortContent = $pdo->quote($shortContent);
                $phone = !empty($phone) ? $pdo->quote($phone) : null;

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $contact, $phone, $city, $shortContent, 'carmudi', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'city', 'contact', 'phone', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'city=VALUES(city)', 'contact=VALUES(contact)', 
                    'phone=VALUES(phone), short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentCarmudi($page, $totalPage, $dataOld);
        }
    }

    protected function getContentBanXeHoi($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.banxehoi') . 'ban-xe/p'.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('.listcar .sellcar-item');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.banxehoi');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('.info .opensanslistauto', 0)->plaintext); // brand car
                $link = trim(@$item->find('.info .opensanslistauto', 0)->href);
                $price = trim(@$item->find('.info .pricenew', 0)->plaintext);
                $city = trim(@$item->find('.contactinfo .city a', 0)->plaintext);
                $phone = trim(@$item->find('.contactinfo .mobile', 0)->plaintext);
                $phone = str_replace('-', ',', $phone);
                $productYear = trim(@$item->find('.detailinfo .year', 0)->plaintext);
                $datePost = trim(@$item->find('.info .newdate', 0)->plaintext);

                // Get Phone number detail
                $shortContent = null;
                if (!empty($link)) {
                    $temps = $this->getDetailBanXeHoi($domain . $link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $phone = $pdo->quote($phone);
                $productYear = $pdo->quote($productYear);
                $shortContent = $pdo->quote($shortContent);
                $datePost = $pdo->quote($this->formatDate($datePost));

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $productYear, $datePost, $shortContent, 'ban_xe_hoi', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'date_post', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)', 
                    'product_year=VALUES(product_year)', 'date_post=VALUES(date_post)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentBanXeHoi($page, $totalPage, $dataOld);
        }
    }

    protected function getContentChoXe($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.choxe') . 'oto/?page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.homeTopCars ul li');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.choxe');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('.info-car h2 a', 0)->plaintext); // brand car
                $link = trim(@$item->find('.info-car h2 a', 0)->href);
                $price = trim(@$item->find('.info-car .pricenew', 0)->plaintext);
                $city = trim(@$item->find('.detailinfo .madein', 0)->plaintext);
                $phone = trim(@$item->find('.info-car .call-horizontal', 0)->plaintext);
                $productYear = trim(@$item->find('.detailinfo .year', 0)->plaintext);

                // Get Phone number detail
                $shortContent = null;
                if (!empty($link)) {
                    $temps = $this->getDetailChoXe($domain . $link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $phone = $pdo->quote($phone);
                $productYear = $pdo->quote($productYear);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $productYear, $shortContent, 'ban_xe_hoi', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)', 
                    'product_year=VALUES(product_year)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentChoXe($page, $totalPage, $dataOld);
        }
    }

    protected function getContentXe360($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.xe360') . '?page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#masonry-container .item');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.xe360');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('.item-main h2 a', 0)->plaintext); // brand car
                $link = trim(@$item->find('.item-main h2 a', 0)->href);
                $price = trim(@$item->find('.item-main .content .vprice', 0)->plaintext);
                $price = trim(str_replace('Giá bán:', '', $price));
                $city = trim(@$item->find('.item-main .footer', 0)->plaintext);
                $contactTemp = trim(@$item->find('.item-main .content div', 2)->plaintext);
                $contact = $phone = null;
                if (!empty($contactTemp)) {
                    $contactTemp = explode('-', $contactTemp);
                    $contact = isset($contactTemp[1]) ? $contactTemp[1] : null;
                    if (isset($contactTemp[0])) {
                        $part = explode(':', $contactTemp[0]);
                        $phone = isset($part[1]) ? str_replace('.', '', $part[1]) : null;
                    }
                }
                $tempYear = trim(@$item->find('.item-main .content div', 1)->plaintext);
                $tempYear = explode('|', $tempYear);
                $productYear = isset($tempYear[0]) ? $tempYear[0] : null;

                // Get Phone number detail
                $shortContent = null;
                if (!empty($link)) {
                    $temps = $this->getDetailXe360($domain . $link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);
                $productYear = $pdo->quote($productYear);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $contact, $city, $productYear, $shortContent, 'xe_360', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'contact', 'city', 'product_year', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'contact=VALUES(contact)', 'city=VALUES(city)', 
                    'product_year=VALUES(product_year)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentXe360($page, $totalPage, $dataOld);
        }
    }

    protected function getContentXe5Giay($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.xe5giay') . 'xe-oto/page-'.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.discussionListItems .discussionListItem');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.xe5giay');

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('.main .title a', 0)->plaintext); // brand car
                $link = trim(@$item->find('.main .title a', 0)->href);
                $datePost = trim(@$item->find('.main .secondRow .DateTime', 0)->plaintext);
                if (!empty($datePost)) {
                    $date = $this->convertDate5Giay($datePost);
                    $datePost = $this->formatDate($date);
                }

                $shortContent = null;
                if (!empty($link)) {
                    $temps = $this->getDetailXe5Giay($domain . $link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }

                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $datePost = $pdo->quote($datePost);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $datePost, $shortContent, 'xe_5giay', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'date_post', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'date_post=VALUES(date_post)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentXe5Giay($page, $totalPage, $dataOld);
        }
    }

    protected function getContentSanOtoVn($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.sanotovn') . 'oto?page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#left-content .item-box');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.sanotovn');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = trim(@$item->find('.item-info a', 0)->plaintext); // brand car
                $link = trim(@$item->find('.item-info a', 0)->href);

                $price = trim(@$item->find('.price', 0)->plaintext);
                $price = trim(str_replace('Giá bán:', '', $price));

                $phone = trim(@$item->find('.car-price', 0)->plaintext);
                $phone = trim(str_replace('Liên hệ:', '', $phone));

                $city = trim(@$item->find('.sell-info .car-price', 0)->plaintext);
                $city = trim(str_replace('Tỉnh thành:', '', $city));

                $shortContent = $color = $productYear = $runKm = null;
                if (!empty($link)) {
                    $temps = $this->getDetailSanOtoVn($domain . $link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                    $color = isset($temps['color']) ? $temps['color'] : null;
                    $productYear = isset($temps['productYear']) ? $temps['productYear'] : null;
                    $runKm = isset($temps['runKm']) ? $temps['runKm'] : null;
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $domain . $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($domain . $link);
                $phone = $pdo->quote($phone);
                $city = $pdo->quote($city);
                $color = $pdo->quote($color);
                $productYear = $pdo->quote($productYear);
                $runKm = $pdo->quote($runKm);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $phone, $city, $color, $productYear, $runKm, $shortContent, 'san_oto', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'phone', 'city', 'color', 'product_year', 'km_run', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'phone=VALUES(phone)', 'city=VALUES(city)',
                    'color=VALUES(color)', 'product_year=VALUES(product_year)', 'km_run=VALUES(km_run)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentSanOtoVn($page, $totalPage, $dataOld);
        }
    }

    protected function getContentMuaBanOto($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.muabanoto') . '?page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.newCar .div2 tr');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.muabanoto');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {

                for ($i = 0; $i < 4; $i ++) {
                    $obj = $item->find('td', $i);
                    if (!is_object($obj)) {
                        continue;
                    }

                    $xx = $obj->find('div', 0);
                    $link = null;
                    if (is_object($xx)) {
                        $link = $xx->find('a', 0)->href;
                    }
                    $tip = $obj->find('div', 2);

                    if ($dataOld !== NULL) {
                        if ($dataOld->link == $domain . $link) {
                            $page = -1;
                            break;
                        }
                    }

                    $price = $title = $contact = $city = $phone = null;
                    if (is_object($tip)) {
                        $title = $tip->find('tr', 0)->find('td', 0)->plaintext;
                        $price = $tip->find('tr', 1)->find('td', 1)->plaintext;
                        $contact = $tip->find('tr', 7)->find('td', 1)->plaintext;
                        $city = $tip->find('tr', 8)->find('td', 1)->plaintext;
                        $phone = $tip->find('tr', 9)->find('td', 1)->plaintext;
                    }

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $phone = $pdo->quote($phone);
                    $city = $pdo->quote($city);
                    $contact = $pdo->quote($contact);
                    $price = $pdo->quote($price);
                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $phone, $city, $price, $contact, 'muaban_oto', \"$createdAt\")";
                }

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'phone', 'city', 'price', 'contact', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'phone=VALUES(phone)', 'city=VALUES(city)',
                    'price=VALUES(price)', 'contact=VALUES(contact)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentMuaBanOto($page, $totalPage, $dataOld);
        }
    }

    protected function getContentMuaBanNhanh($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.muabannhanh') . 'mua-ban-o-to?page='.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.wrap-post-FB .list-unstyled li');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();
            $domain = \config('wrap.url_site.muabannhanh');
            $domain = substr($domain, 0, -1);

            foreach ($items as $indexL => $item) {
                $title = @$item->find('.block-summary a', 0)->plaintext;
                $link = @$item->find('.block-summary a', 0)->href;
                $price = @$item->find('.block-summary .box-price .price-new', 0)->plaintext;
                $district = @$item->find('.block-summary .quick-view a', 0)->plaintext;
                $city = @$item->find('.block-summary .quick-view a', 1)->plaintext;
                $shortContent = @$item->find('.block-summary .create-content-more', 0)->plaintext;
                $phone = @$item->find('.box-footer span.no-display', 0)->plaintext;
                $phone = str_replace(' ', '', $phone);
                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($district . $city);
                $phone = $pdo->quote($phone);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $shortContent, 'muaban_nhanh', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentMuaBanNhanh($page, $totalPage, $dataOld);
        }
    }

    protected function getContentRongBay($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.rongbay') . 'TP-HCM/O-to-c19-trang'.$page.'.html';

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#listCar .listRowCar');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();

            foreach ($items as $indexL => $item) {
                $title = @$item->find('.h3_car_title a', 0)->plaintext;
                $link = @$item->find('.h3_car_title a', 0)->href;
                $price = @$item->find('.param .param_right', 0)->plaintext;
                $price = str_replace('Giá:', '', $price);
                $cityDatePost = @$item->find('.headInfo .city_left_', 0)->plaintext;
                $parts = explode('-', $cityDatePost);
                $city = isset($parts[0]) ? $parts[0] : null;
                $datePost = isset($parts[1]) ? $parts[1] : null;
                $phone = @$item->find('#other a', 0)->plaintext;
                $contact = @$item->find('#other .address', 0)->plaintext;
                $shortContentObj = @$item->find('.param .param_left', 0);
                $shortContent = null;
                if (is_object($shortContentObj)) {
                    $shortContent = $shortContentObj->innertext();
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $datePost = $pdo->quote($datePost);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $datePost, $contact, $shortContent, 'rong_bay', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'date_post', 'contact', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)',
                    'date_post=VALUES(date_post)', 'contact=VALUES(contact)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentRongBay($page, $totalPage, $dataOld);
        }
    }

    protected function getContentEnBac($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.enbac') . 'c331/Xe-hoi/page-'.$page;

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.css_list_oto__ .rd_view');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();

            foreach ($items as $indexL => $item) {
                $attrs = @$item->attr;
                $title = @$item->find('a', 0)->plaintext;
                $link = isset($attrs['data-link']) ? $attrs['data-link'] : null;
                $phone = isset($attrs['data-phones']) ? $attrs['data-phones'] : null;
                $price = @$item->find('.iphone_timeup .price_r span', 0)->plaintext;
                $contact = @$item->find('.iuser a', 1)->plaintext;
                $city = @$item->find('.iuaddress span', 0)->plaintext;
                $datePost = @$item->find('.icity_view .icity span', 1)->plaintext;

                $temps = $this->getDetailEnBac($link);
                $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $datePost = $pdo->quote($datePost);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $datePost, $contact, $shortContent, 'en_bac', \"$createdAt\")";
                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'date_post', 'contact', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)',
                    'date_post=VALUES(date_post)', 'contact=VALUES(contact)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentEnBac($page, $totalPage, $dataOld);
        }
    }

    protected function getContentTheGioiXeOto($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();

        $url = \config('wrap.url_site.thegioixeoto');
        $urlPaging = $url . 'ban-xe?page='.$page;

        $html = $this->loopFetchUrl($urlPaging);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#vs-content .p_list tr');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();

            foreach ($items as $indexL => $item) {

                $productYear = @$item->find('td', 0)->find('strong', 0)->plaintext;
                $codeCarSite = @$item->find('td', 0)->find('div', 0)->plaintext;

                $title = @$item->find('td', 1)->find('a', 0)->plaintext;
                $link = @$item->find('td', 1)->find('a', 0)->href;
                $shortContent = @$item->find('td', 1)->plaintext;

                $price = @$item->find('td', 2)->find('.price', 0)->plaintext;
                $city = @$item->find('td', 2)->find('.flr', 0)->plaintext;
                $temps = @$item->find('td', 2)->find('div', 0);

                $contact = $phone = null;
                if(is_object($temps)) {
                    $parts = $temps->innertext();
                    $parts = explode('<br />', $parts);
                    $contact = isset($parts[0]) ? $parts[0] : null;
                    $contact .= isset($parts[1]) ? $parts[1] : null;
                    $phone = isset($parts[2]) ? $parts[2] : null;
                    $phone = str_replace('ĐT: ', '', $phone);
                    $phone = str_replace('-', ',', $phone);
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $codeCarSite = $pdo->quote($codeCarSite);
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $productYear = $pdo->quote($productYear);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);
                $shortContent = $pdo->quote($shortContent);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $codeCarSite, $title, $price, $phone, $city, $productYear, $contact, $shortContent, 'thegioixe_oto', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'code_car_site', 'brand_car', 'price', 'phone', 'city', 'product_year', 'contact', 'short_content', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'code_car_site=VALUES(code_car_site)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                    'phone=VALUES(phone)', 'city=VALUES(city)', 'product_year=VALUES(product_year)', 'contact=VALUES(contact)',
                    'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentTheGioiXeOto($page, $totalPage, $dataOld);
        }
    }

    protected function getContentOtoThien($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();
        if (empty($page)) {
            return;
        }

        $url = \config('wrap.url_site.otothien');
        $urlPaging = $url . 'mua-o-to/page/'.$page.'/';

        $html = $this->loopFetchUrl($urlPaging);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.stm-isotope-sorting .stm-listing-no-price-labels');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();

            foreach ($items as $indexL => $item) {

                $price = @$item->find('.meta-top .normal-price', 0)->plaintext;
                $link = @$item->find('.title a', 0)->href;
                $title = @$item->find('.title a', 0)->plaintext;

                $kmRun  = @$item->find('.meta-middle-row', 0)->find('.mileage .value', 0)->plaintext;
                $productYear  = @$item->find('.meta-middle-row', 0)->find('.ca-year .value', 0)->plaintext;

                $temps = @$item->find('.meta-bottom .car-action-dealer-info', 0);

                $contact = $phone = null;
                if(is_object($temps)) {
                    $contact = $temps->find('.dealer-info-block .title a', 0)->plaintext;
                    $phoneObj = $temps->find('.dealer-information .phone', 0);
                    if (is_object($phoneObj)) {
                        $phone = $phoneObj->plaintext;
                    }
                }

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $kmRun = $pdo->quote($kmRun);
                $productYear = $pdo->quote($productYear);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $kmRun, $productYear, $contact, 'oto_thien', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'km_run', 'product_year', 'contact', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                    'phone=VALUES(phone)', 'km_run=VALUES(km_run)', 'product_year=VALUES(product_year)', 'contact=VALUES(contact)',
                    'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentOtoThien($page, $totalPage, $dataOld);
        }
    }

    protected function getContentCafeAuto($page, $totalPage, $dataOld)
    {
        echo 'Page: ' . $page . '<br/>';
        flush();
        ob_flush();
        if (empty($page)) {
            return;
        }

        $url = \config('wrap.url_site.cafeauto');
        $urlPaging = $url . 'mua-ban-xe/oto-xe-may-phu-tung/p-'.$page.'.html';

        $html = $this->loopFetchUrl($urlPaging);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.#market .newsmarket');
        }

        $data = array();
        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            unset($html);
            unset($items);
        } else {
            unset($html);

            $pdo = DB::connection()->getPdo();

            foreach ($items as $indexL => $item) {

                $title = @$item->find('.loph1 a', 0)->plaintext;
                $link = @$item->find('.loph1 a', 0)->href;
                $price = @$item->find('div', 2)->find('.left-gia', 0)->plaintext;
                $city = @$item->find('div', 2)->find('.right-gia', 0)->plaintext;
                $productYear = @$item->find('div', 1)->find('.lopline', 2)->plaintext;
                $attr = @$item->find('.lopline', 7)->find('a', 0)->attr;
                $phone = isset($attr['onclick']) ? $attr['onclick'] : null;
                $phone = str_replace(array("showfullphone(this,'", "')"), '', $phone);
                $contact = @$item->find('.lopline', 6)->plaintext;

                if ($dataOld !== NULL) {
                    if ($dataOld->link == $link) {
                        $page = -1;
                        break;
                    }
                }
                $title = $pdo->quote($title);
                $link = $pdo->quote($link);
                $price = $pdo->quote($price);
                $city = $pdo->quote($city);
                $productYear = $pdo->quote($productYear);
                $phone = $pdo->quote($phone);
                $contact = $pdo->quote($contact);

                $createdAt = date('Y-m-d H:i:s');
                $data[] = "($link, $title, $price, $phone, $city, $productYear, $contact, 'cafe_auto', \"$createdAt\")";

                $items[$indexL]->clear();
            }
            unset($items);

            echo 'Data count: '. count($data) . '<br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();

            if (count($data)) {
                $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'contact', 'type', 'created_at');
                $values = array(
                    'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                    'phone=VALUES(phone)', 'city=VALUES(city)', 'product_year=VALUES(product_year)', 'contact=VALUES(contact)',
                    'type=VALUES(type)', 'created_at=VALUES(created_at)');
                $this->inserOrUpdate($fields, $data, $values);

                unset($data);
            }
        }

        if ($page > 0 || $page < $totalPage) {
            if ($dataOld === NULL) {
                $page--;
            } else {
                $page++;
            }
            $this->getContentCafeAuto($page, $totalPage, $dataOld);
        }
    }

    protected function getDetailCarmudi($url, $sku)
    {
        $url  = $url . '/listings/getsellerphonenumbers/?sku=' . $sku;
            $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        if ($header['http_code'] !== 200) {
            return null;
        }
        $data = json_decode($content, true);
        if (isset($data['data']['extraData']['contacts'])) {
            $phones = array();
            $contacts = $data['data']['extraData']['contacts'];
            if (count($contacts)) {
                foreach ($contacts as $contact) {
                    $phones[] = $contact['number'];
                }
                return implode(',', $phones);
            }
        }
        return null;
    }

    protected function getDetailOtoVietNam($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.osThreadFieldsDetails', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['city'] = trim(@$items->find('.city', 0)->plaintext);
            $results['productYear'] = trim(@$items->find('.nsx', 0)->plaintext);
            $results['color'] = trim(@$items->find('.color', 0)->plaintext);
            $results['shortContent'] = trim(@$html->find('.messageContent', 0)->plaintext);

            unset($html);

            return $results;
        }
    }

    protected function getDetailBanXeHoi($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.bound .desc', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim(@$html->find('.description', 0)->innertext());

            unset($html);

            return $results;
        }
    }

    protected function getDetailChoXe($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#detailSection .infoSection', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim(@$html->find('.body-description-car', 0)->innertext());

            unset($html);

            return $results;
        }
    }

    protected function getDetailXe360($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#content .inner', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim(@$html->find('.controls', 0)->innertext());

            unset($html);
            return $results;
        }
    }

    protected function getDetailXe5Giay($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.messageContent', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim(@$html->find('.messageText', 0)->plaintext);

            unset($html);
            return $results;
        }
    }

    protected function getDetailSanOtoVn($url)
    {
        echo '========================================BEGIN Detail SanOto======================================<br/>';
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#left-content', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['productYear'] = trim(@$items->find('table.table-striped', 0)->find('tr', 3)->find('td', 0)->plaintext);
            $results['color'] = trim(@$items->find('.table-striped', 1)->find('tr', 0)->find('td', 0)->plaintext);
            $results['runKm'] = trim(@$items->find('.table-striped', 1)->find('tr', 3)->find('td', 0)->plaintext);

            $results['shortContent'] = trim(@$html->find('.product-content', 0)->innertext());

            echo '========================================END Detail SanOto======================================<br/><br/><br/>';
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function getDetailEnBac($url)
    {
        echo '========================================BEGIN Detail ENBAC======================================<br/>';
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.otoContent', 0);
        }

        if (!$items) {
            echo 'Item empty <br/>';
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
            echo '=========================================================================<br/>';
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim($items->innertext());

            echo '========================================END Detail ENBAC======================================<br/><br/><br/>';
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function loopFetchUrl($url)
    {
        try {
            echo 'url: ' . $url . "<br/>";
            flush();
            ob_flush();

            $html = HtmlDomParser::file_get_html($url);
            return $html;
        } catch (\Exception $e) {
            echo 'Fail connecttion <br/>';
            flush();
            ob_flush();

            sleep(5);
            $this->loopFetchUrl($url);
        }
    }

    protected function inserOrUpdate($fields, $data, $values)
    {
        try {
            DB::insert("INSERT INTO contents (".  implode(',', $fields).") "
                    . "VALUES ".  implode(',', $data) . " ON DUPLICATE KEY UPDATE " . implode(',', $values));

        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    protected function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        $date = new \DateTime($date);
        return $date->format('Y-m-d');
    }

    protected function convertDate5Giay($date)
    {
        $part = explode('/', $date);
        $year = isset($part[2]) ? $part[2] : null;
        $month = isset($part[1]) ? $part[1] : null;
        $day = isset($part[0]) ? $part[0] : null;
        if (empty($year) || empty($month) || empty($day)) {
            return null;
        }
        return $year . '-' . $month . '-' . $day;
    }

}