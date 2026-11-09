#!/bin/bash
set -e
PASS=$(grep APP_DB_PASSWORD /var/www/workroom/application/config/app-config.php | sed "s/.*'\([^']*\)'.*/\1/")
DB=production_crm
mysql -uroot -p"$PASS" "$DB" <<'SQL'
-- Safe to re-run: skip if index exists (MySQL 8 lacks IF NOT EXISTS on all versions)
SET @db = DATABASE();
SELECT CONCAT('Adding indexes on ', @db) AS msg;

-- staffid lookups (dashboard "my tasks")
SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='tbltask_assigned' AND index_name='idx_staffid');
SET @sql := IF(@exists=0, 'ALTER TABLE tbltask_assigned ADD INDEX idx_staffid (staffid)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='tbltask_assigned' AND index_name='idx_taskid');
SET @sql := IF(@exists=0, 'ALTER TABLE tbltask_assigned ADD INDEX idx_taskid (taskid)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @exists := (SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=@db AND table_name='tbltasks' AND index_name='idx_status');
SET @sql := IF(@exists=0, 'ALTER TABLE tbltasks ADD INDEX idx_status (status)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SHOW INDEX FROM tbltask_assigned WHERE Key_name IN ('idx_staffid','idx_taskid');
SQL
echo INDEXES_DONE
