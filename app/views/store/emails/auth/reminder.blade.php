<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Password Reset</title>
</head>
<body
    style="min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;padding: 0;background: #f5f5f5;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;text-align: left;line-height: 19px;font-size: 14px;width: 100% !important;">
<table class="body"
       style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;min-width: 100%;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;margin: 0;background: #f5f5f5;height: 100%;width: 100% !important;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;line-height: 19px;font-size: 14px;">
    <tr style="padding: 0;vertical-align: top;text-align: left;">
        <td class="center" align="center" valign="top"
            style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
            <center style="width: 100%;min-width: 580px;">
                <table class="container"
                       style="border-spacing: 0;border-collapse: collapse;padding: 20px;vertical-align: top;text-align: inherit;width: 580px;margin: 0 auto;">
                    <tr style="padding: 0;vertical-align: top;text-align: left;">
                        <td class="wrapper last"
                            style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
                            <center style="width: 100%;min-width: 580px;margin-top: 20px;">
                                <table class="content"
                                       style="border-spacing: 0;border-collapse: collapse;padding: 0;vertical-align: top;text-align: left;background: #fff;width: 580px;border: 1px solid #f1f1f1;">
                                    <tr style="padding: 0;vertical-align: top;text-align: left;">
                                        <td style="word-break: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;margin: 0;line-height: 19px;font-size: 14px;border-collapse: collapse !important;">
                                            <div class="content-body" style="padding: 30px;">
                                                <h2 style="color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;line-height: 1.3;word-break: normal;font-size: 20px;">
                                                    Password Reset</h2>

                                                <p style="margin-top: 20px;margin-bottom: 20px;color: #222222;font-family: &quot;Helvetica&quot;, &quot;Arial&quot;, sans-serif;font-weight: normal;padding: 0;text-align: left;line-height: 19px;font-size: 14px;">
                                                    To reset your password, please click the link below to complete the form:</p>
                                                <a href="{{ url('drivers/password/reset', array($token)) }}" class="reset-btn"
                                                   style="text-decoration: none;display: block;text-align: center;border: 1px solid #f7941e;color: #ffffff;padding: 8px 0;background: #f7941e;width: 200px !important;">Reset Password</a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </center>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>