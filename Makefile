# VARIABLES
# Read environment variables file
envvars ?= .env
include $(envvars)
export $(shell sed 's/=.*//' $(envvars))

# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

start: ## Iniciar containers
	docker-compose up -d

stop: ## Parar containers
	docker-compose down

remove-all: ## Remove todos os containers/volumes!
	docker-compose down --rmi all --volumes --remove-orphans

nginx-sh: ## NGINX shell
	docker exec -it reverse_proxy /bin/bash

apache-sh: ## APACHE shell
	docker exec -it myapp_1 /bin/bash

mysql-sh: ## MYSQL shell
	docker exec -it dbserver /bin/bash
