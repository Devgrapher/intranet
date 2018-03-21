# Ridibooks Intranet

[![Build Status](https://travis-ci.org/ridi/intranet.svg?branch=master)](https://travis-ci.org/ridi/intranet)
[![](https://images.microbadger.com/badges/version/ridibooks/intranet.svg)](http://microbadger.com/images/ridibooks/intranet "Get your own version badge on microbadger.com")
[![](https://images.microbadger.com/badges/image/ridibooks/intranet.svg)](http://microbadger.com/images/ridibooks/intranet "Get your own version badge on microbadger.com")
[![Join the chat at https://gitter.im/ridibooks/intranet](https://badges.gitter.im/ridibooks/intranet.svg)](https://gitter.im/ridibooks/intranet?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Settings
1. Run `make` command from project's root directory.

2. Write a .env (See [.env.sample](./.env.sample))

## Run

### with PHP Built-in server
 
1. Run ```php -S localhost:8000 -t `web` ```
2. Open `http://localhost:8000`

### with Docker

1. Run `make run-docker` or ```docker run -d --name ridi-intranet -p 8000:80 -v `pwd`:/var/www/html --env-file .env ridibooks/intranet```
2. Open `http://localhost:8000`


## Deploy DB schemas

### with Phinx

1. Write Phinx configuration. ([reference](http://docs.phinx.org/en/latest/configuration.html))

```
$EDITOR phinx.yml
```

2. Run `make deploy-db`
