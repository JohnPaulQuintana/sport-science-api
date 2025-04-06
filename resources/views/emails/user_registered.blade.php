<!DOCTYPE html>
<html>
<head>
    <title>Your System Registration Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .banner {
            width: 100%;
            height: auto;
            border-radius: 8px 8px 0 0;
        }
        h2 {
            color: #333;
            margin-top: 20px;
        }
        p {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            border: 1px solid #1d6f42; /* Dark Green */
            color: #14512f;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
            font-weight: bold;
        }
        .button:hover {
            border: 1px solid #14512f;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="https://i.pinimg.com/736x/a2/1c/e4/a21ce4a62f4c3b79228f8b571cff3507.jpg" alt="Banner" class="banner">
        <h2>Welcome, {{ $user->name }}!</h2>
        <p>You have been successfully registered to the system.</p>
        <p><strong>Login Credentials:</strong></p>
        <ul>
            <li>Email: {{ $user->email }}</li>
            <li>Password: {{ $password }}</li>
        </ul>

        <p>Please change your password after your first login for security reasons.</p>


        <a href="{{ $login_link }}"  class="button">Login Now</a>
        <p>If you did not request this, please ignore this email.</p>
        <p>If button not working click this link or copy</p>
        <a href="{{ $login_link }}">{{ $login_link }}</a>
        <p class="footer">&copy; {{ date('Y') }} Sport Science. All rights reserved.</p>
    </div>
</body>
</html>
