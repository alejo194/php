#### 修改nginx版本，隐藏版本对nginx WEB启动安全的作用，如下：
> $ sed -i -e 's/1.12.2//g' -e 's/nginx\//JWS/g' -e 's/"NGINX"/"JWS"/g' src/core/nginx.h

+ make -j4 其中-j4代表使用cpu核数
+ 端口查看： netstat -tnlp | grep "80"
