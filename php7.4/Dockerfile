FROM php:7.4-fpm
ENV PHP_INI_DIR /usr/local/etc/php
RUN ln -sf /usr/share/zoneinfo/Asia/Taipei  /etc/localtime \
     && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
     && echo 'date.timezone = "Asia/Taipei"' >> $PHP_INI_DIR/php.ini \
     && echo 'expose_php = Off' >> $PHP_INI_DIR/php.ini \
     && sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" $PHP_INI_DIR/php.ini \
     && sed -i "s/memory_limit = 128M/memory_limit = 256M/" $PHP_INI_DIR/php.ini
## install freetds
RUN apt-get update -qq && apt-get install -y --no-install-recommends build-essential libpq-dev nodejs libc6-dev wget
RUN wget ftp://ftp.freetds.org/pub/freetds/stable/freetds-1.00.27.tar.gz && \
                tar -xzf freetds-1.00.27.tar.gz && \
                cd freetds-1.00.27 && \
                ./configure --prefix=/usr/local --with-tdsver=7.3 && \
                make && \
                make install
RUN apt-get update && apt-get install -y \
                libfreetype6-dev \
                libjpeg62-turbo-dev \
                libpng-dev \
                freetds-dev \
                libmcrypt-dev \
                zlib1g-dev \
                libmemcached-dev \
        && ln -s /usr/lib/x86_64-linux-gnu/libsybdb.so.5 /usr/lib/libsybdb.so \
        && ldconfig -v \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd \
        && docker-php-ext-configure mysqli --with-mysqli \
        && docker-php-ext-install mysqli \
        && docker-php-ext-configure gettext --with-gettext \
        && docker-php-ext-install gettext
RUN     pecl install redis-5.1.1 \
        && pecl install xdebug-2.8.1 \
        && docker-php-ext-enable redis xdebug
## install sys environment language
RUN apt-get update && apt-get install -y \
    libmcrypt-dev \
    openssl \
    locales \
    gettext \
    wget \
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
ENV LC_ALL C.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
