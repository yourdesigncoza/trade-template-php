# Initial PRD Product Requirements Document (PRD)


## Project Title:

**Trading Model Execution Tracker (Discipline Logging System)**

---

## 🧭 Purpose

This system is designed to help traders **log, review, and maintain discipline** when executing trades based on predefined models. The objective is to provide a **structured, repeatable framework** that enforces entry/exit criteria, tracks emotional state, and logs both **executed** and **missed trades**. This promotes better decision-making, facilitates backtesting analysis, and improves psychological consistency.

---

## 🧱 Core Modules

### 1. **Strategy Template Setup**

* Each strategy (e.g., "London Breakout") is treated as a top-level model.
* Settings include:

  * Strategy Name
  * Instrument
  * Timeframes used (e.g., M15, H1, H4)
  * Relevant Sessions (Asia, London, New York)
  * Chart Model (Image upload or URL)

---

### 2. **Checklist Modules**

#### ✅ Entry Criteria Checklist

* A customizable list of entry rules per strategy.
* Each condition has:

  * ID
  * Label (short name)
  * Description (how/why/when this condition is validated)
  * Checkbox (checked = met for this trade)

#### ❎ Exit Criteria Checklist

* Similar structure to Entry.
* Defines rules for closing the trade.
* Used to ensure exit was rule-based, not emotional.

#### ❌ Invalidation Overrides

* Used to track conditions where trades **should be skipped** (e.g., high-impact news, ranging market).
* Active status marks whether the invalidation applied to this trade.

---

### 3. **Trade Logging System**

#### 📌 Structure

Each trade log consists of the following fields:

| Field             | Type                 | Description                                 |
| ----------------- | -------------------- | ------------------------------------------- |
| `trade_id`        | String               | Unique ID or timestamp-based key            |
| `taken`           | Boolean              | Was the trade executed?                     |
| `missed_reason`   | Text (nullable)      | If skipped, reason must be logged           |
| `direction`       | Enum                 | Long / Short                                |
| `session`         | Enum                 | Asia / London / New York                    |
| `bias`            | Text                 | e.g. Reversal, Breakout                     |
| `timestamp`       | DateTime             | Time of trade or setup                      |
| `entry_price`     | Float                | Entry point                                 |
| `stop_loss_price` | Float                | Initial SL                                  |
| `exit_price`      | Float                | TP or close price                           |
| `risk_percent`    | Float (0.25, 0.5, 1) | % risked                                    |
| `r_multiple`      | Float                | Calculated: (exit - entry) / (entry - stop) |
| `reason`          | Text                 | 1-line description of trade logic           |
| `emotional_notes` | Text                 | Optional thoughts/feelings                  |
| `screenshots`     | Array\[URL]          | One or more images attached                 |

---

## 🔁 Workflow

### 🔹 New Trade Flow

1. User selects a **strategy** (from saved models)
2. Fills out **entry checklist** → all must be checked to proceed
3. Fills out **exit checklist** (when trade closes)
4. Logs trade execution data or marks it as **missed**
5. Optionally attaches screenshots and emotion notes
6. Trade is saved to the database under that strategy

---

## 📊 Aggregation & Insights (Phase 2)

Once multiple trades are logged, the app will allow users to:

* Calculate **win rate, average R, expectancy**
* Identify **most profitable session, setup types**
* Review **missed trades and reasons**
* Analyze emotional triggers (e.g., hesitation streaks)

> This requires structured trade records with consistent fields (already built into schema).

---

## 📁 Data Model Summary (Developer-Friendly)

### Strategy

```json
{
  "strategy_id": "string",
  "strategy_name": "string",
  "instrument": "string",
  "timeframes": ["string"],
  "sessions": ["string"],
  "chart_model_image_url": "string",
  "entry_criteria_checklist": [ ... ],
  "exit_criteria_checklist": [ ... ],
  "invalidations": [ ... ]
}
```

### Trade Entry

