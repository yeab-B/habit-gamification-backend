import { RouteObject, Navigate } from "react-router-dom";
import MainLayout from "@/components/layout/MainLayout";
import ProtectedRoute from "@/components/layout/ProtectedRoute";
import { lazy } from "react";

// Public (Auth) Pages
const LoginPage = lazy(() => import("@/features/auth/components/LoginPage"));
const RegisterPage = lazy(() => import("@/features/auth/components/RegisterPage"));
const ForgotPasswordPage = lazy(() => import("@/features/auth/components/ForgotPasswordPage"));

// Protected Dashboard Pages
const DashboardPage = lazy(() => import("@/features/dashboard/components/DashboardPage"));
const HabitsPage = lazy(() => import("@/features/habits/components/HabitsPage"));
const ChallengesPage = lazy(() => import("@/features/challenges/components/ChallengesPage"));
const FriendsPage = lazy(() => import("@/features/friends/components/FriendsPage"));
const FinancePage = lazy(() => import("@/features/finance/components/FinancePage"));
const ProfilePage = lazy(() => import("@/features/profile/components/ProfilePage"));
const SettingsPage = lazy(() => import("@/features/settings/components/SettingsPage"));

export const routes: RouteObject[] = [
  // Redirect root path to dashboard
  {
    path: "/",
    element: <Navigate to="/dashboard" replace />,
  },
  
  // Public Route Configurations
  {
    path: "/",
    children: [
      { path: "login", element: <LoginPage /> },
      { path: "register", element: <RegisterPage /> },
      { path: "forgot-password", element: <ForgotPasswordPage /> },
    ],
  },

  // Protected Route Configurations (wrapped with ProtectedRoute and MainLayout)
  {
    path: "/",
    element: <ProtectedRoute />,
    children: [
      {
        element: <MainLayout />,
        children: [
          { path: "dashboard", element: <DashboardPage /> },
          { path: "habits", element: <HabitsPage /> },
          { path: "challenges", element: <ChallengesPage /> },
          { path: "friends", element: <FriendsPage /> },
          { path: "finance", element: <FinancePage /> },
          { path: "profile", element: <ProfilePage /> },
          { path: "settings", element: <SettingsPage /> },
        ],
      },
    ],
  },

  // Catch-all Redirect to root
  {
    path: "*",
    element: <Navigate to="/" replace />,
  },
];

export default routes;
