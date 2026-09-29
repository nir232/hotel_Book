# Use an official PHP image with Apache web server pre-installed
FROM php:8.2-apache

# Copy all your project files into the web server directory
COPY . /var/www/html/

# Expose port 80 so the web service can take traffic
EXPOSE 80
