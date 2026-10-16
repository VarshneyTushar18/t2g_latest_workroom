<?php
defined('BASEPATH') or exit('No direct script access allowed');
$httpCode = isset($http_code) ? (int) $http_code : 503;
if (!headers_sent()) {
    http_response_code($httpCode);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 300');
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workroom — temporary maintenance</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background: linear-gradient(145deg, #0f172a 0%, #141e46 45%, #1e293b 100%);
            color: #e2e8f0;
            padding: 24px;
        }
        .card {
            max-width: 480px;
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 36px 32px;
            text-align: center;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
        }
        .logo {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 12px;
            color: #fff;
        }
        p {
            margin: 0 0 24px;
            font-size: 15px;
            line-height: 1.55;
            color: #cbd5e1;
        }
        .btn {
            display: inline-block;
            padding: 10px 22px;
            border-radius: 8px;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn:hover { background: #1d4ed8; }
        .hint {
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Tech2Globe Workroom</div>
        <h1>We&rsquo;ll be back shortly</h1>
        <p>
            Workroom is undergoing brief maintenance or a temporary issue is being fixed.
            Please try again in a few minutes. Your data is safe.
        </p>
        <a class="btn" href="javascript:location.reload()">Refresh page</a>
        <p class="hint">If this continues, contact your IT team or HR.</p>
    </div>
</body>
</html>
