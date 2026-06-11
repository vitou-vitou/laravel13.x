import { randomUUID } from "node:crypto";

export function jsonResponse(res, status, data) {
  if (res.headersSent || res.writableEnded || res.destroyed) {
    return;
  }
  res.writeHead(status, { "Content-Type": "application/json" });
  res.end(JSON.stringify(data));
}

export function completionResponse(res, model, content, usage = {}) {
  const id = `chatcmpl-${randomUUID()}`;
  jsonResponse(res, 200, {
    id,
    object: "chat.completion",
    created: Math.floor(Date.now() / 1000),
    model,
    choices: [{ index: 0, message: { role: "assistant", content }, finish_reason: "stop" }],
    usage: {
      prompt_tokens: usage.input_tokens || 0,
      completion_tokens: usage.output_tokens || 0,
      total_tokens: (usage.input_tokens || 0) + (usage.output_tokens || 0),
    },
  });
}

export function streamStringAsSSE(res, model, content, usage = {}) {
  const id = `chatcmpl-${randomUUID()}`;
  const created = Math.floor(Date.now() / 1000);
  res.writeHead(200, {
    "Content-Type": "text/event-stream",
    "Cache-Control": "no-cache",
    Connection: "keep-alive",
    "X-Accel-Buffering": "no",
  });

  const send = (payload) => {
    res.write(`data: ${JSON.stringify(payload)}\n\n`);
  };

  send({
    id,
    object: "chat.completion.chunk",
    created,
    model,
    choices: [{ index: 0, delta: { role: "assistant" }, finish_reason: null }],
  });

  const CHUNK = 80;
  const codepoints = Array.from(content);
  for (let i = 0; i < codepoints.length; i += CHUNK) {
    send({
      id,
      object: "chat.completion.chunk",
      created,
      model,
      choices: [{
        index: 0,
        delta: { content: codepoints.slice(i, i + CHUNK).join("") },
        finish_reason: null,
      }],
    });
  }

  send({
    id,
    object: "chat.completion.chunk",
    created,
    model,
    choices: [{ index: 0, delta: {}, finish_reason: "stop" }],
    usage: usage.input_tokens
      ? {
          prompt_tokens: usage.input_tokens,
          completion_tokens: usage.output_tokens,
          total_tokens: usage.input_tokens + usage.output_tokens,
        }
      : undefined,
  });
  res.write("data: [DONE]\n\n");
  res.end();
}
