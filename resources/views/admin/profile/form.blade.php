@extends('admin.common.app')
@section('styles')
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>User Management</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                @include('notifications')
                <div class="box box-primary">
                    <form role="form" action="{{ route('admin_profile_edit') }}" id="form-profile" method="post">
                        <div class="box-body">
                            <div class="form-group @if ($errors->has('name')) has-error @endif">
                                <label class="control-label">Tên</label>
                                <input type="text" class="form-control" id="name" name="name" maxlength="255" value="{{ old('name', isset($user->name) ? $user->name : '') }}" />
                                @if ($errors->has('name'))
                                  <p class="help-block">{{ $errors->first('name') }}</p>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="control-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" maxlength="255" value="{{ old('email', isset($user->email) ? $user->email : '') }}" disabled="disabled" />
                            </div>
                            <div class="form-group @if ($errors->has('password')) has-error @endif">
                                <label class="control-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" maxlength="255" value="" />
                                @if ($errors->has('password'))
                                <p class="help-block">{{ $errors->first('password') }}</p>
                                @endif
                            </div>
                            <div class="form-group @if ($errors->has('confirm_password')) has-error @endif">
                                <label class="control-label">Nhập lại password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" maxlength="255" value="" />
                                @if ($errors->has('confirm_password'))
                                <p class="help-block">{{ $errors->first('confirm_password') }}</p>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Quyền: </label>
                                <label class="control-label">{{ $user->role_name }}</label>
                            </div>
                            <div class="form-group">
                                <label>Trạng thái: </label>
                                <label class="control-label">
                                    @if($user->status === 1)
                                        Active
                                    @else
                                        Inactive
                                    @endif
                                </label>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@section('scripts')
@endsection