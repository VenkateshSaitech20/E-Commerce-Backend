FROM 639782257614.dkr.ecr.ap-south-1.amazonaws.com/lmd-php-8.2:latest
WORKDIR /var/www/html
COPY . /var/www/html/
COPY apache2.conf /etc/apache2/apache2.conf
# Print the working directory
RUN chmod -R 777 storage
RUN chmod -R 777 bootstrap
RUN pwd
RUN ls -la
RUN ls -la storage/
RUN ls -la bootstrap/
# Perform necessary operations
EXPOSE 80
CMD ["apachectl", "-D", "FOREGROUND"]