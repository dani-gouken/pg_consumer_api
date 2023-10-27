SAIL_BIN = ./vendor/bin/sail
.PHONY: enter test cache up
enter:
	${SAIL_BIN} bash
test:
	${SAIL_BIN} artisan test
cache:
	${SAIL_BIN} artisan config:cache
up:
	${SAIL_BIN} up -d
down:
	${SAIL_BIN} down --volumes