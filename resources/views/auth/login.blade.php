<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Login</title>

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
    <body class="hold-transition login-page">
        <div class="center-block">
            <div style="margin: 0 auto; max-width: 600px;">
                <div class="col-md-10">
                    <div class="box box-info">
                        @include('notifications')
                        <div class="box-header with-border" style="text-align: center;"><h4>Login</h4></div>
                        <form class="form-horizontal" name="f" id="f_setting" action="{{ route('auth_login') }}" method="POST">
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Email</label>
                                    <div class="col-sm-8 input-group-sm">
                                        <input type="text" name="email" class="form-control" value="{{ old('email') }}" />
                                        @if ($errors->has('email'))
                                        <p class="text-error">{{ $errors->first('email') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Password</label>
                                    <div class="col-sm-8 input-group-sm">
                                        <input type="password" name="password" class="form-control" value="" />
                                        @if ($errors->has('password'))
                                        <p class="text-error">{{ $errors->first('password') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-groupr">
                                    <label class="col-sm-5 control-label"></label>
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
        </div>
    </body>
</html>
