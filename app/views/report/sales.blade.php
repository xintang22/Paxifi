<!DOCTYPE>
<html>
    <head>
        <meta charset="utf-8"/>
        <style>
            html,
            body {
                padding: 0;
                margin: 0;
                font-size: 40px;
                width: 100%;
                height: 100%;
            }
            h1 {
                text-align: center;
                color: #f7941e;
                font-size: 80px;
                margin: 40px 0;
            }
            table {
                width: 100%;
            }
            table ul {
                margin: 0;
                padding: 0;
                list-style: none;
            }
            table ul li {
                text-align: left;
                overflow: hidden;
            }
            table tr {
                width: 100%;
            }

            table tr td {
                width: 100%;
            }

            table tr.total td {
                width: 25%;
            }

            tr.total td,
            tr.reports td {
                border: 40px solid transparent;
                margin: 0 20px;
                width: 25%;
            }

            tr.reports ul {
                width: 100%;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            tr.total ul li,
            tr.reports ul li {
                border-bottom: 1px solid #000;
                overflow: hidden;
                vertical-align: middle;
                height: 46px;
                line-height: 46px;
                padding: 20px 0;
            }

            tr.total ul li label,
            tr.reports ul li label {
                text-align: left;
                font-size: 40px;
                display: inline-block;
                width: 40%;
            }

            tr.total ul li.title,
            tr.reports ul li.title {
                border-bottom: 4px dotted #f7941e;
            }

            tr.total ul li.title {
                padding: 40px 0;
            }

            tr.total ul li h4 {
                font-size: 60px !important;
            }

            tr.total ul li h4,
            tr.reports ul li h4 {
                text-align: center;
                font-size: 45px;
                color: #f7941e;
                padding: 0;
                margin: 10px 0;
            }

             tr.total ul li div,
             tr.reports ul li div {
                display: inline-block;
                width: 59%;
                text-align: right !important;
            }

            tr.total ul li span.currency,
            tr.reports ul li span.currency {
                font-size: 30px;
                font-weight: bold;
                margin-right: 10px;
                vertical-align: baseline;
            }

            tr.total ul li span.value,
            tr.reports ul li span.value {
                font-size: 45px;
                font-weight: bold;
                color: #f7941e;
            }
        </style>
    </head>
    <body>

        <table>
            <tbody>
                <tr>
                    <td colspan="4">
                        <h1>{{$year}} Reports</h1>
                    </td>
                </tr>
                <tr class="total">
                    <td colspan="2">
                        <div>
                            <ul>
                                <li class="title">
                                    <h4>{{$year}} Reports</h4>
                                </li>
                                <li>
                                    <label for="total_revenue">Total Revenue</label>
                                    <div>
                                        <span class="currency">{{$driver->currency}}</span> <span class="value">{{$statistics['totals']['sales']}}</span>
                                    </div>
                                </li>
                                <li>
                                    <label for="total_revenue">Total Paxifi Fee</label>
                                    <div>
                                        <span class="currency">{{$driver->currency}}</span> <span class="value">{{$statistics['totals']['commission']}}</span>
                                    </div>
                                </li>
                                <li>
                                    <label for="total_revenue">Total Tax</label>
                                    <div>
                                        <span class="currency">{{$driver->currency}}</span> <span class="value">{{$statistics['totals']['tax']}}</span>
                                    </div>
                                </li>
                                <li>
                                    <label for="total_revenue">Total Profit</label>
                                    <div>
                                        <span class="currency">{{$driver->currency}}</span> <span class="value">{{$statistics['totals']['profit']}}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr class="reports">
                    @foreach($reports as $key => $report)
                        <td colspan="1">
                            <div>
                                <ul>
                                    <li class="title">
                                        <h4>{{key($report)}} {{$year}}</h4>
                                    </li>
                                    <li>
                                       <label for="revenue">Revenue</label>
                                       <div>
                                        <span class="currency">{{$driver->currency}}</span> <span class="value">{{$report[key($report)]['total_sales']}}</span>
                                       </div>
                                    </li>
                                    <li>
                                        <label for="revenue">Paxifi Fee</label>
                                        <div>
                                            <span class="currency">{{$driver->currency}}</span> <span class="value">{{$report[key($report)]['commission']}}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <label for="revenue">Tax</label>
                                        <div>
                                            <span class="currency">{{$driver->currency}}</span> <span class="value">{{$report[key($report)]['total_tax']}}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <label for="revenue">Profit</label>
                                        <div>
                                            <span class="currency">{{$driver->currency}}</span> <span class="value">{{$report[key($report)]['profit']}}</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        @if($key%4 == 0)
                        </tr><tr class="reports">
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    </body>
</html>