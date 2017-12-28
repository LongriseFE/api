<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #666;
                font-family: '微软雅黑';
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }
            .content{
                border:1px solid #eee;
                padding:20px;
                display:inline-block;
                border-radius:5px;
                box-shadow: 0 1px 5px #ccc;
                margin:10px;
                width:800px;
            }
            .v-m{
                vertical-align:middle;
            }
            .title{
                font-size:20px;
                color:#fff;
                font-weight:normal;
                background:#E64C65;
                margin:-20px;
                margin-bottom:40px;
                border-radius:5px 5px 0 0;
                text-align:center;
                padding:10px;
            }
            .title img{
                width:30px;
                margin-right:20px;
                margin-top:5px;
            }
            .content p{
                line-height:1.8;
                text-indent:2em;
            }
            .content p .text{
                color:#E64C65;
                padding:0 10px;
                font-weight:600;
            }
            .sub-title{
                font-weight:normal;
                font-size:20px;
                color:#666;
                border-left:4px solid #E64C65;
                padding-left:10px;
                margin-bottom:20px;
            }
            .info{text-align:right;padding-top:20px;}
        </style>
    </head>
    <body>
        <?php 
            date_default_timezone_set('PRC');
        ?>
        <div class="content">
            <h1 class="title">
                <img class="v-m" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAFm0lEQVRoQ9Wav65VVRDGZ15A4QmEihLo7JTEXnkCsTdRC2shFpbAEwiVjQnQ2gAPYITSxCg0tgIvMOZ3MrMze+3Za+19uUcvKznhcu/Za+ab+ebvOSqnfMzsioi8LyLnRISfOU/835eq+uI0RerbXmZmF0TkcxH52F+jK185IEDdV1X+f+JzYgBmhtJfJyufVImHInJXVcNLu+7ZDcDMsPSPIoLl83nuln0mItDklarys/gz/AileHHHB83zAPhiL8U2AzAzOI3inyXBr0Xkjojc2yvYYwUP4sl8bqrqra1u2ATAhT1IVkfxm674W3HYDcNdXyWl8cb1LfExBODuR3k8wHkkIje2XL7Vik4zqHVPRC77c9APEN2s1QXgyj9OinyjqlDmKMe9wf1BK7x7sWesVQBOG5QPyxNgWOjox8wAEZTCE9fWQJQA3BK/Jc7/Z8qHdcwMY4UnHqrq9cpyawDgfGSbo9Km504zo0Z86u8p9VgAaHj/SFVz2jw6fbIAZwIUomaU8VAB+MupQ6q8cNrZZq8FGoPSetzId8wAmBl/pFhxbqkq+bk8HuTfeZBjnd3tQHEHMg/Vu/FEphJZaUqtLQACl3zctX6RoUIeeRthw1OkaJ7BENwx64tcHrpxMBQV/HAmAN5VQp8t1sdKUXCeishH/twwb4dgM8OK0Q/lO16o6sXCC4BCzuzvGQB0gRKcmZsadxLUZKkJqJnl3w2zVkPVw/vNDKve9nsXabt5hrpw8FIGEAifq2oMIgsqNEXmfAR5sugzVb3a41BKj69VNQolXSseZBhaZD/PSP+0DMkArOJYx5VPVZW2+HAyMFUdtShBn5miqXjRip8vZAd1J9kHQQ3/u1XXzMJTs2DKNNoAICw9o1uiycwzyUhRnac4CABYMpq2iV8VDRwsnGcGmLXSrgCXd6crzyrIrO4gFqDh4g4zm+I0jLQbQI/bx/5bDvQWwJQBRu4/tpKD4F8wJTyQ0+DVqhr+n4qnGMidwiEDvmsUeudjYBUAxWRRJM4CbZouIJq6qdjmQrYoEmcQAEbG2FNbnQFMI9xZzERNRzoV2wwgR/iutnhUuNY86UpR+Ia7pVzEcrPZzgNR4p+o6rURhVL/s5iUNjwbOb1snxvuQxtaff6d9WAtgLzOWG2pi96kbL4GRSkoW/Y9DYDMjlmv1gJgYRtDzeoqIwHIBXDW3A2Up12PCavrvWbFw+cLs6VyNdRnLwxjIXWn6IxV6TBXOe0NH4MLdGB0vdJbHzbzx6JTrgBwMf363yLyh4j86mtzcu9CMbcQnWOMmAAhX8dwzjPcieXgfbZg2bp7x8t9eOoTHz3/zPNHeHhtL/SDiHxY0KC0sINA6ZiNRzH80hfE1fCOd6ZBKV30s4h823qrt1ZZUwLvQK1q/YFg4oJX+wEG97HZBijx1c4SPMNKZxoxCwV4hnllkp3rQG5VeZYPGbAQb8aV9CFh4eHcmwL93CjPO2UI6lD+bqIhsmn3Y8U423xUQz2yy5a6Cajh9mHEoyId86u1uMhbi2npVs3E3XS4Z/uwBUCzbRil1MVuaPc8MEprW5TO9BKRL0Xke/9dN233ZuJc6boVuOlJkEtQE5hYbxHYFSDfYMBpApeMFCl4tFDIeh5oXnlgZIXoyd+IyHuFgrgZUO1nWwQjNaBdmv0uIpf8nm5cVbunarG1mmGalvYnEfmFfL4j/7d477v3qP6kXUBj2apgAj4y1Xyxxa0NNbAy2WC6yJUnT4cFJ3d7IEKH+CA7vi+RFYYqKHhIzXmLvUE2yrOPXcjOHiAH55YAYXCaV/s9iM2N29agbnqq+D5FyEbxqBGzTNVW4hZEJb/7wcdWhdv3uRdpVaJgVVf1P6FJ6Q1eQwksz7YY9+MdvgZwql+XKYAgk6IVsulYoTRryMW68V8kEh1en8lP/wAAAABJRU5ErkJggg==" alt="">
                <span class="v-m">永兴元创意设计中心</span>
            </h1>
            <p>恭喜<span class="text">{{$name}}</span>成功注册本站会员，该邮箱将作为日后修改密码、找回密码的唯一凭证，请知悉！如果您忘记登录密码或者用户名，您可通过扫描以下二维码重新获取您的用户信息，请务必妥善保存该二维码！</p>
            <h2 class="sub-title">二维码名片</h2>
            {!! QrCode::size(200)->color(230,76,101)->backgroundColor(252,252,252)->margin(1)->encoding('UTF-8')->generate($qrcode);!!}
            <div class="info">
                <p>永兴元视觉创意中心</p>
                <p>{{date('Y-m-d H:i:s')}}</p>
            </div>
        </div>
    </body>
</html>
