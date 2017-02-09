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
            <div class="col-xs-12" style="margin-bottom: 20px;">
                <a href="{{ route('home_index') }}" class="btn btn-info">Home</a>
            </div>
            <div style="clear: both;"></div>

            @include('notifications')
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border" style="text-align: center;"><h2>Login</h2></div>
                    <form class="form-horizontal" name="f" id="f_setting" action="{{ route('auth_login') }}" method="POST">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-5 control-label">Email</label>
                                <div class="col-sm-5">
                                    <input type="text" name="email" class="form-control" value="{{ old('email') }}" />
                                    @if ($errors->has('email'))
                                    <p class="text-error">{{ $errors->first('email') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label">Password</label>
                                <div class="col-sm-5">
                                    <input type="password" name="password" class="form-control" value="" />
                                    @if ($errors->has('password'))
                                    <p class="text-error">{{ $errors->first('password') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-groupr">
                                <p class="text-error">{{ $message or null }}</p>
                            </div>
                        </div>
                        <div class="box-footer" style="text-align: center;">
                            {{ csrf_field() }}
                            <input class="btn btn-default" type="submit" value="Save" name="submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
