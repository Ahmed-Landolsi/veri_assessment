# How it Works

Docker builds an image containing the application in src/ and all of its dependencies by using the Dockerfile contained in this repository.

The Dockerfile tells docker to use the [official PHP Docker image](https://hub.docker.com/_/php/) as the parent image.

Finally, docker copies everything in src/ inside this repository to the /var/www/html folder inside the image. This is the Apache web root directory.

# Setup

 - `git clone` this repository
 - `sudo docker build -t veriassesment .` 

 - Testing with CLI: Run docker command in interactiv mode:
  `sudo docker run -it veriassesment bash`
  Then execute:
  `php index.php`

 - Testing with browser: Run docker command:
  `sudo docker run -p 80:80 veriassesment`
  Then open browser on localhost:80
