# Habit Gamification Backend

A gamified habit-tracking API built with **Laravel 13** that turns daily tasks into an engaging RPG-like experience. Users earn coins, maintain streaks, unlock achievements, compete in challenges, and manage personal finances — all through a RESTful API.

Built for mobile/web frontends with Sanctum token authentication, email verification, and Google OAuth.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13.x (PHP ^8.3) |
| Auth | Sanctum (tokens) + Socialite (Google OAuth) |
| Database | PostgreSQL (production), SQLite (testing) |
| Queue | Database driver (`jobs` table) |
| Cache | File/database with 1800s TTL on finance endpoints |
| Testing | PHPUnit 12, SQLite in-memory |

---

## Features Overview (13 Phases)

### Phase 1 — Authentication & User Management
- Register, login (email + Google), logout
- Email verification via signed URLs
- Forgot/reset password
- Profile CRUD + password change
- Account deletion

### Phase 2 — Categories & Tasks
- User-defined categories (icon, color)
- Tasks linked to categories with custom point values
- Soft-deletes on all resources

### Phase 3 — Challenges
- User-created multi-participant challenges
- Challenges have start/end dates, required categories
- Join, leave, complete with coin rewards
- Friend Challenges: 1v1 competitive with stake coins

### Phase 4 — Daily Progress
- Complete a task once per day → `TaskCompletion` + `DailyCheckin`
- Points awarded on completion
- View daily check-in history

### Phase 5 — Streaks
- Consecutive-day tracking per user
- Milestones at 3, 7, 30, 100 days → `StreakMilestoneReached` event
- Event triggers coin rewards and achievement checks

### Phase 6 — Coin System
- Virtual economy: earn, spend, bonus, penalty transactions
- Balance tracking per user
- Used for: freezes (3 coins), challenge stakes, promise rewards/penalties

### Phase 7 — Friends
- Search users by name/email
- Send, accept, reject, remove friend requests
- Friend activity tracking (events logged on Friendship model)

### Phase 8 — Achievements
- 18 seeded achievements across 6 categories: task, streak, coin, challenge, social, promise
- Auto-unlocked via event listeners
- e.g. "Complete 100 tasks", "7-day streak", "Earn 500 coins"

### Phase 9 — Promise System
- Self-commitments with a future validation date
- Fulfill = +10 coins, Break = -10 coins penalty
- Also updates user streak

### Phase 10 — Freeze System
- Buy a freeze (3 coins) to protect your streak
- Send freezes to friends (must be friends)
- Use a freeze when you miss a day → streak preserved

### Phase 11 — Dashboard & Statistics
- Aggregated dashboard: total tasks, streaks, coins, achievements, friends
- Statistics with daily, weekly, monthly, yearly breakdowns

### Phase 12 — Income Management
- Income sources (Salary, Freelance, Business, Investment, Gift, Other)
- Multi-source income entries with dates, amounts, notes
- CRUD + soft-deletes

### Phase 13 — Finance Module (FFGR)
Full personal finance engine. See dedicated section below.

---

## Quick Start

```bash
# 1. Install dependencies
composer install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite for local)
touch database/database.sqlite
php artisan migrate --seed

# 4. Start server
php artisan serve

# 5. Run tests
php artisan test
```

---

## API Reference

All authenticated endpoints require the header: `Authorization: Bearer {token}`

### Authentication (Public)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login with email + password |
| POST | `/api/google/login` | Login/register with Google OAuth |
| POST | `/api/forgot-password` | Send password reset link |
| POST | `/api/reset-password` | Reset password with token |
| GET | `/api/email/verify/{id}/{hash}` | Verify email (signed URL) |

### Profile (Verified Email Required)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/logout` | Revoke current token |
| GET | `/api/profile` | Get authenticated user profile |
| PUT | `/api/profile` | Update profile |
| PUT | `/api/change-password` | Change account password |
| DELETE | `/api/profile` | Delete account |

### Categories (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/categories` | List all categories |
| POST | `/api/categories` | Create a category |
| PUT | `/api/categories/{id}` | Update a category |
| DELETE | `/api/categories/{id}` | Delete a category |

