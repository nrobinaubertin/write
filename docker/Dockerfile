FROM alpine:edge

RUN apk update

# install core
RUN set -xe \
    && apk add --no-cache s6 su-exec ca-certificates

# set correct datetime
RUN set -xe \
    && apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && echo "Europe/Paris" >  /etc/timezone \
    && apk del tzdata

# install nginx & php-fpm
RUN set -xe \
    && apk add --no-cache nginx \
    && apk add --no-cache php7-fpm php7-json php7-gd php7-mbstring php7-openssl

COPY nginx.conf /etc/nginx/nginx.conf 
COPY php-fpm.conf /etc/php7/php-fpm.conf
COPY run.sh /usr/local/bin/run.sh 
COPY s6.d /etc/s6.d

# set the www user
RUN set -xe \
    && adduser -D -u 1000 -g 'www' www \
    && mkdir /www \
    && mkdir /log \
    && mkdir -p /log/php \
    && mkdir -p /log/nginx \
    && chown -R www:www /www /log /var/lib /etc/s6.d \
    && chmod -R +x /usr/local/bin /etc/s6.d

VOLUME /www

EXPOSE 80 443

CMD ["run.sh"]
