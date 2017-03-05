@extends('admin.common.app')
@section('styles')
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Phone Black List</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                @include('notifications')
                <div class="box box-primary">
                    <form role="form" action="{{ route('admin_phone_black_list') }}" id="form-profile" method="post">
                        <div class="box-body">
                            <div class="form-group @if ($errors->has('phone')) has-error @endif">
                                <label class="control-label">Phone Black List</label>
                                <textarea class="form-control" rows="5" id="phone" name="phone" style="resize: none;">{{ old('name', isset($phone->content) ? $phone->content : '') }}</textarea>
                                <p class="help-block">Ví dụ: 0909112233,0909334455,...</p>
                                @if ($errors->has('phone'))
                                  <p class="help-block">{{ $errors->first('phone') }}</p>
                                @endif
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