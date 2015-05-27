<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
    <style>
        body{
            width:100% !important;
            min-width: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
        }

        img {
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            width: auto;
            max-width: 100%;
            float: left;
            clear: both;
            display: block;
        }

        a img { border: none; }

        p { margin: 0;}

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        td {
            word-break: break-word;
            -webkit-hyphens: auto;
            -moz-hyphens: auto;
            hyphens: auto;
            border-collapse: collapse !important;
        }

        table.body {
            height: 100%;
            width: 100%;
            font-family: Helvetica, arial, sans-serif;
            line-height: 30px;
        }

        .container {
            width: 700px;
            margin: 0 auto;
            padding-left: 18px;
            padding-right: 18px;
        }

        table.header td.first { padding: 28px 0 28px 18px; }
        table.header td.last {
            text-align: right;
            padding-right: 18px;
            font-size: 24px;
            color: #999999;
        }
        table.header td.first, table.header td.last{
            background-image: url("{{ cloudfront_asset('emails/welcome/bg01_top.jpg') }}");
            background-repeat: no-repeat;
            background-size: contain;
        }
        table.header, table.main {
            background-image: url("{{ cloudfront_asset('emails/welcome/bg01_middle.jpg') }}");
            background-size: contain;
        }

        table.footer {
            background-image: url("{{ cloudfront_asset('emails/welcome/bg01_bottom.jpg') }}");
            background-repeat: no-repeat;
            background-size: contain;
            background-position: bottom;
        }

        h1.center,
        p.center,
        div.center { text-align: center; }

        div.welcome {
            position: absolute;
            width: 700px;
            margin-left: -18px;
            color: #FFFFFF;
            font-size: 24px;
        }
        div.welcome h1 {
            font-size: 36px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        img.welcome {
            margin-bottom: 54px;
        }

        p.steps {
            font-size: 30px;
            color: #F7941E;
        }
        p.steps b {
            margin-left: 20px;
            margin-right: 20px;
        }
        p.steps span {
            background: white url("{{ cloudfront_asset('emails/welcome/short_line.jpg') }}") no-repeat center bottom;
            display: inline-block;
            width: 28px;
            height: 20px;
        }
        img.steps { margin-top: 54px; }

        table.row {
            padding: 0px;
            width: 100%;
            position: relative;
        }

        p.contact-us {
            font-size: 24px;
            color: #F6911D;
            line-height: 25px;
            margin-bottom: 20px;
        }

        hr {
            margin-left: 18px;
            margin-right: 18px;
            border: none;
            height: 2px;
            background-image: url("{{ cloudfront_asset('emails/welcome/imaginary_line.jpg') }}");
        }
        hr.top {
            margin-top: 74px;
            margin-bottom: 40px;
        }
        hr.bottom {
            margin-top: 35px;
            margin-bottom: 0;
        }

        p.email {
            font-size: 24px;
            color: #494949;
        }
        p.email a {
            color: #F7941E;
            text-decoration: none;
        }

        table.footer td {
            padding-top: 18px;
            padding-bottom: 12px;
        }
        table.footer img {
            float: none;
            display: inline-block;
        }
        table.footer img.middle {
            margin-left: 38px;
            margin-right: 38px;
        }

        /*  Media Queries */
        @media only screen and (max-width: 600px) {

            table.body { line-height: 20px !important;}
            .container {
                width: 95% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            table.header td.first { padding: 10px 1px !important;}
            table.header td.last {
                font-size: 14px !important;
                padding-right: 0 !important;
            }
            p.email, p.steps { font-size: 18px !important;}
            hr {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            table.footer img.middle {
                margin-left: 15px !important;
                margin-right: 15px !important;
            }
            div.welcome {
                width: 95% !important;
                margin-left: 0 !important;
                font-size: 13px !important;
                line-height: 1 !important;
            }
            div.welcome h1 {
                margin-top: 12px !important;
                margin-bottom: 8px !important;
                font-size: 20px !important;
            }
            p.steps span { width: 15px !important; }
            p.steps b {
                margin-left: 10px !important;
                margin-right: 10px !important;
            }
        }/*  End of Media Queries */

        /* fix odd problem: image not responsive in Firefox */
        table.main td.container img { width: 100%; }

    </style>
</head>

<body>
<table class="body">
    <tr>
        <td>
            <table class="header container">
                <tr>
                    <td class="first">
                        <img src="{{ cloudfront_asset('emails/welcome/paxifi_logo.jpg') }}" alt="Paxifi logo" />
                    </td>
                    <td class="last">
                        <span>Stores on wheels</span>
                    </td>
                </tr>
            </table>

            <table class="main container">
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td class="container">
                                    <div class="welcome">
                                        <h1 class="center">Welcome to Paxifi</h1>
                                        <p class="center">Thanks for signing up!</p>
                                        <p class="center">Get your Paxifi account ready and join our beta</p>
                                        <p class="center">phase to get a chance to win $1000</p>
                                    </div>
                                    <img src="{{ cloudfront_asset('emails/welcome/topbar_bg.jpg') }}" alt="Top bar" class="welcome" />
                                    <p class="steps center">
                                        <span></span><b>3 STEPS TO SET UP YOUR</b><span></span>
                                    </p>
                                    <p class="steps center">
                                        <b>STORE ON WHEELS</b>
                                    </p>
                                    <img src="{{ cloudfront_asset('emails/welcome/3steps.jpg') }}"  class="steps" alt="4 steps" />
                                </td>
                            </tr>
                        </table>

                        <hr class="top" />
                        <table class="row">
                            <tr>
                                <td>
                                    <p class="contact-us center"><b>Contact Us</b></p>
                                    <p class="center email">Stay in touch with our Paxifi team if you have any question or need any support to be Paxify ready: <a href="mailto:admin@paxifi.com">admin@paxifi.com</a><br /> Visit this website <a href="http://home.paxifi.com">http://home.paxifi.com</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <hr class="bottom" />

                        <table class="row footer">
                            <tr>
                                <td>
                                    <div class="center"><a href="https://twitter.com/paxifi"><img src="{{ cloudfront_asset('emails/welcome/twitter.jpg') }}" alt="Twitter" /></a><a href="https://www.facebook.com/paxifiapp"><img src="{{ cloudfront_asset('emails/welcome/facebook.jpg') }}" alt="Facebook" class="middle" /></a><a href="https://www.youtube.com/watch?v=wiy8zJnEO2A"><img src="{{ cloudfront_asset('emails/welcome/youtube.jpg') }}" alt="Youtube" /></a></div>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table><!-- End of the container for main content -->
        </td>
    </tr>
</table>
</body>

</html>