```json
{
  "trade_id": "string",
  "strategy_id": "string",
  "taken": true,
  "missed_reason": null,
  "direction": "long",
  "session": "London",
  "bias": "Breakout",
  "timestamp": "2025-07-27T08:30:00+02:00",
  "entry_price": 1.0932,
  "stop_loss_price": 1.0917,
  "exit_price": 1.0968,
  "risk_percent": 1.0,
  "r_multiple": 2.4,
  "screenshots": ["url1", "url2"],
  "reason": "Clean breakout of Asian range",
  "emotional_notes": "Confident entry after sweep"
}
```

---

## 📱 UI/UX Considerations

* Trade form should only unlock after all checklist boxes are checked ✅
* If “Missed Trade” is selected:

  * Hide price fields
  * Show “missed reason” and emotional notes
* Checklist builder should allow:

  * Adding/removing rows
  * Drag-and-drop reordering
  * Saving presets per strategy

---

## 🔒 Access & Versioning

* Support multiple strategies per user
* Each trade record is linked to a version of the strategy (in case the strategy is later changed)
* Option to “lock” old versions of strategy templates for integrity

---

## ✅ Acceptance Criteria

| Feature           | Criteria                                                          |
| ----------------- | ----------------------------------------------------------------- |
| Strategy Setup    | User can define strategy, timeframes, and upload chart models     |
| Checklists        | Must support checkbox logic, descriptions, and invalidations      |
| Trade Log         | Must support both executed and missed trades                      |
| Data Capture      | Form must store all execution fields and screenshots              |
| Export            | JSON and CSV export of all trade records per strategy             |
| UI                | Prevent trade logging unless all criteria are confirmed           |
| Emotional Logging | Capture user mindset to aid journaling and backtesting psychology |

---

```json
{
  "strategy_id": "london-breakout-v1",
  "strategy_name": "London Breakout Strategy",
  "instrument": "EURUSD",
  "timeframes": ["M15", "H1", "H4"],
  "sessions": ["Asia", "London", "New York"],
  "chart_model_image_url": "https://yourcdn.com/uploads/london-breakout-v1.png",

  "entry_criteria_checklist": [
    {
      "id": "entry-1",
      "label": "Asian Range Defined",
      "description": "Asian session range is clearly formed and narrow",
      "checked": true
    },
    {
      "id": "entry-2",
      "label": "Liquidity Sweep",
      "description": "Price swept Asian high/low before setup",
      "checked": true
    }
    // Add more as needed
  ],

  "exit_criteria_checklist": [
    {
      "id": "exit-1",
      "label": "TP Hit / Structure Broken",
      "description": "Take profit based on structure break",
      "checked": true
    }
    // Add more
  ],

  "invalidations": [
    {
      "id": "inv-1",
      "label": "High-Impact News",
      "reason": "NFP released during London open",
      "active": false
    },
    {
      "id": "inv-2",
      "label": "No Clean Range",
      "reason": "Asian session had wide indecision candles",
      "active": false
    }
  ],

  "trades": [
    {
      "trade_id": "trade-2025-07-27-01",
      "taken": true,
      "missed_reason": null,

      "direction": "long",
      "session": "London",
      "bias": "Breakout",
      "timestamp": "2025-07-27T08:30:00+02:00",

      "entry_price": 1.0932,
      "stop_loss_price": 1.0917,
      "exit_price": 1.0968,
      "risk_percent": 1.0,

      "r_multiple": 2.4,
      "screenshots": [
        "https://yourcdn.com/trades/2025-07-27-entry.png",
        "https://yourcdn.com/trades/2025-07-27-exit.png"
      ],
      "reason": "Asian range broken cleanly with strong volume and sweep.",
      "emotional_notes": "Felt confident due to clear structure. No hesitation."
    },
    {
      "trade_id": "trade-2025-07-27-02",
      "taken": false,
      "missed_reason": "Setup met all criteria but I was distracted with other charts",
      "direction": "short",
      "session": "New York",
      "bias": "Reversal",
      "timestamp": "2025-07-27T15:00:00+02:00",

      "entry_price": null,
      "stop_loss_price": null,
      "exit_price": null,
      "risk_percent": null,

      "r_multiple": null,
      "screenshots": [
        "https://yourcdn.com/trades/missed-2025-07-27.png"
      ],
      "reason": "OB rejection + FVG fill — ideal confluence",
      "emotional_notes": "Frustrated. Need to focus more during NY session."
    }
  ]
}
```

