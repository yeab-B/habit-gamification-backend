<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "v1",
    title: "DayChallenge API",
    description: "Habit Gamification & Personal Finance API",
    contact: new OA\Contact(
        name: "DayChallenge Support",
        email: "support@daychallenge.com"
    ),
    license: new OA\License(
        name: "MIT",
        url: "https://opensource.org/licenses/MIT"
    )
)]
#[OA\Server(
    url: "/api",
    description: "API Local Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer",
    description: "Enter your Bearer token in the format: Bearer <token>"
)]
#[OA\Tag(name: "Authentication", description: "Endpoints for user register, login, logout, password recovery, and profile management")]
#[OA\Tag(name: "Categories", description: "Manage tasks categories")]
#[OA\Tag(name: "Tasks", description: "Manage daily tasks and habits")]
#[OA\Tag(name: "Challenges", description: "DayChallenge community and individual challenges")]
#[OA\Tag(name: "Daily Progress", description: "Track daily task completions and checkins")]
#[OA\Tag(name: "Streaks", description: "Track daily streak metrics")]
#[OA\Tag(name: "Coins", description: "Track user coins and transactions")]
#[OA\Tag(name: "Friends", description: "Manage friendship requests and search users")]
#[OA\Tag(name: "Promises", description: "Create and track promises to perform tasks")]
#[OA\Tag(name: "Freezes", description: "Manage streak freezes")]
#[OA\Tag(name: "Achievements", description: "Gamified achievement system badges and progress")]
#[OA\Tag(name: "Dashboard", description: "Main dashboard data and stats")]
#[OA\Tag(name: "Finance", description: "Personal Finance Module (FFGR) - Income, Budget, Expenses, Emergency Funds, Investments, Reward Wallet, Finance Dashboard, Statistics")]
#[OA\Tag(name: "Search", description: "Search for users and resources")]
#[OA\Tag(name: "Statistics", description: "Dashboard statistics and graphs data")]
abstract class Controller
{
    //
}
