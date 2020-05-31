<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Tracker</title>
</head>
<body>
<div class="box" id="box">

</div>
<div class="coords" id="coords">
    <span class="x" id="X"></span>
    <span class="y" id="Y"></span>
</div>


<script
    src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
    crossorigin="anonymous">
</script>
<script>
(function( $ ) {
    $.fn.myPlugin = function( options ) {
        var settings = $.extend( {
            'checkInterval': 30,
            'sendInterval' : 3000,
            'url'          : 'test.php'
        }, options);

        return this.each(function() {
            var time = 0;
            var arr = [];

            this.addEventListener('mouseenter', function () {
                var timer = setInterval(()=>{
                    time+=settings.checkInterval;
                },settings.checkInterval)

                setInterval(function () {
                    $.post(settings.url, arr, function(data) {
                        alert(data);
                    })
                    arr.length = 0;
                }, settings.sendInterval)

                this.addEventListener('mouseleave', function () {
                    clearInterval(timer)
                    time = 0;
                })

            })
            this.addEventListener('mousemove', function (e) {
                $('#X').text(e.offsetX)
                $('#Y').text(e.offsetY)

                arr.push({
                    'x' : e.offsetX,
                    'y' : e.offsetY,
                    'time' : time,
                })
                time = 0;
            })

        })
    };
})(jQuery);
</script>
<script>
    $('#box').myPlugin({'checkInterval': 50, 'url': 'test2.php'});
</script>
<style>
    body{
        display: flex;
        margin: 20px;
    }
    .box{
        width: 250px;
        height: 250px;
        background: rgba(124, 76, 76, 0.38);
    }
    .coords{
        margin-left: 20px;
    }
    .coords span{
        display: flex;
    }
    .x:before{
        display: block;
        content: 'X:';
    }
    .y:before{
        display: block;
        content: 'Y:';
    }
</style>
</body>
</html>