@echo off
REM One-shot test: fetch Biometric + push once, then exit
cd /d "%~dp0"
python -c "import requests" 2>nul || python -m pip install requests
python -c "from bridge import load_json, setup_logging, run_once, CONFIG_PATH; cfg=load_json(CONFIG_PATH); setup_logging(cfg.get('log_file') or 'logs/bridge.log'); run_once(cfg); print('Done')"
pause
