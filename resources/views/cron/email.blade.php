<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            Thông tin cần tìm<br/>
            Nhãn hiệu: {{ $setting['brand_car'] }}<br/>
            Loại: {{ $setting['keyword'] }}<br/>
            Năm: {{ $setting['product_year'] }}<br/>

            @if (!empty($setting['city']))
            Thành phố: {{ $setting['city'] }}<br/>
            @endif

            @if (!empty($setting['hop_so']))
            Hộp số: {{ $setting['hop_so'] }}<br/>
            @endif

            @if (!empty($setting['color']))
            Màu: {{ $setting['color'] }}<br/>
            @endif
        </td>
    </tr>

    @if(isset($setting['links']) && count($setting['links']))
    @foreach($setting['links'] as $item)
    <tr>
        <td>{{ $item->link or null }}</td>
    </tr>
    @endforeach
    @endif;
</table>