--- 

# Revised PRD 

Below is the **revised, highly detailed Product Requirements Document (PRD)**—now enriched with concrete technical stack details, database and API definitions, and rigorous usability standards grounded in leading UX research. Citations follow each externally sourced statement.

---

## 📋 Summary

This PRD defines an **MVP v1** for a **Trade Journal & Execution Tracker**: a web app that enforces strategy checklists, logs both executed and missed trades, and provides immediate analytics. The **tech stack** leverages a full-stack TypeScript approach—Next.js 15 with React 19 on the front end, Clerk for authentication, tRPC for type-safe APIs, Convex for real-time data storage and functions, and Vercel for zero-ops hosting. A **relational backup** via FastAPI microservices is optional for heavy analytics. The **UI/UX** adheres to Nielsen-Norman Group form design best practices—inline validation, clear labels, minimized cognitive load, and accessible checkboxes. **Security** follows OWASP authentication guidelines, and **DevOps** uses Vercel Cron, CI/CD pipelines, and error monitoring for production robustness.

---

## 🎯 Purpose & Scope

* **Objective**: Enable disciplined trade execution by

  1. Capturing entry/exit rule confirmations
  2. Logging detailed trade data (including “missed” trades)
  3. Surfacing core performance metrics instantly
* **MVP v1 Boundaries**:

  * User auth & single-user strategy management
  * Checklist-driven trade form
  * Persistent storage of strategies, checklists, trades
  * List views (strategies & trades)
  * Basic analytics (win rate, avg R, drawdown, equity curve)
  * No team roles, export, or advanced visualizations (deferred to v2).

---

## 🏗 Technical Architecture

### Frontend Stack

* **Next.js 15 (App Router + React 19)** for server components, file-based routing, and future-proof React 19 support ([Next.js][1]).
* **TypeScript** on client & server for end-to-end type safety.
* **Tailwind CSS** utility-first styling for rapid layouts ([v2.tailwindcss.com][2]).
* **shadcn/ui** pre-built, accessible React components to mirror wireframe designs ([ui.shadcn.com][3]).

### Backend & Data

* **Clerk** handles sign-up, sessions, MFA, and route protection in Next.js ([Clerk][4]).
* **tRPC** provides a typesafe RPC layer—define routers & procedures in TypeScript, share types automatically ([trpc.io][5]).
* **Convex** serves as the reactive document database with built-in server-side functions and real-time subscriptions ([docs.convex.dev][6]).
* **Vercel** for zero-config deployment of Next.js and Convex edge functions, plus native cron jobs for nightly analytics ([Vercel][7]).
* **Optional Python FastAPI** microservice for heavyweight Pandas/NumPy back-tests; integrates via webhook or scheduled task ([FastAPI][8]).

---

## 🗄️ Data Model & Schema

### Strategies

```sql
CREATE TABLE strategies (
  id UUID PRIMARY KEY,
  name TEXT NOT NULL,
  instrument TEXT NOT NULL,
  timeframes TEXT[] NOT NULL,
  sessions TEXT[] NOT NULL,
  chart_image_url TEXT,
  created_at TIMESTAMP DEFAULT NOW()
);
```

### Checklists

```sql
CREATE TABLE checklists (
  id UUID PRIMARY KEY,
  strategy_id UUID REFERENCES strategies(id),
  type TEXT CHECK(type IN ('entry','exit')),
  label TEXT NOT NULL,
  description TEXT,
  sort_order INT
);
```

### Invalidations

```sql
CREATE TABLE invalidations (
  id UUID PRIMARY KEY,
  strategy_id UUID REFERENCES strategies(id),
  label TEXT NOT NULL,
  reason TEXT
);
```

### Trades

