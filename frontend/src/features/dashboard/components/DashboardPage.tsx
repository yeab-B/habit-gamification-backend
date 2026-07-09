import React from "react";

export const DashboardPage: React.FC = () => {
  return (
    <div className="space-y-6" id="dashboard-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Dashboard</h1>
        <p className="text-muted-foreground">Welcome to your Personal Growth & Gamification Dashboard.</p>
      </div>
      <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
          <h3 className="font-semibold text-lg">Habits Progress</h3>
          <div className="mt-4 h-24 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
            Habit Chart placeholder
          </div>
        </div>
        <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
          <h3 className="font-semibold text-lg">Active Challenges</h3>
          <div className="mt-4 h-24 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
            Active Challenges list placeholder
          </div>
        </div>
        <div className="rounded-xl border border-border bg-card p-6 shadow-sm sm:col-span-2 lg:col-span-1">
          <h3 className="font-semibold text-lg">Finance Summary</h3>
          <div className="mt-4 h-24 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
            Finance Chart placeholder
          </div>
        </div>
      </div>
    </div>
  );
};

export default DashboardPage;
