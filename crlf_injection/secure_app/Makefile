APP=isec3004.assignment1.group47.crlf_injection.secure

all: build

build:
	docker build --rm --tag=$(APP) .

run:
	docker run --init -p 0.0.0.0:8081:8081/tcp -v "$(CURDIR):/home/secure_programming/app" -it --rm $(APP)

clean:
	docker image rm $(APP)
	docker system prune
