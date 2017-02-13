<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setting Book Car</title>

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
        <div class="center-block">
            <div style="margin: 0 auto; max-width: 800px;">
                <div class="col-md-10" style="margin-bottom: 20px;">
                    @if (Auth::check())
                        <a href="{{ route('logout') }}" class="btn btn-info">Logout</a>
                        Xin chào <strong>{{ Auth::user()->name }}</strong>
                        <a href="{{ route('home_index') }}" class="btn btn-info pull-right">Home</a>
                    @else
                        <a href="{{ route('auth_login') }}" class="btn btn-info">Login</a>
                    @endif
                </div>
                <div style="clear: both;"></div>

                <div class="col-md-10">
                    @include('notifications')
                </div>

                <div class="col-md-10">
                    @if (Auth::check())
                    <div class="box box-info">
                        <div class="box-header with-border" style="text-align: center;"><h2>Thiết lập thông tin gửi tự động</h2></div>
                        <form class="form-horizontal" name="f_setting" id="f_setting" action="{{ route('setting_update') }}" method="POST">
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Email sẽ nhận thông tin</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <input type="text" name="email" class="form-control" value="{{ (old('email', isset($config->email) ? $config->email : '')) }}" />
                                        @if ($errors->has('email'))
                                        <p class="text-error">{{ $errors->first('email') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Thương hiệu</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <select name="brand" class="form-control" id="brand">
                                        @foreach ($brands as $key => $brand)
                                        <option value="{{ $key }}"  @if (old('brand', isset($setting->brand_car) ? $setting->brand_car : '') == $key) selected="selected" @endif>{{ $brand }}</option>
                                        @endforeach
                                        </select>
                                        @if ($errors->has('brand'))
                                        <p class="text-error">{{ $errors->first('brand') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Model</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <input type="text" name="product" class="form-control" value="{{ (old('product', isset($setting->keyword) ? $setting->keyword : '')) }}" />
                                        @if ($errors->has('product'))
                                        <p class="text-error">{{ $errors->first('product') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Năm sản xuất</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <select name="product_year" class="form-control" id="product_year">
                                        @foreach ($product_year_list as $keyP => $year)
                                        <option value="{{ $keyP }}"  @if (old('product_year', isset($setting->product_year) ? $setting->product_year : '') == $keyP) selected="selected" @endif>{{ $year }}</option>
                                        @endforeach
                                        </select>
                                        @if ($errors->has('product_year'))
                                        <p class="text-error">{{ $errors->first('product_year') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Thành phố</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <select name="city" class="form-control" id="city">
                                        @foreach ($city_list as $keyL => $cityV)
                                        <option value="{{ $keyL }}"  @if (old('color', isset($setting->city) ? $setting->city : '') == $keyL) selected="selected" @endif>{{ $cityV }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Hộp số</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <select name="hop_so" class="form-control" id="hop_so">
                                        @foreach ($hop_so_list as $keyH => $hopsoV)
                                        <option value="{{ $keyH }}"  @if (old('hop_so', isset($setting->hop_so) ? $setting->hop_so : '') == $keyH) selected="selected" @endif>{{ $hopsoV }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Màu xe</label>
                                    <div class="col-sm-6 input-group-sm">
                                        <select name="color" class="form-control" id="color">
                                        @foreach ($color_list as $keyC => $colorV)
                                        <option value="{{ $keyC }}"  @if (old('color', isset($setting->color) ? $setting->color : '') == $keyC) selected="selected" @endif>{{ $colorV }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Trạng thái</label>
                                    <div class="col-sm-6 input-group-sm" style="padding-top: 7px;">
                                        <input id="status_1" type="radio" name="status" value="1" @if (old('status', isset($setting->status) ? $setting->status : '') == 1) checked="checked" @endif />
                                            <label for="status_1">Active</label>

                                        <input id="status_0" type="radio" name="status" value="0" @if (old('status', isset($setting->status) ? $setting->status : '') != 1) checked="checked" @endif />
                                        <label for="status_0">Inactive</label>

                                        @if ($errors->has('status'))
                                        <p class="text-error">{{ $errors->first('status') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer" style="text-align: center;">
                                {{ csrf_field() }}
                                <input type="hidden" name="setting_id" value="{{ $setting->id or null }}" />
                                <input class="btn btn-default" type="submit" value="Save" name="submit" />
                                <a class="btn btn-default" href="{{ route('setting_index') }}">Cancel</a>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-xs-12">
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Thương hiệu</th>
                                <th>Model</th>
                                <th>Năm sản xuất</th>
                                <th>Thành phố</th>
                                <th>Hộp số</th>
                                <th>Màu xe</th>
                                <th>Ngày tạo</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            @if (isset($pagination) && count($pagination))
                            @foreach ($pagination as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->brand_car }}</td>
                                <td>{{ $item->keyword }}</td>
                                <td>{{ $item->product_year }}</td>
                                <td>{{ $item->city }}</td>
                                <td>{{ $item->hop_so }}</td>
                                <td>{{ $item->color }}</td>
                                <td>@if (!empty($item->created_at)) {{ date('d/m/Y H:i:d', strtotime($item->created_at)) }} @endif</td>
                                <td>
                                    @if ($item->status == 1)
                                    <span class="label label-success">Active</span>
                                    @else
                                    <span class="label label-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('setting_edit', ['setting_id' => $item->id]) }}">Edit</a>
                                    <a onclick="if (!confirm('Bạn có chắc xóa không ?')) {return false;} else { window.location.href = '{{ route('setting_delete', ['setting_id' => $item->id]) }}'; }" href="javascript:void(0);">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="10">No data</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    {!! $pagination->render() !!}
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

