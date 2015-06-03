<?php

return array(

    "auth" => array(

        "login" => "You have successfully logged in.",

        "logout" => "You have successfully logged out.",

        "wrong_credentials" => "Incorrect email or password.",

        "not_logged_in" => "You are not logged in.",

    ),

    "reminder" => array(

        "mail_subject" => "Paxifi | Password Request",

        "driver" => "We can't find a seller with that e-mail address.",

        "sent" => "Password request has been sent to :email!",

        "reset" => "Your password has been successfully updated.",
    ),

    "store" => array(

        "not_found" => "The store ID :id does not exist.",
    ),

    "product" => array(

        "not_found" => "The product ID :id does not exist.",

    ),

    "exceptions" => array(
        "system_error" => "Opps, system error. Please try it again.",
    ),

    "invoice" => array(
        "invoice_not_available" => "The driver has not confirm your cash payment yet. Please kindly remind him/her."
    ),

    "payment" => array(
        "not_found" => "Payment not found.",
        "not_success" => "Payment not success.",
        "not_valid" => "Payment not valid."
    ),

    "stripe" => array(
        "connect_success" => "Driver stripe account successfully connected to Paxifi platform.",

        "connect_failed" => "Driver stripe account connection failed, please try it later.",

        "disconnect_success" => "Driver stripe account successfully disconnected from Paxifi platform.",

        "disconnect_failed" => "Driver stripe account disconnection failed, please try it later.",

        "not_available" => "Stripe payment is not available in this store, please try it later."

    ),
);
