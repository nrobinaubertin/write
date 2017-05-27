#!/bin/sh
#exec su-exec www:www /bin/s6-svscan /etc/s6.d
exec /bin/s6-svscan /etc/s6.d
