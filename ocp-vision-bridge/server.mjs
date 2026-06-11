#!/usr/bin/env node
/**
 * OCP Vision Bridge — OpenAI-compatible proxy in front of OCP.
 * Routes requests with image_url parts to Anthropic /v1/messages (OAuth).
 * Text-only requests forward to OCP unchanged.
 */
import { createServer } from "node:http";
import { randomUUID } from "node:crypto";
import { messagesHaveImage } from "./lib/openai-content.mjs";
import { chatCompletionsWithVision } from "./lib/chat-vision.mjs";
import { jsonResponse, completionResponse, streamStringAsSSE } from "./lib/openai-response.mjs";

const PORT = parseInt(process.env.VISION_BRIDGE_PORT || "3457", 10);
const OCP_UPSTREAM = (process.env.OCP_UPSTREAM || "http://127.0.0.1:3456").replace(/\/$/, "");
const MAX_BODY = 8 * 1024 * 1024;

async function readBody(req) {
  let body = "";
  for await (const chunk of req) {
    body += chunk;
    if (body.length > MAX_BODY) {
      throw new Error("body_too_large");
    }
  }
  return body;
}

async function proxyToOcp(req, res, body) {
  const url = `${OCP_UPSTREAM}${req.url}`;
  const headers = { ...req.headers, host: new URL(OCP_UPSTREAM).host };
  delete headers["content-length"];

  const upstream = await fetch(url, {
    method: req.method,
    headers,
    body: body || undefined,
  });

  res.writeHead(upstream.status, {
    "Content-Type": upstream.headers.get("content-type") || "application/json",
  });

  if (upstream.body) {
    const reader = upstream.body.getReader();
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      res.write(value);
    }
  }
  res.end();
}

async function handleChatCompletions(req, res) {
  let body;
  try {
    body = await readBody(req);
  } catch (e) {
    if (e.message === "body_too_large") {
      return jsonResponse(res, 413, {
        error: { message: "Request body too large", type: "invalid_request_error" },
      });
    }
    throw e;
  }

  let parsed;
  try {
    parsed = JSON.parse(body);
  } catch {
    return jsonResponse(res, 400, { error: { message: "Invalid JSON", type: "invalid_request_error" } });
  }

  const messages = parsed.messages || [];
  if (!messagesHaveImage(messages)) {
    return proxyToOcp(req, res, body);
  }

  const stream = Boolean(parsed.stream);
  try {
    const { text, model, usage, visionFallback } = await chatCompletionsWithVision(parsed);
    if (visionFallback) {
      res.setHeader("X-Vision-Fallback", "1");
    }
    if (stream) {
      return streamStringAsSSE(res, model, text, usage);
    }
    return completionResponse(res, model, text, usage);
  } catch (err) {
    console.error("[vision-bridge] vision error:", err.message);
    return jsonResponse(res, 502, {
      error: { message: err.message, type: "vision_error" },
    });
  }
}

const server = createServer(async (req, res) => {
  try {
    if (req.method === "GET" && req.url === "/health") {
      return jsonResponse(res, 200, {
        ok: true,
        service: "ocp-vision-bridge",
        upstream: OCP_UPSTREAM,
        port: PORT,
      });
    }

    if (req.method === "GET" && req.url === "/v1/models") {
      return proxyToOcp(req, res, "");
    }

    if (req.method === "POST" && req.url === "/v1/chat/completions") {
      return handleChatCompletions(req, res);
    }

    return jsonResponse(res, 404, {
      error: "Not found. Endpoints: GET /health, GET /v1/models, POST /v1/chat/completions",
    });
  } catch (err) {
    console.error("[vision-bridge]", err);
    if (!res.headersSent) {
      jsonResponse(res, 500, { error: { message: err.message, type: "server_error" } });
    }
  }
});

server.listen(PORT, "127.0.0.1", () => {
  console.log(`[vision-bridge] http://127.0.0.1:${PORT}`);
  console.log(`[vision-bridge] upstream OCP: ${OCP_UPSTREAM}`);
  console.log(`[vision-bridge] Cursor Base URL: http://127.0.0.1:${PORT}/v1`);
  console.log(`[vision-bridge] id=${randomUUID().slice(0, 8)}`);
});
