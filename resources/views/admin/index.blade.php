<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>DASHBOARD||ADMIN</title>
</head>
<body>
  <h1>HAI AKU ADMIN</h1>
  <a href="/register">REGISTRASI USER</a>

  <table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>ID Firebase</th>
            <th>Nama</th>
            <th>NIM / Email</th>
            <th>role user</th>
            <th>password</th>
        </tr>
    </thead>
    <tbody>
      @dd($user)
      @if ($users != null)
        @foreach ($users as $index => $user)
            <tr>
                <td>1</td>
                <td>{{ $index }}</td>
                {{-- <td>{{ $user['id'] ?? 'N/A' }}</td> --}}
                <td>{{ $user['username'] ?? 'N/A' }}</td>
                <td>{{ $user['email'] ?? 'N/A' }}</td>
                <td>{{ $user['role_user'] ?? 'N/A' }}</td>
                <td>{{ $user['password'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
        
      @endif
    </tbody>
</body>
</html>