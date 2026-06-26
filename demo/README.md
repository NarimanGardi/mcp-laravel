# Demo

A reproducible demo of the MCP server — it boots the **real** server (via
Testbench), sends the requests in [`requests.jsonl`](requests.jsonl), and prints
the actual responses. Nothing here is mocked.

```bash
./demo/run.sh
```

[`session.md`](session.md) is the captured output of that command. Regenerate it
with `./demo/run.sh > demo/session.md`.

## Recording a GIF

No GIF is committed (it'd just be a binary blob). Generate one yourself with
[VHS](https://github.com/charmbracelet/vhs):

```bash
vhs demo/demo.tape   # writes demo/demo.gif
```

The tape simply runs `./demo/run.sh`, so the GIF shows the same real session.