```sql
CREATE TABLE trades (
  id UUID PRIMARY KEY,
  strategy_id UUID REFERENCES strategies(id),
  taken BOOLEAN NOT NULL,
  missed_reason TEXT,
  direction TEXT CHECK(direction IN ('long','short')),
  session TEXT,
  bias TEXT,
  timestamp TIMESTAMP NOT NULL,
  entry_price NUMERIC,
  stop_loss_price NUMERIC,
  exit_price NUMERIC,
  risk_percent NUMERIC,
  r_multiple NUMERIC,
  reason TEXT,
  emotional_notes TEXT,
  created_at TIMESTAMP DEFAULT NOW()
);
```

### Screenshots & Checklist Logs

```sql
CREATE TABLE trade_screenshots ( id UUID PRIMARY KEY, trade_id UUID REFERENCES trades(id), image_url TEXT );
CREATE TABLE trade_checklist_logs ( id UUID PRIMARY KEY, trade_id UUID REFERENCES trades(id), checklist_id UUID REFERENCES checklists(id), checked BOOLEAN );
```

---

## 🔌 API Endpoints

| Path                                 | Method | Description                                     |
| ------------------------------------ | ------ | ----------------------------------------------- |
| `/api/strategies`                    | GET    | List strategies                                 |
| `/api/strategies`                    | POST   | Create strategy                                 |
| `/api/strategies/{id}`               | GET    | Get/edit/delete specific strategy               |
| `/api/strategies/{id}/checklists`    | POST   | Add entry/exit checklist item                   |
| `/api/strategies/{id}/invalidations` | POST   | Add invalidation rule                           |
| `/api/trades`                        | GET    | List trades (filter by strategy, taken/missed)  |
| `/api/trades`                        | POST   | Log new trade                                   |
| `/api/trades/{id}`                   | GET    | Trade details                                   |
| `/api/trades/{id}`                   | PUT    | Update trade                                    |
| `/api/trades/{id}`                   | DELETE | Delete trade                                    |
| `/api/trades/{id}/screenshots`       | POST   | Upload screenshots                              |
| `/api/reports/summary`               | GET    | Compute win rate, avg R, drawdown, total trades |

*All routes protected via Clerk middleware and validated in tRPC/Convex functions.*

---

## 🎨 UI/UX & Usability

1. **Minimize Cognitive Load**:

   * Use clear grouping and section headings for checklists vs. trade data ([Nielsen Norman Group][9]).
2. **Labels & Placeholders**:

   * Always show labels outside fields; avoid placeholder-only labels to prevent memory strain ([Nielsen Norman Group][10]).
3. **Inline Validation**:

   * Validate numeric fields (prices, risk %) immediately on blur; show errors next to fields ([Nielsen Norman Group][11]).
4. **Required Fields**:

   * Mark required inputs with asterisks; only mark optional fields sparingly ([Nielsen Norman Group][12]).
5. **Checkbox Design**:

   * Present entry/exit criteria as standalone checkboxes with clear labels and sufficient hit area ([Nielsen Norman Group][13]).
6. **Date & Time Inputs**:

   * Use native date-picker components with unambiguous formats; support keyboard navigation ([Nielsen Norman Group][14]).
7. **Screenshots Upload**:

   * Provide drag-and-drop or file dialog; limit size and show thumbnails post-upload.
8. **Error Handling**:

   * Show concise, polite error messages; allow quick correction without page reload ([Nielsen Norman Group][11]).

---

## 🔒 Security & Compliance

* **Authentication**: JWT sessions via Clerk with optional 2FA ([Tailwind CSS][15]).
* **Authorization**: ACL at Convex function layer; enforce `userId` scoping.
* **Data Protection**: TLS-encrypted in transit; Convex auto-encrypts at rest.
* **Input Sanitization**: Sanitize free-form text and filenames; prevent XSS/SQLi.
* **Privacy**: GDPR-compliant user-data deletion workflow; cookie consent banner.

---

## ⚙️ DevOps & Monitoring

* **Hosting**: Vercel for Next.js, Convex edge functions, and Cron jobs ([Vercel][7]).
* **CI/CD**: GitHub Actions run lint/tests and deploy to staging on PR, prod on main.
* **Error Tracking**: Sentry for front-end exceptions; Convex logs for server errors.
* **Performance**: Vercel Edge Cache on public assets; Convex real-time queries for live dashboards.
* **Backups**: Daily Convex snapshots; S3 retention for screenshots.

