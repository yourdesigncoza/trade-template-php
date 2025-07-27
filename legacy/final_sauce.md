## 🎯 Project Overview

You are building an **MVP v1** of a **Trade Journal & Execution Tracker** web app. Its core goal is to enforce trader discipline through checklist-driven entries, capture both executed and missed trades with rich metadata, and surface immediate analytics (win rate, average R, drawdown, equity curve). All data is scoped per user.

---

## 🏗️ Tech Stack

- **Frontend:**  
  - Next.js 15 with App Router & React 19 (Server + Client Components)  
  - TypeScript end-to-end  
  - Tailwind CSS + shadcn/ui components
- **Auth & Sessions:**  
  - Clerk for sign-up, login, MFA, session management, and route protection  
- **API & Business Logic:**  
  - tRPC for type-safe RPC procedures  
  - Convex for serverless, strongly-typed document database, real-time queries, and edge functions  
- **Hosting & Cron:**  
  - Vercel for Next.js + Convex deployment, preview URLs, and native cron jobs  
- **(Optional) Analytics Service:**  
  - Python FastAPI microservice for heavier Pandas/NumPy back-tests, called via webhook or scheduled task  

---

## 🗄️ Data Model

### strategies  
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

### checklists  
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

### invalidations  
```sql
CREATE TABLE invalidations (
  id UUID PRIMARY KEY,
  strategy_id UUID REFERENCES strategies(id),
  label TEXT NOT NULL,
  reason TEXT
);
```

### trades  
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

### trade_screenshots & trade_checklist_logs  
```sql
CREATE TABLE trade_screenshots (
  id UUID PRIMARY KEY,
  trade_id UUID REFERENCES trades(id),
  image_url TEXT
);

CREATE TABLE trade_checklist_logs (
  id UUID PRIMARY KEY,
  trade_id UUID REFERENCES trades(id),
  checklist_id UUID REFERENCES checklists(id),
  checked BOOLEAN
);
```

---

## 🌐 API Endpoints (tRPC / REST)

- **Strategies**  
  - `GET  /api/strategies`  
  - `POST /api/strategies`  
  - `GET  /api/strategies/{id}`  
  - `PUT  /api/strategies/{id}`  
  - `DELETE /api/strategies/{id}`  
- **Checklists & Invalidations**  
  - `POST /api/strategies/{id}/checklists`  
  - `POST /api/strategies/{id}/invalidations`  
- **Trades**  
  - `GET    /api/trades?strategyId={}`  
  - `POST   /api/trades`  
  - `GET    /api/trades/{id}`  
  - `PUT    /api/trades/{id}`  
  - `DELETE /api/trades/{id}`  
- **Screenshots**  
  - `POST   /api/trades/{id}/screenshots`  
  - `DELETE /api/trades/{id}/screenshots/{sid}`  
- **Reports (Phase 1)**  
  - `GET /api/reports/summary`  → win rate, avg R, max drawdown, total trades, equity curve  

_All routes protected by Clerk middleware. All inputs & outputs validated via tRPC/Convex schema._

---

## 🎨 UI/UX Guidelines

1. **Checklist First**  
   - Entry & exit criteria shown as clear checkboxes.  
   - “New Trade” form unlocks only when **all entry** boxes are checked.  
2. **Form Layout**  
   - Fields: Taken (Yes/No), Direction, Session, Bias, Timestamp  
   - Price inputs: Entry, Stop Loss, Exit (if taken)  
   - Risk % selector (0.25, 0.5, 1)  
   - Reason (1-line), Emotional Notes (optional)  
   - Screenshots drag-and-drop with thumbnail preview  
   - Missed trades hide price fields & require “Missed Reason”  
3. **Validation & Accessibility**  
   - Inline validation on blur  
   - Labels outside inputs; asterisks on required  
   - Native date-time pickers  
   - Accessible tab order & ARIA labels  

---

## 📅 MVP v1 Roadmap

| Week | Goals                                                                    |
|------|--------------------------------------------------------------------------|
| 1    | Set up Next.js + Clerk auth, Convex connection, basic CI/CD             |
| 2    | Strategy CRUD, checklist & invalidation UI + DB migrations              |
| 3    | Trade logging form, checklist enforcement, screenshot upload            |
| 4    | Trades list/detail views with filters (taken vs. missed)                |
| 5    | Analytics dashboard: win rate, avg R, max drawdown, equity curve chart  |
| 6    | QA tests (unit, integration, manual), staging deploy, beta feedback     |

---

## 🔍 Acceptance Criteria

- **Auth & Data Isolation**: Users sign up, log in, and see only their own strategies & trades.  
- **Checklist Enforcement**: Cannot log a trade until **all** entry criteria are checked.  
- **Trade Logging**: Supports executed & missed trades with full metadata.  
- **Persistence**: Strategies, checklists, trades, screenshots, and logs saved reliably.  
- **Analytics**: Instant calculation of core metrics and rendering of an equity curve.  
- **UI/UX**: Responsive, accessible, and validated forms as per Nielsen‐Norman best practices.  

---

## 🔗 Next Steps

1. **Fork starter repo** (e.g., Next.js 15 + Clerk + Convex + shadcn/ui).  
2. **Implement data models** in Convex or your chosen DB.  
3. **Build tRPC procedures** for strategy, checklist, trade, and report operations.  
4. **Design UI** using shadcn/ui components and Tailwind.  
5. **Deploy** to Vercel; configure Cron for nightly roll-ups.  
6. **Invite beta users** for rapid feedback and iterate before v1.1.  
