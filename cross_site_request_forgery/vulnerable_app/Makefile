APP=isec3004.assignment1.group47.csrf.vulnerable

all: build

build:
	docker build --rm --tag=$(APP) .

run:
	docker run --init -p 0.0.0.0:8081:8000/tcp -it --rm $(APP)

clean:
	docker image rm $(APP)
	docker system prune
