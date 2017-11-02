.PHONY: all
 
all: intranet-restart
 
intranet-up intranet-down: cluster := perf-cluster-2
 
intranet-up: 
  ecs-cli compose up --cluster $(cluster)
 
intranet-down: 
  ecs-cli compose down --cluster $(cluster)
 
intranet-restart: intranet-down intranet-up
