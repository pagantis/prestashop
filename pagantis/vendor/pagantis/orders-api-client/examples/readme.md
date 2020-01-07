# OrdersApiClient demo

This file demonstrates how to generate a order in Pagantis using Orders API Client

## Run

To run this demo, you need to clone it and install dependencies:

```
git clone https://github.com/pagantis/orders-api-client.git .
composer install
```

Then you can run the web application using PHP's built-in server:

```
php -S 0.0.0.0:8000 -t examples/
```

The web application is running at [http://localhost:8000/simpleTest.php](http://localhost:8000/simpleTest.php).

To trace the execution in real time, you can open the log file using the command: 
```
tail -f examples/pagantis.log
``` 
