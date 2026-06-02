<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at 15% 10%, rgba(48, 184, 132, 0.14), transparent 35%),
                linear-gradient(145deg, #f7fcf9, #edf7f2);
            color: #153b2f;
        }
        .card {
            width: min(94vw, 430px);
            border: 1px solid #d8ece3;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fdfb);
            box-shadow: 0 18px 30px rgba(20, 76, 61, 0.1);
            padding: 1.1rem;
        }
        h1 {
            margin: 0 0 .1rem;
            font-family: "Cormorant Garamond", serif;
            font-size: 2rem;
        }
        p { margin: 0 0 .9rem; color: #5f8275; }
        label { display: block; margin-bottom: .3rem; font-weight: 600; }
        input {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #d1e8de;
            background: #ffffff;
            color: #153b2f;
            padding: .58rem .65rem;
            margin-bottom: .75rem;
            font: inherit;
        }
        button {
            border: none;
            border-radius: 10px;
            padding: .58rem .9rem;
            font: inherit;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, #22bc85, #149f6e);
        }
        .error {
            border: 1px solid #f0b5b5;
            border-radius: 10px;
            background: #fff2f2;
            color: #7b2323;
            padding: .55rem .65rem;
            margin-bottom: .8rem;
        }
    </style>
</head>
<body>
    <section class="card">
        <h1>Admin Panel</h1>
        <p>Sign in to manage listings and moderation.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="username">Username</label>
            <input id="username" name="username" value="{{ old('username') }}" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Login</button>
        </form>
    </section>
</body>
</html>
