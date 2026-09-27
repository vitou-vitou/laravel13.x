# API Architectures at the Application Layer — Team-Lead Decision Guide

**Question:** Decision-ready overview of 10 modern API architectures at the application layer, with use case, performance characteristics, adoption cost, risks, and a selection framework.

**Method:** Investigated via the research skill flow. Primary sources are the official specifications, first-party docs, and reference implementations of each technology (cited per claim). This repo already keeps notes in `docs/`, so the file lives here.

---

## 1. The Ten Architectures

### 1. REST
- **Definition:** Resource-oriented architecture over HTTP using uniform verbs (GET/POST/PUT/DELETE) and representations (usually JSON).
- **Use case:** Public/external CRUD APIs; the default interop choice.
- **Performance:** Request/response over HTTP/1.1 or HTTP/2; over/under-fetching costs payload efficiency; caching via HTTP semantics is a strength.
- **Adoption cost:** Low — every stack and framework (including Laravel routing/controllers) supports it natively; team skills are ubiquitous.
- **Risk/trade-off:** No formal contract (OpenAPI is a bolt-on spec); evolving resources without breaking clients takes discipline.
- **When NOT to use:** Deeply nested/aggregated queries, real-time push, or strict typed contracts between internal services.
- **Source:** RFC 9110/9111 (HTTP semantics and caching, IETF) and the OpenAPI Specification (OpenAPI Initiative).

### 2. GraphQL
- **Definition:** Single-endpoint typed query language where clients declare exact fields; server resolves via a resolver graph.
- **Use case:** Mobile/web frontends needing flexible aggregation over many domain objects.
- **Performance:** One round trip for complex reads, but resolver fan-out (N+1) requires DataLoader-style batching; no free HTTP caching.
- **Adoption cost:** Medium — schema design, resolver layer, and query-cost control are new skills.
- **Risk/trade-off:** Server complexity, query abuse, and authorization per field; bad schemas become a client/server contract mess.
- **When NOT to use:** Simple CRUD, cache-heavy public APIs, or small teams without bandwidth for schema governance.
- **Source:** GraphQL spec, graphql.org (GraphQL Foundation).

### 3. gRPC
- **Definition:** Binary RPC over HTTP/2 with Protobuf IDL contracts; supports unary, streaming, and bidirectional calls.
- **Use case:** Internal service-to-service calls; polyglot microservices.
- **Performance:** Low payload size and latency; multiplexed HTTP/2 streams; best-in-class throughput.
- **Adoption cost:** Medium — Protobuf toolchain, codegen, and ops tracing (gRPC is not natively browser-friendly).
- **Risk/trade-off:** Binary payloads are opaque to humans and to plain HTTP tooling; debugging needs dedicated tooling.
- **When NOT to use:** Browser-first or external public APIs (without a gateway), or where JSON debuggability matters more than throughput.
- **Source:** gRPC overview and HTTP/2 spec (RFC 7540/9113, IETF).

### 4. WebSocket
- **Definition:** Persistent, full-duplex TCP connection upgraded from HTTP, framed by a small message protocol.
- **Use case:** Chat, collaborative editing, live dashboards.
- **Performance:** Very low push latency after handshake; server holds long-lived connections (connection management cost).
- **Adoption cost:** Medium — stateful servers, reconnection/replay logic, heartbeats.
- **Risk/trade-off:** Horizontal scaling needs sticky sessions or a pub/sub backbone; connection state complicates deploys.
- **When NOT to use:** Primarily one-directional updates — SSE is simpler; or rare client polls.
- **Source:** RFC 6455 (The WebSocket Protocol, IETF).

