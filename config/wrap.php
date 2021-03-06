<?php
define('USER_STATUS_WAITING_VALIDATE', 0);
define('USER_STATUS_VALIDATE', 1);

define('ROLE_LICENSEE', 1);
define('ROLE_CONTROLLER', 2);

return [
    'roles' => array(
        'admin' => 1,
        'sale' => 2,
    ),
    'ban_xe_hoi' => array(
        'xe-acura',
        'xe-kia',
        'xe-audi',
        'xe-lamborghini',
        'xe-landrover',
        'xe-bentley',
        'xe-baic',
        'xe-lexus',
        'xe-bmw',
        'xe-lincoln',
        'xe-luxgen',
        'xe-cadillac',
        'xe-maserati',
        'xe-mazda', 
        'xe-chevrolet',
        'xe-mercedes-benz',
        'xe-mini',
        'xe-mitsubishi',
        'xe-nissan',
        'xe-peugeot',
        'xe-porsche',
        'xe-renault',
        'xe-rolls-royce',
        'xe-ferrari', 
        'xe-ssangyong',
        'xe-gmc',
        'xe-subaru',
        'xe-suzuki',
        'xe-hyundai',
        'xe-toyota',
        'xe-infiniti',
        'xe-isuzu',
        'xe-volkswagen',
        'xe-volvo',
    ),
    'url_site' => array(
        'bonbanh' => 'http://www.bonbanh.com/',
        'muaban'  => 'https://muaban.net/',
        'chotot'  => 'https://xe.chotot.com/',
        'chotot_gateway' => 'https://gateway.chotot.com/v1/public/ad-listing?region=&cg=2010&page=%d&sp=0&limit=%d&o=%s&st=s,k',
        'chotot_gateway_detail' => 'https://gateway.chotot.com/v1/public/ad-listing/%s',
        'otovietnam'  => 'https://www.otovietnam.com/',
        'carmudi' => 'https://www.carmudi.vn/',
        'banxehoi' => 'http://banxehoi.com/',
        'choxe' => 'https://choxe.net/',
        'xe360' => 'http://xe360.vn/',
        'xe5giay' => 'https://www.5giay.vn/',
        'sanotovn' => 'http://sanotovietnam.com.vn/',
        'muabanoto' => 'http://www.muabanoto.vn/',
        'otos' => 'http://otos.vn/',
        'muabannhanh' => 'https://muabannhanh.com/',
        'rongbay' => 'http://rongbay.com/',
        'enbac' => 'http://oto.enbac.com/',
        'thegioixeoto' => 'http://thegioixeoto.com/',
        'otothien' => 'https://otothien.vn/',
        'cafeauto' => 'http://cafeauto.vn/',
        'banotore' => 'https://banotore.com/',
        'motoring' => 'http://motoring.vn/',
        'sanxehot' => 'https://www.sanxehot.vn/',
    ),
    'brands' => array(
        '' => '--Thương hiệu--',
        'Acura' => 'Acura',
        'Aston Martin' => 'Aston Martin',
        'Audi' => 'Audi',
        'Bentley' => 'Bentley',
        'BMW' => 'BMW',
        'Buick' => 'Buick',
        'BYD' => 'BYD',
        'Cadillac' => 'Cadillac',
        'Chevrolet' => 'Chevrolet',
        'Chrysler' => 'Chrysler',
        'Citroen' => 'Citroen',
        'Daewoo' => 'Daewoo',
        'Dodge' => 'Dodge',
        'Ferrari' => 'Ferrari',
        'Fiat' => 'Fiat',
        'Ford' => 'Ford',
        'Geely' => 'Geely',
        'GMC' => 'GMC',
        'Haima' => 'Haima',
        'Honda' => 'Honda',
        'Hummer' => 'Hummer',
        'Hyundai' => 'Hyundai',
        'Infiniti' => 'Infiniti',
        'Isuzu' => 'Isuzu',
        'Jaguar' => 'Jaguar',
        'Jeep' => 'Jeep',
        'JRD' => 'JRD',
        'Kia' => 'Kia',
        'Lamborghini' => 'Lamborghini',
        'Land Rover' => 'Land Rover',
        'Land Rover' => 'Land Rover',
        'Lexus' => 'Lexus',
        'Lifan' => 'Lifan',
        'Luxgen' => 'Luxgen',
        'Maserati' => 'Maserati',
        'Maybach' => 'Maybach',
        'Mazda' => 'Mazda',
        'Mercedes' => 'Mercedes',
        'Mercury' => 'Mercury',
        'Merkur' => 'Merkur',
        'MINI' => 'MINI',
        'Mitsubishi' => 'Mitsubishi',
        'Nissan' => 'Nissan',
        'Peugeot' => 'Peugeot',
        'Plymouth' => 'Plymouth',
        'PMC' => 'PMC',
        'Porsche' => 'Porsche',
        'Ranger Rover' => 'Ranger Rover',
        'Renault' => 'Renault',
        'Rolls Royce' => 'Rolls Royce',
        'Saab' => 'Saab',
        'Saturn' => 'Saturn',
        'Scion' => 'Scion',
        'Smart' => 'Smart',
        'Ssangyong' => 'Ssangyong',
        'Steering' => 'Steering',
        'Subaru' => 'Subaru',
        'Suzuki' => 'Suzuki',
        'Tesla' => 'Tesla',
        'Toyota' => 'Toyota',
        'Volkswagen' => 'Volkswagen',
        'Volvo' => 'Volvo',
    ),
    'hop_so_list' => array(
        '' => '--Hộp số--',
        'AT' => 'AT',
        'MT' => 'MT',
    ),
    'color_list' => array(
        '' => '--Màu--',
        'Bạc' => 'Bạc',
        'Cam' => 'Cam',
        'Cát' => 'Cát',
        'Đỏ' => 'Đỏ',
        'Đồng' => 'Đồng',
        'Đen' => 'Đen',
        'Ghi' => 'Ghi',
        'Hồng' => 'Hồng',
        'Kem' => 'Kem',
        'Nâu' => 'Nâu',
        'Tím' => 'Tím',
        'Trắng' => 'Trắng',
        'Vàng' => 'Vàng',
        'Xanh' => 'Xanh',
        'Xám' => 'Xám',
    ),
    'city_list' => array(
        '' => '--Toàn quốc--',
        'Hà Nội' => 'Hà Nội',
        'TP HCM' => 'TP HCM',
        'An Giang' => 'An Giang',
        'Bà Rịa - Vũng Tàu' => 'Bà Rịa - Vũng Tàu',
        'Bắc Giang' => 'Bắc Giang',
        'Bắc Kạn' => 'Bắc Kạn',
        'Bạc Liêu' => 'Bạc Liêu',
        'Bắc Ninh' => 'Bắc Ninh',
        'Bến Tre' => 'Bến Tre',
        'Bình Định' => 'Bình Định',
        'Bình Dương' => 'Bình Dương',
        'Bình Thuận' => 'Bình Thuận',
        'Cà Mau' => 'Cà Mau',
        'Cần Thơ' => 'Cần Thơ',
        'Cao Bằng' => 'Cao Bằng',
        'Đà Nẵng' => 'Đà Nẵng',
        'Đắk Lắk' => 'Đắk Lắk',
        'Đắk Nông' => 'Đắk Nông',
        'Điện Biên' => 'Điện Biên',
        'Đồng Nai' => 'Đồng Nai',
        'Đồng Tháp' => 'Đồng Tháp',
        'Gia Lai' => 'Gia Lai',
        'Hà Giang' => 'Hà Giang',
        'Hà Nam' => 'Hà Nam',
        'Hà Tĩnh' => 'Hà Tĩnh',
        'Hải Dương' => 'Hải Dương',
        'Hải Phòng' => 'Hải Phòng',
        'Hậu Giang' => 'Hậu Giang',
        'Hòa Bình' => 'Hòa Bình',
        'Hưng Yên' => 'Hưng Yên',
        'Khánh Hòa' => 'Khánh Hòa',
        'Kiên Giang' => 'Kiên Giang',
        'Kon Tum' => 'Kon Tum',
        'Lai Châu' => 'Lai Châu',
        'Lâm Đồng' => 'Lâm Đồng',
        'Lạng Sơn' => 'Lạng Sơn',
        'Lào Cai' => 'Lào Cai',
        'Long An' => 'Long An',
        'Nam Định' => 'Nam Định',
        'Nghệ An' => 'Nghệ An',
        'Ninh Bình' => 'Ninh Bình',
        'Ninh Thuận' => 'Ninh Thuận',
        'Phú Yên' => 'Phú Yên',
        'Phú Thọ' => 'Phú Thọ',
        'Quảng Bình' => 'Quảng Bình',
        'Quảng Nam' => 'Quảng Nam',
        'Quảng Ngãi' => 'Quảng Ngãi',
        'Quảng Ninh' => 'Quảng Ninh',
        'Quảng Trị' => 'Quảng Trị',
        'Sóc Trăng' => 'Sóc Trăng',
        'Sơn La' => 'Sơn La',
        'Tây Ninh' => 'Tây Ninh',
        'Thái Bình' => 'Thái Bình',
        'Thái Nguyên' => 'Thái Nguyên',
        'Thanh Hóa' => 'Thanh Hóa',
        'Thừa Thiên Huế' => 'Thừa Thiên Huế',
        'Tiền Giang' => 'Tiền Giang',
        'Trà Vinh' => 'Trà Vinh',
        'Tuyên Quang' => 'Tuyên Quang',
        'Vĩnh Long' => 'Vĩnh Long',
        'Vĩnh Phúc' => 'Vĩnh Phúc',
        'Yên Bái' => 'Yên Bái',
    ),
    'time_list' => array(
        '' => 'Mọi lúc',
        'hour' => 'Giờ qua',
        '24_hour' => '24 giờ qua',
        'week' => 'Tuần qua',
        'month' => 'Tháng qua',
        'year' => 'Năm qua',
    ),
    'time_list_value' => array(
        'hour' => '-1 hour',
        '24_hour' => '-24 hours',
        'week' => '-1 week',
        'month' => '-1 month',
        'year' => '-1 year',
    ),
];