---

## 📅 MVP Roadmap (6 Weeks)

| Week | Deliverable                                                |
| ---- | ---------------------------------------------------------- |
| 1    | Auth setup (Clerk), Next.js scaffold, Convex connection    |
| 2    | Strategy CRUD + checklist UI/forms + DB migrations         |
| 3    | Trade logging form + validation + screenshot upload        |
| 4    | Trades list/detail views + filtering                       |
| 5    | Analytics widgets (win rate, avg R, equity curve)          |
| 6    | QA, performance tuning, staging deploy, beta user feedback |

---

With this comprehensive PRD—spanning **stack selection**, **data architecture**, **API design**, and **industry-standard usability**—your development team has a clear blueprint to ship an MVP that’s robust, secure, and delightfully usable.

[1]: https://nextjs.org/blog/next-15?utm_source=chatgpt.com "Next.js 15"
[2]: https://v2.tailwindcss.com/docs?utm_source=chatgpt.com "Documentation - Tailwind CSS"
[3]: https://ui.shadcn.com/docs?utm_source=chatgpt.com "Introduction - Shadcn UI"
[4]: https://clerk.com/docs/references/nextjs/auth?utm_source=chatgpt.com "auth() - Next.js - Clerk"
[5]: https://trpc.io/docs/?utm_source=chatgpt.com "11.x - tRPC"
[6]: https://docs.convex.dev/home?utm_source=chatgpt.com "Convex Docs | Convex Developer Hub"
[7]: https://vercel.com/docs/frameworks/nextjs?utm_source=chatgpt.com "Next.js on Vercel"
[8]: https://fastapi.tiangolo.com/?utm_source=chatgpt.com "FastAPI"
[9]: https://www.nngroup.com/articles/4-principles-reduce-cognitive-load/?utm_source=chatgpt.com "4 Principles to Reduce Cognitive Load in Forms - NN/g"
[10]: https://www.nngroup.com/articles/form-design-placeholders/?utm_source=chatgpt.com "Placeholders in Form Fields Are Harmful - NN/g"
[11]: https://www.nngroup.com/articles/errors-forms-design-guidelines/?utm_source=chatgpt.com "10 Design Guidelines for Reporting Errors in Forms - NN/g"
[12]: https://www.nngroup.com/articles/required-fields/?utm_source=chatgpt.com "Marking Required Fields in Forms - NN/g"
[13]: https://www.nngroup.com/articles/checkboxes-design-guidelines/?utm_source=chatgpt.com "Checkboxes: Design Guidelines - NN/g"
[14]: https://www.nngroup.com/articles/date-input/?utm_source=chatgpt.com "Date-Input Form Fields: UX Design Guidelines - NN/g"
[15]: https://tailwindcss.com/docs?utm_source=chatgpt.com "Installing Tailwind CSS with Vite"


## DB Design

Perfect. Here's a structured outline for both **API endpoints** and a **relational database schema** tailored to your **Trading Model Execution Tracker**.

---

## 🧩 Database Schema (PostgreSQL or similar RDBMS)

### 🔹 `strategies`

Stores user-defined trading strategies.

| Column            | Type      | Notes                           |
| ----------------- | --------- | ------------------------------- |
| `id`              | UUID (PK) | Primary key                     |
| `name`            | TEXT      | Strategy name                   |
| `instrument`      | TEXT      | E.g., EURUSD                    |
| `timeframes`      | TEXT\[]   | Array: \['M15', 'H1']           |
| `sessions`        | TEXT\[]   | Array: \['Asia', 'London']      |
| `chart_image_url` | TEXT      | External or uploaded image link |
| `created_at`      | TIMESTAMP |                                 |

---

### 🔹 `checklists`

Stores both entry and exit checklists per strategy.

| Column        | Type      | Notes                         |
| ------------- | --------- | ----------------------------- |
| `id`          | UUID (PK) |                               |
| `strategy_id` | UUID (FK) | References `strategies.id`    |
| `type`        | TEXT      | `entry` / `exit`              |
| `label`       | TEXT      | E.g., “Liquidity Sweep”       |
| `description` | TEXT      | Optional extended description |
| `sort_order`  | INTEGER   | For UI display ordering       |

