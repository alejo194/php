#!/bin/bash
/sbin/ip route|awk '/default/ { print $3 " docker1" }' >> /etc/hosts
/usr/local/sbin/php-fpm

