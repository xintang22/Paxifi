<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <style>
        html,
        body {
            width: 100%;
            padding: 0 !important;
            margin: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, "lucida grande", sans-serif;
            color: #58585a;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        table.six {
            width: 300px;
        }

        td {
            word-break: break-word;
            -webkit-hyphens: auto;
            -moz-hyphens: auto;
            hyphens: auto;
            border-collapse: collapse !important;
        }

        table.body {
            width: 100%;
            padding: 0;
        }

        table.content {
            width: 600px;
            background: #fff;
            margin: 0 auto;
        }

        table.container {
            width: 600px;
        }

        table.container td.paxifi-logo {
            padding: 14px 0;
        }

        table.container.first {
            border-bottom-width: 10px;
            border-bottom-color: #f7941e;
            border-bottom-style: solid;
        }

        table.row td.last,
        table.container td.last {
            padding-right: 0px;
        }

        table.comment p {
            font-size: 13px;
            line-height: 15px;
            vertical-align: middle;
            padding: 0;
        }

        .body .columns td.six,
        .body .column td.six {
            width: 50%;
        }

        td.template-label {
            text-align: right;
        }

        tr.data-title th.three {
            width: 25%;
        }

        tr.data-title th.four {
            width: 33.333333%;
        }

        tr.data-title th.five {
            width: 41.666666%
        }

        tr.data-title th,
        tr.data-title td {
            text-align: left;
            border-top-width: 1px;
            border-top-color: #f7941e;
            border-top-style: solid;
            border-bottom-width: 1px;
            border-bottom-color: #f7941e;
            border-bottom-style: solid;
            height: 48px;
            vertical-align: middle;
        }

        tr.data-body td {
            height: 48px;
        }

        td.offset-by-seven {
            padding-left: 350px;
        }

        td.authorized {
            font-weight: bold;
            font-size: 13px;
            color: #f7941e;
            padding: 40px 0;
            text-align: center;
        }

        div.title {
            padding-bottom: 28px;
            border-bottom-width: 1px;
            border-bottom-color: #f7941e;
            border-bottom-style: solid;
        }
    </style>
</head>
<body>
<table class="body">
    <tr>
        <td class="center" align="center" valign="top">
            <table class="content">
                <tr>
                    <td>
                        <table class="container first">
                            <tr>
                                <td class="six paxifi-logo">
                                    <img src="{{{$template['pdf_logo']}}}"/>
                                </td>
                                <td class="six last template-label">
                                    Invoice
                                </td>
                            </tr>
                        </table>

                        <br><br>

                        <table class="container">
                            <tr>
                                <td class="wrapper">
                                    <table class="six columns">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <b>{{{$content['billed']}}} </b>{{{ $order['buyer_email'] }}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b>{{{$content['invoice']}}} #: </b>{{{$order['id']}}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b>{{{$content['date']}}} </b>{{{$order['updated_at']}}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b>{{{$content['payment_amount']}}} </b>{{{$order['total_sales']}}}USD
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="wrapper last">
                                    <table class="six columns">
                                        <tbody>
                                        <tr>
                                            <td class="seller-logo">
                                                <img src="{{{$template['pdf_driver_logo']}}}"/>
                                            </td>
                                            <td>
                                                <table>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <b>{{{$content['seller']}}} </b>{{{$driver['name']}}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <b>{{{$content['seller_id']}}} </b>{{{$driver['seller_id']}}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <b>{{{$content['email']}}} </b>{{{$driver['email']}}}
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <br><br>

                        <table class="container">
                            <tbody>
                            <tr class="data-title">
                                <th class="three" style="width: 25%;">{{{$content['name']}}}</th>
                                <th class="four" style="width: 33.333333%;">{{{$content['quantity']}}}</th>
                                <th class="five" style="width: 41.666666%;">{{{$content['price']}}}({{{$driver['currency']}}})</th>
                            </tr>
                            @foreach($products as $product)
                            <tr class="data-body">
                                <td>{{$product['name']}}</td>
                                <td>{{$product['pivot']['quantity']}}</td>
                                <td>{{$product['unit_price']}}</td>
                            </tr>
                            @endforeach
                            <tr class="data-title">
                                <td colspan="3" class="five offset-by-seven"><b>{{{$content['sub_total']}}} </b>{{{$order['total_sales'] - $order['total_tax']}}}{{{$driver['currency']}}}</td>
                            </tr>
                            <tr class="data-title">
                                <td colspan="3" class="five offset-by-seven"><b>{{{$content['sales_tax']}}} </b>{{{$order['total_tax']}}} {{{$driver['currency']}}}</td>
                            </tr>
                            <tr class="data-title">
                                <td colspan="3" class="five offset-by-seven"><b>{{{$content['total']}}} </b>{{{$order['total_sales']}}} {{{$driver['currency']}}}</td>
                            </tr>
                            </tbody>
                        </table>

                        <br><br>

                        <table class="container comment">
                            <tbody>
                            <tr>
                                <td>
                                    <div class="title">
                                        <b>{{{$content['title']}}}</b>
                                        <p>{{{$content['description']}}}</p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="authorized">
                                    Powered by Paxifi
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>