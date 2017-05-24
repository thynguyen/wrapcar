<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Search Car</title>

        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.5 -->
        <link rel="stylesheet" href="{{asset('admin_lte/bootstrap/css/bootstrap.min.css')}}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('admin_lte/dist/css/AdminLTE.min.css')}}">
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="{{ asset('/admin_lte/plugins/jQueryUI/jquery-ui.min.css') }}">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <script src="{{asset('js/jQuery-2.1.4.min.js')}}"></script>

        <!-- Styles -->
        <style>
            .text-error { color: red; }
        </style>
    </head>
    <body>
        @include('analyticstracking')
        <div class="center-block">
            <div style="margin: 0 auto; max-width: 800px">
                <div class="col-md-10" style="margin-bottom: 20px;">
                    @if (Auth::check())
                        <a href="{{ route('logout') }}" class="btn btn-info">Logout</a>
                        Xin chào <strong>{{ Auth::user()->name }}</strong>
                        <a href="{{ route('setting_index') }}" class="btn btn-info pull-right">List book xe</a>
                    @else
                        <a href="{{ route('auth_login') }}" class="btn btn-info">Login</a>
                    @endif
                </div>
                <div style="clear: both;"></div>

                <div class="col-md-10">
                    <div class="box box-info" style="margin-top: 5px;">
                        <form name="f" id="f" action="{{ route('search_index') }}" method="GET">
                            <div class="box-body">
                                <div class="form-group">
                                    <div class="col-sm-10 input-group-sm">
                                        <input type="textbox" class="form-control" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="Keyword" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-5 input-group-sm" style="margin-top: 5px;">
                                        <select name="time" class="form-control">
                                            @if(isset($timeList) && count($timeList))
                                            @foreach($timeList as $val => $item)
                                            <option value="{{ $val }}" @if ($val == $time) selected="selected" @endif>{{ $item }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-sm-5 input-group-sm" style="margin-top: 5px;">
                                        <select name="city" class="form-control">
                                            @if(isset($citiList) && count($citiList))
                                            @foreach($citiList as $valC => $itemC)
                                            <option value="{{ $valC }}" @if ($valC == $city) selected="selected" @endif>{{ $itemC }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-sm-5 input-group-sm" style="margin-top: 5px;">
                                        <select name="color" class="form-control">
                                            @if(isset($colorList) && count($colorList))
                                            @foreach($colorList as $valCo => $itemCo)
                                            <option value="{{ $valCo }}" @if ($valCo == $color) selected="selected" @endif>{{ $itemCo }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer" style="text-align: center;">
                                <input class="btn btn-default" type="submit" value="Search" name="submit" />
                            </div>
                        </form>
                    </div>
                </div>
                <div style="clear: both;"></div>

                <div class="col-md-10">
                @if (isset($pagination) && $pagination->total())
                    @foreach ($pagination as $item)
                    <div class="box-body" style="border-bottom: 1px dotted gray;">
                        <h3>
                            <a href="{{ $item->link }}" xxx="{{ $item->is_owner }}">{{ $item->brand_car }}</a>
                            @if ($item->is_owner == 1)
                            <img src="{{ asset('image/chinhchu.png') }}" />
                            @endif
                        </h3>
                        <h6>
                            <a href="{{ $item->link }}">{{ $item->link }}</a>
                        </h6>
                        <div>Giá: {!! str_replace('Giá:', '', $item->price) !!}</div>
                        <div>Liên hệ: {{ $item->contact }}</div>
                        <div>Thành phố: {{ $item->city }}</div>
                        <div>Màu: {{ $item->color }}</div>
                        <div>
                            {!! mb_substr(strip_tags($item->short_content), 0, 1000, 'UTF-8') !!}...
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                    @endforeach

                    <div>
                        {!! $pagination->render() !!}
                    </div>
                @endif
                </div>
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

