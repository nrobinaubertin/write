Each of your posts should be a markdown file with his assets in its own directory.  
All the post directories should go in a posts/ directory at the root of the repo.
You should create a homepage in the same way you create a post : by writing a markdown file. However, the homepage mardown file must live at the root of the posts/ directory.

The are some options that can be specified by placing html comments in your markdown file like this:
```
<!-- key: value -->
```
The options are:
- cover-image : specify a relative path to an image that should be used as cover.
- title : specify a title for your post
- description : specify a description (useful for social media sharing)
- title-font : specify a relative path to a font that will be used on your titles
- text-font : specify a relative path to a font that will be used on your text

To install the project:
```
php install.php
```

Example nginx configuration:
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

Docker is a good way to get up and running in no time.  
Just use my dockerfile [nginx-php](https://github.com/nrobinaubertin/dockerfiles/tree/master/nginx-php) with PHP_EXT="php7-json php7-gd php7-mbstring php7-openssl".
