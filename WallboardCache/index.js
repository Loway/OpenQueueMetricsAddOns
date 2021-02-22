const puppeteer = require("puppeteer-core"),
  url = require("url"),
  http = require("http"),
  fileSystem = require("fs"),
  path = require("path");

//
// Here we define our wallboards
//
const my_qm =
  "https://**.queuemetrics-live.com:443/****/qm_wab2.do?user=robot&pass=****&";

const SAMPLE_WALLBOARDS = {
  plain:
    my_qm +
    "queues=500%7C501%7C502%7C770%7C771%7C772%7Cpark-default&wallboardId=17",
  classic:
    my_qm +
    "queues=500%7C501%7C502%7C770%7C771%7C772%7Cpark-default&wallboardId=16",
  hn: "https://news.ycombinator.com/"
};


const WALLBOARDS = process.env.URL 
  ? {wb: process.env.URL } 
  : SAMPLE_WALLBOARDS;


//
// Misc settings
//
const delay = (process.env.DELAY * 1000) ||  5000;
const localChrome = process.env.CHROME ||
  "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
const extra_args = process.env.ROOT 
       ? ["--disable-setuid-sandbox", "--no-sandbox"] 
       : [];
const workspace = process.env.WORKSPACE || "./workspace";
const images = workspace + "/image_";
const sessions = workspace + "/session_";

// Just sleeps for a while
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// If a string is null, replace it with ""
function ss(s) {
  return s == null ? "" : s;
}

// Url-encode a (possibly null) string.
function ue(s) {
  return encodeURIComponent(ss(s));
}

// Returns a given image by name
function sendImage(response, name, agent) {
  console.log("Agent '%s' is requesting wallboard '%s'", agent, name);
  var filePath = images + name + ".png" ; // path.join(__dirname, images + name + ".png");
  var stat = fileSystem.statSync(filePath);

  response.writeHead(200, {
    "Cache-Control": "no-cache",
    "Content-Type": "image/png",
    "Content-Length": stat.size
  });

  var readStream = fileSystem.createReadStream(filePath);
  // We replaced all the event handlers with a simple call to readStream.pipe()
  readStream.pipe(response);
}

// Pushes some HTML to the client
function sendHtml(response, html) {
  response.writeHead(200, { "Content-Type": "text/html" });
  response.write(html);
  response.end();
}

// Creates an index page with known wallboards
function makeIndexPage(response, agent) {
  var b = "";
  for (const wb in WALLBOARDS) {
    b += `<li>
    <a href='/?wb=${ue(wb)}&agent=${ue(agent)}'>
    ${wb}
    </li>\n`;
  }

  sendHtml(
    response,
    `
      <h1>Please select a wallboard:</h1>
      <p>
      <ul>
      ${b}
      </ul>
      `
  );
}

// Builds a wallboard page, that reloads the
// inner image every $delay ms.
//
// Image is rescaled to occupy 100% of the page.
function makeWallboardPage(response, wallboard, agent) {
  const image_url = `/img/?wb=${ue(wallboard)}&agent=${ue(agent)}`;
  const p = `
      <html>
      <body>
      <!-- ${wallboard} -->
      <div style="display: flex; justify-content: center; align-items: center; height: 100%; width: 100%;">
      <img src="${image_url}" id='screenshot' style='width: 90%'>
      </div>
      <script>
      setInterval(function() {
        var myScreenshot = document.getElementById('screenshot');
        myScreenshot.src = '${image_url}&rand=' + Math.random();
      }, ${delay});
      </script>
      </body>
      </html>

    `;

  sendHtml(response, p);
}

// A plain HTTP server
//
// We handle just a few cases:
//
// - / -> lists wallboards
// - /?wb=abc -> Displays a wallboard for abc
// - /img/?wb=abc -> the image for wallboard abc
const app = http.createServer((request, response) => {
  try {
    //console.log(url.parse(request.url, true));

    const parsedUrl = url.parse(request.url, true);
    const wallboard = ss(parsedUrl.query.wb);
    const agent = ss(parsedUrl.query.agent);
    const page = parsedUrl.pathname;

    if (page == "/" && wallboard == "") {
      makeIndexPage(response, agent);
    } else if (page == "/") {
      makeWallboardPage(response, wallboard, agent);
    } else if (page == "/img/") {
      sendImage(response, wallboard, agent);
    } else {
      throw `Unknown page: ${page}`;
    }
  } catch (err) {
    response.writeHead(500, { "Content-Type": "text/html" });
    response.write(`<h1>Error: ${err}</h1>`);
    response.end();
  }
});

// The browser loop that keeps a Chrome running
// and takes a new snapshot every $delay ms.
//
// Every browser has its own data folder, so you
// can run multiple in parallel without leaking their
// session.
async function browser(name, page_url) {
  const browser = await puppeteer.launch({
    product: "chrome",
    executablePath: localChrome,
    userDataDir: sessions + name + "/",
    args: extra_args
  });
  const page = await browser.newPage();

  await page.setViewport({ width: 1200, height: 800 });

  await page.goto(page_url, { waitUntil: "networkidle2" });

  while (true) {
    console.log("Taking a screenshot of wallboard '%s'", name);
    const imageFile = images + name + ".png"

    await page.screenshot({ path: imageFile });
    // console.log("Scritto %s", imageFile);
    await sleep(delay);
  }
  await browser.close();
}

//
// Starts all browsers, one for each wallboard,
// and then launches the HTTP server.
//
(async () => {
  for (const wb in WALLBOARDS) {
    console.log(`Starting wallboard: ${wb} `);
    browser(wb, WALLBOARDS[wb]);
  }
})();
app.listen(3000);
