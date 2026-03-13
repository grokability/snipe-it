<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ Lang::get(Config::get('maintenancemode.language-path', 'maintenancemode::defaults.') . '.title') }}</title>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #333;
            -webkit-transform-style: preserve-3d;
            -moz-transform-style: preserve-3d;
            transform-style: preserve-3d;
            font-family: -apple-system, BlinkMacSystemFont,
            "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell",
            "Fira Sans", "Droid Sans", "Helvetica Neue",
            sans-serif;

        }
        header {
            width: 80%;
            color: #fff;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            text-align: center;
            margin: 0 auto;
        }
        header h1, header p  {
            margin: 0;
            padding: .25em 0;
        }
        header p {
            color: #999;
            font-size: .8em;
        }
    </style>
</head>
<body>
<header>
    <h1>{{ ${Config::get('maintenancemode.inject.prefix').'Message'} }}</h1>
    <p>{{ Lang::get(Config::get('maintenancemode.language-path', 'maintenancemode::defaults.') . '.last-updated', ['timestamp' => ${Config::get('maintenancemode.inject.prefix').'Timestamp'}->diffForHumans()]) }}</p>
</header>
</body>
</html>
