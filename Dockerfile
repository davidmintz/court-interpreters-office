FROM ubuntu
MAINTAINER interpretersoffice.org

# Suppress warnings from apt about lack of Dialog
ENV DEBIAN_FRONTEND noninteractive

LABEL maintainer="interpretersoffice.org" \
      org.label-schema.docker.dockerfile="/Dockerfile" \
      org.label-schema.name="nginx and PHP 7.2" \
      org.label-schema.url="https://interpretersoffice.org/" \
      org.label-schema.vcs-url="https://github.com/davidmintz/court-interpreters-office"

# Initial apt update
RUN apt-get update
RUN apt-get install --yes apt-utils
RUN apt-get install --yes software-properties-common
RUN apt-get install --yes git
RUN apt-get install --yes wget
RUN apt-get install --yes curl
RUN apt-get install --yes vim 
RUN apt-get install --yes zip 
RUN apt-get install --yes unzip 
RUN apt-get install --yes default-mysql-client
RUN apt-get install --yes inetutils-syslogd
RUN apt-get install --yes postfix
RUN apt-get install --yes mailutils
RUN apt-get install --yes systemd
RUN apt-get install --yes redis

# Install nginx
RUN apt-get install --yes nginx

# Install PHP 7.2
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update
RUN apt-get install --yes \
    php7.2 \
    php7.2-fpm \
    php7.2-intl \
    php7.2-mysqli \
    php7.2-mbstring \
    php7.2-dom \
    php7.2-pdo-sqlite \
    php7.2-redis

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod a+x /usr/local/bin/composer

# Configure php & php-fpm
COPY .docker/config/php-fpm.conf /etc/php/7.2/fpm/php-fpm.conf
COPY .docker/config/php.ini /etc/php/7.2/cli/php.ini
COPY .docker/config/php.ini /etc/php/7.2/fpm/php.ini

# Configure nginx
RUN rm /etc/nginx/sites-enabled/default && rm /etc/nginx/sites-available/default
COPY .docker/config/000-default.conf /etc/nginx/sites-available/000-default.conf
RUN ln -s /etc/nginx/sites-available/000-default.conf /etc/nginx/sites-enabled/

# Configure working dir
ADD . /var/www
ADD buildfiles /var/www/config/autoload
RUN rm /var/www/config/development.config.php
RUN chown -R www-data /var/www
RUN chown -R www-data /var/lib/php/sessions
RUN chmod -R 777 /var/www/data

# Install composer
WORKDIR /var/www
RUN composer install

# Expose http
EXPOSE 80

## Use for docker-compose and google cloud
CMD service inetutils-syslogd start && service nginx start && service php7.2-fpm start && tail -f /var/log/nginx/access.log
