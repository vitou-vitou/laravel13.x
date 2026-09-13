# Envoy Proxy — overview

Source: [envoyproxy.io/docs/envoy/latest/intro/what_is_envoy](https://www.envoyproxy.io/docs/envoy/latest/intro/what_is_envoy) (official docs, fetched 2026-09-13)

## What it is
L7 proxy + communication bus for service-oriented architectures. Goal: make the network transparent to apps.

## Core architecture
- **Out-of-process**: runs as sidecar next to each app instance, not a library. Language-agnostic (works with PHP, Java, Go, etc.), upgrades independently of app code.
- **L3/L4 filter chain**: pluggable TCP/UDP filters (raw TCP, Redis, Postgres, MongoDB, TLS auth...).
- **L7 HTTP filter layer**: on top of L3/L4 — buffering, rate limiting, routing, DynamoDB sniffing, etc.
- **HTTP/1.1, HTTP/2, HTTP/3**: transparently bridges any combination between client and upstream.
- **L7 routing**: route/redirect by path, authority, content-type, runtime values — used both as edge proxy and service mesh.
- **gRPC support**: native, since gRPC rides HTTP/2.

## Operational features
- **Dynamic config (xDS)**: layered APIs for hosts, clusters, routes, listeners, TLS certs — centrally managed; or fall back to static config + DNS for simple deployments.
- **Health checking**: active (polling) + passive (outlier detection).
- **Advanced load balancing**: retries, circuit breaking, global rate limiting (external service), request shadowing, outlier detection.
- **Edge/front proxy capable**: TLS termination + HTTP routing, same binary as the mesh sidecar.
- **Observability**: stats via statsd-compatible sink or admin port; distributed tracing via third-party providers.

## Relevance note (not in source, my read)
Envoy solves service-mesh / multi-language microservice problems. This repo is a single-language Laravel monolith — Envoy's sidecar-mesh value prop (cross-language routing, per-service circuit breaking) doesn't apply unless this becomes a multi-service architecture. If the goal was "reverse proxy in front of Laravel," Nginx/Caddy is the simpler fit; Envoy is justified once there's a mesh of multiple services to coordinate.