### 5. WebHooks
- **Definition:** Server-to-server push over HTTP callbacks triggered by events the provider emits.
- **Use case:** Integrations (payments, CI, SaaS event delivery).
- **Performance:** Decoupled and near-real-time; delivery is at-least-once, so consumers must be idempotent.
- **Adoption cost:** Low — consumer side is just an HTTP endpoint; producer side needs a retry/queue system.
- **Risk/trade-off:** Endpoint is publicly reachable: signature verification (e.g., HMAC), replay protection, and secrets management are mandatory.
- **When NOT to use:** Client-side consumers (they can't receive callbacks) or strict low-latency ordering guarantees.
- **Source:** Standard HMAC signature conventions (RFC 2104) and provider docs (e.g., Stripe/GitHub webhook signing).

### 6. SOAP
- **Definition:** XML messaging protocol with WSDL contracts, WS-* extensions, and strict envelope/body/header structure.
- **Use case:** Enterprise, banking, telecom, and legacy government integrations.
- **Performance:** Verbose XML, higher parsing cost; but WS-ReliableMessaging/WS-Security give formal guarantees.
- **Adoption cost:** Medium-high — WSDL tooling, namespaces, and WS-* stacks are unfamiliar to most modern web teams.
- **Risk/trade-off:** Heavy boilerplate and poor ecosystem fit for greenfield web/mobile work.
- **When NOT to use:** Any modern lightweight web or mobile API — pick JSON-based styles instead.
- **Source:** SOAP 1.2 (W3C Recommendation) and WSDL 1.1 (W3C Note).

### 7. gRPC-Web / Connect
- **Definition:** Browser-compatible variants of gRPC — gRPC-Web uses a proxy/gateway, Connect supports plain HTTP with optional streaming and JSON/protobuf framing.
- **Use case:** Sharing one typed RPC contract between browser, mobile, and backend services.
- **Performance:** Close to gRPC with a proxy hop (gRPC-Web) or near-native HTTP (Connect unary).
- **Adoption cost:** Medium — one more protocol variant to learn; Connect notably reduces gateway burden.
- **Risk/trade-off:** gRPC-Web cannot do full bidirectional streaming client-side; proxy adds operational surface.
- **When NOT to use:** Teams that don't need cross-tier type sharing — REST/GraphQL is simpler.
- **Source:** gRPC-Web spec (grpc.github.io) and Connect protocol docs (connectrpc.com).

### 8. SSE (Server-Sent Events)
- **Definition:** One-way server push over plain HTTP using `text/event-stream` framing, with auto-reconnect built into browsers.
- **Use case:** Feeds, notifications, live prices, LLM token streaming.
- **Performance:** Lightweight; works over normal HTTP infrastructure and CDNs; single-direction only.
- **Adoption cost:** Low — trivially simple server side (Laravel streaming responses work fine).
- **Risk/trade-off:** One-way only; connection limits on HTTP/1.1 browsers; custom auth headers are awkward in the browser EventSource API.
- **When NOT to use:** Client-to-server messaging or bidirectional sessions — use WebSocket.
- **Source:** HTML Living Standard, Server-Sent Events section (WHATWG).

### 9. tRPC
- **Definition:** End-to-end typed RPC for TypeScript full-stack apps; procedure schemas inferred as static types on the client.
- **Use case:** One TypeScript codebase (e.g., Next.js + Node) with zero-contract-drift client calls.
- **Performance:** Comparable to a thin JSON API; gains are in developer velocity, not wire speed.
- **Adoption cost:** Low-medium for TS teams; effectively zero fit for non-TypeScript consumers.
- **Risk/trade-off:** Couples API to TS monorepo — external/mobile consumers still need REST or GraphQL.
- **When NOT to use:** Public APIs, polyglot backends, or non-TS clients.
- **Source:** tRPC docs (trpc.io).

### 10. Event-Driven / Messaging APIs (Kafka, AMQP, MQTT)
- **Definition:** Asynchronous publish/subscribe over a broker (log-based Kafka, queue-based AMQP such as RabbitMQ, lightweight MQTT for constrained devices).
- **Use case:** Decoupling domains, ingestion pipelines, IoT telemetry, event sourcing.
- **Performance:** Very high aggregate throughput; consumers process at their own pace; Kafka gives ordered partition logs.
- **Adoption cost:** High — broker ops, partitioning, delivery semantics (at-least-once vs exactly-once), idempotent consumers.
- **Risk/trade-off:** Eventual consistency and event schema evolution become distributed-systems problems.
- **When NOT to use:** Simple synchronous request/response flows, or small teams without ops capacity for brokers.
- **Source:** Kafka protocol/design docs (Apache Kafka), AMQP 1.0 (OASIS standard), MQTT 5.0 (OASIS standard).

---

## 2. Comparison Table

| # | Architecture | Sync model | Perf profile | Adoption cost | Best fit |
|---|--------------|-----------|--------------|---------------|----------|
| 1 | REST | Sync req/resp | Good, HTTP-cacheable | Low | Public CRUD APIs |
| 2 | GraphQL | Sync req/resp | 1 round trip, N+1 risk | Medium | Flexible frontend reads |
| 3 | gRPC | Sync/stream | Best binary throughput | Medium | Internal microservices |
| 4 | WebSocket | Async bidirectional | Sub-ms push, stateful | Medium | Chat, collaboration |
| 5 | WebHooks | Async push (HTTP) | Near-real-time, retries | Low | B2B integrations |
| 6 | SOAP | Sync req/resp | Verbose XML | Med-high | Legacy enterprise |
| 7 | gRPC-Web/Connect | Sync | Near-gRPC | Medium | Browser + typed RPC |
| 8 | SSE | Async one-way | Light, HTTP-native | Low | Feeds, AI streaming |
| 9 | tRPC | Sync req/resp | Thin JSON, DX win | Low (TS only) | TS monorepos |
| 10 | Kafka/AMQP/MQTT | Async pub/sub | Highest aggregate | High | Event pipelines, IoT |

**Synchronous group:** REST, GraphQL, gRPC, SOAP, gRPC-Web/Connect, tRPC.
**Asynchronous/streaming group:** WebSocket, WebHooks, SSE, event-driven messaging.

---

## 3. Team-Lead Decision Framework

1. **Exposure:** External/public → REST (OpenAPI documented) or GraphQL; internal services → gRPC or Connect.
2. **Traffic profile:** Simple CRUD → REST; aggregate reads → GraphQL; high-throughput internal → gRPC; telemetry/pipelines → Kafka/AMQP; device/IoT → MQTT.
3. **Realtime needs:** One-way updates → SSE; bidirectional sessions → WebSocket; partner notifications → WebHooks.
4. **Team shape:** Small web team → REST + SSE, add others on demand; TS monorepo → tRPC internally; polyglot platform org → gRPC contracts + REST edge.
5. **Ops capacity:** No broker expertise → postpone event-driven; no gateway → prefer Connect over gRPC-Web.
6. **Practical default for a Laravel 13 / PHP 8.3 shop (this repo):** REST (Laravel controllers + OpenAPI) for external, SSE for one-way realtime (streamed responses), queued jobs + WebHooks for outbound integrations; introduce gRPC/Kafka only when a concrete scale problem demands it.

---

*Findings verified against the primary specifications and first-party documentation cited per section, per the research skill flow.*
