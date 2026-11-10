#!/bin/bash
set -e
# Live cron uses http://127.0.0.1 — application/config/app_email_policy.php MUST allow
# production_crm (see app_is_live_production_workroom). Verify: curl .../cron/cron_health
# expects cron_loopback_email_blocked=false and cron_email_delivery_ok=true.
MARK="# Workroom scheduled daily report emails"
LOGDIR="/home/ubuntu/workroom-cron-logs"
mkdir -p "$LOGDIR"
chmod 755 "$LOGDIR"

# Replace broken /var/log paths (ubuntu crontab cannot write there)
crontab -l 2>/dev/null | sed "s|/var/log/workroom-cron|$LOGDIR/workroom-cron|g" > /tmp/cron.new.$$
grep -q 'send_scheduled_daily_morning_reports' /tmp/cron.new.$$ || echo "30 2 * * 1-6 wget -q -O $LOGDIR/workroom-cron-morning.json http://127.0.0.1/cron/send_scheduled_daily_morning_reports >/dev/null 2>&1" >> /tmp/cron.new.$$
grep -q 'send_scheduled_daily_snapshot_report' /tmp/cron.new.$$ || echo "35 7 * * 1-6 wget -q -O $LOGDIR/workroom-cron-snapshot.json http://127.0.0.1/cron/send_scheduled_daily_snapshot_report >/dev/null 2>&1" >> /tmp/cron.new.$$
grep -q 'send_scheduled_daily_afternoon_reports' /tmp/cron.new.$$ || echo "15 4 * * 1-6 wget -q -O $LOGDIR/workroom-cron-afternoon.json http://127.0.0.1/cron/send_scheduled_daily_afternoon_reports >/dev/null 2>&1" >> /tmp/cron.new.$$
crontab /tmp/cron.new.$$
rm -f /tmp/cron.new.$$
echo "Crontab log dir: $LOGDIR"
crontab -l | grep -E 'scheduled_daily|send_daily_ecommerce_break'
