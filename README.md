# ISEC3004
## Assignment 1
### Group 47

Each application is a Docker image built and started from its own `Makefile`. Docker and Make must be installed. Only one application can run at a time, because every target publishes the app on host port **8080**.

From the application directory:

```bash
make build
make run
```

`make build` builds the image. `make run` starts the container. Open [http://localhost:8080](http://localhost:8080) in a browser. Stop the container with `Ctrl+C`.

Remove the image when you are finished:

```bash
make clean
```

## CRLF injection

| Application | Directory |
| --- | --- |
| Vulnerable | `crlf_injection/vulnerable_app` |
| Secure | `crlf_injection/secure_app` |

```bash
cd crlf_injection/vulnerable_app
make build
make run
```

Use `crlf_injection/secure_app` for the secure version. Sign in with `alice` / `alice123` or `bob` / `bob123`.

## Cross-site request forgery

| Application | Directory |
| --- | --- |
| Vulnerable | `cross_site_request_forgery/vulnerable_app` |
| Secure | `cross_site_request_forgery/secure_app` |

```bash
cd cross_site_request_forgery/vulnerable_app
make build
make run
```

Use `cross_site_request_forgery/secure_app` for the secure version. Sign in with `demo` / `demo123`.
