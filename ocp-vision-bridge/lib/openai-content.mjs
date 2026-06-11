export function contentHasImage(content) {
  if (!Array.isArray(content)) {
    return false;
  }
  return content.some((p) => p?.type === "image_url");
}

export function messagesHaveImage(messages) {
  if (!Array.isArray(messages)) {
    return false;
  }
  return messages.some((m) => contentHasImage(m.content));
}

function parseDataUrl(url) {
  const match = /^data:([^;]+);base64,(.+)$/s.exec(url);
  if (!match) {
    return null;
  }
  return { mediaType: match[1], data: match[2] };
}

export async function openAiPartToAnthropic(part) {
  if (!part || typeof part !== "object") {
    return null;
  }
  if (part.type === "text" && typeof part.text === "string") {
    return { type: "text", text: part.text };
  }
  if (part.type !== "image_url" || !part.image_url?.url) {
    return null;
  }

  const url = part.image_url.url;
  const parsed = parseDataUrl(url);
  if (parsed) {
    return {
      type: "image",
      source: {
        type: "base64",
        media_type: parsed.mediaType,
        data: parsed.data,
      },
    };
  }

  if (url.startsWith("http://") || url.startsWith("https://")) {
    return {
      type: "image",
      source: { type: "url", url },
    };
  }

  throw new Error(`Unsupported image_url: ${url.slice(0, 40)}...`);
}

export async function openAiContentToAnthropic(content) {
  if (typeof content === "string") {
    return content;
  }
  if (!Array.isArray(content)) {
    return "";
  }

  const blocks = [];
  for (const part of content) {
    const block = await openAiPartToAnthropic(part);
    if (block) {
      blocks.push(block);
    }
  }
  if (blocks.length === 0) {
    return "";
  }
  if (blocks.length === 1 && blocks[0].type === "text") {
    return blocks[0].text;
  }
  return blocks;
}

function textFromOpenAiContent(content) {
  if (typeof content === "string") {
    return content;
  }
  if (!Array.isArray(content)) {
    return "";
  }
  return content
    .filter((p) => p?.type === "text" && typeof p.text === "string")
    .map((p) => p.text)
    .join("");
}

export async function openAiMessagesToAnthropic(messages) {
  const systemParts = [];
  const out = [];

  for (const m of messages) {
    if (m.role === "system") {
      systemParts.push(textFromOpenAiContent(m.content));
      continue;
    }
    if (m.role === "assistant") {
      out.push({ role: "assistant", content: textFromOpenAiContent(m.content) });
      continue;
    }
    if (m.role === "user") {
      out.push({ role: "user", content: await openAiContentToAnthropic(m.content) });
    }
  }

  const merged = [];
  for (const msg of out) {
    const prev = merged[merged.length - 1];
    if (prev && prev.role === msg.role) {
      if (typeof prev.content === "string" && typeof msg.content === "string") {
        prev.content = `${prev.content}\n\n${msg.content}`;
      } else {
        const prevBlocks = typeof prev.content === "string"
          ? [{ type: "text", text: prev.content }]
          : prev.content;
        const nextBlocks = typeof msg.content === "string"
          ? [{ type: "text", text: msg.content }]
          : msg.content;
        prev.content = [...prevBlocks, ...nextBlocks];
      }
    } else {
      merged.push({ ...msg });
    }
  }

  return {
    system: systemParts.filter(Boolean).join("\n\n") || undefined,
    messages: merged,
  };
}