---

### 🔹 `invalidations`

| Column        | Type      | Notes                      |
| ------------- | --------- | -------------------------- |
| `id`          | UUID (PK) |                            |
| `strategy_id` | UUID (FK) | References `strategies.id` |
| `label`       | TEXT      | E.g., “High-Impact News”   |
| `reason`      | TEXT      | Optional                   |

---

### 🔹 `trades`

| Column            | Type      | Notes                         |
| ----------------- | --------- | ----------------------------- |
| `id`              | UUID (PK) | Trade record ID               |
| `strategy_id`     | UUID (FK) |                               |
| `taken`           | BOOLEAN   | Trade executed or missed      |
| `missed_reason`   | TEXT      | Nullable                      |
| `direction`       | TEXT      | Long / Short                  |
| `session`         | TEXT      | Asia / London / NY            |
| `bias`            | TEXT      | Reversal / Breakout / etc.    |
| `timestamp`       | TIMESTAMP | When trade was logged         |
| `entry_price`     | NUMERIC   | Nullable if not taken         |
| `stop_loss_price` | NUMERIC   |                               |
| `exit_price`      | NUMERIC   |                               |
| `risk_percent`    | NUMERIC   | (e.g., 0.5, 1.0)              |
| `r_multiple`      | NUMERIC   | (exit-entry)/(entry-SL)       |
| `reason`          | TEXT      | Short reason for taking trade |
| `emotional_notes` | TEXT      | Optional reflection           |
| `created_at`      | TIMESTAMP |                               |

---

### 🔹 `trade_screenshots`

| Column      | Type      | Notes |
| ----------- | --------- | ----- |
| `id`        | UUID (PK) |       |
| `trade_id`  | UUID (FK) |       |
| `image_url` | TEXT      |       |

---

### 🔹 `trade_checklist_logs`

Used to track which checklist items were checked for a trade.

| Column         | Type      | Notes      |
| -------------- | --------- | ---------- |
| `id`           | UUID (PK) |            |
| `trade_id`     | UUID (FK) |            |
| `checklist_id` | UUID (FK) |            |
| `checked`      | BOOLEAN   | true/false |

---

## 🌐 API Endpoint Suggestions (RESTful, FastAPI-style)

### 🧱 Strategy Setup

| Endpoint           | Method | Description             |
| ------------------ | ------ | ----------------------- |
| `/strategies/`     | GET    | List all strategies     |
| `/strategies/`     | POST   | Create a new strategy   |
| `/strategies/{id}` | GET    | Get a specific strategy |
| `/strategies/{id}` | PUT    | Update a strategy       |
| `/strategies/{id}` | DELETE | Delete a strategy       |

---

### 📋 Checklist / Invalidation

| Endpoint                         | Method | Description           |
| -------------------------------- | ------ | --------------------- |
| `/strategies/{id}/checklists`    | POST   | Add checklist item    |
| `/strategies/{id}/invalidations` | POST   | Add invalidation rule |

---

### 🧾 Trades

| Endpoint       | Method | Description       |
| -------------- | ------ | ----------------- |
| `/trades/`     | GET    | List all trades   |
| `/trades/`     | POST   | Log a new trade   |
| `/trades/{id}` | GET    | Get trade details |
| `/trades/{id}` | PUT    | Update trade      |
| `/trades/{id}` | DELETE | Delete trade      |

---

### 📸 Screenshots

| Endpoint                         | Method | Description          |
| -------------------------------- | ------ | -------------------- |
| `/trades/{id}/screenshots`       | POST   | Upload screenshot(s) |
| `/trades/{id}/screenshots/{sid}` | DELETE | Remove a screenshot  |

---

### 📈 Analytics (Phase 2)

| Endpoint            | Method | Description                   |
| ------------------- | ------ | ----------------------------- |
| `/reports/summary`  | GET    | Aggregate metrics by strategy |
| `/reports/streaks`  | GET    | Max win/loss streaks          |
| `/reports/emotions` | GET    | Emotional trend tagging       |

