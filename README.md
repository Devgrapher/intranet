# Ridibooks Intranet
[![Join the chat at https://gitter.im/ridibooks/intranet](https://badges.gitter.im/ridibooks/intranet.svg)](https://gitter.im/ridibooks/intranet?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Settings
1. Run `make` command from project's root directory.

2. Write a .env (See [.env.sample](./.env.sample))

## Run

### with PHP Built-in server
 
1. Run ```php -S localhost:8000 -t `pwd` ```
2. Open `http://localhost:8000`.


### with Docker

1. Run `make run-docker` or ```docker run -d --name ridi-intranet -p 8000:80 -v `pwd`:/var/www/html --env-file .env ridibooks/intranet```
2. Open `http://localhost:8000`.



## Deploy codes

### with Deployer

1. Write a server configuration at docs/deployer/stage ([reference](https://deployer.org/docs/hosts#inventory-file))
```
prod:
    repository: https://github.com/ridibooks/intranet
    branch: master
    host: intra.ridi.com
    port: 22
    user: <id>
    password: <password>
    deploy_path: <path to deploy>
    keep_releases: 10
dev:
    repository: https://github.com/ridibooks/intranet
    branch: master
    local: true
    deploy_path: <path to deploy>
```

2. Run `make deploy`

## Deploy DB schemas

### with Phinx

1. Write Phinx configuration. ([reference](http://docs.phinx.org/en/latest/configuration.html))

```
cp phinx.sample.yml phinx.yml
$EDITOR phinx.yml
```

2. Run `make deploy-db`
