# AI Comment Helper API

Small internal API that lets the post page ask OpenAI for a contextual reply to a user’s question about a post. Stateless; only used from the post detail page via JavaScript.

## Endpoint
- `POST /ai/comment-response`
- Script: `app/ai/aiCommentResponse.php`
- Router (in `app/public/index.php`):
  ```php
  elseif ($uri === 'ai/comment-response') {
      if (!is_post()) { http_response_code(405); echo 'Method Not Allowed'; exit; }
      require __DIR__ . '/../ai/aiCommentResponse.php';
      exit;
  }
  ```

## Request
- JSON body:
  ```json
  {
    "event": "userChat",
    "question": "Short question about this post",
    "post": "Full text of the post"
  }
  ```
- `event` must be `"userChat"`.
- `question` and `post` are required strings.
- Header: `Content-Type: application/json`.

## Backend flow (`aiCommentResponse.php`)
- Require `POST`; otherwise 405.
- Parse JSON body; basic validation on `event`/`question`.
- Load API key: `API_KEY` env var (not sent to client).
- Trim inputs to avoid huge payloads.
- Build system/user messages and call OpenAI Chat Completions with model `gpt-5-nano` (update if your key needs a different model).
- Return the raw OpenAI JSON on success; otherwise return an error object.

## Response
- Success: raw OpenAI chat completion JSON; typical content is in `choices[0].message.content`.
- Frontend usually resolves text as:
  ```js
  const text =
    data?.choices?.[0]?.message?.content ||
    data.reply ||
    data.error ||
    'No reply.';
  ```

## Errors
- Invalid/missing JSON: `{"error": "Invalid JSON body"}`
- Bad shape (`event`/`question` missing): `{"error": "Invalid request"}`
- Missing API key: `{"error": "API key missing or unreadable secret"}`
- cURL/HTTP issues: `{"error": "cURL error: ..."}`
- OpenAI errors are passed through with the HTTP status from the API.

## Frontend usage (post detail page)
- `window.postBodyForAI` is set in the view to the current post body.
- `app/public/js/postCommentsAI.js` sends:
  ```js
  fetch('/ai/comment-response', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ event: 'userChat', question, post: window.postBodyForAI })
  })
  ```
- UI shows the response text as plain text to avoid XSS.

## Configuration notes
- Set `API_KEY` in `.env` (root) for the container; the endpoint returns an error if missing.
- Adjust `model` in `aiCommentResponse.php` if your key cannot access `gpt-5-nano`.
- Endpoint accepts `POST` only; responses should be rendered as text, not raw HTML.
