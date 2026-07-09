import React from "react";
import { useLocation } from "react-router-dom";
import { useApplicationStore, useAuthStore } from "@/app/store";
import { Sun, Moon, Bell, User as UserIcon } from "lucide-react";

export const Navbar: React.FC = () => {
  const location = useLocation();
  const { theme, setTheme } = useApplicationStore();
  const { user } = useAuthStore();

  // Dynamically generate current page title from current URL path
  const getPageTitle = () => {
    const path = location.pathname.substring(1);
    if (!path) return "Dashboard";
    
    return path
      .split("/")
      .filter(Boolean)[0] // Take first path segment
      .split("-")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ");
  };

  const toggleTheme = () => {
    setTheme(theme === "dark" ? "light" : "dark");
  };

  return (
    <header className="flex h-16 w-full items-center justify-between border-b border-border bg-card px-6 text-card-foreground" id="navbar">
      {/* Dynamic Title */}
      <h2 className="text-xl font-semibold tracking-tight text-foreground">
        {getPageTitle()}
      </h2>

      {/* Right Controls */}
      <div className="flex items-center space-x-4">
        {/* Theme Toggle Button */}
        <button
          onClick={toggleTheme}
          className="rounded-lg p-2 hover:bg-accent text-muted-foreground hover:text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
          aria-label="Toggle Theme"
        >
          {theme === "dark" ? (
            <Sun className="h-5 w-5" />
          ) : (
            <Moon className="h-5 w-5" />
          )}
        </button>

        {/* Notifications Icon */}
        <button 
          className="rounded-lg p-2 hover:bg-accent text-muted-foreground hover:text-foreground transition-colors relative focus:outline-none focus:ring-2 focus:ring-ring"
          aria-label="Notifications"
        >
          <Bell className="h-5 w-5" />
          <span className="absolute top-1 right-1 h-2.5 w-2.5 rounded-full bg-primary border-2 border-card" />
        </button>

        <div className="h-8 w-px bg-border" />

        {/* User Info & Avatar */}
        <div className="flex items-center space-x-3">
          <div className="flex flex-col text-right hidden sm:flex">
            <span className="text-sm font-medium text-foreground">
              {user?.name || "Explorer"}
            </span>
            <span className="text-xs text-muted-foreground">
              Level 1 Rank
            </span>
          </div>
          <div className="h-9 w-9 rounded-full bg-accent flex items-center justify-center border border-border overflow-hidden">
            {user?.avatar ? (
              <img
                src={user.avatar}
                alt={user.name}
                className="h-full w-full object-cover"
              />
            ) : (
              <UserIcon className="h-5 w-5 text-muted-foreground" />
            )}
          </div>
        </div>
      </div>
    </header>
  );
};

export default Navbar;
