<?php
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\BaseController;
use Illuminate\Support\Facades\Mail;

class CronController extends BaseController
{
    public function index(Request $request)
    {
        $this->getChoTot();

        $this->getCarmundi();
        $this->getBonBanh();
        $this->getMuaBan();

        $this->getOtoVietNam(1, 20, '304');
        $this->getOtoVietNam(1, 20, '305');

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

        $this->getCafeAuto();

        $this->getTheGioiXeOto();

        $this->getOtoThien();

        $this->getBanOtoRe();

        $this->getSanXeHot();


        //$this->addDataToSolr();
    }

    public function cronManual()
    {
//        $this->getChoTot();

        $this->getCarmundi();

        $this->getMuaBan();

        $this->getBanXeHoi();

        $this->getMuaBanNhanh(); // still dump

        $this->getOtoVietNam(1, 20, '304');
        $this->getOtoVietNam(1, 20, '305');
    }

    public function bookAuto(Request $request)
    {
        echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
        echo '=========================================================================<br/>';
        flush();
        ob_flush();

        $settings = \App\Models\Settings::where('status', 1)->get();
        if ($settings === NULL) {
            echo 'Không có data để setting. Vui lòng setting trước khi chạy cron';
            exit;
        }
        $arrExecs = array();
        foreach ($settings as $setting) {
            $arrExecs[$setting->user_id][] = array(
                'id' => $setting->id,
                'brand_car' => $setting->brand_car,
                'keyword' => $setting->keyword,
                'product_year' => $setting->product_year,
                'city' => $setting->city,
                'hop_so' => $setting->hop_so,
                'color' => $setting->color,
                'updated_at' => date('Y-m-d H:i:s', strtotime($setting->updated_at)),
            );
        }

        foreach ($arrExecs as $key => $values)
        {
            foreach ($values as $index => $value) {
                $content = new \App\Models\Contents();
                $rows = $content->getBookAuto($value);
                if ($rows->count() === 0) {
                    continue;
                }
                $arrExecs[$key][$index]['links'] = $rows;
            }
        }

        foreach ($arrExecs as $user_id => $rows) {
            $config = \App\Models\Config::where('user_id', $user_id)->first();
            if ($config === null) {
                continue;
            }

            $strHtml = '';
            foreach ($rows as $keyIndex => $row) {
                $links = isset($row['links']) ? $row['links'] : null;
                if (empty($links) || $links->count() === 0) {
                    unset($arrExecs[$user_id][$keyIndex]);
                    continue;
                }

                echo '======Information=========<br/>';
                echo 'Total send link: ' . $links->count(). '<br/>';
                flush();
                ob_flush();

                $strHtml .= \View::make('cron.email', ['setting' => $row])->render();

                $rowObj = \App\Models\Settings::find($row['id']);
                if ($rowObj) {
                    $rowObj->updated_at = date('Y-m-d H:i:s');
                    $rowObj->save();
                }
            }
            if (empty($strHtml)) {
                continue;
            }

            $emailTitle = 'Xe mới nhất...';
            $toEmail = $config->email;
            Mail::send([], [], function($message) use ($toEmail, $emailTitle, $strHtml) {
                $message->to($toEmail, '')->subject($emailTitle)
                    ->setBody($strHtml, 'text/html');
            });
        }
        echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
        echo '=========================================================================<br/>';
        flush();
        ob_flush();

        echo "DONE";
        exit;
    }

    public function testBookAuto(Request $request)
    {
        echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
        echo '=========================================================================<br/>';
        flush();
        ob_flush();

        $arrExecs = array(
            3 => array(
                array(
                    'brand_car' => 'Kia',
                    'keyword' => 'morning',
                    'product_year' => '2016',
                    'city' => 'Hà Nội',
                    'hop_so' => 'AT',
                    'color' => 'Xám',
                    'updated_at' => '2017-02-17 10:10:10',
                )
            )
        );

        foreach ($arrExecs as $key => $values)
        {
            foreach ($values as $index => $value) {
                $content = new \App\Models\Contents();
                $rows = $content->getBookAuto($value);
                if ($rows->count() === 0) {
                    continue;
                }
                $arrExecs[$key][$index]['links'] = $rows;
            }
        }

        foreach ($arrExecs as $user_id => $rows) {
            $config = \App\Models\Config::where('user_id', $user_id)->first();
            if ($config === null) {
                continue;
            }

            $strHtml = '';
            foreach ($rows as $keyIndex => $row) {
                $links = isset($row['links']) ? $row['links'] : null;
                if (empty($links) || $links->count() === 0) {
                    unset($arrExecs[$user_id][$keyIndex]);
                    continue;
                }

                echo '======Information=========<br/>';
                echo 'Total send link: ' . $links->count(). '<br/>';
                flush();
                ob_flush();

                $strHtml .= \View::make('cron.email', ['setting' => $row])->render();
            }
            if (empty($strHtml)) {
                continue;
            }
            echo $strHtml . '<br/>';
            echo '=======================================================<br/>';
            flush();
            ob_flush();
        }
        echo 'Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '<br/>';
        echo '=========================================================================<br/>';
        flush();
        ob_flush();

        echo "DONE";
        exit;
    }

    public function testEmail()
    {
        $toEmail = 'thynguyen222@gmail.com';
        $emailTitle = 'Test email';
        $strHtml = 'Bla bla bla';
        Mail::send([], [], function($message) use ($toEmail, $emailTitle, $strHtml) {
            $message->to($toEmail, '')->subject($emailTitle)
                ->setBody($strHtml, 'text/html');
        });
        echo 'DONE';
        exit;
    }

    protected function addDataToSolr()
    {
        $options = array(
            'hostname' => 'localhost',
            'port'     => 8983,
            'path'     => 'solr/admin',
        );

        $client = new \SolrClient($options);

        $contents = \App\Models\Contents::all();
        if ($contents === NULL) {
            echo 'No data update';
            return;
        }
        echo '=========Begin push data to Solr=========';
        flush();
        ob_flush();
        $i = 0;
        foreach ($contents as $content) {
            $i++;

            echo $i . ". Data: {$content->id}, {$content->link}, {$content->code_car_site}, {$content->color}, {$content->km_run},"
            . "{$content->product_year}, {$content->brand_car}, {$content->price}, {$content->contact}, {$content->phone},"
            . "{$content->city}" . "\n<br/>";
            flush();
            ob_flush();

            $doc = new \SolrInputDocument();
            $doc->addField('id', $content->id);
            $doc->addField('link', trim($content->link));
            $doc->addField('code_car_site', trim($content->code_car_site));
            $doc->addField('color', trim($content->color));
            $doc->addField('km_run', trim($content->km_run));
            $doc->addField('product_year', trim($content->product_year));
            $doc->addField('brand_car', trim($content->brand_car));
            $doc->addField('price', trim($content->price));
            $doc->addField('contact', trim($content->contact));
            $doc->addField('phone', trim($content->phone));
            $doc->addField('city', trim($content->city));
            $doc->addField('short_content', trim($content->short_content));
            $doc->addField('date_post', trim($content->date_post));
            $updateResponse = $client->addDocument($doc);
        }

        echo '=========End push data to Solr=========';
        flush();
        ob_flush();
        return;
    }
}