---

## 🔐 Authentication

Add JWT-based user auth or OAuth2 if, Multiple users will use the platform, Personal strategies/trades must be isolated



## Development Stack 

Here’s the **quick-hit “sauce”** — the minimal opinionated stack that lets you ship the trade-journal app fast, with real-time UX, zero-ops hosting, and full TypeScript safety end-to-end:

| Layer                       | Tech                                                                                                                                                                        | 1-liner “why” |
| --------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| **Frontend**                | **Next.js 15 (App Router + React 19)** – modern file-based routing, server components, and instant edge-ready pages ([nextjs.org][1], [nextjs.org][2])                      |               |
| **Styling / UI**            | **Tailwind CSS + shadcn/ui** – drop-in, accessible component library that uses Tailwind tokens out of the box ([ui.shadcn.com][3], [ui.shadcn.com][4])                      |               |
| **Type-safety glue**        | **tRPC** – generates type-safe RPC calls; your client auto-infers all backend input/output types, no REST schema boilerplate ([trpc.io][5], [Deno][6])                      |               |
| **Auth**                    | **Clerk** – plug-and-play Next.js auth (sessions, orgs, MFA, social log-ins) without writing Passport/OAuth flows ([Clerk][7], [Clerk][8])                                  |               |
| **Realtime DB + Functions** | **Convex** – serverless, strongly-typed document DB with built-in real-time queries and cron/scheduled jobs for nightly stat roll-ups ([Convex][9], [stack.convex.dev][10]) |               |
| **Edge hosting & cron**     | **Vercel** – one-command deploys for Next.js + Convex functions, plus native cron jobs for summaries/back-tests ([Vercel][11], [Vercel][12])                                |               |

### How it snaps together

1. **Next.js pages/components** render the wire-frame trade form; shadcn/ui gives you the checkboxes, selects, modals.
2. The **form submits via tRPC** to a Convex function; the same TypeScript `Trade` interface is used on both sides (no API drift).
3. **Convex** writes the trade doc, broadcasts live updates to any open dashboards (equity curve, win-rate widgets).
4. **Clerk middleware** guards routes & passes `userId` to Convex so every record is scoped per trader.
5. Nightly **Vercel Cron** pings a Convex scheduled function that aggregates expectancy, streaks, etc., and stores the summary back in Convex.
6. Everything deploys with `vercel deploy`; you get preview URLs for every PR.

That’s the sauce. 🚀

[1]: https://nextjs.org/blog/next-15?utm_source=chatgpt.com "Next.js 15"
[2]: https://nextjs.org/docs/app?utm_source=chatgpt.com "Next.js Docs: App Router"
[3]: https://ui.shadcn.com/docs/tailwind-v4?utm_source=chatgpt.com "Tailwind v4 - Shadcn UI"
[4]: https://ui.shadcn.com/docs/installation/manual?utm_source=chatgpt.com "Manual Installation - Shadcn UI"
[5]: https://trpc.io/?utm_source=chatgpt.com "tRPC - Move Fast and Break Nothing. End-to-end typesafe APIs ..."
[6]: https://deno.com/blog/build-typesafe-apis-trpc?utm_source=chatgpt.com "Build a Typesafe API with tRPC and Deno"
[7]: https://clerk.com/nextjs-authentication?utm_source=chatgpt.com "Next.js Authentication - Best Auth Middleware for your Next app!"
[8]: https://clerk.com/docs/references/nextjs/auth?utm_source=chatgpt.com "auth() - Next.js - Clerk"
[9]: https://www.convex.dev/?utm_source=chatgpt.com "Convex | The reactive database for app developers"
[10]: https://stack.convex.dev/building-a-typescript-quiz-app-with-convex-and-expo?utm_source=chatgpt.com "How I Built a Quiz App with Convex + TypeScript in 6 Weeks"
[11]: https://vercel.com/docs/cron-jobs?utm_source=chatgpt.com "Cron Jobs - Vercel"
[12]: https://vercel.com/guides/how-to-setup-cron-jobs-on-vercel?utm_source=chatgpt.com "How to Setup Cron Jobs on Vercel"
