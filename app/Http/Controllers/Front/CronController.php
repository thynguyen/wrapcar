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

        $this->getCafeAuto();

        $this->getTheGioiXeOto();

        $this->getOtoThien();


        //$this->addDataToSolr();
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