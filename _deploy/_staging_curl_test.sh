#!/bin/bash
curl -sS -o /dev/null -w 'admin=%{http_code} redirect=%{redirect_url}\n' --max-time 30 -L http://127.0.0.1/admin/
curl -sS -o /dev/null -w 'auth=%{http_code}\n' --max-time 30 http://127.0.0.1/admin/authentication
tail -5 /var/log/nginx/error.log
