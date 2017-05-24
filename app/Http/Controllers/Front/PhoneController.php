<?php
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Front\BaseController;

class PhoneController extends BaseController
{
    public function index(Request $request)
    {
        ini_set('max_execution_time', 0);
        $row = \App\Models\Contents::where('is_owner', 1)->first();
        $total = 0;
        if ($row !== null) {
            $total = $row->count();
        }
        $limit = 1000;
        $step = 0;

        echo "======BEGIN=========<br/>";
        echo 'Total: '. $total . '<br/>';
        flush();
        ob_flush();

        $total_row = \App\Models\Phones::count();
        $max_time = null;
        if ($total_row) {
            $row = \DB::table("phones")->orderBy('created_at', 'DESC')->take(1)->first();
            $max_time = $row->created_at;
        }

        $this->stepRun($total, $step, $limit, $max_time);

        // Begin update into content
        $query = \App\Models\Phones::where('count', '>', 1);
        if (!empty($max_time)) {
            $query->where('created_at', '>', $max_time);
        }
        $total_phone = $query->count();
        $limit = 500;

        echo 'Total phone: '. $total_phone . '<br/>';
        flush();
        ob_flush();
        $this->stepRunNotOwner($total_phone, $step, $limit);

        echo "======END=========<br/>";
        flush();
        ob_flush();
    }

    protected function hasNumber($string)
    {
        if (preg_match("/[0-9]{4}/", $string)) {
            return true;
        }
        return false;
    }

