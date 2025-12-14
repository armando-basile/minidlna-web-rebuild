#!/bin/bash

chown -R www-data:www-data "/var/www/webapps/minidlna-web-rebuild"
find "/var/www/webapps/minidlna-web-rebuild" -type d -exec chmod 775 {} +
find "/var/www/webapps/minidlna-web-rebuild" -type f -exec chmod 664 {} +