### Tasks (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/tasks` | List all tasks |
| POST | `/api/tasks` | Create a task |
| PUT | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task |

### Daily Progress (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/tasks/{task}/complete` | Complete a task for today |
| GET | `/api/daily-checkins` | Get daily check-in history |
| GET | `/api/today` | Get today's progress summary |

### Streaks (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/streaks` | Get streak history |
| GET | `/api/streaks/current` | Get current streak info |

### Coins (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/coins` | Get coin balance |
| GET | `/api/coin-transactions` | List all coin transactions |

### Achievements (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/achievements` | List all available achievements |
| GET | `/api/my-achievements` | Get user's unlocked achievements |

### Friends (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/users/search?q=` | Search users by name/email |
| GET | `/api/friends` | List friends and pending requests |
| POST | `/api/friends/request` | Send friend request |
| POST | `/api/friends/accept` | Accept friend request |
| POST | `/api/friends/reject` | Reject friend request |
| DELETE | `/api/friends/{id}` | Remove friend |

### Challenges (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/challenges` | List all challenges |
| POST | `/api/challenges` | Create a challenge |
| GET | `/api/challenges/{id}` | Get challenge details |
| PUT | `/api/challenges/{id}` | Update a challenge |
| DELETE | `/api/challenges/{id}` | Delete a challenge |
| POST | `/api/challenges/{id}/join` | Join a challenge |
| DELETE | `/api/challenges/{id}/leave` | Leave a challenge |

### Freezes (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/freezes` | List freezes (available/sent/received) |
| POST | `/api/freezes` | Buy or send a freeze |

### Promises (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/promises` | List promises |
| POST | `/api/promises` | Create a promise |

### Income Sources (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/income-sources` | List income sources |
| POST | `/api/income-sources` | Create an income source |

### Incomes (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/incomes` | List incomes |
| POST | `/api/incomes` | Record an income entry |
| PUT | `/api/incomes/{id}` | Update an income |
| DELETE | `/api/incomes/{id}` | Delete an income |

### Dashboard (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/dashboard` | Aggregated user overview |
| GET | `/api/statistics` | Task completion statistics |

### Finance — Budget (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/budget` | Get current month budget overview |
| POST | `/api/finance/budget/settings` | Update budget percentage settings |

### Finance — Expenses (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/expenses` | List expenses (filterable by category/date) |
| POST | `/api/finance/expenses` | Create an expense |
| PUT | `/api/finance/expenses/{id}` | Update an expense |
| DELETE | `/api/finance/expenses/{id}` | Delete an expense |

### Finance — Emergency Fund (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/emergency-funds` | List emergency funds |
| POST | `/api/finance/emergency-funds` | Create an emergency fund goal |
| POST | `/api/finance/emergency-funds/{id}/deposit` | Deposit into emergency fund |
| POST | `/api/finance/emergency-funds/{id}/withdraw` | Withdraw from emergency fund |

### Finance — Investments (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/investments` | List investments |
| POST | `/api/finance/investments` | Create an investment |
| POST | `/api/finance/investments/{id}/transactions` | Add transaction (deposit/withdraw/profit/loss) |

### Finance — Reward Wallet (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/reward-wallet` | Get reward wallet balance |
| GET | `/api/finance/reward-transactions` | List reward transactions |

### Finance — Dashboard & Analytics (Authenticated)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/finance/dashboard` | Finance dashboard overview |
| GET | `/api/finance/statistics?period=monthly` | Finance statistics (weekly/monthly/yearly/5year) |

---

## Gamification System

### Streaks
- Tracked per user via `current_streak`, `longest_streak`, `last_completed_date`
- Updated on every task completion / promise fulfillment
- Milestone events fire at 7, 30, and 100 consecutive days
- Freezes protect the streak when a day is missed

### Coins
- **Earn**: complete tasks, challenges, achieve milestones, fulfill promises
- **Spend**: buy freezes (3 coins), stake in challenges
- **Penalty**: broken promises (-10 coins)
- Transactions recorded with type (earn/spend/bonus/penalty) and reason

### Achievements (18 total)

