# MiniDLNA Web Rebuild
Web application to manage MiniDLNA database rebuild and update contents

# Install
- Create folder _/var/www/webapps_
  ```
  # mkdir -p /var/www/webapps
  ```
- Assign _www-data_ as owner for _/var/www/webapps_
  ```
  # chown www-data:www-data /var/www/webapps
  ```
- Clone git repo into _/var/www/webapps_
  ```
  # cd /var/www/webapps
  # git clone https://github.com/armando-basile/minidlna-web-rebuild.git minidlna-web-rebuild
  # cd /var/www/webapps/minidlna-web-rebuild
  # git config --global --add safe.directory /var/www/webapps/minidlna-web-rebuild
  # git config core.fileMode false
  ```
- Copy _contrib/minidlna-web-rebuild.conf_ to _/etc/nginx/conf.d/minidlna-web-rebuild.conf_
  ```
  # cp /var/www/webapps/minidlna-web-rebuild/contrib/minidlna-web-rebuild.conf /etc/nginx/conf.d/minidlna-web-rebuild.conf
  ```
- Set web app permissions
  ```
  # bash /var/www/webapps/minidlna-web-rebuild/contrib/set_permissions.sh
  ```

- If nginx return permission error, set selinux
  ```
  # semanage port -a -t http_port_t -p tcp 8201
  ```


# Usage
Open a web browser and navigate to http://{mediacenter ip}:8201/
