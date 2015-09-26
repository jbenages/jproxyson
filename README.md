# jproxyson
Create a proxy in free webhosting with curl and php.


## What need you?

**Server** - A web server in hosting, can be free or not, but the server has to be installed PHP and PHP-curl.

**Client** - PHP 5 or > ,PHP-cli and PHP-curl.


## What can do for you?

If you need proxys of others countries or more fast that public proxies **jProxySon** is your solution. It make tunnel with curl request with Client ( in your script ) to Server ( in web hosting server ) and return you the code of page of your choise.


## What cannot do for you?

Cant do proxy for navigators or other programs, jProxySon isn't proxy HTTP or SOCKS4/5. Only work for your own scripts/webs.


## How does it work?

1. The Client Script encrypt data request and send with curl to the Server Script.
2. The Server Script decrypt data client request and mount it and send curl to Url.
3. The Server Script encrypt data of request url and return the code of page request and more information ( like errors in request ).
4. The Client Script get and decrypt data, finally return array with all data of request.


## INSTALL

1. IMPORTANT. Change the key for encrypt in *src/Client.php* and *src/Server.php*. It's in constant KEY.
2. Search webhosting with PHP support.
3. Put project in root folder of webhosting.
4. Set the index.php main file of web.
5. Find url of your webhost and put in Client Class *$proxys*.
6. Configure client for your request and READY!
