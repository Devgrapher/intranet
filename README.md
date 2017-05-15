# Ridibooks Intranet
[![Join the chat at https://gitter.im/ridibooks/intranet](https://badges.gitter.im/ridibooks/intranet.svg)](https://gitter.im/ridibooks/intranet?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Development Settings
1. Run `make` command from project's root directory.

2. Fill DB information at `.env`.
    ```
    public static $mysql_host = '';
    public static $mysql_user = '';
    public static $mysql_password = '';
    public static $mysql_db = '';
    ```

## 실행법

### PHP Built-in server
 
1. php -S localhost:8000 -t {현재 폴더}
2. 브라우저에서 localhost:8000 접속


### HHVM

1. /etc/hhvm/server.ini 파일 수정
    ```
    hhvm.php7.all = 1
    
    hhvm.server.port = 9000
    hhvm.server.type = proxygen  ;fastcgi
    
    hhvm.virtual_host[default][rewrite_rules][common][pattern] = "(.*)"
    hhvm.virtual_host[default][rewrite_rules][common][to] = "index.php/$1"
    hhvm.virtual_host[default][rewrite_rules][common][qsa] = true
    ```
2. hhvm -m server -c /etc/hhvm/server.ini
3. 브라우저에서 localhost:9000 접속


## 배포 방법

### 소스 배포

1. deployer설치 (https://deployer.org/)

2. deployer파일은 프로젝트 docs/deployer에 존재한다.
docs/deployer/stage에 deploy할 서버 설정 yml파일을 넣는다.
한 파일에 여러 서버를 적거나, 여러 파일로 나누어 적어도 된다.
(https://deployer.org/docs/servers 참고)

```
prod:
    repository: https://github.com/ridibooks/intranet
    branch: master
    host: intra.ridi.com
    port: 22
    user: <id>
    password: <password>
    deploy_path: <deploy위치>
    keep_releases: 10
dev:
    repository: https://github.com/ridibooks/intranet
    branch: master
    local: true
    deploy_path: <deploy위치>
```

3. deploy.php가 있는 경로상에서 아래 명령을 실행한다.

```
dep deploy [deploy할 stage]
```

### DB 배포

1. phinx설치 (https://phinx.org/)

2. phinx 설정파일 생성. testing및 production에 적절한 계정 정보를 세팅한다.

```
cp phinx.sample.yml phinx.yml
$EDITOR phinx.yml
```

3. 라이브 배포

```
vendor/bin/phinx migrate -e production
```
