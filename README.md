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

### 소스 배포 (deployer)

1. docs/deployer/stage에 deployer 서버 설정 파일 작성
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

2. 아래 명령을 실행

```
make deploy
```

### .env 파일 배포

위에서 설치한 deployer를 이용해 배포한다

1. 서버의 `.env` 파일 다운로드

```
cd docs/deployer
dep env:pull [stage파일 이름]
```

2. 아래 이름으로 다운로드된 파일의 내용을 수정한다

```
.env_[서버이름]
```

3. 서버에 배포

```
dep env:push [stage파일 이름]
```

업로드된 내용 복구

```
dep env:rollback [stage파일 이름]
```

복구는 마지막으로 백업된 1개의 내용에 대해서만 가능하다.

### DB 배포 (phinx)

1. phinx 설정파일 생성. testing및 production에 적절한 계정 정보를 세팅한다.

```
cp phinx.sample.yml phinx.yml
$EDITOR phinx.yml
```

2. 아래 명령 실행

```
make deploy-db
```
