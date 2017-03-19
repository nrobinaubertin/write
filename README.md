example nginx configuration :
```
location /write {
    try_files $uri /write/index.php$is_args$args;

    location ~ index\.php(/|$) {
        fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
}
```
