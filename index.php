<?php require_once __DIR__ . '/init.php';
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Log-In</title>
    <style>
        :root {
            --panel-w: 420px;
            --accent: #d33;
        }

        html,
        body {
            height: 100%;
            margin: 0
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #222;
            background-image: url('assets/coffee-bg.jpg');
            /* place your background image at assets/coffee-bg.jpg */
            background-size: cover;
            background-position: center center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: var(--panel-w);
            max-width: calc(100% - 40px);
            background: linear-gradient(rgba(20, 20, 20, 0.75), rgba(15, 15, 15, 0.75));
            color: #fff;
            border-radius: 10px;
            padding: 34px 30px 26px 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.06);
            /* subtle fade-in and lift */
            animation: fadeInUp .48s cubic-bezier(.2, .9, .2, 1) both;
        }

        .avatar {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            left: calc(50% - 37px);
            top: -37px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
            border: 4px solid rgba(255, 255, 255, 0.05);
        }

        .avatar svg {
            fill: #fff;
            width: 34px;
            height: 34px
        }

        h1.title {
            margin: 10px 0 18px 0;
            text-align: center;
            font-size: 20px;
            letter-spacing: 1px
        }

        .field {
            margin-bottom: 12px
        }

        .field input[type="text"],
        .field input[type="password"] {
            width: 100%;
            background: transparent;
            border: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            padding: 10px 6px;
            color: #fff;
            font-size: 14px;
            outline: none
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, 0.5)
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .remember input {
            transform: scale(1.05)
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px 14px;
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer
        }

        .links {
            margin-top: 12px;
            text-align: center;
            font-size: 13px
        }

        .links a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: underline
        }

        .msg {
            margin-bottom: 12px;
            padding: 8px;
            border-radius: 6px;
            font-size: 14px
        }

        .msg.success {
            background: rgba(0, 150, 0, 0.12);
            color: #bff2b0
        }

        .msg.error {
            background: rgba(200, 0, 0, 0.08);
            color: #ffd0d0
        }

        /* animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* mobile tweaks */
        @media (max-width: 480px) {
            :root {
                --panel-w: 92%;
            }

            .card {
                padding: 20px 16px 18px 16px;
                border-radius: 8px;
            }

            .avatar {
                width: 58px;
                height: 58px;
                left: calc(50% - 29px);
                top: -29px;
            }

            .avatar svg {
                width: 28px;
                height: 28px;
            }

            h1.title {
                font-size: 18px;
            }

            .field input[type="text"],
            .field input[type="password"] {
                font-size: 16px;
                padding: 12px 8px;
            }

            .btn {
                padding: 14px 12px;
                font-size: 16px;
            }

            .row {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="avatar" aria-hidden>
            <!-- simple user icon -->
            <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z" />
            </svg>
        </div>
        <h1 class="title">LOG-IN</h1>

        <?php if (!empty($_GET['registered'])): ?>
            <div class="msg success">Registration successful. Please log in.</div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="msg error"><?= h($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" style="margin-top:6px">
            <div class="field">
                <input type="text" name="user" placeholder="Username or Email" required autocomplete="username">
            </div>
            <div class="field">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <div class="row">
                <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
            </div>
            <button class="btn" type="submit">Log-In</button>
        </form>
</body>

</html>