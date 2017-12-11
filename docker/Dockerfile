FROM php:5.6-fpm

RUN apt-get update && apt-get install -y libreoffice && apt-get install -y vim

WORKDIR /root/lwacparser/src

ENTRYPOINT ["sh","-c","/var/www/lwacparser/scripts/lwacrun.sh"]
