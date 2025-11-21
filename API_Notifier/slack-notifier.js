const url = require("url");

// ------- Configuration -------
const QM_BASE   = "https://my.queuemetrics-live.com/queuemetrics";  // Your QueueMetrics instance Link
const QM_USER   = "robot";
const QM_PASS   = "robotPassword";
const QM_QUEUES = encodeURIComponent("1000|1001");         // queue list, separated by pipe symbol
const SLACK_WEBHOOK = "https://hooks.slack.com/services/XXXXX/XXXXX";

const POLL_INTERVAL_SEC = 5;         // how often to poll, default 5 seconds
const INTERVAL_MS = POLL_INTERVAL_SEC * 1000;

const DEBUG = false;                 // set true to see per-poll logs
const TLS_INSECURE = false;          // set true only if your QueueMetrics HTTPS uses self-signed certs
const ANNOUNCE_ON_START = false;     // set true to post each agent's current state once on startup
// ----------------------------------

// ------- tiny HTTP helper using built-ins only -------
function httpDo(method, fullUrl, { headers = {}, body, insecure = false } = {}) {
  return new Promise((resolve, reject) => {
    const u = url.parse(fullUrl);
    const isHttps = u.protocol === "https:";
    const mod = isHttps ? require("https") : require("http");
    const opts = {
      method,
      hostname: u.hostname,
      port: u.port || (isHttps ? 443 : 80),
      path: u.path,
      headers,
      rejectUnauthorized: !insecure
    };
    const req = mod.request(opts, res => {
      let data = "";
      res.setEncoding("utf8");
      res.on("data", chunk => (data += chunk));
      res.on("end", () => {
        if (res.statusCode >= 200 && res.statusCode < 300) return resolve({ status: res.statusCode, data });
        return reject({ status: res.statusCode, data });
      });
    });
    req.on("error", reject);
    if (body) req.write(body);
    req.end();
  });
}
const b64 = s => Buffer.from(s, "utf8").toString("base64");
const now = () => new Date().toISOString();
const j = o => JSON.stringify(o);

// Format a Unix timestamp (seconds) to HH:MM in the server's local time
function fmtHHMM(tst) {
  const n = Number(tst) || 0;
  const d = n ? new Date(n * 1000) : new Date();
  const hh = String(d.getHours()).padStart(2, "0");
  const mm = String(d.getMinutes()).padStart(2, "0");
  return `${hh}:${mm}`;
}

// helpers for pause logic
const isNonEmpty = v => v !== undefined && v !== null && String(v).trim() !== "";
const normCode = v => isNonEmpty(v) ? String(v).trim() : ""; // "" means no code
const tsNum = v => Number(v) || 0;
const fmtIso = tst => { const n = tsNum(tst); return n ? new Date(n * 1000).toISOString() : "now"; };

// ------- state -------
const lastState = new Map(); // agent -> { paused: boolean, code: string }
let firstRun = true;

async function postSlack(text) {
  if (!SLACK_WEBHOOK) return;
  try {
    await httpDo("POST", SLACK_WEBHOOK, {
      headers: { "Content-Type": "application/json" },
      body: j({ text })
    });
  } catch (e) {
    console.error(now(), "slack post error:", e.status || e.code, (e.data || "").toString().slice(0, 300));
  }
}

async function pollOnce() {
  // QueueMetrics realtime agents (simple format)
  const apiUrl = `${QM_BASE}/QmRealtime/jsonStatsApi.do?queues=${QM_QUEUES}&block=RealtimeDO.RtAgentsRaw&jsonFormat=simple`;

  const headers = {
    "User-Agent": "curl/7.88.1",  // gets past strict proxy/WAF checks
    "Accept": "application/json",
    "Content-Type": "application/json",
    "Referer": `${QM_BASE}/index.jsp`,
    "Authorization": `Basic ${b64(`${QM_USER}:${QM_PASS}`)}`
  };

  const res = await httpDo("GET", apiUrl, { headers, insecure: TLS_INSECURE });
  const data = JSON.parse(res.data);
  const rows = data["RealtimeDO.RtAgentsRaw"] || [];

  for (const ag of rows) {
    const agent = ag["ACB_agent"];                 // e.g. "agent/402"
    if (!agent) continue;

    // Your instance: paused when ACB_curPauseCode is non-empty; unpaused when empty.
    const curCode = normCode(ag["ACB_curPauseCode"]);
    const onPause = normCode(ag["ACB_onPause"]);     // often empty; fallback
    const pausedNow = curCode !== "" || onPause !== "";

    const prev = lastState.get(agent); // { paused, code } or undefined

    if (DEBUG) {
      console.log(
        now(), "agent:", agent,
        "prev:", prev ? JSON.stringify(prev) : "∅",
        "now:", JSON.stringify({ paused: pausedNow, code: curCode })
      );
    }

    // Optional: announce current state on first sighting
    if (!prev && ANNOUNCE_ON_START) {
      const sinceIso = fmtIso(ag["ACB_inCurrStatusSinceTst"]);
      await postSlack(
        (pausedNow ? "🛑 " : "✅ ") +
        `*${agent}* is ${pausedNow ? "*PAUSED*" : "*UNPAUSED*"} ` +
        (pausedNow ? `(code \`${curCode || "-"}\`) ` : "") +
        `• since \`${sinceIso}\``
      );
    }

    // --- Transition detection (emit first, then save) ---

    // Case 1: UNPAUSED → PAUSED
    if (prev && prev.paused === false && pausedNow === true) {
      const sinceTxt = fmtHHMM(ag["ACB_curPauseTst"] || ag["ACB_inCurrStatusSinceTst"]);
      const codeTxt  = curCode || onPause || "-";
      await postSlack(`🛑 *${agent}* is PAUSED (pause code ${codeTxt}) at ${sinceTxt}`);
    }

    // Case 2: PAUSED → UNPAUSED
    if (prev && prev.paused === true && pausedNow === false) {
      const sinceTxt = fmtHHMM(ag["ACB_inCurrStatusSinceTst"]);
      const wasCode  = prev.code || "-";
      await postSlack(`✅ *${agent}* is UNPAUSED (was ${wasCode}) at ${sinceTxt}`);
    }

    // Case 3: PAUSED → PAUSED (pause code changed)
    if (prev && prev.paused === true && pausedNow === true && curCode !== prev.code) {
      const sinceTxt = fmtHHMM(ag["ACB_curPauseTst"]);
      const fromCode = prev.code || "-";
      const toCode   = curCode || "-";
      await postSlack(`🔁 *${agent}* changed pause (${fromCode} → ${toCode}) at ${sinceTxt}`);
    }

    // Save current state *after* emitting events
    lastState.set(agent, { paused: pausedNow, code: curCode });
  }

  if (DEBUG) console.log(now(), "polled", rows.length, "agent rows");
  firstRun = false;
}

// cadence loop
(async function main(){
  console.log(`QM notifier running: polling every ${INTERVAL_MS/1000}s…`);
  for (;;) {
    try { await pollOnce(); }
    catch (e) {
      console.error(now(), "poll error:", e.status || e.code || e.message, (e.data || "").toString().slice(0,300));
    }
    await new Promise(r => setTimeout(r, INTERVAL_MS));
  }
})();
