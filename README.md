
## Developing with Docker

Ensure Docker is installed!

```
docker build -t betterblog .
docker run --rm -it --name betterblog-test -v`pwd`/app:/var/www/html betterblog
```

## Notes

* https://www.exploit-db.com/papers/45870


