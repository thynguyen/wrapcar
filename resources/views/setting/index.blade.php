<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setting</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        
        <!-- Bootstrap 3.3.5 -->
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        <script src="{{asset('js/jQuery-2.1.4.min.js')}}"></script>

        <!-- Styles -->
        <style>
            .text-error { color: red; }
        </style>
    </head>
    <body>
        <div class="container">
            @include('notifications')
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border"><h2>Thiết lập thông tin gửi tự động</h2></div>
                    <form class="form-horizontal" name="f" id="f" action="{{ route('setting_update') }}" method="POST">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Email sẽ nhận thông tin</label>
                                <div class="col-sm-10">
                                    <input type="text" name="email" class="form-control" value="{{ (old('email', isset($config->value) ? $config->value : '')) }}" />
                                    @if ($errors->has('email'))
                                    <p class="text-error">{{ $errors->first('email') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Thương hiệu</label>
                                <div class="col-sm-10">
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
                                <label class="col-sm-2 control-label">Thông tin cần tìm</label>
                                <div class="col-sm-10">
                                    <input type="text" name="product" class="form-control" value="{{ (old('product', isset($setting->keyword) ? $setting->keyword : '')) }}" />
                                    @if ($errors->has('product'))
                                    <p class="text-error">{{ $errors->first('product') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Năm sản xuất</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="product_year" value="{{ (old('product_year', isset($setting->product_year) ? $setting->product_year : '')) }}" />
                                    @if ($errors->has('product_year'))
                                    <p class="text-error">{{ $errors->first('product_year') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Thành phố</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="city" value="{{ (old('city', isset($setting->city) ? $setting->city : '')) }}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Hộp số</label>
                                <div class="col-sm-10">
                                    <input type="text" name="hop_so" class="form-control" value="{{ (old('hop_so', isset($setting->hop_so) ? $setting->hop_so : '')) }}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Màu xe</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="color" value="{{ (old('color', isset($setting->color) ? $setting->color : '')) }}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Trạng thái</label>
                                <div class="col-sm-10">
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
                        <div class="box-footer">
                            {{ csrf_field() }}
                            <input class="btn btn-default" type="submit" value="Save" name="submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>

