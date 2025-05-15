build:
	docker compose -f compose.yaml up --build

run:
	docker compose -f compose.yaml up -d

install:
	docker compose -f compose.yaml exec -it app composer install

stop:
	docker compose -f compose.yaml down

clean:
	docker compose -f compose.yaml down --volumes --remove-orphans

.PHONY: build run install stop clean