| Name | Category | Requirement |
|------|----------|-------------|
| First Task | task | Complete 1 task |
| Task Master | task | Complete 100 tasks |
| Task Legend | task | Complete 500 tasks |
|早起き | streak | 3-day streak |
| 習慣化 | streak | 7-day streak |
| 軌道 | streak | 30-day streak |
| 不屈 | streak | 100-day streak |
| First Coins | coin | Earn 10 coins |
| Coin Collector | coin | Earn 100 coins |
| Coin Hoarder | coin | Earn 500 coins |
| First Challenge | challenge | Complete 1 challenge |
| Challenge King | challenge | Complete 10 challenges |
| 友達 | social | Make 1 friend |
| Social Butterfly | social | Make 5 friends |
| Promise Keeper | promise | Fulfill 1 promise |
| Promise Legend | promise | Fulfill 10 promises |
| Freeze User | social | Send 1 freeze |
| Keep First Promise | promise | Keep the very first promise |

---

## Finance Module (FFGR)

The FFGR (Faith-based Financial Growth & Responsibility) engine implements a structured budgeting framework based on proportional allocation.

### Budget Flow

```
Income
  │
  ├── 10% → Asrat (Tithe)
  │
  └── 90% → Remaining
              │
              ├── 50% → Foundation (Needs / Expenses)
              ├── 20% → Emergency Fund
              ├── 20% → Growth (Investments)
              └── 10% → Reward Wallet (Fun Money)
```

All percentages are configurable via `BudgetSetting`.

### Financial Health Score

A composite score (0–100) calculated daily:

| Factor | Weight |
|--------|--------|
| Savings Rate | 30% |
| Expense Control | 25% |
| Emergency Fund Adequacy | 20% |
| Investment Growth | 15% |
| Goal Completion | 10% |

### Scheduled Jobs (via Laravel Scheduler)

| Job | Frequency | Description |
|-----|-----------|-------------|
| `UpdateFinanceStatisticsJob` | Daily | Recalculate all finance aggregates |
| `CheckEmergencyGoalsJob` | Daily | Auto-complete emergency fund goals when target reached |
| `CalculateFinancialHealthScoreJob` | Daily | Recalculate health scores for all users |
| `GenerateMonthlyBudgetReportJob` | Monthly | Summarize monthly budget performance |
| `GenerateMonthlyFinanceReportJob` | Monthly | Generate detailed finance reports |

---

## Database Schema (28 Tables)

### Core Tables

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `users` | uuid, name, email, password, google_id, avatar | User accounts |
| `categories` | uuid, user_id, name, icon, color, description | Task categories |
| `tasks` | uuid, user_id, category_id, title, description, points | Habit tasks |
| `task_completions` | uuid, user_id, task_id, completed_at | Daily task completions |
| `daily_checkins` | uuid, user_id, checkin_date, tasks_count, points_earned | Daily summary |
| `streaks` | uuid, user_id, current_streak, longest_streak, last_completed_date | Streak tracking |
| `coin_transactions` | uuid, user_id, type, amount, balance_after, reason | Coin ledger |
| `achievements` | uuid, name, category, description, requirement | Available achievements |
| `user_achievements` | uuid, user_id, achievement_id | Unlocked achievements |
| `challenges` | uuid, creator_id, title, description, start/end dates, required_categories | Group challenges |
| `challenge_user` | uuid, challenge_id, user_id, status | Challenge participants |
| `challenge_categories` | uuid, challenge_id, category_id | Challenge required categories |
| `friend_challenges` | id (auto), user1_id, user2_id, bet_coins, winner_id | 1v1 challenges |
| `friendships` | uuid, sender_id, receiver_id, status | Friend connections |
| `freezes` | uuid, sender_id, receiver_id, used_at | Streak protection |
| `promises` | uuid, user_id, title, validate_date, status | Self-commitments |
| `income_sources` | uuid, user_id, name, icon | Income categories |
| `incomes` | uuid, user_id, income_source_id, amount, income_date | Income records |

