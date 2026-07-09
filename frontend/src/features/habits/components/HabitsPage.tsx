import React from "react";

export const HabitsPage: React.FC = () => {
  return (
    <div className="space-y-6" id="habits-page">
      <div className="flex flex-col gap-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Habits</h1>
        <p className="text-muted-foreground">Track and manage your daily habits to earn XP and level up.</p>
      </div>
      <div className="rounded-xl border border-border bg-card p-6 shadow-sm">
        <div className="h-60 rounded-lg border border-dashed border-border flex items-center justify-center text-sm text-muted-foreground">
          Habits List and Tracking Grid Placeholder
        </div>
      </div>
    </div>
  );
};

export default HabitsPage;
