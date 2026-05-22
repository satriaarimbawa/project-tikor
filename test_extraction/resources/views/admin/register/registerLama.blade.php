<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
</head>
<body>
    <!-- Contoh potongan kode di register.blade.php -->
<form action="/test-store" method="POST">
    <!-- Wajib ada @csrf untuk keamanan Laravel -->
    @csrf 

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="email">email:</label>
    <input type="text" id="email" name="email" required>

    <label for="role user">Role user:</label>
    <input type="text" id="role_user" name="role_user" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Daftar</button>
</form>
</body>
</html>