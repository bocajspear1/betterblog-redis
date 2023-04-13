FROM ubuntu:18.04


RUN apt-get -qq update \
&& DEBIAN_FRONTEND="noninteractive" apt-get install -y apache2 libapache2-mod-php php-redis redis-server iputils-ping wget 

RUN rm /var/www/html/index.html

RUN sed -i 's/display_errors = Off/display_errors = On/' /etc/php/7.2/apache2/php.ini
RUN sed -i 's/allow_url_include = Off/allow_url_include = On/' /etc/php/7.2/apache2/php.ini

COPY app/ /var/www/html

EXPOSE 80

CMD /usr/sbin/apachectl start; /usr/bin/redis-server
