IMAGE := gitlab.sanasport.cz:5005/eshop/sanasport/flyimg:post-v1

help:
	@echo "\nhelp\nbuild-and-push\nbuild\npush\n"

build-and-push: build push

build:
	docker build -f Dockerfile -t $(IMAGE) .

push:
	docker push $(IMAGE)