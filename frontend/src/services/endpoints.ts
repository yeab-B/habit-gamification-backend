export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: "/auth/login",
    REGISTER: "/auth/register",
    LOGOUT: "/auth/logout",
    USER: "/auth/user",
    FORGOT_PASSWORD: "/auth/forgot-password",
  },
  HABITS: {
    BASE: "/habits",
    DETAIL: (id: string | number) => `/habits/${id}`,
    LOG: (id: string | number) => `/habits/${id}/log`,
  },
  CHALLENGES: {
    BASE: "/challenges",
    DETAIL: (id: string | number) => `/challenges/${id}`,
    JOIN: (id: string | number) => `/challenges/${id}/join`,
  },
  FINANCE: {
    TRANSACTIONS: "/finance/transactions",
    BUDGETS: "/finance/budgets",
    SUMMARY: "/finance/summary",
  },
  FRIENDS: {
    BASE: "/friends",
    REQUESTS: "/friends/requests",
  },
  ACHIEVEMENTS: {
    BASE: "/achievements",
  },
  PROFILE: {
    BASE: "/profile",
  },
};
