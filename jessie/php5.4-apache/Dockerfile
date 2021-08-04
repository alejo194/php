#
# NOTE: THIS DOCKERFILE IS GENERATED VIA "apply-templates.sh"
#
# PLEASE DO NOT EDIT IT DIRECTLY.
#

FROM debian:jessie

# prevent Debian's PHP packages from being installed
# https://github.com/docker-library/php/pull/542
RUN set -eux; \
	{ \
		echo 'Package: php*'; \
		echo 'Pin: release *'; \
		echo 'Pin-Priority: -1'; \
	} > /etc/apt/preferences.d/no-debian-php

# dependencies required for running "phpize"
# (see persistent deps below)
ENV PHPIZE_DEPS \
		autoconf \
		dpkg-dev \
		file \
		g++ \
		gcc \
		libc-dev \
		make \
		pkg-config \
		re2c

# persistent / runtime deps
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		$PHPIZE_DEPS \
		ca-certificates \
		curl \
		xz-utils \
	; \
	rm -rf /var/lib/apt/lists/*

ENV PHP_INI_DIR /usr/local/etc/php
RUN set -eux; \
	mkdir -p "$PHP_INI_DIR/conf.d"; \
# allow running as an arbitrary user (https://github.com/docker-library/php/issues/743)
	[ ! -d /var/www/html ]; \
	mkdir -p /var/www/html; \
	chown www-data:www-data /var/www/html; \
	chmod 777 /var/www/html

ENV APACHE_CONFDIR /etc/apache2
ENV APACHE_ENVVARS $APACHE_CONFDIR/envvars

RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends apache2; \
	rm -rf /var/lib/apt/lists/*; \
	\
# generically convert lines like
#   export APACHE_RUN_USER=www-data
# into
#   : ${APACHE_RUN_USER:=www-data}
#   export APACHE_RUN_USER
# so that they can be overridden at runtime ("-e APACHE_RUN_USER=...")
	sed -ri 's/^export ([^=]+)=(.*)$/: ${\1:=\2}\nexport \1/' "$APACHE_ENVVARS"; \
	\
# setup directories and permissions
	. "$APACHE_ENVVARS"; \
	for dir in \
		"$APACHE_LOCK_DIR" \
		"$APACHE_RUN_DIR" \
		"$APACHE_LOG_DIR" \
	; do \
		rm -rvf "$dir"; \
		mkdir -p "$dir"; \
		chown "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$dir"; \
# allow running as an arbitrary user (https://github.com/docker-library/php/issues/743)
		chmod 777 "$dir"; \
	done; \
	\
# delete the "index.html" that installing Apache drops in here
	rm -rvf /var/www/html/*; \
	\
# logs should go to stdout / stderr
	ln -sfT /dev/stderr "$APACHE_LOG_DIR/error.log"; \
	ln -sfT /dev/stdout "$APACHE_LOG_DIR/access.log"; \
	ln -sfT /dev/stdout "$APACHE_LOG_DIR/other_vhosts_access.log"; \
	chown -R --no-dereference "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$APACHE_LOG_DIR"

# Apache + PHP requires preforking Apache for best results
RUN a2dismod mpm_event && a2enmod mpm_prefork

# PHP files should be handled by PHP, and should be preferred over any other file type
RUN { \
		echo '<FilesMatch \.php$>'; \
		echo '\tSetHandler application/x-httpd-php'; \
		echo '</FilesMatch>'; \
		echo; \
		echo 'DirectoryIndex disabled'; \
		echo 'DirectoryIndex index.php index.html'; \
		echo; \
		echo '<Directory /var/www/>'; \
		echo '\tOptions -Indexes'; \
		echo '\tAllowOverride All'; \
		echo '</Directory>'; \
	} | tee "$APACHE_CONFDIR/conf-available/docker-php.conf" \
	&& a2enconf docker-php

ENV PHP_EXTRA_BUILD_DEPS apache2-dev
ENV PHP_EXTRA_CONFIGURE_ARGS --with-apxs2 --disable-cgi

# Apply stack smash protection to functions using local buffers and alloca()
# Make PHP's main executable position-independent (improves ASLR security mechanism, and has no performance impact on x86_64)
# Enable optimization (-O2)
# Enable linker optimization (this sorts the hash buckets to improve cache locality, and is non-default)
# https://github.com/docker-library/php/issues/272
# -D_LARGEFILE_SOURCE and -D_FILE_OFFSET_BITS=64 (https://www.php.net/manual/en/intro.filesystem.php)
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_CPPFLAGS="$PHP_CFLAGS"
ENV PHP_LDFLAGS="-Wl,-O1 -pie"

ENV GPG_KEYS CBAF69F173A0FEA4B537F470D66C9593118BCCB6 F38252826ACD957EF380D39F2F7956BC5DA04B5D

ENV PHP_VERSION 5.4.45
ENV PHP_URL="http://php.net/get/php-5.4.45.tar.bz2/from/this/mirror"  PHP_ASC_URL="http://php.net/get/php-5.4.45.tar.bz2.asc/from/this/mirror"

RUN set -xe; \
        \
        fetchDeps=' \
                wget \
        '; \
        if ! command -v gpg > /dev/null; then \
                fetchDeps="$fetchDeps \
                        dirmngr \
                        gnupg \
                "; \
        fi; \
        apt-get update; \
        apt-get install -y --no-install-recommends $fetchDeps; \
        rm -rf /var/lib/apt/lists/*; \
        \
        mkdir -p /usr/src; \
        cd /usr/src; \
        \
        wget -O php.tar.bz2 "$PHP_URL"; \
        \
        if [ -n "$PHP_SHA256" ]; then \
                echo "$PHP_SHA256 *php.tar.bz2" | sha256sum -c -; \
        fi; \
        if [ -n "$PHP_MD5" ]; then \
                echo "$PHP_MD5 *php.tar.bz2" | md5sum -c -; \
        fi; \
        \
        if [ -n "$PHP_ASC_URL" ]; then \
                wget -O php.tar.bz2.asc "$PHP_ASC_URL"; \
                export GNUPGHOME="$(mktemp -d)"; \
                for key in $GPG_KEYS; do \
                        gpg --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
                done; \
                gpg --batch --verify php.tar.bz2.asc php.tar.bz2; \
                rm -rf "$GNUPGHOME"; \
        fi; 

COPY docker-php-source /usr/local/bin/

RUN set -eux; \
        \
        savedAptMark="$(apt-mark showmanual)"; \
        apt-get update; \
        apt-get install -y --no-install-recommends \
                libcurl4-openssl-dev \
                libpcre3-dev \
                libreadline6-dev \
                librecode-dev \
                libedit-dev \
                libsqlite3-dev \
                libssl-dev \
                libxml2-dev \
                zlib1g-dev \
                bzip2 \
                curl \
                libbz2-dev \
                ${PHP_EXTRA_BUILD_DEPS:-} \
        ; \
        rm -rf /var/lib/apt/lists/*; \
        \
        export \
                CFLAGS="$PHP_CFLAGS" \
                CPPFLAGS="$PHP_CPPFLAGS" \
                LDFLAGS="$PHP_LDFLAGS" \
        ; \
        docker-php-source extract; \
        cd /usr/src/php; \
        ./configure \
                --with-config-file-path="$PHP_INI_DIR" \
                --with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
                $PHP_EXTRA_CONFIGURE_ARGS \
                --disable-cgi \
                --enable-mysqlnd \
                --with-curl \
                --with-openssl \
                --with-pcre \
                --with-readline \
                --with-recode \
                --with-zlib \
                --with-mssql \
                --with-pod-dblib \
                --enable-mbstring \
        ; \
        make ; \
        make install; \
        find /usr/local/bin /usr/local/sbin -type f -executable -exec strip --strip-all '{}' + || true; \
        make clean; \
        cd /; \
        docker-php-source delete; \
        \
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
        apt-mark auto '.*' > /dev/null; \
        [ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
        find /usr/local -type f -executable -exec ldd '{}' ';' \
                | awk '/=>/ { print $(NF-1) }' \
                | sort -u \
                | xargs -r dpkg-query --search \
                | cut -d: -f1 \
                | sort -u \
                | xargs -r apt-mark manual \
        ; \
        apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
        \
        php --version; \
        \
# https://github.com/docker-library/php/issues/443
        pecl update-channels; \
        rm -rf /tmp/pear ~/.pearrc

COPY docker-php-ext-* docker-php-entrypoint php.ini-production /usr/local/bin/

RUN  set -eux \
     && mkdir -p /usr/src/php \
     && cp /usr/local/bin/php.ini-production /usr/src/php \
     && ln -sf /usr/share/zoneinfo/Asia/Taipei  /etc/localtime \
     && cp /usr/src/php/php.ini-production $PHP_INI_DIR/php.ini \
     && echo 'date.timezone = "Asia/Taipei"' >> $PHP_INI_DIR/php.ini \
     && echo 'expose_php = Off' >> $PHP_INI_DIR/php.ini \
     && sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" $PHP_INI_DIR/php.ini \
     && sed -i "s/memory_limit = 128M/memory_limit = 256M/" $PHP_INI_DIR/php.ini
COPY package.xml memcache-2.2.4 /usr/src/php/ext/memcache/ 
RUN apt-get update && apt-get install -y \
         freetds-dev \
         libfreetype6-dev \
         libjpeg62-turbo-dev \
         libmcrypt-dev \
         libpng12-dev \
         zlib1g-dev \
         libmemcached-dev \
         postgresql \
         postgresql-server-dev-all \

     && ln -s /usr/lib/x86_64-linux-gnu/libsybdb.so.5 /usr/lib/libsybdb.so \
     && ldconfig -v \
     && docker-php-ext-install memcache \ 
     && docker-php-ext-configure mssql --with-mssql \
     && docker-php-ext-install mssql \
     && docker-php-ext-configure pdo_dblib --with-pdo-dblib \
     && docker-php-ext-install pdo_dblib \
     && docker-php-ext-configure mcrypt --with-mcrypt \
     && docker-php-ext-install   mcrypt \
     && docker-php-ext-configure pdo --with-pdo \
     && docker-php-ext-install pdo \
     && docker-php-ext-configure pdo_pgsql --with-pdo_pgsql \
     && docker-php-ext-install pdo_pgsql \
     && docker-php-ext-configure zip --with-zip \
     && docker-php-ext-install zip \
     && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
     && docker-php-ext-install gd \

     #&& docker-php-ext-download memcache 2.2.4 \
     #&& docker-php-ext-install memcache \

     && rm -Rf /tmp/pear \
     && apt-get autoclean \
     && apt-get clean \
     && rm -rf /var/lib/apt/lists/*

ENTRYPOINT ["docker-php-entrypoint"]
# https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

COPY apache2-foreground /usr/local/bin/
WORKDIR /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
