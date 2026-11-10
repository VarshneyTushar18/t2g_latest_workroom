#!/bin/bash
curl -sk -D - -o /tmp/snap_staff_body.txt -w "\nstaff_code:%{http_code}\n" \
  -H 'Host: t2gworkroom.com' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -H 'Accept: application/json' \
  --data 'email=not-a-user@example.com&password=wrong' \
  https://127.0.0.1/api/api/staff | head -n 20
echo '---BODY---'
head -c 600 /tmp/snap_staff_body.txt
echo
echo '---TOKEN---'
curl -sk -o /tmp/snap_token.txt -w "token_code:%{http_code} size:%{size_download}\n" \
  -H 'Host: t2gworkroom.com' https://127.0.0.1/token.json
head -c 120 /tmp/snap_token.txt
echo
echo '---LIVE STAFF MODEL HEAD---'
sudo sed -n '28,55p' /var/www/html/t2gworkroom/api/application/models/api/Staff_model.php
echo '---LIVE CONTROLLER BRANCH---'
sudo sed -n '26,70p' /var/www/html/t2gworkroom/api/application/controllers/api/Staff.php