    protected function stepRun($total, $step, $limit, $max_time)
    {
        if ($total % $limit === 0) {
            $floor = floor($total/$limit);
        } else {
            $floor = floor($total/$limit) + 1;
        }

        echo '======BEGIN Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '==============<br/>';
        flush();
        ob_flush();

        $offset = $step * $limit;

        if ($floor > 0 && $step >= $floor) {
            return;
        }
        unset($floor);

        echo 'Step: ' . $step . '<br/>';
        echo 'Offet: ' . $offset . '<br/>';
        echo 'Limit: ' . $limit . '<br/>';
        flush();
        ob_flush();

        $query = \DB::table('contents')->select('id', 'phone', 'type', 'contact', 'short_content', 'created_at');
        if (!empty($max_time)) {
            $query->where('created_at', '>', $max_time);
        }
        $query->where('is_owner', 1);
        $results = $query->skip($offset)->take($limit)->get();

        if (!$results->count()) {
            unset($results);
            unset($offset);
            unset($limit);
            return;
        }

        $data = array();
        $pdo = \DB::connection()->getPdo();
        foreach ($results as $result) {
            /*
            if ($this->hasNumber($result->phone)) {
                $phone = $this->parsePhone($result->phone);
            } else if ($this->hasNumber($result->contact)) {
                if (preg_match("/^[+84]/", $result->contact)) {
                    $phone = $this->parsePhone($result->contact);
                } else {
                    $phone = $this->parseContact($result->contact);
                    var_dump($phone);
                    flush();
                    ob_flush();
                }
            } else {
                $phone = $this->parseShortContent($result->short_content);
            }
             * 
             */

            $phone_temp = str_replace(' ', '', $result->phone);
            if (preg_match('/[0-9]{4}/', $phone_temp)) {
                $phone = $this->getPhoneFromString($phone_temp);
            } else if (preg_match('/[0-9]{4}/', $result->contact)) {
                $phone = $this->getPhoneFromString($result->contact);
            } else {
//                $phone = $this->getPhoneFromString($result->short_content);
                $phone = $this->parseShortContent($result->short_content);
                $phone = $this->getPhoneFromString($phone);
            }
//            var_dump($phone);
//            var_dump($result->id);
//            echo '<br/>';
//            flush();
//            ob_flush();

            $phone = $this->replaceString($phone);
            $phone = preg_replace('/[^A-Za-z0-9\-]/', '', $phone);

            $length = strlen($phone);
            if (empty($phone) || $length < 10 || $length > 15) {
                continue;
            }
            
            $phone = $pdo->quote($phone);
            $type = $pdo->quote($result->type);
            $count = 1;
            $createdAt = date('Y-m-d H:i:s');
            $contentId = $result->id;

            $data[] = "({$contentId}, {$phone}, {$type}, {$count}, \"{$createdAt}\")";
            unset($phone);
            unset($contentId);
            unset($createdAt);
            unset($count);
            unset($type);
        }
        unset($pdo);
        unset($results);

        if (count($data)) {
            $fields = array('content_ids', 'phone_number', 'type', 'count', 'created_at');
            $values = array('content_ids = CONCAT_WS(if(content_ids is null OR content_ids = "", "", ","),content_ids, VALUES(content_ids))
', 'phone_number=VALUES(phone_number)', 'type=VALUES(type)', 'count=count+1', 'created_at=VALUES(created_at)');
            $this->insertOrUpdatePhone($fields, $data, $values);
            unset($data);
        }

        echo '======END Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '=========<br/>';
        echo '<br/>';
        flush();
        ob_flush();

        $step++;
        return $this->stepRun($total, $step, $limit, $max_time);
    }

    protected function getPhoneFromString($string)
    {
        if (preg_match('/[0-9]{4}/', $string)) {

            preg_match('/[0-9]{4}+\S+[0-9]{2,3}+\S+[0-9]+\S/', $string, $matches1);
            $part = explode(',', @$matches1[0]);

            if (isset($part[0]) && !empty($part[0])) {
                return $part[0];
            } else if (isset($matches1[0]) && !empty($matches1[0])) {
                return $matches1[0];
            }

            preg_match('/[0-9]+\.+[0-9]+\.+[0-9]+\s/', $string, $matches2);
            if (isset($matches2[0])) {
                return $matches2[0];
            }

//            preg_match('/\S+[0-9]+\S/', $string, $matches3);
//            if (isset($matches3[0])) {
//                return $matches3[0];
//            }
            preg_match('/ĐT:\s+[0-9]+\S/', $string, $matches3);
            if (isset($matches3[0])) {
                return $matches3[0];
            }

            preg_match('/LH:\s+[0-9]+\S/', $string, $matches4);
            if (isset($matches4[0])) {
                return $matches4[0];
            }
            preg_match('/LH:\s+[0-9]+\S/', $string, $matches5);
            if (isset($matches5[0])) {
                return $matches5[0];
            }
            preg_match('/Hotline:\s+[0-9]+\S/', $string, $matches6);
            if (isset($matches6[0])) {
                return $matches6[0];
            }
            preg_match('/ĐT.\s+[0-9]+\S/', $string, $matches7);
            if (isset($matches7[0])) {
                return $matches7[0];
            }
            preg_match('/LH :\s+[0-9]+\S/', $string, $matches8);
            if (isset($matches8[0])) {
                return $matches8[0];
            }
            preg_match('/\S+[0-9\,]/', $string, $matches9);
            if (isset($matches9[0])) {
                return $matches9[0];
            }

            return null;
        }
    }

    protected function stepRunNotOwner($total, $step, $limit)
    {
        if ($total % $limit === 0) {
            $floor = floor($total/$limit);
        } else {
            $floor = floor($total/$limit) + 1;
        }

        echo '======BEGIN Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '==============<br/>';
        flush();
        ob_flush();

        $offset = $step * $limit;

        if ($floor > 0 && $step >= $floor) {
            return;
        }
        unset($floor);

        echo 'Step: ' . $step . '<br/>';
        echo 'Offet: ' . $offset . '<br/>';
        echo 'Limit: ' . $limit . '<br/>';
        flush();
        ob_flush();

        $query = \DB::table('phones')->select('phone_number', 'type', 'content_ids')->where('count', '>', 1);
        $results = $query->skip($offset)->take($limit)->get();

        if (!$results->count()) {
            unset($results);
            unset($offset);
            unset($limit);
            return;
        }

        $pdo = \DB::connection()->getPdo();
        $data = array();
        foreach ($results as $result) {
            $parts = explode(',', $result->content_ids);
            if (sizeof($parts)) {
                foreach ($parts as $part) {
                    $data[] = "({$part}, 0)";
                }
            }
            unset($parts);
        }
        unset($pdo);
        unset($results);

        if (count($data)) {
//            echo '<pre>';print_r($data);
//            flush();
//            ob_flush();
            $fields = array('id', 'is_owner');
            $values = array('id=VALUES(id)', 'is_owner=VALUES(is_owner)');
            $this->inserOrUpdate($fields, $data, $values);
            unset($data);
        }

        $step++;
        return $this->stepRunNotOwner($total, $step, $limit);
    }

    public function pushPhoneDefine()
    {
        ini_set('max_execution_time', 0);

        $now = date('Y-m-d');

        $row = \App\Models\PhoneExcept::where(\DB::raw('DATE_FORMAT(updated_at, "%Y-%m-%d")'), '=', $now)->first();
        if ($row === null) {
            echo 'Please update data before run';
            return;
        }
        if (empty($row->content)) {
            echo 'Please update data before run';
            return;
        }
        $phones = explode(',', $row->content);
        $phones = array_unique($phones);
        echo "======BEGIN=========<br/>";
        echo "Data: ". implode(',', $phones) . '<br/>';
        flush();
        ob_flush();

        $step = 0;
        $this->loopDefinePhone($step, $phones);
        unset($phones);

        return;
    }

    protected function loopDefinePhone($step, $phones)
    {
        if (!isset($phones[$step])) {
            return;
        }

        echo '======BEGIN Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '==============<br/>';
        echo 'Step: ' . $step . '<br/>';
        flush();
        ob_flush();

        $phone = trim($phones[$step]);
        if (!empty($phone)) {
            echo 'Phone: ' . '<br/>';
            var_dump($phone);
            flush();
            ob_flush();

            $query = \App\Models\Contents::select('id', 'updated_at')
                ->where(function($query) use ($phone) {
                    $query->where(\DB::raw('REPLACE("phone", "+84", "0")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("phone", ".", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("phone", " ", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere('phone', 'LIKE', "%{$phone}%");
                })
                ->orWhere(function($query) use ($phone) {
                    $query->where(\DB::raw('REPLACE("contact", "+84", "0")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("contact", ".", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("contact", " ", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere('contact', 'LIKE', "%{$phone}%");
                })
                ->orWhere(function($query) use ($phone) {
                    $query->where(\DB::raw('REPLACE("short_content", "+84", "0")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("short_content", ".", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere(\DB::raw('REPLACE("short_content", " ", "")'), 'LIKE', "%{$phone}%")
                        ->orWhere('short_content', 'LIKE', "%{$phone}%");
                });

            $rows = $query->get();
            $data = array();
            if ($rows !== NULL) {
                foreach ($rows as $row) {
                    $updatedAt = $row->updated_at;
                    $data[] = "({$row->id}, 0, \"{$updatedAt}\")";
                }
            }
            unset($rows);

            if (count($data)) {
                $fields = array('id', 'is_owner', 'updated_at');
                $values = array('id=VALUES(id)', 'is_owner=VALUES(is_owner)', 'updated_at=VALUES(updated_at)');
                $this->inserOrUpdate($fields, $data, $values);
                unset($data);
            }
            echo '======END Memory: ' . round((memory_get_usage()) / 1024 / 1024) . '==============<br/>';
            flush();
            ob_flush();

            $step++;
            return $this->loopDefinePhone($step, $phones);
        }
    }

    protected function replaceString($string)
    {
//        if ($string == '0975701704') {
//            $string = str_replace('0906221685', '', $string);
//        }
//        if ($string == '01254823333 s' || $string == '01635096558 s') {
//            $string = str_replace('s', '', $string);
//        }

//        $return = str_replace(array('Ms.Hảo:', 'MR QUÂN', 'Dương Đình Anh', 'BMW Đà Nẵng-Đà Nẵng', 'Mr Thắng', 'Mr Đạt 0977299882', 
//            'dulichnetviet', ' Liên hệ', 'Mr Tuyến', '0904 88 - 0904616997 -', '- 093 4 - 094 999 3388', ' (A.Tu?n)', ' (C.Giang)',
//            'Hoặc 0947116996', 'ĐT : '), '', $string);
        
        $return = str_replace(array('ĐT: ', 'ĐT:', 'LH:', 'LH :', 'Hotline:', 'ĐT.', ' ', '', 'Hyundai10', '4695x1815x1825', '12240x2460x15201310', '79507780x23701550x1330', '2015-20161010', '40059kG1310', '39309mm1310', '85807640x24801800x28001800', '7690x2330x1420', '<b>'), '', $string);
        return trim($return);
    }

    protected function parsePhone($phone)
    {
        $part = explode(',', $phone);
        $phone1 = isset($part[0]) ? trim($part[0]) : null;
        $phone2 = isset($part[1]) ? trim($part[1]) : null;

        if (!empty($phone1)) {
            return $phone1;
        }
        if (!empty($phone2)) {
            return $phone2;
        }
        return $phone;
    }

    protected function parseContact($text)
    {
        $strPost = strpos($text, 'ĐT:');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 4);
            return $this->splitContact($value);
        }

        $strPost = strpos($text, 'Đt:');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 4);
            return $this->splitContact($value);
        }

        $strPost = strpos($text, 'LH :');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 4);
            return $this->splitContact($value);
        }

        $strPost = strpos($text, 'ĐT.');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 4);
            return $this->splitContact($value);
        }
    }
    
    protected function parseShortContent($text)
    {
        $strPost = strpos($text, 'Hotline: ');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 8);
            return $value;
        }

        $strPost = strpos($text, 'ĐT:');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 4, 13);
            return $value;
        }

        $strPost = strpos($text, 'LH:');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 3, 11);
            return $value;
        }
        $strPost = strpos($text, 'lh:');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 3, 11);
            return $value;
        }
        $strPost = strpos($text, 'LH: Mr.Vinh');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 12, 11);
            return $value;
        }
        $strPost = strpos($text, 'LH: Mr Điệp');
        if ($strPost !== false) {
            $value = substr($text, $strPost + 15, 11);
            return $value;
        }
    }

    protected function splitContact($value)
    {
        $part = explode('-', $value);
        $part1 = isset($part[0]) ? trim($part[0]) : null;
        $part2 = isset($part[1]) ? trim($part[1]) : null;

        if (!empty($part1)) {
            return $part1;
        }
        if (!empty($part2)) {
            return $part2;
        }
        return $value;
    }

    protected function insertOrUpdatePhone($fields, $data, $values)
    {
        try {
            \DB::insert("INSERT INTO phones (".  implode(',', $fields).") "
                    . "VALUES ".  implode(',', $data) . " ON DUPLICATE KEY UPDATE " . implode(',', $values));

        } catch (\Exception $e) {
            echo $e->getMessage();exit;
        }
    }
}