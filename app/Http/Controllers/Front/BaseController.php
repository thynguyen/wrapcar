<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Contents;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public $type = null;
    public $step_fail = 0;
    public $empty_count = 0;

    public function __construct(Request $request) {
        $this->type = $request->get('type');
    }

    protected function getBonBanh($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'bon_banh')->orderBy('id', 'DESC')->first();

        echo "======BEGIN bonbanh=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.bonbanh');

        /*
        $html = HtmlDomParser::file_get_html($url);
        $temp = $html->find('div.pagging div.cpage', 0);
        if (!is_object($temp)) {
            unset($temp);
            return;
        }
        $textTotal = $temp->innertext();
        unset($temp);
        unset($html);
        preg_match_all('/<b>([^{]*)<\/b>/s', $textTotal, $matches);

        $totalPage = 1;
        if (isset($matches[1][0])) {
            $number = str_replace(',', '', $matches[1][0]);
            preg_match_all('/\d+/', $number, $matches);
            $total = isset($matches[0][0]) ? $matches[0][0] : 0;
            $totalPage = $this->getTotalPage($total, $limit);
        }
         * 
         */
        $totalPage = 807;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentBonBanh($page, $totalPage, $dataOld);

        echo "======END bonbanh=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getChoTot($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'chotot')->orderBy('id', 'DESC')->first();

        echo "======BEGIN chotot=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = sprintf(\config('wrap.url_site.chotot_gateway'), $page, $limit, 0);
        $data = $this->execCurl($url);

        if ($data === null) {
            Log::debug('Can not get content chotot');
            return;
        }
        $total = $data['total'];
        $totalPage = $this->getTotalPage($total, $limit);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }

        $this->getContentChotot($page, $totalPage, $dataOld);

        echo "======END chotot=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function execCurl($url, $param = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode === 200) {
            return $this->convertJson($content);
        }
        return null;
    }

    protected function convertJson($data)
    {
        return json_decode($data, true);
    }

    protected function getContentChoTot($page, $totalPage, $dataOld)
    {
        try {
            $oPost = ($totalPage * 20) - ($page * 20);

            $max_loop = false;
            echo 'Page: ' . $page . "\n\<br/>";
            flush();
            ob_flush();

            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = sprintf(\config('wrap.url_site.chotot_gateway'), $page, 20, $oPost);
            echo 'url: ' . $url . "<br/>";
            flush();
            ob_flush();

            $data = array();
            $contents = $this->execCurl($url);
            if ($contents === null) {
                echo "Item empty \n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo "=========================================================================\n<br/>";
                flush();
                ob_flush();

                unset($contents);
            } else {
                $items = $contents['ads'];
                unset($contents);

                $pdo = DB::connection()->getPdo();
                $domain = \config('wrap.url_site.chotot');

                foreach ($items as $indexL => $item) {
                    $link = $this->formatLinkChoTot($domain, $item);

                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'chotot')->where('link', $link)->count();
                        if (!empty($count)) {
                            unset($count);
                            $max_loop = true;
                            continue;
                        }
                    }
                    $title = isset($item['subject']) ? trim($item['subject']) : null;
                    $city = isset($item['region_name']) ? trim($item['region_name']) : null;
                    $price = isset($item['price_string']) ? trim($item['price_string']) : null;

                    $detailUrl = sprintf(\config('wrap.url_site.chotot_gateway_detail'), $item['list_id']);
                    $detail = $this->getDetailChoTot($detailUrl);
                    $shortContent = isset($detail['shortContent']) ? $detail['shortContent'] : null;
                    $phone = isset($detail['phone']) ? $detail['phone'] : null;
                    $contact = isset($detail['contact']) ? $detail['contact'] : null;
                    $productYear = isset($detail['productYear']) ? $detail['productYear'] : null;
                    $kmRun = isset($detail['kmRun']) ? $detail['kmRun'] : null;

                    $title = $pdo->quote($title);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $link = $pdo->quote($link);
                    $shortContent = $pdo->quote($shortContent);
                    $phone = $pdo->quote($phone);
                    $contact = $pdo->quote($contact);
                    $productYear = $pdo->quote($productYear);
                    $kmRun = $pdo->quote($kmRun);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $contact, $city, $productYear, $kmRun, $shortContent, 'chotot', \"$createdAt\")";
                }
                unset($items);
                unset($pdo);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'contact', 'city', 'product_year', 'km_run', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                        'phone=VALUES(phone)', 'contact=VALUES(contact)', 'city=VALUES(city)', 
                        'product_year=VALUES(product_year)', 'km_run=VALUES(km_run)','short_content=VALUES(short_content)', 
                        'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);
                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentChoTot($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }
    protected function getDetailChoTot($url)
    {
        echo "======BEGIN detail chotot=========\n<br/>";
        echo 'url: ' . $url . "\n<br/>";
        flush();
        ob_flush();

        $data = $this->execCurl($url);
        if ($data === null) {
            return array();
        }

        echo "======END detail chotot=========\n<br/>";
        flush();
        ob_flush();
        return array(
            'shortContent' => isset($data['ad']['body']) ? $data['ad']['body'] : null,
            'phone' => isset($data['ad']['phone']) ? $data['ad']['phone'] : null,
            'contact' => isset($data['ad']['account_name']) ? $data['ad']['account_name'] : null,
            'productYear' => isset($data['ad_params']['mfDate']['value']) ? $data['ad_params']['mfDate']['value'] : null,
            'kmRun' => isset($data['ad_params']['mileage']['value']) ? $data['ad_params']['mileage']['value'] : null,
        );
    }
    
    protected function formatLinkChoTot($url, $item)
    {
        if (isset($item['area_name'])) {
            $district = $this->vnStrFilter($item['area_name']);
        } else {
            $district = $this->vnStrFilter($item['region_name']);
        } 
        $subject = $this->vnStrFilter($item['subject']);
        $productId = $item['list_id'];

        $link = $url . $district . '/mua-ban-o-to/' . $subject . '-' . $productId . '.htm';
        return $link;
    }
    
    function vnStrFilter ($str) {
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );

       foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
       }
       $str = str_replace(' ', '-', strtolower($str));
       $str = str_replace(',', '-', strtolower($str));
       $str = preg_replace('/[^A-Za-z0-9\-]/', '', $str);
       return $str;
    }

    protected function getMuaBan($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'mua_ban')->orderBy('id', 'DESC')->first();

        echo "======BEGIN muaban=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 200;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBan($page, $totalPage, $dataOld);

        echo "======END muaban=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getOtoVietNam($page = 1, $limit = 20, $type = null)
    {
        $dataOld = Contents::where('type', 'otovietnam')->orderBy('id', 'DESC')->first();

        echo "======BEGIN OTO VIET NAM=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);
        if (!defined('MAX_FILE_SIZE')) {
            DEFINE('MAX_FILE_SIZE', 6000000);
        }

        $url = \config('wrap.url_site.otovietnam') . 'forums/xe-moi.' . $type;
        $html = HtmlDomParser::file_get_html($url);
        $temp = $html->find('div.PageNav', 0);
        if (!is_object($temp)) {
            unset($temp);
            return;
        }
        $attr = $temp->attr;
        unset($temp);
        $totalPage = isset($attr['data-last']) ? $attr['data-last'] : 0;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentOtoVietNam($page, $totalPage, $dataOld, $type);

        echo "======END OTO VIET NAM=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getCarmundi($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'carmudi')->orderBy('id', 'DESC')->first();
        echo '======BEGIN CARMUDI=========' . "\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);
        if (!defined('MAX_FILE_SIZE')) {
            DEFINE('MAX_FILE_SIZE', 6000000);
        }

        $url = \config('wrap.url_site.carmudi') . '/all/?sort=suggested&page=1';
        $html = HtmlDomParser::file_get_html($url);
        if (!is_object($html)) {
            unset($html);
            return;
        }
        $obj = $html->find('ul.pagination', 0);
        if (!is_object($obj)) {
            unset($obj);
            return;
        }
        unset($html);
        $attrs = $obj->attr;
        unset($obj);

        $totalPage = isset($attrs['data-total-pages']) ? $attrs['data-total-pages'] : 0;
        if (empty($totalPage)) {
            return;
        }

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentCarmudi($page, $totalPage, $dataOld);

        echo "======END CARMUDI=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getBanXeHoi($type, $page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'ban_xe_hoi')->orderBy('id', 'DESC')->first();

        echo "======BEGIN BANXEHOI: ".$type."=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.banxehoi');

        $html = HtmlDomParser::file_get_html($url . $type);
        $temp = $html->find('.listcar div.textcount span', 0);
        if (!is_object($temp)) {
            unset($temp);
            unset($html);
            return;
        }
        $textTotal = trim($temp->plaintext);
        unset($temp);
        unset($html);

        $totalPage = 1;
        if ($textTotal) {
            $total = str_replace(',', '', $textTotal);
            $totalPage = $this->getTotalPage($total, $limit);
        }
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentBanXeHoi($type, $page, $totalPage, $dataOld);

        echo "======END BANXEHOI: ".$type."=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getChoXe($page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'cho_xe')->orderBy('id', 'DESC')->first();

        echo "======BEGIN CHOXE=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.choxe');

        $html = HtmlDomParser::file_get_html($url . 'oto/?page=1');
        $li = $html->find('.pagination li', 4);
        if (is_object($li)) {
            $a = $li->find('a', 0);
            if (is_object($a)) {
                $href = $a->href;
                preg_match('!\d+!', $href, $matches);
                $totalPage = isset($matches[0]) ? $matches[0] : 0;
            }
        }
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentChoXe($page, $totalPage, $dataOld);

        echo "======END CHOXE=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getXe360($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'xe_360')->orderBy('id', 'DESC')->first();

        echo "======BEGIN XE 360=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 3222; // default
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentXe360($page, $totalPage, $dataOld);

        echo "======END XE 360=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getXe5giay($page = 1, $limit = 18)
    {
        $dataOld = Contents::where('type', 'xe_5giay')->orderBy('id', 'DESC')->first();

        echo "======BEGIN XE5GIAY=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.xe5giay');

        $html = HtmlDomParser::file_get_html($url . 'xe-oto/');
        $attrs = $html->find('.PageNav', 0)->attr;
        $totalPage = isset($attrs['data-last']) ? $attrs['data-last'] : 0;

        unset($html);
        if (empty($totalPage)) {
            return;
        }

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentXe5Giay($page, $totalPage, $dataOld);

        echo "======END XE5GIAY=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getSanOtoVn($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'san_oto')->orderBy('id', 'DESC')->first();

        echo "======BEGIN SANOTO=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.sanotovn');

        //$html = HtmlDomParser::file_get_html($url . 'oto/?page=1');
        //$href = @$html->find('.pagination li', 6)->find('a', 0)->href;
        //$totalPage = str_replace('/oto?page=', '', $href);
        //unset($html);
        $totalPage = 434;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentSanOtoVn($page, $totalPage, $dataOld);

        echo "======END SANOTO=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getMuaBanOto($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'muaban_oto')->orderBy('id', 'DESC')->first();

        echo "======BEGIN MUABAN OTO=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 16;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBanOto($page, $totalPage, $dataOld);

        echo "======END MUABAN OTO=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getMuaBanNhanh($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'muaban_nhanh')->orderBy('id', 'DESC')->first();

        echo "======BEGIN MUABAN NHANH=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.muabannhanh');

        $html = HtmlDomParser::file_get_html($url . 'mua-ban-o-to?page=1');
        $temp = @$html->find('.page-navi ul li', 6);
        $totalPage = 0;
        if (is_object($temp)) {
            $href = $temp->find('a', 0)->href;
            $totalPage = str_replace('https://muabannhanh.com/mua-ban-o-to?page=', '', $href);
        }
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentMuaBanNhanh($page, $totalPage, $dataOld);

        echo "======END MUABAN NHANH=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getRongBay($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'rong_bay')->orderBy('id', 'DESC')->first();

        echo "======BEGIN RONG BAY=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 153;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentRongBay($page, $totalPage, $dataOld);

        echo "======END RONG BAY=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getEnBac($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'en_bac')->orderBy('id', 'DESC')->first();

        echo "======BEGIN ENBAC=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 200;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentEnBac($page, $totalPage, $dataOld);

        echo "======END ENBAC=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getTheGioiXeOto($page = 1, $limit = 20)
    {
        $dataOld = Contents::where('type', 'thegioixe_oto')->orderBy('id', 'DESC')->first();

        echo "======BEGIN THEGIOIXEOTO=========\n<br/>";
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
        }
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentTheGioiXeOto($page, $totalPage, $dataOld);

        echo "======END THEGIOIXEOTO=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getOtoThien($page = 1, $limit = 12)
    {
        $dataOld = Contents::where('type', 'oto_thien')->orderBy('id', 'DESC')->first();

        echo "======BEGIN OTOTHIEN=========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.otothien');

        $html = HtmlDomParser::file_get_html($url . 'mua-o-to/');
        $totalPage = $html->find('.page-numbers li', 4)->find('.page-numbers', 0)->plaintext;
        unset($html);

        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentOtoThien($page, $totalPage, $dataOld);

        echo "======END OTOTHIEN=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getCafeAuto($page = 1, $limit = 15)
    {
        $dataOld = Contents::where('type', 'cafe_auto')->orderBy('id', 'DESC')->first();

        echo "======BEGIN CAFE AUTO=========\n<br/>";
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

        echo "======END CAFE AUTO=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getBanOtoRe($page = 1, $limit = 12)
    {
        $dataOld = Contents::where('type', 'banotore')->orderBy('id', 'DESC')->first();

        echo "======BEGIN BAN OTO RE========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $url = \config('wrap.url_site.banotore') . 'ban-xe/';

        $html = HtmlDomParser::file_get_html($url);
        $totalPage = 0;
        $obj = @$html->find('#PageNum', 0);
        if (is_object($obj)) {
            $totalPage = @$obj->attr['value'];
        }

        unset($html);
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentBanOtoRe($page, $totalPage, $dataOld);

        echo "======END BAN OTO RE=========\n<br/>";
        flush();
        ob_flush();
    }

    protected function getSanXeHot($page = 1, $limit = 16)
    {
        $dataOld = Contents::where('type', 'sanxehot')->orderBy('id', 'DESC')->first();

        echo "======BEGIN SAN XE HOT========\n<br/>";
        flush();
        ob_flush();

        ini_set('max_execution_time', 0);

        $totalPage = 1000;
        if ($dataOld === NULL) {
            $page = $totalPage;
        }
        $this->getContentSanXeHot($page, $totalPage, $dataOld);

        echo "======END SAN XE HOT=========\n<br/>";
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
        try {
            $max_loop = false;
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = \config('wrap.url_site.bonbanh') . 'oto/page,'.$page.'/';
            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = $html->find('.g-box-content .car-item');
            }
            $data = array();
            if (!$items) {
                echo "Item empty \n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo "=========================================================================\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();
                $domain = \config('wrap.url_site.bonbanh');

                foreach ($items as $indexL => $item) {
                    $objA = $item->find('a',0);
                    if (!is_object($objA)) {
                        unset($objA);
                        continue;
                    }

                    $link = trim($objA->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'bon_banh')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            unset($objA);
                            continue;
                        }
                    }

                    $productYear = trim($objA->find('div', 0)->plaintext);
                    $title = trim($objA->find('div', 1)->plaintext); // brand car
                    $price = trim($item->find('div', 2)->innertext());
                    $city = trim($item->find('div', 3)->plaintext);

                    $carCode = null;
                    $carCodeTemp = $item->find('div', 4);
                    if ($carCodeTemp) {
                        $carCodeTemp1 = $carCodeTemp->find('span.car_code', 0);
                        if ($carCodeTemp1) {
                            $carCode = trim($carCodeTemp1->plaintext);
                            unset($carCodeTemp1);
                        }
                        unset($carCodeTemp);
                    }
                    $shortContent = trim($item->find('div', 5)->innertext());
                    $contactAndPhone = trim($item->find('div.cb7', 0)->innertext());

                    $detail = $this->getDetailBonBanh($domain . $link);

                    $productYear = $pdo->quote($productYear);
                    $title = $pdo->quote($title);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $link = $pdo->quote($domain . $link);
                    $carCode = $pdo->quote($carCode);
                    $shortContent = $pdo->quote($shortContent);
                    $contactAndPhone = $pdo->quote($contactAndPhone);
                    $color = isset($detail['color']) ? $pdo->quote($detail['color']) : null;

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $carCode, $productYear, $title, $price, $color, $contactAndPhone, $city, $shortContent, 'bon_banh', \"$createdAt\")";

                    unset($objA);
                    $items[$indexL]->clear();
                }
                unset($items);
                unset($pdo);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo "=========================================================================\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'code_car_site', 'product_year', 'brand_car', 'price', 'color', 'contact', 'city', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'code_car_site=VALUES(code_car_site)', 'product_year=VALUES(product_year)', 'brand_car=VALUES(brand_car)', 
                        'price=VALUES(price)', 'color=VALUES(color)', 'contact=VALUES(contact)', 'city=VALUES(city)', 'short_content=VALUES(short_content)', 
                        'type=VALUES(type)', 'date_post=VALUES(date_post)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);
                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentBonBanh($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentMuaBan($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = \config('wrap.url_site.muaban') . 'ban-o-to-toan-quoc-l0-c41?cp='.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = $html->find('.mbn-box-list .mbn-box-list-content');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '========================================================================='."\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);
                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $link = trim($item->find('a.mbn-image', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'mua_ban')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = trim(@$item->find('div.mbn-content .mbn-title', 0)->plaintext); // brand car
                    $price = trim(@$item->find('div.mbn-content .mbn-price', 0)->plaintext);
                    $city = trim(@$item->find('div.mbn-content .mbn-address', 0)->plaintext);
    //                $datePost = trim(@$item->find('div.mbn-content .mbn-date', 0)->plaintext);
    //                $summary = trim(@$item->find('div.mbn-content .mbn-item-summary', 0));
    //                $shortContent = null;
    //                if (is_object($summary)) {
    //                    $shortContent = trim($summary->innertext());
    //                }

                    $detail = $this->getDetailMuaBan($link);
                    $phone = isset($detail['phone']) ? $detail['phone'] : null;
                    $shortContent = isset($detail['shortContent']) ? $detail['shortContent'] : null;
                    $productYear = isset($detail['productYear']) ? $detail['productYear'] : null;
                    $color = isset($detail['color']) ? $detail['color'] : null;
                    $contact = isset($detail['contact']) ? $detail['contact'] : null;

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $phone = $pdo->quote($phone);
                    $productYear = $pdo->quote($productYear);
                    $color = $pdo->quote($color);
                    $contact = $pdo->quote($contact);
                    $shortContent = $pdo->quote($shortContent);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $contact, $city, $productYear, $color, $shortContent, 'mua_ban', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'contact', 'city', 'product_year', 'color', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                        'phone=VALUES(phone)', 'contact=VALUES(contact)', 'city=VALUES(city)', 'product_year=VALUES(product_year)',
                        'color=VALUES(color)', 'short_content=VALUES(short_content)', 
                        'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentMuaBan($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentOtoVietNam($page, $totalPage, $dataOld, $type)
    {
        try {
            $max_loop = false;
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = \config('wrap.url_site.otovietnam') . 'forums/xe-moi.' . $type . '/page-' . $page;
            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = $html->find('ol.discussionListItems li');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();
                $domain = \config('wrap.url_site.otovietnam');

                $const = ($type == '305') ? 3 : 2;
                foreach ($items as $indexL => $item) {
                    if ($indexL <= $const) {
                        continue;
                    }

                    $linkTemp = @$item->find('div', 1);
                    if (!is_object($linkTemp)) {
                        unset($linkTemp);
                        continue;
                    }
                    $linkTemp1 = $linkTemp->find('a.PreviewTooltip', 0);
                    if (!is_object($linkTemp1)) {
                        unset($linkTemp1);
                        continue;
                    }

                    $link = $linkTemp1->href;
                    unset($linkTemp);
                    unset($linkTemp1);

                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'otovietnam')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            $max_loop = true;
                            unset($count);
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

    //                $title = trim(@$item->find('a', 1)->plaintext); // brand car
    //                $price = trim(@$item->find('.threadPrice', 0)->plaintext);
    //                $atrDatePost = @$item->find('div', 1)->find('span.DateTime', 0)->plaintext;
    //                $kmRun = trim(@$item->find('.threadOdo', 0)->plaintext);
    //                $datePost = isset($atrDatePost['data-time']) ? date('Y-m-d', $atrDatePost['data-time']) : null;

                    $urlDetail = \config('wrap.url_site.otovietnam') . $link;
                    $details = $this->getDetailOtoVietNam($urlDetail);

                    $title = isset($details['title']) ? $details['title'] : null;
                    $city = isset($details['city']) ? $details['city'] : null;
    //                $phone = isset($details['phone']) ? $details['phone'] : null;
                    $contact = isset($details['contact']) ? $details['contact'] : null;
                    $price = isset($details['price']) ? $details['price'] : null;
                    $color = isset($details['color']) ? $details['color'] : null;
                    $kmRun = isset($details['km_run']) ? $details['km_run'] : null;
                    $shortContent = isset($details['shortContent']) ? $details['shortContent'] : null;

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $price = $pdo->quote($price);
                    $kmRun = $pdo->quote($kmRun);
                    $city = $pdo->quote($city);
                    $color = $pdo->quote($color);
                    $contact = $pdo->quote($contact);
    //                $phone = $pdo->quote($phone);
                    $shortContent = $pdo->quote($shortContent);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $kmRun, $city, $color, $contact, $shortContent, 'otovietnam', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'km_run', 'city', 'color', 'contact', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)',
                        'km_run=VALUES(km_run)', 'city=VALUES(city)', 'color=VALUES(color)', 'contact=VALUES(contact)',
                        'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentOtoVietNam($page, $totalPage, $dataOld, $type);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentCarmudi($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = \config('wrap.url_site.carmudi') . 'all/?sort=suggested&page='.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = $html->find('.catalog-listing-item');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
                    $link = trim($item->find('.catalog-listing-description-data a', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'carmudi')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            $max_loop = true;
                            unset($count);
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = trim($item->find('.catalog-listing-description-data a', 0)->plaintext); // brand car
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
                    $detail = $this->getDetailCarmudiColor($domain . $link);

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $contact = $pdo->quote($contact);
                    $shortContent = $pdo->quote($shortContent);
                    $phone = $pdo->quote($phone);
                    $color = isset($detail['color']) ? $pdo->quote($detail['color']) : null;

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $city, $contact, $phone, $color, $shortContent, 'carmudi', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'city', 'contact', 'phone', 'color', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'city=VALUES(city)', 'contact=VALUES(contact)', 
                        'phone=VALUES(phone), color=VALUES(color), short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentCarmudi($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentBanXeHoi($type, $page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();
            if ($page <= 0 || $page > $totalPage) {
                return;
            }

            $url = \config('wrap.url_site.banxehoi') . $type . '/p'.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = $html->find('.listcar .sellcar-item');
            }
            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
                    $attr = @$item->attr;
                    $class = isset($attr['class']) ? $attr['class'] : null;
                    $link = trim(@$item->find('.info .opensanslistauto', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'ban_xe_hoi')->where('link', $domain . $link)->count();
                        if (!empty($count) && preg_match('/vippro/i', $class)) {
    //                        $page = -1;
                            $max_loop = true;
                            unset($count);
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = trim(@$item->find('.info .opensanslistauto', 0)->plaintext); // brand car
                    $price = trim(@$item->find('.info .pricenew', 0)->plaintext);
                    $city = trim(@$item->find('.contactinfo .city a', 0)->plaintext);
                    $phone = trim(@$item->find('.contactinfo .mobile', 0)->plaintext);
                    $phone = str_replace('-', ',', $phone);
                    $productYear = trim(@$item->find('.detailinfo .year', 0)->plaintext);
                    $datePost = trim(@$item->find('.info .newdate', 0)->plaintext);
                    $shortContent = trim(@$item->find('.info', 0)->innertext());

                    // Get color from detail
                    $detail = $this->getDetailBanXeHoi($domain . $link);

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $phone = $pdo->quote($phone);
                    $productYear = $pdo->quote($productYear);
                    $shortContent = $pdo->quote($shortContent);
                    $datePost = $pdo->quote($datePost);
                    $color = isset($detail['color']) ? $detail['color'] : null;
                    $color = $pdo->quote($color);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $color, $city, $productYear, $datePost, $shortContent, 'ban_xe_hoi', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'color', 'city', 'product_year', 'date_post', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'color=VALUES(color)', 'city=VALUES(city)', 
                        'product_year=VALUES(product_year)', 'date_post=VALUES(date_post)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }
            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentBanXeHoi($type, $page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentChoXe($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage || $this->empty_count > 2) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            $url = \config('wrap.url_site.choxe') . 'oto/?page='.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = @$html->find('table.list-news tr');
            }

            $data = array();
            if (!$items) {
                $this->empty_count ++;
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $link = trim(@$item->find('.info-left a.img', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'cho_xe')->where('link', $link)->count();
                        if (!empty($count)) {
                            $max_loop = true;
                            unset($count);
                            $items[$indexL]->clear();
                            continue;
                        }
                    }

                    $title = trim(@$item->find('.info-left .tit-news', 0)->plaintext); // brand car
                    $price = trim(@$item->find('.info-left .price', 0)->plaintext);
                    $city = trim(@$item->find('.info-left .city', 0)->plaintext);

                    // Get Phone number detail
                    $temps = $this->getDetailChoXe($link);
                    $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                    $kmRun = isset($temps['kmRun']) ? $temps['kmRun'] : null;
                    $productYear = isset($temps['productYear']) ? $temps['productYear'] : null;
                    $color = isset($temps['color']) ? $temps['color'] : null;
                    $phone = isset($temps['phone']) ? $temps['phone'] : null;

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($link);
                    $price = $pdo->quote($price);
                    $kmRun = $pdo->quote($kmRun);
                    $city = $pdo->quote($city);
                    $color = $pdo->quote($color);
                    $phone = $pdo->quote($phone);
                    $productYear = $pdo->quote($productYear);
                    $shortContent = $pdo->quote($shortContent);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $city, $productYear, $kmRun, $color, $shortContent, 'cho_xe', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'km_run', 'color', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 'phone=VALUES(phone)', 'city=VALUES(city)', 
                        'product_year=VALUES(product_year)', 'km_run=VALUES(km_run)', 'color=VALUES(color)', 'short_content=VALUES(short_content)', 'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentChoXe($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentXe360($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            $url = \config('wrap.url_site.xe360') . '?start='.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = @$html->find('#masonry-container .item');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
                    $link = trim(@$item->find('.item-main h2 a', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'xe_360')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
    //                        break;
                            $items[$indexL]->clear();
                            continue;
                        }
                    }

                    $title = trim(@$item->find('.item-main h2 a', 0)->plaintext); // brand car
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

                    $shortContent = trim(@$item->find('.item-main', 0)->innertext());
                    // Get Phone number detail
                    /*
                    $shortContent = null;
                    if (!empty($link)) {
                        $temps = $this->getDetailXe360($domain . $link);
                        $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                    }
                     * 
                     */

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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentXe360($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentXe5Giay($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();
                $domain = \config('wrap.url_site.xe5giay');

                foreach ($items as $indexL => $item) {
                    $link = trim(@$item->find('.main .title a', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'xe_5giay')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
    //                        break;
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
                        }
                    }

                    $title = trim(@$item->find('.main .title a', 0)->plaintext); // brand car
                    $datePost = trim(@$item->find('.main .secondRow .DateTime', 0)->plaintext);

                    $shortContent = trim(@$item->find('.main', 0)->innertext());
                    /*
                    $shortContent = null;
                    if (!empty($link)) {
                        $temps = $this->getDetailXe5Giay($domain . $link);
                        $shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                    }*/


                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $datePost = $pdo->quote($datePost);
                    $shortContent = $pdo->quote($shortContent);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $datePost, $shortContent, 'xe_5giay', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentXe5Giay($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentSanOtoVn($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
                    $link = trim(@$item->find('.item-info a', 0)->href);
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'san_oto')->where('link', $domain . $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
    //                        break;
                            $items[$indexL]->clear();
                            continue;
                        }
                    }

                    $title = trim(@$item->find('.item-info a', 0)->plaintext); // brand car

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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentSanOtoVn($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentMuaBanOto($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();

            $url = \config('wrap.url_site.muabanoto') . '?page='.$page;

            $html = $this->loopFetchUrl($url);
            $items = null;
            if (is_object($html)) {
                $items = @$html->find('.newCar .div2 table tr');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

                    for ($i = 0; $i < 4; $i++) {
                        $obj = $item->find('td', $i);
                        if (!is_object($obj)) {
                            continue;
                        }

                        $xx = $obj->find('div', 0);
                        $link = null;
                        if (is_object($xx)) {
                            $link = $xx->find('a', 0)->href;
                            unset($xx);
                        }
                        if (empty($link)) {
                            continue;
                        }

                        $tip = $obj->find('div', 2);
                        if ($this->type !== 'all') {
                            $count = Contents::where('type', 'muaban_oto')->where('link', $domain . $link)->count();
                            if (!empty($count)) {
                                unset($count);
                                $max_loop = true;
//                                $items[$indexL]->clear();
                                continue;
                            }
                        }

                        $price = $title = $contact = $city = $phone = null;
                        if (is_object($tip)) {
                            $title = $tip->find('tr', 0)->find('td', 0)->plaintext;
                            $price = $tip->find('tr', 1)->find('td', 1)->plaintext;
                            $contact = $tip->find('tr', 7)->find('td', 1)->plaintext;
                            $city = $tip->find('tr', 8)->find('td', 1)->plaintext;
                            $phone = $tip->find('tr', 9)->find('td', 1)->plaintext;
                            unset($tip);
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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentMuaBanOto($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentMuaBanNhanh($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $link = @$item->find('.block-summary a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'muaban_nhanh')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = trim(@$item->find('.block-summary a', 0)->plaintext);
                    $price = trim(@$item->find('.block-summary .box-price .price-new', 0)->plaintext);
                    $district = trim(@$item->find('.block-summary .quick-view a', 0)->plaintext);
                    $city = trim(@$item->find('.block-summary .quick-view a', 1)->plaintext);
                    $shortContent = trim(@$item->find('.block-summary .create-content-more', 0)->plaintext);
                    $phone = trim(@$item->find('.box-footer span.no-display', 0)->plaintext);
                    $phone = str_replace(' ', '', $phone);

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($district . $city);
                    $phone = $pdo->quote($phone);
                    $shortContent = $pdo->quote('');

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $city, $shortContent, 'muaban_nhanh', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentMuaBanNhanh($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentRongBay($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $link = @$item->find('.h3_car_title a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'rong_bay')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }
                    $title = @$item->find('.h3_car_title a', 0)->plaintext;
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
                        unset($shortContentObj);
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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentRongBay($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentEnBac($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $attrs = @$item->attr;
                    $link = isset($attrs['data-link']) ? $attrs['data-link'] : null;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'en_bac')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = @$item->find('a', 0)->plaintext;
                    $phone = isset($attrs['data-phones']) ? $attrs['data-phones'] : null;
                    $price = @$item->find('.iphone_timeup .price_r span', 0)->plaintext;
                    $contact = @$item->find('.iuser a', 1)->plaintext;
                    $city = @$item->find('.iuaddress span', 0)->plaintext;
                    $datePost = @$item->find('.icity_view .icity span', 1)->plaintext;

                    //$temps = $this->getDetailEnBac($link);
                    //$shortContent = isset($temps['shortContent']) ? $temps['shortContent'] : null;
                    $shortContent = null;

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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentEnBac($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentTheGioiXeOto($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {

                    $link = @$item->find('td', 1)->find('a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'thegioixe_oto')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $productYear = @$item->find('td', 0)->find('strong', 0)->plaintext;
                    $codeCarSite = @$item->find('td', 0)->find('div', 0)->plaintext;

                    $title = @$item->find('td', 1)->find('a', 0)->plaintext;
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
                        unset($temps);
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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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
            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentTheGioiXeOto($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentOtoThien($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                $items = @$html->find('#listings-result .stm-isotope-sorting');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {
                    $childs = $item->find('.listing-list-loop');
                    if (!$childs) {
                        unset($childs);
                        continue;
                    }
                    foreach ($childs as $indexX => $info) {

                        $link = $info->find('.content .title a', 0)->href;
                        echo $link . "\n<br/>";
                        flush();
                        ob_flush();

                        if ($this->type !== 'all') {
                            $count = Contents::where('type', 'oto_thien')->where('link', $link)->count();
                            if (!empty($count)) {
                                unset($count);
                                $max_loop = true;
                                $childs[$indexX]->clear();
                                continue;
                            }
                        }
                        $price = $info->find('.meta-top .normal-price', 0)->plaintext;
                        $title = $info->find('.title a', 0)->plaintext;

                        $kmRun = $productYear = null;
                        $row = $info->find('.meta-middle-row', 0);
                        if ($row) {
                            if ($row->find('.mileage .value', 0)) {
                                $kmRun = $row->find('.mileage .value', 0)->plaintext;
                            }
                            if ($row->find('.ca-year .value', 0)) {
                                $productYear = $row->find('.ca-year .value', 0)->plaintext;
                            }
                        }
                        unset($row);

                        $temps = $info->find('.meta-bottom .car-action-dealer-info', 0);
                        $contact = $phone = null;
                        if(is_object($temps)) {
                            $contact = $temps->find('.dealer-info-block .title a', 0)->plaintext;
                            $phoneObj = $temps->find('.dealer-information .phone', 0);
                            if (is_object($phoneObj)) {
                                $phone = $phoneObj->plaintext;
                                unset($phoneObj);
                            }
                        }
                        unset($temps);

                        $title = $pdo->quote($title);
                        $link = $pdo->quote($link);
                        $price = $pdo->quote($price);
                        $kmRun = $pdo->quote($kmRun);
                        $productYear = $pdo->quote($productYear);
                        $phone = $pdo->quote($phone);
                        $contact = $pdo->quote($contact);

                        $createdAt = date('Y-m-d H:i:s');
                        $data[] = "($link, $title, $price, $phone, $kmRun, $productYear, $contact, 'oto_thien', \"$createdAt\")";
                        $childs[$indexX]->clear();
                    }
                    unset($childs);
                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentOtoThien($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentCafeAuto($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
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
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                foreach ($items as $indexL => $item) {

                    $link = @$item->find('.loph1 a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'cafe_auto')->where('link', $link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = @$item->find('.loph1 a', 0)->plaintext;
                    $price = @$item->find('div', 2)->find('.left-gia', 0)->plaintext;
                    $city = @$item->find('div', 2)->find('.right-gia', 0)->plaintext;
                    $productYear = @$item->find('div', 1)->find('.lopline', 2)->plaintext;
                    $attr = @$item->find('.lopline', 7)->find('a', 0)->attr;
                    $phone = isset($attr['onclick']) ? $attr['onclick'] : null;
                    $phone = str_replace(array("showfullphone(this,'", "')"), '', $phone);
                    $contact = @$item->find('.lopline', 6)->plaintext;

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

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
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

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentCafeAuto($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentBanOtoRe($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();
            if (empty($page)) {
                return;
            }

            $url = \config('wrap.url_site.banotore');
            $urlPaging = $url . 'ban-xe/p' . $page;

            $html = $this->loopFetchUrl($urlPaging);
            $items = null;
            if (is_object($html)) {
                $items = @$html->find('.post-list .post-item');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                $domain = substr($url, 0, -1);
                foreach ($items as $indexL => $item) {

                    $link = @$item->find('.pi-meta h3 a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'banotore')->where('link', $domain.$link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = @$item->find('.pi-meta h3 a', 0)->plaintext;
                    $datePost = @$item->find('.pi-meta span.date', 0)->plaintext;
                    $ul0 = @$item->find('.pi-meta ul', 0);
                    $price = @$ul0->find('li', 0)->plaintext; 

                    $ul1 = @$item->find('.pi-meta ul', 1);
                    $city = @$ul1->find('li', 1)->plaintext;

                    $contact = $phone = null;
                    $contactTemp = @$ul1->find('li', 2);
                    if (is_object($contactTemp)) {
                        $contact = $contactTemp->find('span', 1)->plaintext;
                    }
                    $phoneTemp = @$ul1->find('li', 3);
                    if (is_object($phoneTemp)) {
                        $phone = $phoneTemp->find('span', 1)->plaintext;
                    }

                    $productYear = $color = $kmRun = $content = '';
                    $temp = $this->getDetailBanOtoRe($domain . $link);
                    if (count($temp)) {
                        $productYear = $temp['productYear'];
                        $color = $temp['color'];
                        $kmRun = $temp['km_run'];
                        $content = $temp['content'];
                        unset($temp);
                    }

                    $title = $pdo->quote($title);
                    $datePost = $pdo->quote($datePost);
                    $link = $pdo->quote($domain . $link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $phone = $pdo->quote($phone);
                    $contact = $pdo->quote($contact);
                    $productYear = $pdo->quote($productYear);
                    $color = $pdo->quote($color);
                    $kmRun = $pdo->quote($kmRun);
                    $content = $pdo->quote($content);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $city, $productYear, $contact, $color, $kmRun, $content, 'banotore', $datePost, \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'contact', 'color', 'km_run', 'short_content', 'type', 'date_post', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                        'phone=VALUES(phone)', 'city=VALUES(city)', 'product_year=VALUES(product_year)', 'contact=VALUES(contact)',
                        'color=VALUES(color)', 'km_run=VALUES(km_run)', 'short_content=VALUES(short_content)',
                        'type=VALUES(type)', 'date_post=VALUES(date_post)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentBanOtoRe($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getContentSanXeHot($page, $totalPage, $dataOld)
    {
        try {
            $max_loop = false;
            if ($page <= 0 || empty($page) || $page > $totalPage) {
                return;
            }
            echo 'Page: ' . $page . "\n<br/>";
            flush();
            ob_flush();
            if (empty($page)) {
                return;
            }

            $url = \config('wrap.url_site.sanxehot');
            $urlPaging = $url . 'mua-ban-xe-pg' . $page;

            $html = $this->loopFetchUrl($urlPaging);
            $items = null;
            if (is_object($html)) {
                $items = @$html->find('.list-car li');
            }

            $data = array();
            if (!$items) {
                echo 'Item empty ' . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                unset($html);
                unset($items);
            } else {
                unset($html);

                $pdo = DB::connection()->getPdo();

                $domain = substr($url, 0, -1);
                foreach ($items as $indexL => $item) {

                    $link = @$item->find('div a', 0)->href;
                    if ($this->type !== 'all') {
                        $count = Contents::where('type', 'sanxehot')->where('link', $domain.$link)->count();
                        if (!empty($count)) {
    //                        $page = -1;
                            unset($count);
                            $max_loop = true;
                            $items[$indexL]->clear();
                            continue;
    //                        break;
                        }
                    }

                    $title = @$item->find('div a', 0)->plaintext;

                    $phone = $kmRun = $city = $price = $contact = null;
                    $table = @$item->find('.mota', 0);
                    if (is_object($table)) {
                        $kmRun = $table->find('tr', 0)->find('td', 0)->plaintext;
                        $price = $table->find('tr', 0)->find('td', 1)->plaintext;
                        $city = $table->find('tr', 2)->find('td', 1)->plaintext;
                        $contact = $table->find('tr', 3)->find('td', 1)->plaintext;
                        $phone = $table->find('tr', 4)->find('td', 1)->plaintext;
                    }

                    $productYear = $color = $content = '';
                    $temp = $this->getDetailSanXeHot($domain . $link);
                    if (count($temp)) {
                        $productYear = $temp['productYear'];
                        $color = $temp['color'];
                        $content = $temp['content'];
                        unset($temp);
                    }

                    $title = $pdo->quote($title);
                    $link = $pdo->quote($domain . $link);
                    $price = $pdo->quote($price);
                    $city = $pdo->quote($city);
                    $phone = $pdo->quote($phone);
                    $contact = $pdo->quote($contact);
                    $productYear = $pdo->quote($productYear);
                    $color = $pdo->quote($color);
                    $kmRun = $pdo->quote($kmRun);
                    $content = $pdo->quote($content);

                    $createdAt = date('Y-m-d H:i:s');
                    $data[] = "($link, $title, $price, $phone, $city, $productYear, $contact, $color, $kmRun, $content, 'sanxehot', \"$createdAt\")";

                    $items[$indexL]->clear();
                }
                unset($items);

                echo 'Data count: '. count($data) . "\n<br/>";
                echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
                echo '=========================================================================' . "\n<br/>";
                flush();
                ob_flush();

                if (count($data)) {
                    $fields = array('link', 'brand_car', 'price', 'phone', 'city', 'product_year', 'contact', 'color', 'km_run', 'short_content', 'type', 'created_at');
                    $values = array(
                        'link=VALUES(link)', 'brand_car=VALUES(brand_car)', 'price=VALUES(price)', 
                        'phone=VALUES(phone)', 'city=VALUES(city)', 'product_year=VALUES(product_year)', 'contact=VALUES(contact)',
                        'color=VALUES(color)', 'km_run=VALUES(km_run)', 'short_content=VALUES(short_content)',
                        'type=VALUES(type)', 'created_at=VALUES(created_at)');
                    $this->inserOrUpdate($fields, $data, $values);

                    unset($data);
                }
            }

            if ($max_loop && $page >= 2) {
                unset($max_loop);
                return;
            }

            if ($page > 0 || $page < $totalPage) {
                if ($dataOld === NULL) {
                    $page--;
                } else {
                    $page++;
                }
                $this->getContentSanXeHot($page, $totalPage, $dataOld);
            }
        } catch (\Exception $e) {
            Log::debug($e);
            return;
        }
    }

    protected function getDetailBonBanh($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('.tabbertab', 0);
        }
        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $temp = $html->find('.col #mail_parent', 4);
            if ($temp) {
                $color = $temp->find('.txt_input', 0);
                if ($color) {
                    $results['color'] = trim($color->plaintext);
                }
                unset($color);
                unset($temp);
            }

            unset($html);

            return $results;
        }
    }
    protected function getDetailCarmudiColor($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = $html->find('#details', 0);
        }
        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $temp = $html->find('.contents .left', 0);
            if ($temp) {
                $color = $temp->find('li', 0);
                if ($color) {
                    $results['color'] = str_replace('Nhóm màu xe', '', $color->plaintext);
                }
                unset($color);
                unset($temp);
            }

            unset($html);

            return $results;
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

    protected function getDetailMuaBan($url)
    {
        $html = $this->loopFetchUrl($url);
        if (!is_object($html)) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
        } else {
            $results = array();
            $phone = $html->find('.contact-mobile', 0);
            if (is_object($phone)) {
                $results['phone'] = trim($phone->plaintext);
            }
            $contact = $html->find('.contact-name', 0);
            if (is_object($contact)) {
                $results['contact'] = trim($contact->plaintext);
            }

            $shortContent = $html->find('.ct-tech', 0);
            if (is_object($shortContent)) {
                $results['shortContent'] = trim($shortContent->innertext());
            }
            $productYear = $html->find('.ct-tech ul li', 0);
            if (is_object($productYear)) {
                $results['productYear'] = $productYear->find('.item-value', 0)->plaintext;
            }
            $color = $html->find('.ct-tech ul li', 5);
            if (is_object($color)) {
                $results['color'] = $color->find('.item-value', 0)->plaintext;
            }

            unset($shortContent);
            unset($productYear);
            unset($color);
            unset($html);

            return $results;
        }
    }

    protected function getDetailOtoVietNam($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.osThreadFieldsDetailsWrapper', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['title'] = trim(@$html->find('.titleBar h1', 0)->plaintext);

            $results['city'] = trim(@$items->find('dl dd.city', 0)->plaintext);
            $results['contact'] = trim(@$items->find('dl dd.address', 0)->plaintext);
            $results['price'] = trim(@$items->find('dl dd.price', 0)->plaintext);
            $results['color'] = trim(@$items->find('dl dd.color', 0)->plaintext);
            $results['km_run'] = trim(@$items->find('dl dd.distance', 0)->plaintext);
            $results['shortContent'] = trim(@$html->find('.osThreadFieldsDetails', 0)->plaintext);
            unset($html);

            return $results;
        }
    }

    protected function getDetailBanXeHoi($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.infotechv2', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $color = $this->getColorBanXeHoi($html, 2);
            if ($color == null) {
                $color = $this->getColorBanXeHoi($html, 3);
            }
            $results = array(
                'color' => $color,
            );

            unset($html);

            return $results;
        }
    }

    protected function getColorBanXeHoi($obj, $num)
    {
        $colorText = null;
        $color = $obj->find('.colright .row', $num);
        if ($color) {
            $temp = $color->find('label', 0)->plaintext;
            if (preg_match('/xe/', $temp)) {
                $colorText = $color->find('span', 0)->plaintext;
            }
            unset($color);
        }
        return $colorText;
    }

    protected function getDetailChoXe($url)
    {
        $results = array();

        $html = $this->loopFetchUrl($url);
        if (!is_object($html)) {
            unset($html);
            return array();
        }

        echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
        echo '=========================================================================' . "\n<br/>";
        flush();
        ob_flush();

        $kmRunT = $html->find('.detail-xe', 1);
        if (is_object($kmRunT)) {
            $kmRun = $kmRunT->find('b', 0);
            if (is_object($kmRun)) {
                $results['kmRun'] = $kmRun->plaintext;
            }
            unset($kmRun);
        }
        $productYearT = $html->find('.detail-xe', 6);
        if (is_object($productYearT)) {
            $productYear = $productYearT->find('b', 0);
            if (is_object($productYear)) {
                $results['productYear'] = $productYear->plaintext;
            }
            unset($productYear);
        }
        $colorT = $html->find('.detail-xe', 8);
        if (is_object($colorT)) {
            $color = $colorT->find('b', 0);
            if (is_object($color)) {
                $results['color'] = $color->plaintext;
            }
            unset($color);
        }
        $shortContent = $html->find('.mo-ta', 0);
        if (is_object($shortContent)) {
            $results['shortContent'] = $shortContent->innertext();
        }
        $info = $html->find('.box-info-nban', 0);
        if (is_object($info)) {
            $a = $info->find('.btn-orange-48', 0);
            if ($a) {
                $attr = $a->href;
                $results['phone'] = str_replace('tel:', '', $attr);
                unset($attr);
            }
            unset($a);
        }

        unset($kmRunT);
        unset($productYear);
        unset($color);
        unset($shortContent);
        unset($info);
        unset($html);

        return $results;
    }

    protected function getDetailXe360($url)
    {
        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#content .inner', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $objContent = @$html->find('.controls', 0);
            $content = null;
            if (is_object($objContent)) {
                $content = trim($objContent->innertext());
            }
            $results['shortContent'] = $content;

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
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
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
        echo '========================================BEGIN Detail SanOto======================================' . "\n<br/>";
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#left-content', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
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

            $results['shortContent'] = trim(@$html->find('.table-striped ', 0)->parent()->innertext()) . trim(@$html->find('.table-striped ', 1)->parent()->innertext());

            echo '========================================END Detail SanOto======================================' . "\n<br/>"."\n<br/>"."\n<br/>";
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function getDetailEnBac($url)
    {
        echo '========================================BEGIN Detail ENBAC======================================' . "\n<br/>";
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.otoContent', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $results = array();
            $results['shortContent'] = trim($items->innertext());

            echo '========================================END Detail ENBAC======================================' . "\n<br/>". "\n<br/>". "\n<br/>";
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function getDetailBanOtoRe($url)
    {
        echo '========================================BEGIN Detail BAN OTO RE======================================' . "\n<br/>";
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('.info-detail', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $ul = $items->find('.left-content ul', 0);
            if(!is_object($ul)) {
                return array();
            }
            $color = $productYear = $kmRun = null;
            $color1 = $ul->find('li', 5);
            if (is_object($color1)) {
                $color2 = $color1->find('span.content', 0);
                if (is_object($color2)) {
                    $color = $color2->plaintext;
                }
                unset($color2);
            }
            unset($color1);

            $productYear1 = $ul->find('li', 2);
            if (is_object($productYear1)) {
                $productYear2 = $productYear1->find('span.content', 0);
                if (is_object($productYear2)) {
                    $productYear = $productYear2->plaintext;
                }
                unset($productYear2);
            }
            unset($productYear1);

            $kmRun1 = $ul->find('li', 4);
            if (is_object($kmRun1)) {
                $kmRun2 = $kmRun1->find('span.content', 0);
                if (is_object($kmRun2)) {
                    $kmRun = $kmRun2->plaintext;
                }
                unset($kmRun2);
            }
            unset($kmRun1);

            $results = array(
                'productYear' => $productYear,
                'km_run' => $kmRun,
                'color' => $color,
                'content' => trim($items->innertext()),
            );

            echo '========================================END Detail BAN OTO RE======================================' . "\n<br/>"."\n<br/>"."\n<br/>";
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function getDetailSanXeHot($url)
    {
        echo '========================================BEGIN Detail SAN XE HOT======================================' . "\n<br/>";
        flush();
        ob_flush();

        $html = $this->loopFetchUrl($url);
        $items = null;
        if (is_object($html)) {
            $items = @$html->find('#chitietxe', 0);
        }

        if (!$items) {
            echo 'Item empty ' . "\n<br/>";
            echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . "\n<br/>";
            echo '=========================================================================' . "\n<br/>";
            flush();
            ob_flush();
            return null;

            unset($html);
            unset($items);
        } else {
            $table = $items->find('table.info', 0);
            if(!is_object($table)) {
                return array();
            }
            $results = array(
                'productYear' => $table->find('tr', 2)->find('td', 1)->plaintext,
                'color' => $table->find('tr', 7)->find('td', 1)->plaintext,
                'content' => trim($html->find('#mota', 0)->innertext()),
            );

            echo '========================================END Detail SAN XE HOT======================================' . "\n<br/>"."\n<br/>"."\n<br/>";
            flush();
            ob_flush();
            unset($html);
            return $results;
        }
    }

    protected function loopFetchUrl($url)
    {
        try {
            echo 'url: ' . $url . "\n<br/>";
            flush();
            ob_flush();

            $html = HtmlDomParser::file_get_html($url);
            return $html;
        } catch (\Exception $e) {
            $this->step_fail++;
            echo 'Fail connecttion ' . "\n<br/>";
            echo 'Step fail: ' . $this->step_fail . "\n<br/>";
            flush();
            ob_flush();

            if ($this->step_fail <= 2) {
                sleep(5);
                $this->loopFetchUrl($url);
            } else {
                $this->step_fail = 0;
            }
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