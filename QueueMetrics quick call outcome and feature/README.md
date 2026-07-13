# QueueMetrics Quick Outcome Panel

## Important

This HTML page is provided as a simple example of how to integrate an external CRM page with QueueMetrics.

It is **not production ready**.

Before using it in a real environment, it should be reviewed, customized, and tested by the customer or system integrator.

In particular:

* QueueMetrics connection parameters are hardcoded inside the HTML file.
* API credentials are stored in plain text inside the JavaScript.
* Anyone with access to the HTML source can view these credentials.
* The page performs direct API calls from the browser to QueueMetrics.
* It does not implement authentication, access control, input validation, or any additional security measures.

This approach is acceptable for demonstrations, testing, or controlled internal environments. It is **not recommended** for public deployments.

For a production installation, we recommend implementing a server side component that communicates with QueueMetrics instead of exposing API credentials in the browser.

---

## Configuration

Before using the page, edit the `CONFIG` section inside the HTML file.

```javascript
const CONFIG = {
  // QueueMetrics URL
  qmBaseUrl: "https://my.queuemetrics-live.com/your_queuemetrics",

  endpoint: "qm_jsonsvc_do_pbxactions.do",

  // QueueMetrics API user
  apiUsername: "robot",

  // QueueMetrics API password
  apiPassword: "robot",

  server: "",
  urlCallIdParams: ["unique", "callid", "uid", "u"]
};
```

Replace these values with your own QueueMetrics installation.

At minimum, configure:

* `qmBaseUrl`
* `apiUsername`
* `apiPassword`

The outcome list and any additional customization should also be adapted to match your own business workflow.

---

## Installation

### 1. Configure the HTML file

Edit the `CONFIG` section with the correct QueueMetrics URL and API credentials.

---

### 2. Host the file

Upload the HTML file to a web server.

Requirements:

* The page must be reachable over HTTPS.
* The QueueMetrics server must be reachable from the browser.
* If the page is hosted on a different domain than QueueMetrics, Cross Origin Resource Sharing (CORS) must be configured accordingly.

Hosting the page on the same domain as QueueMetrics is generally the simplest approach.

---

### 3. Configure QueueMetrics

Open:

**QueueMetrics Homepage → Settings → Edit System Parameters**

Configure the CRM properties so that QueueMetrics opens your page.

Example:

```
default.crmlabel=Outcome
default.crmapp=https://yourserver.example.com/quick_outcome.html?unique=[U]
```

QueueMetrics will replace the URL parameters with the current call information when opening the page.

---

### 4. Test

Open the Agent page.

Click the CRM button during or after a call.

The page should:

* receive the QueueMetrics unique call ID
* allow the agent to select an outcome
* allow the agent to enter a note
* submit both values back to QueueMetrics using the JSON API

The page sends two API requests:

1. `calloutcome`
2. `addfeature`

---

## Customization

This example is intentionally simple.

Customers are expected to customize it to suit their own workflow.

Typical customizations include:

* replacing the outcome list
* changing the page layout and branding
* adding validation
* integrating with external CRM systems
* replacing hardcoded credentials with a secure server side implementation
* adding logging and auditing

---

## Support

This page should be considered a starting point for development rather than a finished product.

Customers are responsible for reviewing, adapting, and securing the implementation before using it in production.
