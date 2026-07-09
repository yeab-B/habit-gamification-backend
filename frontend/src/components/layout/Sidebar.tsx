import React from "react";
import { NavLink } from "react-router-dom";
import { useApplicationStore, useAuthStore } from "@/app/store";
import { 
  LayoutDashboard, 
  CheckSquare, 
  Trophy, 
  Users, 
  Wallet, 
  User, 
  Settings, 
  LogOut, 
  Menu,
  ChevronLeft,
  Sparkles
} from "lucide-react";
import { cn } from "@/utils";

export const Sidebar: React.FC = () => {
  const { sidebarOpen, toggleSidebar } = useApplicationStore();
  const { clearAuth } = useAuthStore();

  const navItems = [
    { name: "Dashboard", path: "/dashboard", icon: LayoutDashboard },
    { name: "Habits", path: "/habits", icon: CheckSquare },
    { name: "Challenges", path: "/challenges", icon: Trophy },
    { name: "Friends", path: "/friends", icon: Users },
    { name: "Personal Finance", path: "/finance", icon: Wallet },
    { name: "Profile", path: "/profile", icon: User },
    { name: "Settings", path: "/settings", icon: Settings },
  ];

  return (
    <aside
      className={cn(
        "flex flex-col h-screen border-r border-border bg-card text-card-foreground transition-all duration-300 ease-in-out z-30",
        sidebarOpen ? "w-64" : "w-20"
      )}
      id="sidebar"
    >
      {/* Brand Header */}
      <div className="flex items-center justify-between p-4 border-b border-border h-16">
        <div className="flex items-center space-x-3 overflow-hidden">
          <Sparkles className="h-6 w-6 text-primary flex-shrink-0 animate-pulse" />
          {sidebarOpen && (
            <span className="font-bold text-lg tracking-tight whitespace-nowrap">
              DayChallenge
            </span>
          )}
        </div>
        <button
          onClick={toggleSidebar}
          className="p-1.5 rounded-lg border border-border bg-background hover:bg-accent text-accent-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
          aria-label="Toggle Sidebar"
        >
          {sidebarOpen ? (
            <ChevronLeft className="h-4 w-4" />
          ) : (
            <Menu className="h-4 w-4" />
          )}
        </button>
      </div>

      {/* Navigation Links */}
      <nav className="flex-1 space-y-1 p-3 overflow-y-auto">
        {navItems.map((item) => {
          const Icon = item.icon;
          return (
            <NavLink
              key={item.path}
              to={item.path}
              className={({ isActive }) =>
                cn(
                  "flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all duration-150",
                  isActive
                    ? "bg-primary text-primary-foreground shadow-sm"
                    : "hover:bg-accent hover:text-accent-foreground text-muted-foreground"
                )
              }
            >
              <Icon className="h-5 w-5 flex-shrink-0" />
              {sidebarOpen && <span className="truncate">{item.name}</span>}
            </NavLink>
          );
        })}
      </nav>

      {/* Footer / Actions */}
      <div className="p-3 border-t border-border">
        <button
          onClick={clearAuth}
          className={cn(
            "flex items-center space-x-3 w-full px-3 py-2.5 rounded-md text-sm font-medium text-destructive hover:bg-destructive/10 transition-colors focus:outline-none focus:ring-2 focus:ring-destructive"
          )}
        >
          <LogOut className="h-5 w-5 flex-shrink-0" />
          {sidebarOpen && <span className="truncate">Logout</span>}
        </button>
      </div>
    </aside>
  );
};

export default Sidebar;
