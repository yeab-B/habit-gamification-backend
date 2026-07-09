import { create } from "zustand";

// --- AUTH STORE TYPES & INITIALIZATION ---

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  created_at: string;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  setAuth: (user: User, token: string) => void;
  clearAuth: () => void;
  setLoading: (loading: boolean) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: localStorage.getItem("auth_token"),
  isAuthenticated: !!localStorage.getItem("auth_token"),
  isLoading: false,
  setAuth: (user, token) => {
    localStorage.setItem("auth_token", token);
    set({ user, token, isAuthenticated: true, isLoading: false });
  },
  clearAuth: () => {
    localStorage.removeItem("auth_token");
    set({ user: null, token: null, isAuthenticated: false, isLoading: false });
  },
  setLoading: (loading) => set({ isLoading: loading }),
}));


// --- APPLICATION STORE TYPES & INITIALIZATION ---

interface ApplicationState {
  sidebarOpen: boolean;
  theme: "light" | "dark";
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
  setTheme: (theme: "light" | "dark") => void;
}

export const useApplicationStore = create<ApplicationState>((set) => ({
  sidebarOpen: true,
  theme: (localStorage.getItem("theme") as "light" | "dark") || "light",
  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
  setSidebarOpen: (open) => set({ sidebarOpen: open }),
  setTheme: (theme) => {
    localStorage.setItem("theme", theme);
    if (typeof document !== "undefined") {
      const root = window.document.documentElement;
      if (theme === "dark") {
        root.classList.add("dark");
      } else {
        root.classList.remove("dark");
      }
    }
    set({ theme });
  },
}));
