<html>
    <head>
        <title></title>
        <script src="{{asset('js/jQuery-2.1.4.min.js')}}"></script>
    </head>
<body>
    <div id="xxx" style="border: 1px solid red;">
    
    </div>
    <!--<iframe id="iframe_content" src="http://vnexpress.net" width="100%" height="100%" />-->
    <iframe id="iframe_content" width="100%" height="100%" src="http://vnexpress.net"></iframe>
    
    <script>
        /*
        alert(333);
        $.get('http://vnexpress.net', function(string){
            var html = $.parseHTML(string);
            alert(html);
            var contents = $(html).contents();
            alert(contents);
        },'html');*/

        
        $(document).ready(function(){
             var html = $('#iframe_content').contents().find('body').html();
            //var html = $('#iframe_content').contents().find("html").html();
            alert(html);
        });
        </script>
</body>
</html>