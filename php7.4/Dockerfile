FROM php:7.4-fpm
ENV PHP_INI_DIR /usr/local/etc/php
RUN ln -sf /usr/share/zoneinfo/Asia/Taipei  /etc/localtime \
     && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
     && echo 'date.timezone = "Asia/Taipei"' >> $PHP_INI_DIR/php.ini \
     && echo 'expose_php = Off' >> $PHP_INI_DIR/php.ini \
     && sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" $PHP_INI_DIR/php.ini \
     && sed -i "s/memory_limit = 128M/memory_limit = 256M/" $PHP_INI_DIR/php.ini
# odbc driver
# https://learn.microsoft.com/en-us/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-linux-2017&tabs=debian18-install%2Cdebian17-install%2Cdebian8-install%2Credhat7-13-install%2Crhel7-offline#13
RUN curl https://packages.microsoft.com/keys/microsoft.asc |  tee /etc/apt/trusted.gpg.d/microsoft.asc \
    && curl https://packages.microsoft.com/config/debian/11/prod.list |  tee /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    # optional: for bcp and sqlcmd
    && ACCEPT_EULA=Y apt-get install -y mssql-tools \
    && echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc \
    # optional: for unixODBC development headers
    && apt-get install -y unixodbc-dev \
    # optional: kerberos library for debian-slim distributions
    &&  apt-get install -y libgssapi-krb5-2
RUN apt-get update && apt-get install -y \
                libfreetype6-dev \
                libjpeg62-turbo-dev \
                libpng-dev \
                freetds-dev \
                libmcrypt-dev \
                zlib1g-dev \
                libmemcached-dev \
                # intl depend
                zlib1g-dev libicu-dev g++ \
                # uuid depend
                uuid-dev \
                # zip depend
                libzip-dev \
        && ln -s /usr/lib/x86_64-linux-gnu/libsybdb.so.5 /usr/lib/libsybdb.so \
        && ldconfig -v \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd \
        && docker-php-ext-configure mysqli --with-mysqli \
        && docker-php-ext-install mysqli \
        && docker-php-ext-configure intl \
        && docker-php-ext-install intl \
        && docker-php-ext-configure zip --with-zip \
        && docker-php-ext-install zip \
        && docker-php-ext-configure gettext --with-gettext \
        && docker-php-ext-install gettext
RUN     pecl install redis-5.1.1 mcrypt-1.0.5 xlswriter-1.5.2  uuid-1.2.0 sqlsrv-5.10.1 pdo_sqlsrv-5.10.1 \
        && docker-php-ext-enable redis mcrypt xlswriter uuid sqlsrv pdo_sqlsrv
## install sys environment language
RUN apt-get install -y \
    libmcrypt-dev \
    locales \
    gettext \
    git \
    libicu-dev \
    zlib1g-dev
RUN dpkg-reconfigure locales \
        && locale-gen \
        && /usr/sbin/update-locale LANG=C.UTF-8
RUN echo 'zh_CN.UTF-8 UTF-8' >> /etc/locale.gen \
    && echo 'id_ID.UTF-8 UTF-8' >> /etc/locale.gen \
    && echo 'ko_KR.UTF-8 UTF-8' >> /etc/locale.gen \
    && echo 'zh_TW.UTF-8 UTF-8' >> /etc/locale.gen \
    && echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen \
    && echo "ja_JP.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo 'vi_VN.UTF-8 UTF-8' >> /etc/locale.gen \
        && locale-gen
RUN cd /var/www && php -r "readfile('https://getcomposer.org/installer');" | php \
    && mv /var/www/composer.phar /usr/local/bin/composer && chmod 755 /usr/local/bin/composer
# install openssl 1.1.1p
RUN apt-get install -y wget \
    && cd /tmp \
    && wget https://www.openssl.org/source/openssl-1.1.1p.tar.gz -O openssl-1.1.1p.tar.gz \
    && tar -zxvf openssl-1.1.1p.tar.gz \
    && cd openssl-1.1.1p \
    && ./config \
    && make \
    && make install \
    && ldconfig  \
    && cd /tmp && rm -rf /tmp/openssl* \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    # optional: for bcp and sqlcmd
    && ACCEPT_EULA=Y apt-get install -y mssql-tools
ENV LC_ALL C.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
