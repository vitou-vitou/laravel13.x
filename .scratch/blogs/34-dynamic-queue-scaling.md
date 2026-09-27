# Auto-Scale Queue Workers on Kubernetes and Docker Based on Backlog Depth

You run a fixed pool of six queue worker containers in production. For twenty-two hours of the day, five of those containers sit completely idle, burning your cloud hosting budget. But during the morning peak or a flash marketing push, 40,000 jobs enter the queue and your six workers take four hours to clear the backlog. You attempt to configure a standard Kubernetes Horizontal Pod Autoscaler (HPA) based on CPU utilization, but because workers spending time in network I/O only use twelve percent CPU, Kubernetes never adds a single pod.

Standard CPU and memory metrics fail to reflect background worker load. An idle worker and a worker processing an I/O-bound API call can look identical to a CPU metric monitor. If you scale your worker containers based on **queue backlog depth** using tools like KEDA (Kubernetes Event-driven Autoscaling) and configure graceful termination timeouts, your cluster will automatically scale to fifty workers during traffic spikes and scale down to zero or one when the queue is clear.

## The Flaw of CPU-Based Autoscaling for Workers

A traditional web server's CPU spikes when handling thousands of concurrent requests. Queue workers behave differently:

- A worker waiting 1.5 seconds for Stripe or SendGrid to respond consumes almost 0% CPU.
- 50,000 jobs waiting in Redis generate 0% CPU load on the worker pods.

Because the CPU threshold is never crossed, standard Kubernetes HPAs do not spin up additional containers. Your customers wait hours for their receipts while your cluster believes the system is completely healthy.

## Scale on Queue Depth with KEDA

The authoritative metric for queue workers is **backlog depth** (the number of jobs waiting in the queue) or **wait time** (the age of the oldest unhandled job).

KEDA (Kubernetes Event-driven Autoscaling) allows Kubernetes to query your Redis or database queue directly and scale pods based on actual backlog size.

Here is a production KEDA `ScaledObject` configuration that monitors a Laravel Redis queue:

keda-worker-scaledobject.yaml:
```yaml
apiVersion: keda.sh/v1alpha1
kind: ScaledObject
metadata:
  name: laravel-queue-worker-scaler
  namespace: production
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: laravel-queue-worker
  minReplicaCount: 1       # Keep 1 worker running during idle periods
  maxReplicaCount: 25      # Scale up to 25 workers during heavy bursts
  cooldownPeriod: 300      # Wait 5 minutes of empty queue before scaling down
  pollingInterval: 15      # Check queue depth in Redis every 15 seconds
  triggers:
    - type: redis
      metadata:
        address: redis-service.production.svc.cluster.local:6379
        listName: queues:default
        listLength: "100"   # Target: add 1 worker for every 100 pending jobs
```

Notice how this operates:
- When the Redis list `queues:default` has 10 pending jobs, KEDA maintains 1 worker container.
- When an import dispatches 1,500 jobs, KEDA instantly scales the deployment up to 15 pods.
- Once the backlog is processed and the queue remains empty for 5 minutes, KEDA scales the deployment back down to 1 pod.

## Configure Graceful Termination for Scale-Down

When KEDA scales down workers after a rush, Kubernetes sends a `SIGTERM` signal to the pod. If your worker is halfway through a two-minute report generation task and Kubernetes terminates it after thirty seconds, the job is killed mid-execution.

Configure your Kubernetes pod deployment and worker command to respect long execution windows:

deployment.yaml:
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-queue-worker
spec:
  template:
    spec:
      terminationGracePeriodSeconds: 300  # Give workers up to 5 minutes to finish
      containers:
        - name: worker
          image: myapp:latest
          command:
            - php
            - artisan
            - queue:work
            - redis
            - --timeout=240
            - --max-time=3600
```

When Kubernetes initiates a scale-down:
1. It sends `SIGTERM` to the worker process.
2. Laravel catches `SIGTERM`, stops taking new jobs from the queue, and finishes processing the current in-flight job.
3. Because `terminationGracePeriodSeconds: 300` exceeds the worker's `--timeout=240`, the job completes cleanly without premature termination.

## What Can Go Wrong

A dangerous trap when auto-scaling queue workers is **downstream database saturation**.

If you allow KEDA to scale your worker deployment from 2 pods to 100 pods in two minutes, 100 worker containers will suddenly open hundreds of simultaneous database connections. You can easily trigger MySQL's `Too many connections` limit or max out your database server's CPU.

Always set a conservative `maxReplicaCount` based on your database server's capacity and connection limits, or ensure you have connection pooling in place (such as AWS RDS Proxy or PgBouncer).

## Summary

Do not rely on CPU or memory metrics to auto-scale background queue workers.

Scale your worker containers based on actual queue depth using KEDA or cloud queue depth metrics. Target a specific backlog ratio per worker, configure a generous `terminationGracePeriodSeconds`, and cap your maximum container count to protect your database.

You pay for worker compute only when work actually exists, while clearing sudden customer backlogs in minutes.

## Further Reading

- [KEDA Redis Scaler Documentation](https://keda.sh/docs/scalers/redis-lists/)
- [Kubernetes Pod Lifecycle and Termination Grace](https://kubernetes.io/docs/concepts/workloads/pods/pod-lifecycle/#pod-termination)
- [Laravel Queues: Graceful Shutdown](https://laravel.com/docs/queues#graceful-shutdown)
- [Horizontal Pod Autoscaling Best Practices](https://kubernetes.io/docs/tasks/run-application/horizontal-pod-autoscale/)

How do you handle scaling background worker nodes during traffic spikes? Tell us about your cloud infrastructure setup in the comments below.
