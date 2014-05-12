<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="favicon.ico">

    <title>Paxifi | Reset Password</title>

    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
</head>

<body>

<div class="container">

    <h1>Password reset</h1>

    @if (Session::get('success'))
    <p class=" alert alert-success">{{ Session::get('success') }}</p>
    @endif

    @if (Session::get('error'))
    <p class="alert alert-danger">{{ Session::get('error') }}</p>
    @endif

    <form action="{{ action('Paxifi\Store\Controller\RemindersController@reset') }}" method="POST">

        <p class="form-group">
            <label for="email">Email: </label>
            <input id="email" class="form-control" type="email" name="email" placeholder="Enter email">
        </p>

        <p class="form-group">
            <label for="password">Password: </label>
            <input type="password" class="form-control" name="password" placeholder="Enter password">
        </p>

        <p class="form-group">
            <label for="password_confirmation">Confirm password: </label>
            <input type="password" class="form-control" name="password_confirmation"
                   placeholder="Enter password confirmation">
        </p>

        <p class="form-group">
            <input class="btn btn-warning" type="submit" value="Reset password">
        </p>
        <input type="hidden" name="token" value="{{ $token }}">
    </form>

</div>
</body>
</html>
