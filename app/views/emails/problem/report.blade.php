<html>
    <head>
        <meta charset="utf-8"/>
        <title>Product Problem Report</title>
    </head>
    <body>
        <h3>Dear <b>{{{$driver_name}}} :</b></h3>
        <p>We have been informed that one of your passenger had reported an item <b>{{{$product['name']}}}</b> for the following reason: <b>{{{$problem_type['name']}}}</b></p>
        <p>The passenger has choose to provide you his/her email address so you can contact him/her directly.</p>
        <p><b>Email :</b> {{{$reporter_email}}}</p>
        <p>Thank you for using Paxifi. Sincerely,</p>
        <p>The Paxifi team</p>
    </body>
</html>