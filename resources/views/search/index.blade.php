<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Search Car</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        
        <!-- Bootstrap 3.3.5 -->
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        <script src="{{asset('js/jQuery-2.1.4.min.js')}}"></script>

        <!-- Styles -->
        <style>
            
        </style>
    </head>
    <body>
        <div class="container">

            <form name="f" id="f" action="{{ route('search_index') }}" method="GET">
                <input type="textbox" name="keyword" value="{{ $keyword }}" />

                <input type="submit" value="Search" />
            </form>

            @if (isset($pagination) && count($pagination))
                @foreach ($pagination as $item)
                <div style="border-bottom: 1px dotted gray;">
                    <h3>
                        <a href="{{ $item->link }}">{{ $item->brand_car }}</a>
                    </h3>
                    <div>Giá: {{ str_replace('Giá:', '', $item->price) }}</div>
                    <div>Liên hệ: {{ $item->contact }}</div>
                    <div>Thành phố: {{ $item->city }}</div>
                    <div>
                        {!! $item->short_content !!}
                    </div>
                </div>
                @endforeach

                <div>
                    {!! $pagination->appends(['keyword' => $keyword])->render() !!}
                </div>
            @endif
        </div>
        <script>
            $(document).ready(function(){
                $('.pagination a').click(function () {
                    href = $(this).attr('href');
                    $('#f').attr('action', href);
                    $('#f').submit();
                });
            });
        </script>
    </body>
</html>

