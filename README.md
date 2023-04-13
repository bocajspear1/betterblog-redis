## BetterBlog

A definitely not buggy and insecure PHP blogging platform! Works even without a database too, though if you want better features, enable a database.

I might need to do a bit of work to secure this some day, so don't put this on anything valuable right now...

## Developing with Docker

Ensure Docker is installed!

```
docker build -t betterblog-redis .
docker run --rm -it --name betterblog-test -v`pwd`/app:/var/www/html betterblog-redis
```

## Notes

* https://www.exploit-db.com/papers/45870


