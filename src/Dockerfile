# Use an official PHP image from Docker Hub
FROM php:7.4-cli

# Set the working directory inside the container
WORKDIR /usr/src/app

# Copy the project files into the container
COPY . .

# Install any PHP dependencies or extensions here if needed
# RUN docker-php-ext-install pdo pdo_mysql

# Set the command to run your bot
CMD ["php", "src/bot.php"]
