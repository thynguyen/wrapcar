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
            .text-error { color: red; }
            .box {
                border-radius: 3px;
                margin-bottom: 20px;
                position: relative;
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="col-md-6" style="margin-bottom: 20px;">
                @if (Auth::check())
                    <a href="{{ route('logout') }}" class="btn btn-info">Logout</a>
                    Xin chào <strong>{{ Auth::user()->name }}</strong>
                    <a href="{{ route('setting_index') }}" class="btn btn-info pull-right">List book xe</a>
                @else
                    <a href="{{ route('auth_login') }}" class="btn btn-info">Login</a>
                @endif
            </div>
            <div style="clear: both;"></div>

            <div class="col-md-6">
                <div class="box box-info" style="margin-top: 5px;">
                    <form name="f" id="f" action="{{ route('search_index') }}" method="GET">
                        <div class="box-body">
                            <div class="form-group">
                                <div class="col-sm-10">
                                    <input type="textbox" class="form-control" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Keyword" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-5" style="margin-top: 5px;">
                                    <select name="time" class="form-control">
                                        @if(isset($timeList) && count($timeList))
                                        @foreach($timeList as $val => $item)
                                        <option value="{{ $val }}" @if ($val == $time) selected="selected" @endif>{{ $item }}</option>
                                        @endforeach
                                        @endif
                                  </select>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <input class="btn btn-default" type="submit" value="Search" name="submit" />
                        </div>
                    </form>
                </div>
            </div>
            <div style="clear: both;"></div>

            <div class="col-xs-12">
                @if (isset($pagination) && count($pagination))
                    @foreach ($pagination as $item)
                    <div class="box-body" style="border-bottom: 1px dotted gray;">
                        <h3>
                            <a href="{{ $item->link }}">{{ $item->brand_car }}</a>
                        </h3>
                        <h6>
                            <a href="{{ $item->link }}">{{ $item->link }}</a>
                        </h6>
                        <div>Giá: {!! str_replace('Giá:', '', $item->price) !!}</div>
                        <div>Liên hệ: {{ $item->contact }}</div>
                        <div>Thành phố: {{ $item->city }}</div>
                        <div>
                            {!! substr($item->short_content, 0, 1000) !!}...
                        </div>
                    </div>
                    @endforeach

                    <div>
                        {!! $pagination->appends(['keyword' => $keyword])->render() !!}
                    </div>
                @endif
            </div>
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

