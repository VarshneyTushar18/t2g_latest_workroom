#!/bin/bash
grep "FastCGI" /var/log/nginx/error.log | tail -3 | cut -c1-500