### Finance Tables

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `budget_settings` | id, user_id, asrat_pct, needs_pct, emergency_pct, investment_pct, reward_pct | Budget rules |
| `budget_allocations` | id, user_id, month, total_income, asrat_amount, needs_amount, emergency_amount, investment_amount, reward_amount | Monthly allocations |
| `expense_categories` | id, user_id, name, icon, color | Expense categories |
| `expenses` | uuid, user_id, expense_category_id, amount, expense_date, description | Expense records |
| `emergency_funds` | uuid, user_id, name, goal_amount, current_amount, completed_at | Savings goals |
| `emergency_transactions` | uuid, emergency_fund_id, type (deposit/withdraw), amount | Fund transactions |
| `investments` | uuid, user_id, name, type | Investment accounts |
| `investment_transactions` | uuid, investment_id, type (deposit/withdraw/profit/loss), amount | Investment activity |
| `reward_wallets` | id, user_id, available_balance, locked_balance | Fun money wallet |
| `reward_transactions` | id, reward_wallet_id, type (earn/spend), amount, description | Wallet transactions |

---

## Event-Driven Architecture

Key domain events and their listeners:

```
TaskCompleted
  ├── RewardTaskCoins
  ├── UpdateUserStreak
  └── CheckTaskAchievements

CoinsEarned
  └── CheckCoinAchievements

StreakMilestoneReached
  ├── RewardStreakCoins
  └── CheckStreakAchievements

ChallengeCompleted
  ├── RewardChallengeCoins
  └── CheckChallengeAchievements

FriendRequestSent / FriendRequestAccepted / FriendRemoved
  ├── RecordFriendActivity
  └── CheckSocialAchievements

PromiseFulfilled
  ├── RewardPromise
  ├── UpdateUserStreak
  └── CheckSocialAchievements

PromiseBroken
  ├── PenaltyPromise
  └── UpdateUserStreak

IncomeCreated
  └── GenerateBudgetAllocation

ExpenseCreated / BudgetAllocated / EmergencyFundCompleted / InvestmentCreated / RewardEarned
  ├── RefreshFinanceDashboardCache
  ├── UpdateFinanceAnalytics
  ├── UpdateFinanceStatistics
  └── CheckFinanceAchievements
```

---

## Architecture & Patterns

- **Service Layer**: Business logic extracted into dedicated service classes (e.g., `StreakService`, `CoinService`, `BudgetService`)
- **Event-Driven**: Events decouple side effects (achievements, rewards, cache invalidation)
- **Repository-less**: Direct model usage with query scopes
- **UUID Primary Keys**: All user-facing models use UUIDs for security
- **Soft Deletes**: Enabled on most models for data recovery
- **Policy Authorization**: 9 policies protecting resource ownership
- **SQLite Testing**: In-memory SQLite for fast, isolated test runs

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php vendor/bin/phpunit tests/Feature/FinanceDashboardAnalyticsTest.php

# Run with coverage (requires Xdebug/PCOV)
php artisan test --coverage
```

17 feature test files covering all 13 phases. Tests use SQLite in-memory database with `RefreshDatabase` trait.

---

## Project Structure

```
app/
├── Console/
│   └── Kernel.php              # Console kernel
├── Finance/
│   ├── Controllers/            # 6 finance controllers
│   ├── Events/                 # Finance events
│   ├── Jobs/                   # 5 scheduled jobs
│   ├── Listeners/              # Finance event listeners
│   ├── Models/                 # 10 finance models
│   └── Services/               # 5 finance services
├── Http/
│   ├── Controllers/Api/        # 15 core API controllers
│   └── Middleware/              # Custom middleware
├── Models/                     # 18 core models
├── Policies/                   # 9 authorization policies
└── Providers/
    └── AppServiceProvider.php  # Event bindings

config/
├── database.php                # Multi-database config
└── sanctum.php                 # Sanctum settings

database/
├── migrations/                 # 28 migration files
└── seeders/                    # 4 seeders

routes/
├── api.php                     # All API routes (133 lines)
└── console.php                 # Scheduled job definitions

tests/
├── Feature/                    # 17 feature test files
└── Unit/                       # 1 unit test
```

---

## Postman Collection

A merged Postman collection is available at `docs/postman/DayChallenge_API_Complete.postman_collection.json` with all 85+ endpoints organized by phase.

```bash
# Import the collection
# Import the companion environment
docs/postman/DayChallenge_Local.postman_environment.json
```

---

## License

MIT
