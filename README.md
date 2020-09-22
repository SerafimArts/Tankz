# Tankz

Online free to play super military RPG shooter without registration and SMS.

> *Note: It's enough to sell your soul to the devil

![](https://habrastorage.org/webt/hw/jm/_s/hwjm_sz0vsxbpo5qy-cabnupw0u.png)

## Installation

```sh
$ composer install
```

## Execution

1) Open `server.php` and edit TCP connection
```sh
$server->run(
    '0.0.0.0:80' // << Your IP:PORT here
);
```

2) Open `client.php` and edit TCP connection (you should paste server's connection here)
```sh
$app->run(
    '127.0.0.1:80' // << Server's IP:PORT here
);
```

3) Run `client.php`
```sh
$ php client.php
```

4) Call the mental postal ♥
