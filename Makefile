.PHONY: help
help: ## Displays this list of targets with descriptions
    @echo "The following commands are available:\n"
    @grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: fix
fix: rector fix-cs## Run rector and cgl fixes

.PHONY: rector
rector: ## Run rector
	Build/Scripts/runTests.sh -s rector

.PHONY: fix-cgl
fix-cgl: ## Fix PHP coding styles
	Build/Scripts/runTests.sh -s cgl

.PHONY: phpstan
phpstan: ## Run phpstan tests
	Build/Scripts/runTests.sh -s phpstan

.PHONY: phpstan-baseline
phpstan-baseline: ## Update the phpstan baseline
	Build/Scripts/runTests.sh -s phpstanBaseline

.PHONY: composerUpdate
composerUpdate: ## composerUpdate
	Build/Scripts/runTests.sh -s composerUpdate
