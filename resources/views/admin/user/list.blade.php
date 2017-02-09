@extends('admin.common.app')
@section('styles')
@endsection
@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>User Management</h1>
        <a href="{{ route('admin_user_create') }}">Add</a>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                @include('notifications')
                <div class="box">
                    <div class="box-body table-responsive no-padding">
                        <form action="" id="form-user" method="post">
                        <input type="hidden" name="user_id" id="user_id" />
                        {{ csrf_field() }}
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <td>#ID</td>
                                    <td>Tên</td>
                                    <td>Email</td>
                                    <td>Quyền</td>
                                    <td>Trạng thái`</td>
                                    <td>Ngày tạo</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                              <tbody>
                              @foreach ($users as $key => $user) 
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        <?php
                                            if($user->status === 1) {
                                                echo 'Active';
                                            } else {
                                                echo 'Inactive';
                                            }
                                        ?>
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                                    <td style="width: 120px">
                                      <a href="{{ route('admin_user_edit', ['id' => $user->id]) }}" class="btn-button">Edit</a>
                                      <a href="javascript:void(0);" data-id="{{ $user->id }}" data-href="{{ route('admin_user_delete') }}" class="user-delete">Delete</a>
                                    </td>
                                </tr>
                              @endforeach
                              @if (count($users)==0)
                                  <tr><td colspan="6" align="center">Data not found</td></tr>
                              @endif
                            </tbody>
                        </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    
    {{ $users->links() }}

    <div id="dialog-confirm-delete" style="display: none">
        <p>Bạn có chắc xóa không ?</p>
    </div>

</div>
@endsection
@section('scripts')
<script>
var okButton = 'Ok';
var cancelButton = 'Cancel';
</script>
<script src="{{ asset('js/user.js') }}"></script>
@endsection