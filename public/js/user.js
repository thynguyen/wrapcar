$(document).ready(function() {
    $('.user-delete').on('click', function() {
        userId = $(this).attr('data-id');
        href = $(this).attr('data-href');
        $( "#dialog-confirm-delete" ).dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: [
                {
                    text: cancelButton,
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                },
                {
                    text: okButton,
                    click: function () {
                        $('#user_id').val(userId);
                        $('#form-user').attr('action', href);
                        $('#form-user').submit();
                        $( this ).dialog( "close" );
                    }
                }
            ]
        });
    });
});
